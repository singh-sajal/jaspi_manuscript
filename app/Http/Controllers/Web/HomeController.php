<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Admin\Auth\AdminAuthRepository;
use App\Http\Controllers\Author\Auth\AuthorAuthRepository;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Author;
use App\Models\Webquery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Throwable;

class HomeController extends Controller
{

    public function contact(Request $request)
    {
        return view('contact');
    }

    public function index(Request $request)
    {
        return view('home');
    }

    public function contactSave(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:250|min:3',
                'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'phone' => 'required|max:10|min:10',
                'subject' => 'required|string|max:250|min:3',
                'description' => 'required|string|max:250|min:3',

            ]);
            if ($validator->fails())
                return response()->json(['status' => '400', 'errors' => $validator->errors()]);
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'query_id' => uniqid(),
                'subject' => $request->subject,
                'description' => $request->description
            ];
            $created = Webquery::create($data);
            if ($created) {
                // $url = route('admin.customer.show', $created->id);

                // return successResponse('Query Send Successfully');
                return response()->json(['status' => '200', 'msg' => 'Query Send Successfully']);
            }
            // return errorResponse('Something went wrong');
            return response()->json(['status' => '500', 'msg' => 'Something went wrong']);
        } catch (Throwable $th) {
            Log::emergency('Exception occurred: ' . $th->getMessage());
            return response()->json(['status' => '500', 'msg' => $th->getMessage()]);
        }
        abort(405, 'method not allowed');
    }



    public function forgot_password(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('forgot_pw');
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'role' => 'required|in:author,admin',
                'username' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = $request->role;
            $email = $request->username;

            if ($role == 'author') {
                $repository = new AuthorAuthRepository();
                $newRequest = $request->merge([
                    'email' => $email
                ]);


                return $repository->sendPasswordResetLink($newRequest);
                // $user_info = Author::where('email', $email)->first();
            } elseif ($role == 'admin') {
                $repository = new AdminAuthRepository();
                $newRequest = $request->merge([
                    'email' => $email
                ]);


                return $repository->sendPasswordResetLink($newRequest);
            } else {
                return errorResponse('Invalid role');
            }

            if ($user_info) {
                // Password reset logic goes here, e.g. sending email
                return successResponse('Password Reset Link has been sent to your email');
            }

            return errorResponse('User not found');
        }

        abort(405, 'method not allowed');
    }
}
