<?php

namespace App\Http\Controllers\User\Auth;



use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Admin;

use Ramsey\Uuid\Uuid;
use App\Models\EntityPlan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\UserPasswordResetLink;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationNotification;

class UserAuthRepository extends AuthRepository
{

    // All the necessary setup to integrate the authentication via the AuthRepository
    // No Need to pass for user model

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
        return view('user.auth.login');
    }


    // Profile Manager
    public function profile($request, $uuid)
    {
        $user = Auth::guard('web')->user();
        if ($request->isMethod('get')) {
            return view('profile.index', compact('user'));
        }
        if ($request->isMethod('post')) {
            try {
                $data = [
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                ];
                if ($request->hasFile('avatar')) {
                    $oldAvatar = $user->avatar;
                    $file = $request->file('avatar');
                    $data['avatar'] = replaceFile($file, 'uploads/avatar/', $oldAvatar);
                }
                $updated = $user->update($data);

                if ($updated) {
                    authUser()->refresh();
                    return response()->json([
                        'status' => '200',
                        'msg' => 'Updated Successfully',
                        'redirect' => route('admin.profile', $uuid),
                    ], 200);
                }
                return response()->json(['status' => '500', 'msg' => 'Something went wrong'], 500);
            } catch (Throwable $th) {
                return response()->json([
                    'status' => '200',
                    'msg' => 'An Exception occurred',
                    'exception' => $th->getMessage()
                ], 500);
            }
        }
        abort(405, 'Method now allowed');
    }


    // ----------------Forget Passwor manager---------------
    public function sendPasswordResetLink($request)
    {
        if ($request->isMethod('get')) {
            return view('authentication.password-reset.forget-password');
        }
        if ($request->isMethod('post')) {
            $validated = Validator::make(
                $request->only('email'),
                [
                    'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ]
            );
            if ($validated->fails()) {
                return response()->json(['status' => '400', 'errors' => $validated->errors()]);
            }
            try {
                $user = User::where('email', $request->email)->first();
                if ($user == null) {
                    return response()->json(['status' => '500', 'msg' => 'User does not exist']);
                }
                DB::table('password_resets')->where('email', $request->email)->delete();
                $secureToken = Str::random(60);
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $secureToken,
                    'tokenable_type' => 'user',
                    'created_at' => Carbon::now(),
                ]);
                if ($this->sendResetEmail($user, $secureToken)) {
                    return response()->json(['status' => '200', 'msg' => 'A reset link has been sent to your email address.']);
                }
                return response()->json(['status' => '500', 'msg' => 'Something went wrong']);
            } catch (\Throwable $th) {
                return response()->json(['status' => '500', 'msg' => 'An exception occurred', 'exception' => $th->getMessage()]);
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
            $isValidToken = DB::table('password_resets')->where(['token' => $token, 'tokenable_type' => 'user'])->first();
            if (!$isValidToken) {
                abort(403, 'Link Expired');
            }
            return view('authentication.password-reset.reset-password', compact('token'));
        }
        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/',
                'token' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => '400', 'errors' => $validator->errors()], 400);
            }
            try {
                $password = $request->password;
                $isValidToken = DB::table('password_resets')->where(['token' => $request->token, 'tokenable_type' => 'user'])->first();
                if (!$isValidToken) {
                    return response()->json(['status' => '500', 'msg' => 'Link Expired Try generating new one'], 500);
                }
                $user = User::where('email', $isValidToken->email)->first();
                if (!$user) {
                    return response()->json(['status' => '500', 'msg' => 'No Associated user found'], 500);
                }
                $user->password = Hash::make($password);
                $user->update();
                DB::table('password_resets')->where(['email' => $user->email, 'tokenable_type' => 'user'])->delete();

                return response()->json([
                    'status' => '200',
                    'msg' => 'Password has be reset successfully',
                    'redirect' => route('admin.login')
                ], 200);
            } catch (Throwable $th) {
                return response()->json(['status' => '500', 'msg' => 'An exception occurred', 'exception' => $th->getMessage()], 500);
            }
        }
        abort(405, 'Method now allowed');
    }

    private function sendResetEmail($user, $token)
    {
        try {
            $user->notify(new UserPasswordResetLink($token));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // --Update password from profile
    public function updatePassword($request, $uuid)
    {
        if ($request->isMethod('get')) {
            return view('profile.change-password', compact('uuid'));
        }
        if ($request->isMethod('post')) {
            $url = route('admin.logout');
            try {
                $user = authUser('web');

                if (Hash::check($request->oldpw, $user->password)) {
                    $newpw = Hash::make($request->password);
                    $updated = $user->update(['password' => $newpw]);
                    if ($updated) {

                        return response()->json([
                            'status' => '200',
                            'msg' => 'Password Changed Successfully',
                            'redirect' => $url
                        ], 200);
                    }
                    return response()->json(['status' => '500', 'msg' => 'Something Went Wrong']);
                }
                return response()->json(['status' => '500', 'msg' => 'Current password do not match our records']);
            } catch (Throwable $th) {
                return response()->json(['status' => '500', 'msg' => 'An exception occurred', 'exception' => $th->getMessage()]);
            }
        }
        abort(405, 'Method not allowed');
    }
}
