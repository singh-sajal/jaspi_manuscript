<?php

namespace App\Http\Controllers\Author\Auth;



use App\Mail\TestMail;
use App\Models\Author;
use App\Models\EntityPlan;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PasswordResetLinkNotification;
use App\Notifications\UserPasswordResetLink;
use App\Notifications\VerificationNotification;
use App\Repositories\AuthRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Ramsey\Uuid\Uuid;
use Throwable;
use App\Mail\OtpMail;

class AuthorAuthRepository extends AuthRepository
{

    // All the necessary setup to integrate the authentication via the AuthRepository

    protected $model = Author::class;
    protected $guard = 'web';
    protected $_2FaCallbackUrl = 'author.2fa.login';
    protected $loginSuccessRedirectUrl = 'author.dashboard';
    protected $loginUrl = 'author.login';
    protected $_2FaView = 'author.auth.2fa';
    public function register($request)
    {
        $session_data = session('otp_data');
        // return $session_data;
        if ($request->isMethod('GET')) {
            return $this->registerPage();
        }

        if ($request->isMethod('POST')) {
            return $this->registerAuthor($request);
        }
        abort(403, 'Method Not Allowed');
    }

    public function userCheck($request)
    {

        if ($request->isMethod('GET')) {
            return view('author.auth.userCheck');
        }

        if ($request->isMethod('POST')) {

            try {
                $email = $request->email;
                $phone = $request->phone;
                // return $email;
                $author = Author::where('email', $email)
                    ->orWhere('phone', $phone)
                    ->first();

                if ($author) {
                    if ($author->email == $email && $author->phone == $phone) {
                        return errorResponse('Account with email and phone number already exist');
                    }
                    if ($author->email == $email) {
                        return errorResponse('Account with email already exist');
                    }
                    return errorResponse('Account with phone number already exist');
                } else {
                    $otp = mt_rand(100000, 999999);
                    $data = new \stdClass();
                    $data->otp = $otp;
                    $data->phone = $request->phone;
                    $data->email = $request->email;
                    $data->otp_verify_attempt = 0;
                    $data->is_otp_verify = 0;
                    $data->created_at = now();

                    session(['otp_data' => $data]);

                    // Mail::to($email)->send(new TestMail());
                    // return successResponse('Email sent successfully');
                    try {
                        Mail::to($request->email)->send(new OtpMail($otp));
                        // return response()->json(['message' => 'OTP sent successfully!']);
                    } catch (\Exception $e) {
                        // log error or return failure response
                        Log::error('Mail sending failed: ' . $e->getMessage());
                        return response()->json(['message' => 'Failed to send mail', 'error' => $e->getMessage()], 500);
                    }


                    return response()->json([
                        'status' => '200',
                        'msg' => 'OTP Send to mail Successfully',
                        'redirect' => route('author.verifyOtp')
                    ], 200);
                }
            } catch (\Exception $e) {
                return 'Error: ' . $e->getMessage();
            }

            // return $this->registerAuthor($request);
        }
        abort(403, 'Method Not Allowed');
    }

    public function verifyOtp($request)
    {

        $otpData = session('otp_data');
        //  return $otpData;
        if ($request->isMethod('GET')) {
            $email = $otpData->email;
            $phone = $otpData->phone;
            if ($otpData) {
                return view('author.auth.email-otp-verify', compact('email', 'phone'));
            }
            return view('author.auth.userCheck');
        }

        if ($request->isMethod('POST')) {
            if ($request->otp == $otpData->otp && $request->email == $otpData->email && $request->phone == $otpData->phone) {
                $otpData->is_otp_verify = 1;
                $otpData->updated_at = now();
                session(['otp_data' => $otpData]);

                return successResponse('OTP verified successfully', $redirectUrl = route('author.registration'));
            }

            return errorResponse('Invalid OTP retry again');
        }
        abort(403, 'Method Not Allowed');
    }
    private function registerPage()
    {
        $otpData = session('otp_data');
        $email = $otpData->email;
        $phone = $otpData->phone;

        // $states = State::indian()->get();
        if ($otpData->is_otp_verify == 1) {
            return view('author.auth.author-signup', compact('email', 'phone'));
        }
        return view('author.auth.userCheck');
    }
    public function registrationDone($request)
    {
        return view('author.auth.registrationDone');
    }



