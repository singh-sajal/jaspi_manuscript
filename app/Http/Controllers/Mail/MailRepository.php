<?php

namespace App\Http\Controllers\Mail;

use Throwable;
use App\Models\Admin;
use App\Models\Author;
use App\Models\Service;
use App\Models\Checklist;
use App\Mail\AdminMessageMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailRepository
{


    public function index()
    {
        $layout = auth()->guard('admin')->check() ? 'admin.app' : 'author.app';
        $view = auth()->guard('admin')->check() ? 'emails.send-mail-form' : 'emails.send-mail-form-author';
        return view($view, compact('layout'));
    }

    public function getUsersByRole($request)
    {
        $role = $request->role;


        if (!$role) {
            return response()->json(['error' => 'Role is required'], 400);
        }
        if ($role == 'author') {
            $users = Author::where('status', 1)->get();
        } else {
            $users = Admin::where('is_admin', false)->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            })->get();
        }
        return response()->json($users);
    }

    public function sendMail($request)
    {
        $request->validate([
            // 'email' => 'required|email',    //receiver
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $email = $request->has('email') ? $request->email : 'editor@jaspi.saspi.in';
        $data = [
            'sender_name' => auth()->guard('admin')->check()
                ? auth()->guard('admin')->user()->name
                : auth()->user()->name,

            'sender_email' => auth()->guard('admin')->check()
                ? auth()->guard('admin')->user()->email
                : auth()->user()->email,

            // 'sender_email' => !empty(authUser('admin')->email) ? authUser('admin')->email : authUser()->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];


        // return [$data,$email];

        try {
            Mail::to($email)->send(new AdminMessageMail($data));
            return successResponse('Email send successfully!');
            // return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return errorResponse('Email not sent!');
        }
    }
}
