<?php

namespace App\Http\Controllers\Admin\Auth;

use Throwable;
use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Mail\OtpMail;
use App\Models\Admin;
use App\Models\Integration;
use App\Models\State;
use Ramsey\Uuid\Uuid;
use App\Models\Author;
use App\Classes\Logger;
use App\Models\EntityPlan;
use Illuminate\Support\Str;
use App\Traits\HasIpBlocker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Notifications\UserPasswordResetLink;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationNotification;
use App\Notifications\PasswordResetLinkNotification;

class AdminAuthRepository extends AuthRepository
{

    // All the necessary setup to integrate the authentication via the AuthRepository

    protected $model = Admin::class;
    protected $guard = 'admin';
    protected $_2FaCallbackUrl = 'admin.2fa.login';
    protected $loginSuccessRedirectUrl = 'admin.dashboard';
    protected $loginUrl = 'admin.login';
    protected $_2FaView = 'admin.auth.2fa';

    public function register($request)
    {
        $session_data = session('otp_data');
        if ($request->isMethod('GET')) {
            return $this->registerPage();
        }

        if ($request->isMethod('POST')) {
            // return $request;
            return $this->registerReviewer($request);
        }
        abort(403, 'Method Not Allowed');
    }

    public function userCheck($request)
    {

        if ($request->isMethod('GET')) {
            return view('admin.auth.userCheck');
        }

        if ($request->isMethod('POST')) {

            try {
                $email = $request->email;
                $phone = $request->phone;
                // return $email;
                $admin = Admin::where('email', $email)
                    ->orWhere('phone', $phone)
                    ->first();

                if ($admin) {
                    if ($admin->email == $email && $admin->phone == $phone) {
                        return errorResponse('Account with email and phone number already exist');
                    }
                    if ($admin->email == $email) {
                        return errorResponse('Account with email already exist');
                    }
                    return errorResponse('Account with phone number already exist');
                } else {
                    $otp = mt_rand(100000, 999999);
                    // $otp= 999999;
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
                        'redirect' => route('admin.verifyOtp')
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
                return view('admin.auth.email-otp-verify', compact('email', 'phone'));
            }
            return view('admin.auth.userCheck');
        }

        if ($request->isMethod('POST')) {
            if ($request->otp == $otpData->otp && $request->email == $otpData->email && $request->phone == $otpData->phone) {
                $otpData->is_otp_verify = 1;
                $otpData->updated_at = now();
                session(['otp_data' => $otpData]);

                return successResponse('OTP verified successfully', $redirectUrl = route('admin.registration'));
            }

            return errorResponse('Invalid OTP retry again');
        }
        abort(403, 'Method Not Allowed');
    }

    private function registerPage()
    {
        // $states = State::indian()->get();
        // $plans = Plan::active()->demo()->get();
        // return view('authentication.signup', compact('states', 'plans'));
        $otpData = session('otp_data');
        $email = $otpData->email;
        $phone = $otpData->phone;

        $role = Role::where('name', 'Reviewer')->first();
        $integrations = Integration::orderBy('name')->get();

        // return $role;


        // $states = State::indian()->get();
        if ($otpData->is_otp_verify == 1) {
            return view('admin.auth.reviewer-signup', compact('email', 'phone', 'role', 'integrations'));
        }
        return view('admin.auth.userCheck');
    }

    private function registerReviewer($request)
    {
        // try {


        //     $subscriptionPlanId = $request->plan_id;
        //     $organizationData = [
        //         'uuid' => (string) Uuid::uuid4(),
        //         'name' => $request->name,
        //         'contact_person' => $request->contactperson,
        //         'email' => $request->email,
        //         'phone' => $request->phone,
        //         'address' => $request->address,
        //         'pan_number' => $request->pan,
        //         'gstin' => $request->gstin,
        //         'state_id' => $request->state,
        //         'status' => 1,

        //     ];
        //     $userData = [
        //         'name' => 'Super Admin',
        //         'username' => generateUniqueId(User::class, 'ATR'),
        //         'phone' => $request->phone,
        //         'email' => $request->loginemail,
        //         'password' => Hash::make($request->password),
        //         'status' => 1,
        //         'is_admin' => '1',
        //     ];
        //     // Storing Data Until the verification is completed;
        //     session()->put('subscriptionPlanId', $subscriptionPlanId);
        //     session()->put('organization', $organizationData);
        //     session()->put('user', $userData);

        //     $verificationCode = $this->generateVerificationCode();
        //     Notification::route('mail', $request->loginemail)
        //         ->notify(new VerificationNotification($verificationCode));
        //     $verificationScreen = view('authentication.verify-account', ['email' => $request->loginemail])->render();
        //     return response()->json([
        //         'status' => "200",
        //         'msg' => 'Account Registered successfully.',
        //         'jsFunction' => 'handleVerification',
        //         'parameters' => [$verificationScreen]
        //     ]);
        // } catch (Throwable $th) {
        //     return response()->json([
        //         'status' => '500',
        //         'msg' => 'An exception occured',
        //         'exception' => $th->getMessage()
        //     ], 500);
        // }
        try {
            $hasIntegrations = Integration::query()->exists();

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:admins',
                    'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:admins',
                    'dob' => 'required|date|before:today',
                    'password' => 'required|min:6',
                    'terms' => 'required',
                    'gender' => 'required|in:Male,Female,Other',
                    'saspi_member' => 'required|in:Yes,No',
                    'membership_id' => 'nullable|required_unless:saspi_member,No|string|max:255',
                    'designation' => 'nullable|string|max:255',
                    'affiliation' => 'nullable|string|max:255',
                    'research_experience_years' => 'required|numeric|min:0|max:100',
                    'avg_articles_published' => 'required|numeric|min:0',
                    'h_index' => 'nullable|string|alpha_num',
                    'remarks' => 'nullable|string',
                    'integrations' => $hasIntegrations ? 'required|array|min:1' : 'nullable|array',
                    'integrations.*' => 'integer|exists:integrations,id',
                ],
                [
                    'password.min' => 'Password must be at least 6 characters',
                    'password.required' => 'Password is required',
                    'integrations.required' => 'Please select at least one area of interest',
                    'integrations.*.exists' => 'One of the selected areas of interest is invalid',
                ]
            );

            if ($validator->fails()) {
                return failedValidation($validator);
            }
            $userData = [
                'username' => generateUniqueId(Admin::class),
                'name' => $request->name,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'gender' => $request->gender,
                'saspi_member' => $request->saspi_member,
                'membership_id' => $request->saspi_member === 'Yes' ? $request->membership_id : null,
                'designation' => $request->designation,
                'affiliation' => $request->affiliation,
                'research_experience_years' => $request->research_experience_years,
                'avg_articles_published' => $request->avg_articles_published,
                'h_index' => $request->h_index,
                'remarks' => $request->remarks,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'terms_accepted' => $request->terms,
                'status' => '0', // Author is immediately active
                'rev_type' => 'external',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $reviewer = Admin::create($userData);
            if ($reviewer) {
                $reviewer->assignRole($request->role_name);
                $reviewer->integrations()->sync(
                    array_values(array_unique(array_map('intval', (array) $request->input('integrations', []))))
                );

                session()->forget('otp_data');
                // Log in the author immediately using the 'author' guard
                Auth::guard('web')->login($reviewer);

                return response()->json([
                    'status' => '200',
                    'msg' => 'Registered successfully.',
                    'redirect' => route('registered-successfully'),
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
        // return view('admin.auth.login-with-password');
        return redirect(route('home'));
    }



    // ----------------Forget Passwor manager---------------
    public function sendPasswordResetLink($request)
    {
        if ($request->isMethod('get')) {
            return view('admin.auth.forget-password.send-reset-link');
        }
        if ($request->isMethod('post')) {

            try {

                $admin = Admin::where('email', $request->email)->first();
                DB::table('password_resets')->where('email', $request->email)->delete();
                $secureToken = Str::random(60);
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $secureToken,
                    'tokenable_type' => 'admin',
                    'created_at' => Carbon::now(),
                ]);
                // return $admin;
                if ($this->sendResetEmail($admin, $secureToken)) {
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
            $isValidToken = DB::table('password_resets')->where(['token' => $token, 'tokenable_type' => 'admin'])->first();
            if (!$isValidToken) {
                abort(403, 'Link Expired');
            }
            return view('admin.auth.forget-password.reset-password', compact('token'));
        }
        if ($request->isMethod('post')) {

            try {
                $password = $request->password;
                $isValidToken = DB::table('password_resets')->where([
                    'token' => $request->token,
                    'tokenable_type' => 'admin'
                ])->first();
                if (!$isValidToken) {
                    return errorResponse('Link Expired Try generating new one');
                }
                $admin = Admin::where('email', $isValidToken->email)->first();
                if (!$admin) {
                    return errorResponse('No Associated user found');
                }
                $admin->password = Hash::make($password);
                $admin->update();
                DB::table('password_resets')->where(['email' => $admin->email, 'tokenable_type' => 'admin'])->delete();
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
            // return $user;
            $url = route('admin.password.reset', ['token' => $token]);

            $user->notify(new PasswordResetLinkNotification($url, $token));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // --------------------Student Profile Manager-----------
    public function getProfile($uuid = null)
    {
        $admin = Admin::uuid($uuid)->first();
        // return $admin;
        $states = getStateList();
        $integrations = Integration::orderBy('name')->get();
        $adminIntegrations = $admin ? $admin->integrations()->pluck('integration_id')->toArray() : [];
        return view('admin.profile.admin-profile', compact('admin', 'states', 'integrations', 'adminIntegrations'));
    }

    public function updateProfile($request, $uuid)
    {

        try {
            $admin = Admin::uuid($uuid)->first();
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
            if ($admin->hasRole('Reviewer')) {
                $data = array_merge($data, [
                    'saspi_member' => $request->saspi_member,
                    'membership_id' => $request->saspi_member === 'Yes' ? $request->membership_id : null,
                    'designation' => $request->designation,
                    'affiliation' => $request->affiliation,
                    'research_experience_years' => $request->research_experience_years,
                    'avg_articles_published' => $request->avg_articles_published,
                    'h_index' => $request->h_index,
                    'remarks' => $request->remarks,
                ]);
            }
            $updated = $admin->update($data);
            if ($updated) {
                $admin->integrations()->sync($request->integrations ?? []);
                return successResponse('Profile has been updated successfully', route('admin.profile', ['uuid' => $admin->uuid]));
            }
            return errorResponse('Something went wrong');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }


    // -----------Chnage Password ------------

    public function changePasswordPage()
    {
        return view('admin.profile.change-password');
    }
    public function changePassword($request)
    {
        $url = route('admin.logout');
        try {
            $admin = authUser('admin');

            if (Hash::check($request->old_password, $admin->password)) {
                $newpw = Hash::make($request->password);
                $updated = $admin->update(['password' => $newpw]);
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

    public function staffChangePassword(AdminChangePasswordRequest $request, $uuid)
    {
        $admin = Author::where('uuid', $uuid)->first();

        if ($request->isMethod('GET')) {
            return view('admin.profile.change-password', compact('admin'));
        }
        if ($request->isMethod('POST')) {
            try {
                $updated = $admin->update([
                    'status' => true,
                    'password' => Hash::make($request->password)
                ]);
                if ($updated) {
                    return successResponse('Credentials changed Successfully', route('admin.getStaffProfile', $uuid));
                }
                return failureResponse('Something went wrong');
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }
    }

    // ---------Update Avatar----------
    public function updateAvatar($request)
    {

        try {
            $admin = authUser('admin');
            $data = [];
            if ($request->hasFile('avatar')) {
                $oldAvatar = $admin->avatar;
                $file = $request->file('avatar');
                $data['avatar'] = replaceFile($file, 'uploads/admin/avatar/', $oldAvatar);
            }
            $updated = $admin->update($data);
            if ($updated) {
                authUser('admin')->refresh();
                return successResponse('Avatar has been updated successfully', route('admin.profile', ['uuid' => $admin->uuid]));
            }
            return errorResponse('Something went wrong');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }

    //===================Author Profile===============
    public function getAuthorProfile($uuid = null)
    {
        $admin = Author::uuid($uuid)->first();
        // return $admin;
        $states = getStateList();
        return view('admin.profile.author-profile', compact('admin', 'states'));
    }

    public function updateAuthorProfile($request, $uuid)
    {
        try {
            $author = Author::uuid($uuid)->first();

            $data = [
                'name' => $request->name,
                'lname' => $request->lname,
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
                return successResponse('Author profile has been updated successfully', route('admin.getAuthorProfile', ['uuid' => $author->uuid]));
            }
            return errorResponse('Something went wrong');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }

    public function authorChangePassword(AdminChangePasswordRequest $request, $uuid)
    {
        $admin = Author::where('uuid', $uuid)->first();

        if ($request->isMethod('GET')) {
            return view('admin.profile.author-change-password', compact('admin'));
        }
        if ($request->isMethod('POST')) {
            try {
                $updated = $admin->update([
                    'status' => true,
                    'password' => Hash::make($request->password)
                ]);
                if ($updated) {
                    return successResponse('Credentials changed Successfully', route('admin.getAuthorProfile', $uuid));
                }
                return failureResponse('Something went wrong');
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }
    }
}
