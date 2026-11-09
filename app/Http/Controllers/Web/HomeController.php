<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Admin\Auth\AdminAuthRepository;
use App\Http\Controllers\Author\Auth\AuthorAuthRepository;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Application;
use App\Models\Author;
use App\Models\Webquery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class HomeController extends Controller
{

    public function redirect()
    {

        if (Auth::guard('admin'))
            $user = authUser('admin');
        else
            $user = authUser('');

        return $user;
    }

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

    public function published_index(Request $request)
    {

        $perPage = $request->perPage ?? 50;

        // $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
        //     $q->where('name', 'Editor');
        // })->get();
        $type = $request->type ?? '';

        $applications = Application::query()->with('assignee', 'attachments')->where('status', 'published');


        // if (authUser('admin')->is_admin == 0) {
            // $applications = $applications->where('assigned_to_id', authUser('admin')->id)
            //     // ->where('status','!=','incomplete')
            //     ->orWhereHas('timelines', function ($query) {
            //         $query->where('assigned_to_id', authUser('admin')->id);
            //     });
        // }

        // if (authUser('admin')->rev_type == 'external') {
        //     $applications = Application::query()->where('assigned_to_ex_id', authUser('admin')->id);
        // }


        // return $applications->get();

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);
        // return $applications;

        if ($request->ajax()) {

            try {
                $datatable = view('published.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('published.index', compact('type'));
    }

    private function search($request, $query)
    {
        // if (!empty($request->search)) {
        //     $statusMap = [
        //         'active' => true,
        //         'inactive' => false,
        //     ];
        //     $statusValue = $statusMap[strtolower($request->search)] ?? null;
        //     $query->where(function ($subQuery) use ($request, $statusValue) {
        //         $subQuery->where('title', 'LIKE', "%{$request->search}%")
        //             ->orWhere('type', 'LIKE', "%{$request->search}%")
        //             ->orWhereHas('service', function ($q) use ($request) {
        //                 $q->where('name', 'LIKE', "%{$request->search}%");
        //             })
        //             ->orWhereHas('creator', function ($q) use ($request) {
        //                 $q->where('name', 'LIKE', "%{$request->search}%");
        //             })
        //             ->orWhere('created_at', 'LIKE', "%{$request->search}%");

        //         if (!is_null($statusValue)) {
        //             $subQuery->orWhere('status', $statusValue);
        //         }
        //     });
        // }

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('application_id', 'like', "%{$searchTerm}%")
                    ->orWhere('submission_type', 'like', "%{$searchTerm}%")
                    ->orWhere('title', 'like', "%{$searchTerm}%");
                // ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
    }

    private function sort($request, $query, $validColumns = [])
    {

        // if (!empty($request->sortColumn) && in_array($request->sortColumn, $validColumns)) {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, "{$request->sortDirection}");
        } else {
            $query->orderBy('updated_at', 'desc');
        }
    }
}
