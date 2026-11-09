<?php

namespace App\Http\Controllers\Admin\InviteReviewer;

use App\Exports\PendingReviewersExport;
use App\Imports\ReviewerImport;
use App\Mail\ReviewerInvitation;
use App\Models\Admin;
use App\Models\InviteReviewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InviteReviewerRepository
{
    private const MAX_INVITATIONS = 3;

    // ── Existing ──────────────────────────────────────────────────────────────

    public function index($request)
    {
        $perPage = $request->perPage ?? 50;

        $records = InviteReviewer::whereNotIn('email', function ($query) {
            $query->select('email')->from('admins')->whereNotNull('email');
        });

        $this->search($request, $records);
        $this->sort($request, $records);

        $records = $records->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.inviteReviewer.datatable', compact('records'))->render();
                return response()->json([
                    'status'         => '200',
                    'msg'            => 'Data loaded',
                    'data'           => $datatable,
                    'paginationInfo' => getPaginationInfo($records),
                ], 200);
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }

        return view('admin.inviteReviewer.index');
    }

    public function store($request)
    {
        if ($request->isMethod('GET')) {
            // Pending reviewers removed from here as per your rules.
            return view('admin.inviteReviewer.create');
        }

        if ($request->isMethod('POST')) {
            $request->validate(['email' => 'required|email|unique:invite_reviewers,email']);

            if (Admin::where('email', $request->email)->exists()) {
                return errorResponse('This reviewer is already registered on the portal.');
            }

            $inviteReviewer = InviteReviewer::create([
                'email'            => $request->email,
                'invitation_count' => 1,
                'last_invited_at'  => now(),
            ]);

            Mail::to($inviteReviewer->email)->send(
                new ReviewerInvitation($inviteReviewer, route('admin.userCheck'))
            );

            return successResponse('Invitation sent successfully!', route('admin.inviteReviewer.index'));
        }

        return errorResponse('Invalid Request');
    }

    public function resend($request, $id)
    {
        $inviteReviewer = InviteReviewer::findOrFail($id);

        if (Admin::where('email', $inviteReviewer->email)->exists()) {
            return redirect()->back()->with('error', 'Cannot resend: This reviewer has already registered.');
        }

        if ($inviteReviewer->invitation_count >= self::MAX_INVITATIONS) {
            return redirect()->back()->with('error', 'Maximum invitation limit (3) reached for this reviewer.');
        }

        Mail::to($inviteReviewer->email)->send(
            new ReviewerInvitation($inviteReviewer, route('admin.userCheck'))
        );

        $inviteReviewer->increment('invitation_count');
        $inviteReviewer->update(['last_invited_at' => now()]);

        return successResponse('Invitation resent successfully!', route('admin.inviteReviewer.index'));
    }

    public function destroy($request, $id)
    {
        InviteReviewer::findOrFail($id)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => '200',
                'success' => true,
                'msg'     => 'Removed successfully.',
            ]);
        }

        return successResponse('Invitation deleted successfully!', route('admin.inviteReviewer.index'));
    }

    // ── New ───────────────────────────────────────────────────────────────────

    public function sendBulk($request)
    {
        $request->validate([
            'emails'   => 'required|array|min:1',
            'emails.*' => 'required|email',
        ]);

        $registeredEmails = Admin::pluck('email')
            ->map(fn($e) => strtolower($e))
            ->toArray();

        $alreadyInvited = InviteReviewer::pluck('email')
            ->map(fn($e) => strtolower($e))
            ->toArray();

        $registrationUrl = route('admin.userCheck');
        $sent    = 0;
        $skipped = 0;

        foreach ($request->emails as $email) {
            $email = strtolower(trim($email));

            if (in_array($email, $registeredEmails) || in_array($email, $alreadyInvited)) {
                $skipped++;
                continue;
            }

            $invite = InviteReviewer::create([
                'email'            => $email,
                'invitation_count' => 1,
                'last_invited_at'  => now(),
            ]);

            Mail::to($email)->send(new ReviewerInvitation($invite, $registrationUrl));
            $sent++;
        }

        return response()->json([
            'success' => true,
            'sent'    => $sent,
            'skipped' => $skipped,
            'message' => "{$sent} invitation(s) sent successfully."
                . ($skipped > 0 ? " {$skipped} skipped (already registered or invited)." : ''),
        ]);
    }

    public function import($request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        // Store temporarily so PhpSpreadsheet can read from disk
        $path     = $request->file('file')->store('temp');
        $fullPath = storage_path('app/' . $path);

        $importer = new ReviewerImport();
        $emails   = $importer->import($fullPath);

        // Clean up temp file immediately
        Storage::delete($path);

        if ($emails->isEmpty()) {
            return redirect()->back()->with(
                'error',
                'No emails found in the uploaded file. Make sure the column header is exactly "email".'
            );
        }

        $registeredEmails = Admin::pluck('email')
            ->map(fn($e) => strtolower($e))
            ->toArray();

        $alreadyInvited = InviteReviewer::pluck('email')
            ->map(fn($e) => strtolower($e))
            ->toArray();

        $newEmails = $emails
            ->reject(fn($e) => in_array($e, $registeredEmails))
            ->reject(fn($e) => in_array($e, $alreadyInvited));

        if ($newEmails->isEmpty()) {
            return redirect()->back()->with(
                'error',
                'All emails in the file are either already registered or already invited.'
            );
        }

        $registrationUrl = route('admin.userCheck');
        $imported        = 0;

        foreach ($newEmails as $email) {
            $invite = InviteReviewer::create([
                'email'            => $email,
                'invitation_count' => 1,
                'last_invited_at'  => now(),
            ]);

            Mail::to($email)->send(new ReviewerInvitation($invite, $registrationUrl));
            $imported++;
        }

        return redirect()
            ->route('admin.inviteReviewer.index')
            ->with('success', "{$imported} reviewer(s) imported and invited successfully.");
    }

    public function bulkInvite($request)
    {
        $registeredEmails = Admin::pluck('email')
            ->map(fn($e) => strtolower($e))
            ->toArray();

        // Only send to those not yet registered and under the 3-invite cap
        $pending = InviteReviewer::whereNotIn('email', $registeredEmails)
            ->where('invitation_count', '<', self::MAX_INVITATIONS)
            ->get();

        if ($pending->isEmpty()) {
            return redirect()->back()->with(
                'error',
                'No pending reviewers eligible for a reminder — all are either registered or have already received 3 invitations.'
            );
        }

        $registrationUrl = route('admin.userCheck');
        $sent            = 0;

        foreach ($pending as $inviteReviewer) {
            Mail::to($inviteReviewer->email)->send(
                new ReviewerInvitation($inviteReviewer, $registrationUrl)
            );

            $inviteReviewer->increment('invitation_count');
            $inviteReviewer->update(['last_invited_at' => now()]);
            $sent++;
        }

        return redirect()
            ->route('admin.inviteReviewer.index')
            ->with('success', "Reminder sent to {$sent} pending reviewer(s).");
    }

    public function export()
    {
        $exporter = new PendingReviewersExport();

        return $exporter->download(
            'pending-reviewers-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function search($request, $query)
    {
        if ($request->filled('search')) {
            $query->where('email', 'like', "%{$request->search}%");
        }
    }

    private function sort($request, $query)
    {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, $request->sortDirection);
        } else {
            $query->latest('id');
        }
    }
}
