<?php

namespace App\Http\Controllers\Mail;

use Throwable;
use App\Models\Admin;
use App\Models\Author;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\EmailLog;
use App\Mail\AdminMessageMail;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailRepository
{

    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }


    public function index()
    {
        $layout = auth()->guard('admin')->check() ? 'admin.app' : 'author.app';
        $view = auth()->guard('admin')->check() ? 'emails.send-mail-form' : 'emails.send-mail-form-author';
        // $isAdmin = authUser('admin')->is_admin == 1 ? true : false;
        $isAdmin = auth()->guard('admin')->check() && auth()->guard('admin')->user()->is_admin == 1;

        // Staff (is_admin = 0) always writes to the Editor in Chief; surface
        // their name/email on the form so the destination is explicit.
        $chief = null;
        if (auth()->guard('admin')->check() && !$isAdmin) {
            $chief = $this->chiefAdmin();
        }

        $emailLogs = EmailLog::query()->with('sender', 'recipient')->latest('sent_at')->limit(20)->get();

        return view($view, compact('layout', 'isAdmin', 'emailLogs', 'chief'));
    }

    public function getUsersByRole($request)
    {
        $role = $request->role;


        if (!$role) {
            return response()->json(['error' => 'Role is required'], 400);
        }
        [$users, $type, $recipientType] = $this->usersForRole($role);
        if (!$users) {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        $counts = EmailLog::query()
            ->where('recipient_type', $recipientType)
            ->whereIn('recipient_id', $users->pluck('id'))
            ->selectRaw('recipient_id, COUNT(*) as total')
            ->groupBy('recipient_id')
            ->pluck('total', 'recipient_id');

        $payload = $users->map(function ($user) use ($type, $recipientType, $counts) {
            return [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'type'           => $type,
                'recipient_type' => $recipientType,
                'sent_count'     => (int) $counts->get($user->id, 0),
            ];
        })->values();

        return response()->json($payload);
    }

    private function usersForRole($role)
    {
        if ($role == 'author') {
            return [Author::where('status', 1)->get(), 'author', Author::class];
        }

        $admins = Admin::whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        })->get();

        if ($admins->isEmpty()) {
            return [null, null, null];
        }

        return [$admins, 'admin', Admin::class];
    }

    /**
     * The Editor in Chief / superadmin inbox. Staff messages are always
     * routed here.
     */
    private function chiefAdmin()
    {
        return Admin::whereHas('roles', function ($q) {
            $q->where('name', 'superadmin');
        })->first();
    }

    public function sendMail($request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $email = $request->input('email', config('mail.from.address'));

        // Determine sender
        if (auth('admin')->check()) {
            $admin = auth('admin')->user();
            if ($admin->is_admin) {
                $senderName  = config('mail.from.name');
                $senderEmail = config('mail.from.address');
            } else {
                $senderName  = $admin->name;
                $senderEmail = $admin->email;
            }
            $sender = $admin;
        } elseif (auth('web')->check()) {
            $author = auth('web')->user();
            $senderName  = $author->name;
            $senderEmail = $author->email;
            $sender = $author;
        } else {
            return errorResponse('Unauthenticated user.');
        }

        $isStaff         = auth('admin')->check() && auth('admin')->user()->is_admin == 0;
        $pickerAvailable = auth('admin')->check() && !$isStaff;

        // Staff only ever message the Editor in Chief (superadmin); the
        // recipient picker is reserved for is_admin == 1 users. Ignore any
        // crafted recipient fields posted by a staff account.
        $recipients = $isStaff ? [] : $this->resolveRecipients($request);

        // is_admin == 1 users always use the picker: require a selection.
        if ($pickerAvailable && empty($recipients)) {
            return errorResponse('Please select at least one recipient.');
        }

        if (empty($recipients)) {
            if ($isStaff) {
                $chief = $this->chiefAdmin();
                if (!$chief || empty($chief->email)) {
                    return errorResponse('No Editor in Chief recipient is configured.');
                }
                $recipients[] = ['model' => $chief, 'email' => $chief->email];
            } elseif ($email) {
                // Flows without the picker (author help & support): match the
                // target inbox to an Admin row if possible.
                $recipient = Admin::where('email', $email)->first();
                $recipients[] = ['model' => $recipient, 'email' => $email];
            }
        }

        if (empty($recipients)) {
            return errorResponse('No recipient to send the email to.');
        }

        $sent = 0;
        $failed = 0;
        $lastError = null;

        foreach ($recipients as $recipientRow) {
            $recipient = $recipientRow['model'];
            $toEmail   = $recipientRow['email'];

            $data = [
                'sender_name'  => $senderName,
                'sender_email' => $senderEmail,
                'to_email'     => $toEmail, // Passed to view
                'date'         => now()->format('F j, Y, g:i A'), // Passed to view
                'subject'      => $request->subject,
                'message'      => $request->message,
            ];

            try {
                Mail::to($toEmail)
                    ->cc('akanksha.itconsultant@gmail.com')  //uncomment it
                    ->bcc($senderEmail) // BCC added here
                    ->send(new AdminMessageMail($data));

                EmailLog::create([
                    'sender_type'    => $sender ? $sender::class : null,
                    'sender_id'      => $sender?->id,
                    'recipient_type' => $recipient ? $recipient::class : null,
                    'recipient_id'   => $recipient?->id,
                    'to_email'       => $toEmail,
                    'subject'        => $request->subject,
                    'message'        => $request->message,
                    'status'         => EmailLog::STATUS_SENT,
                    'sent_at'        => now(),
                ]);

                // Mirror the email in the recipient's in-app inbox when the
                // address belongs to a registered Admin or Author. Do not
                // notify the sender about their own message.
                $isSelf = $sender && $recipient
                    && $recipient::class === $sender::class
                    && $recipient->getKey() === $sender->getKey();

                if ($recipient && !$isSelf) {
                    $this->notifications->send($recipient, [
                        'category' => 'message',
                        'type' => 'message.received',
                        'title' => $request->subject,
                        'message' => $request->message,
                        'url' => $recipient instanceof Admin
                            ? route('admin.notifications.index')
                            : route('author.notifications.index'),
                        'icon' => 'ri-mail-line',
                        'severity' => 'info',
                        'meta' => [
                            'sender_name' => $senderName,
                            'sender_email' => $senderEmail,
                        ],
                    ]);
                }

                $sent++;
            } catch (\Exception $e) {
                EmailLog::create([
                    'sender_type'    => $sender ? $sender::class : null,
                    'sender_id'      => $sender?->id,
                    'recipient_type' => $recipient ? $recipient::class : null,
                    'recipient_id'   => $recipient?->id,
                    'to_email'       => $toEmail,
                    'subject'        => $request->subject,
                    'message'        => $request->message,
                    'status'         => EmailLog::STATUS_FAILED,
                    'error'          => $e->getMessage(),
                    'sent_at'        => now(),
                ]);
                $failed++;
                $lastError = $e->getMessage();
            }
        }

        if ($failed > 0 && $sent === 0) {
            return errorResponse('Failed to send email: ' . (string) $lastError);
        }

        $message = "Email sent successfully to {$sent} recipient(s)!";
        if ($failed > 0) {
            $message .= " {$failed} failed.";
        }

        return successResponse($message);
    }

    /**
     * Turn the posted recipients[] (recipient_type + recipient_id per entry)
     * into model rows. Also accepts the legacy singular recipient fields.
     *
     * @return array<int, array{model: \Illuminate\Database\Eloquent\Model|null, email: string}>
     */
    private function resolveRecipients($request)
    {
        $recipients = [];

        $raw = $request->input('recipients', []);
        if (is_array($raw)) {
            foreach ($raw as $entry) {
                $type = $entry['recipient_type'] ?? null;
                $id   = $entry['recipient_id'] ?? null;

                if (!$type || !$id) {
                    continue;
                }

                $model = $type::find($id);
                if ($model && $model->email) {
                    $recipients[] = ['model' => $model, 'email' => $model->email];
                }
            }
        }

        // Legacy singular selection (old single-recipient UI)
        if (empty($recipients) && $request->input('recipient_type')) {
            $model = $request->input('recipient_type')::find($request->input('recipient_id'));
            if ($model && $model->email) {
                $recipients[] = ['model' => $model, 'email' => $model->email];
            }
        }

        return $recipients;
    }
}