    private function registerAuthor($request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:authors',
                    'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:authors',
                    'dob' => 'required|date|before:today',
                    'password' => 'required|min:6',
                    'terms' => 'required',
                    'gender' => 'required|in:Male,Female,Other',
                ],
                [
                    'password.min' => 'Password must be at least 6 characters',
                    'password.required' => 'Password is required',
                ]
            );

            if ($validator->fails()) {
                return failedValidation($validator);
            }
            $userData = [
                'username' => generateUniqueId(Author::class),
                'name' => $request->name,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'gender' => $request->gender,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'terms_accepted' => $request->terms,
                'status' => '1', // Author is immediately active
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $author = Author::create($userData);

            if ($author) {
                session()->forget('otp_data');
                // Log in the author immediately using the 'author' guard
                Auth::guard('web')->login($author);

                return response()->json([
                    'status' => '200',
                    'msg' => 'Registered  successfully.',
                    'redirect' => route('author.dashboard'),
                ], 200);
            }

            return response()->json([
                'status' => '500',
                'msg' => 'Something went wrong during registration.',
            ], 500);
        } catch (Throwable $th) {
            return response()->json([
                'status' => '500',
                'msg' => 'An exception occurred.',
                'exception' => $th->getMessage()
            ], 500);
        }
    }


    // Processing successfull part
    public function completeRegistration($request)
    {

        $validator = Validator::make(
            $request->all(),
            ['verification_code' => 'required|max:6',],
            ['verification_code.required' => 'Please enter verification code',]
        );
        if ($validator->fails()) {

            return response()->json(['status' => "400", 'errors' => $validator->errors()]);
        }
        try {
            $enteredCode = $request->verification_code;
            $verificationCode = session()->get('verification_code');
            if ($enteredCode == $verificationCode) {
                $organizationData = session()->get('organization');
                $subscriptionPlanId = session()->get('subscriptionPlanId');
                $userData = session()->get('user');
                $organizationId = DB::table('organizations')->insertGetId($organizationData);
                // Applying the subscription plan to user for 7 days
                $endDate = now()->addDays(7);
                EntityPlan::updateOrCreate(
                    [
                        'organization_id' => $organizationId, // Matching criteria
                        'is_active' => true, // Only one active plan at a time
                    ],
                    [
                        'plan_id' => $subscriptionPlanId,
                        'start_date' => now(),
                        'end_date' => $endDate,
                        'is_active' => true, // Ensure this is the new active plan
                    ]
                );

                // Attaching the super admin to the organization
                $userData['organization_id'] = $organizationId;
                $userCreated = User::create($userData);
                if ($userCreated) {
                    $userCreated->assignRole('superadmin');
                    session()->forget(['verification_code', 'organization', 'user']);
                    return response()->json([
                        'status' => '200',
                        'msg' => 'Account Verified Sucessfully',
                        'redirect' => route('admin.login')
                    ]);
                }
                return response()->json(['status' => '500', 'msg' => 'Something Went Wrong']);
            }
            return response()->json(['status' => '500', 'msg' => "Invalid Verification code"]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => '500',
                'msg' => "An exception occured",
                'exception' => $th->getMessage()
            ]);
        }
    }


    // ----------------------Authentication Logic----------------------
    public function login($request)
    {
        if ($request->isMethod('POST')) {
            return $this->processLoginRequest($request);
        }
        // return view('author.auth.login-with-password');
        return redirect(route('home'));
    }


    // ----------------Forget Passwor manager---------------
    public function sendPasswordResetLink($request)
    {
        if ($request->isMethod('get')) {
            return view('author.auth.forget-password.send-reset-link');
        }
        if ($request->isMethod('post')) {

            try {
                $author = Author::where('email', $request->email)->first();
                DB::table('password_resets')->where('email', $request->email)->delete();
                $secureToken = Str::random(60);
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $secureToken,
                    'tokenable_type' => 'author',
                    'created_at' => Carbon::now(),
                ]);
                if ($this->sendResetEmail($author, $secureToken)) {
                    return successResponse('A reset link has been sent to your email address.');
                }
                return errorResponse('Something went wrong');
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }
        abort(405, 'Method Not allowed');
    }

    public function resetPassword($request)
    {
        if ($request->isMethod('get')) {
            $token = $request->token;
            if (!$token) {
                abort(403);
            }
            $isValidToken = DB::table('password_resets')->where(['token' => $token, 'tokenable_type' => 'author'])->first();
            if (!$isValidToken) {
                abort(403, 'Link Expired');
            }
            return view('author.auth.forget-password.reset-password', compact('token'));
        }
        if ($request->isMethod('post')) {

            try {
                $password = $request->password;
                $isValidToken = DB::table('password_resets')->where([
                    'token' => $request->token,
                    'tokenable_type' => 'author'
                ])->first();
                if (!$isValidToken) {
                    return errorResponse('Link Expired Try generating new one');
                }
                $author = Author::where('email', $isValidToken->email)->first();
                if (!$author) {
                    return errorResponse('No Associated user found');
                }
                $author->password = Hash::make($password);
                $author->update();
                DB::table('password_resets')->where(['email' => $author->email, 'tokenable_type' => 'author'])->delete();
                return successResponse('Password has been reset successfully', route('home'));
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        abort(405, 'Method now allowed');
    }

    private function sendResetEmail($user, $token)
    {
        try {
            $url = route('author.password.reset', ['token' => $token]);
            $user->notify(new PasswordResetLinkNotification($url, $token));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    // --------------------Student Profile Manager-----------
    public function getProfile($uuid = null)
    {
        $author = Author::uuid($uuid)->first();
        $states = getStateList();
        return view('author.profile.author-profile', compact('author', 'states'));
    }

    public function updateProfile($request, $uuid)
    {

        try {
            $author = Author::uuid($uuid)->first();
            $data = [
                'name' => $request->name,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'country' => $request->country ?? 'India',
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'address' => $request->address,

            ];
            $updated = $author->update($data);
            if ($updated) {
                return successResponse('Profile has been updated successfully', route('author.profile', ['uuid' => $author->uuid]));
            }
            return errorResponse('Something went wrong');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }


    // -----------Chnage Password ------------

    public function changePasswordPage()
    {
        return view('author.profile.change-password');
    }
    public function changePassword($request)
    {
        $url = route('author.logout');
        try {
            $author = authUser('web');

            if (Hash::check($request->old_password, $author->password)) {
                $newpw = Hash::make($request->password);
                $updated = $author->update(['password' => $newpw]);
                if ($updated) {
                    return successResponse('Password Changed Successfully', $url);
                }
                return errorResponse('Something went wrong');
            }
            return errorResponse('Old Password does not match our records');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    // ---------Update Avatar----------
    public function updateAvatar($request)
    {

        try {
            $author = authUser('web');
            $data = [];
            if ($request->hasFile('avatar')) {
                $oldAvatar = $author->avatar;
                $file = $request->file('avatar');
                $data['avatar'] = replaceFile($file, 'uploads/author/avatar/', $oldAvatar);
            }
            $updated = $author->update($data);
            if ($updated) {
                authUser('web')->refresh();
                return successResponse('Avatar has been updated successfully', route('author.profile', ['uuid' => $author->uuid]));
            }
            return errorResponse('Something went wrong');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }
}
