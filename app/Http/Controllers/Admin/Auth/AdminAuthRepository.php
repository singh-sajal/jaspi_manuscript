<?php

namespace App\Http\Controllers\Admin\Auth;

use Throwable;
use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Admin;
use App\Models\State;
use Ramsey\Uuid\Uuid;
use App\Models\Author;
use App\Classes\Logger;
use App\Models\EntityPlan;
use Illuminate\Support\Str;
use App\Traits\HasIpBlocker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if ($request->isMethod('GET')) {
            return $this->registerPage();
        }

        if ($request->isMethod('POST')) {
            return $this->registerOrganization($request);
        }
        abort(403, 'Method Not Allowed');
    }

    private function registerPage()
    {
        $states = State::indian()->get();
        $plans = Plan::active()->demo()->get();
        return view('authentication.signup', compact('states', 'plans'));
    }

    private function registerOrganization($request)
    {
        try {

            $subscriptionPlanId = $request->plan_id;
            $organizationData = [
                'uuid' => (string) Uuid::uuid4(),
                'name' => $request->name,
                'contact_person' => $request->contactperson,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'pan_number' => $request->pan,
                'gstin' => $request->gstin,
                'state_id' => $request->state,
                'status' => 1,

            ];
            $userData = [
                'name' => 'Super Admin',
                'username' => generateUniqueId(User::class, 'ATR'),
                'phone' => $request->phone,
                'email' => $request->loginemail,
                'password' => Hash::make($request->password),
                'status' => 1,
                'is_admin' => '1',
            ];
            // Storing Data Until the verification is completed;
            session()->put('subscriptionPlanId', $subscriptionPlanId);
            session()->put('organization', $organizationData);
            session()->put('user', $userData);

            $verificationCode = $this->generateVerificationCode();
            Notification::route('mail', $request->loginemail)
                ->notify(new VerificationNotification($verificationCode));
            $verificationScreen = view('authentication.verify-account', ['email' => $request->loginemail])->render();
            return response()->json([
                'status' => "200",
                'msg' => 'Account Registered successfully.',
                'jsFunction' => 'handleVerification',
                'parameters' => [$verificationScreen]
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => '500',
                'msg' => 'An exception occured',
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
        return view('admin.profile.admin-profile', compact('admin', 'states'));
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
            $updated = $admin->update($data);
            if ($updated) {
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
