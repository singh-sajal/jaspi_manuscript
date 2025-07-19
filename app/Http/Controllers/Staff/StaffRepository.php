<?php

namespace App\Http\Controllers\Staff;

use Throwable;
use App\Models\Admin;

use App\Models\Service;
use App\Traits\PermissionGuard;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class StaffRepository
{

    use PermissionGuard;

    protected $basePath = 'uploads/staff/avatar/';

    private function getStaff($uuid)
    {
        return Admin::where('uuid', $uuid)->first();
    }

    public function index($request)
    {

        if ($this->canAccessPage('staff')) {

            $perPage = $request->perPage ?? 15;
            $records = Admin::where('is_admin', false)->with(['roles' => fn($q) => $q->select('id', 'name')]);

            // dd($records);

            // If Requested for specific service Admin
            if (!empty($request->serviceId)) {
                $serviceId = Service::uuid($request->serviceId)->first()->id ?? null;
                $records->where('service_id', $serviceId);
            }
            $this->search($request, $records);
            $this->sort($request, $records);
            $records = $records->paginate($perPage);
            // return $records;
            if ($request->ajax()) {

                try {
                    $datatable = view('admin.cms.staff.datatable', compact('records'))->render();
                    return response()->json([
                        'status' => '200',
                        'msg' => 'Data loaded',
                        'data' => $datatable,
                        'paginationInfo' => getPaginationInfo($records)
                    ], 200);
                } catch (Throwable $th) {
                    return throwException($th);
                }
            }
            return view('admin.cms.staff.index');
        }
        abort(403, 'Access Denied');
    }

    public function create($request)
    {
        $roles = Role::where('name', '!=', 'superadmin')->get();
        return view('admin.cms.staff.create', compact('roles'));
    }
    public function store($request)
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => generateUniqueId(Admin::class),
                'is_admin' => '0',
                'is_email_verified' => true,
                'is_phone_verified' => true,
                'status' => false,

            ];


            if ($request->hasFile('avatar')) {
                $data['avatar'] = uploadFile($request->file('avatar'), $this->basePath);
            }

            $created = Admin::create($data);
            if ($created) {
                $created->assignRole($request->role_id);
                return successResponse('Staff Created Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function edit($uuid)
    {
        try {
            $record = Admin::where('is_admin', false)->where('uuid', $uuid)->with('roles')->firstOrFail();
            $roles = Role::where('name', '!=', 'superadmin')->get();
            $staffRoles = $record->roles()->pluck('id')->toArray();
            return view('admin.cms.staff.edit', compact('record', 'roles', 'staffRoles'));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function update($request, $uuid)
    {
        try {
            $record = $this->getStaff($uuid);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            if ($request->hasFile('avatar')) {
                $data['avatar'] = replaceFile($request->file('avatar'), $this->basePath, $record->avatar);
            }

            $updated = $record->update($data);

            // Sync role

            if ($updated) {

                $record->syncRoles([$request->role_id]);
                return successResponse('Staff updated successfully');
            }

            return errorResponse('Something Went wrong');
            // return successResponse('Staff updated successfully');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function destroy($uuid)
    {
        try {
            $record = $this->getStaff($uuid);

            // Optionally remove avatar from storage
            if ($record->avatar && file_exists(public_path($record->avatar))) {
                unlink(public_path($record->avatar));
            }

            // Detach roles (optional)
            $record->roles()->detach();

            $record->delete();

            return successResponse('Staff deleted successfully');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function show($uuid)
    {
        $this->check('staff.show');
        $admin = Admin::uuid($uuid)->with('creator', 'service')->first();
        return view('admin.cms.staff.show', compact('admin'));
    }

    public function toggleStatus($request, $uuid)
    {
        $staff = $this->getStaff($uuid);
        if (updateStatus($staff, $request->status, $request->column)) {
            return successResponse('Staff Status Updated Successfully');
        } else {
            return errorResponse('Something went wrong');
        }
    }

    public function generateCredentials($request, $uuid)
    {
        $staff = $this->getStaff($uuid);

        if ($request->isMethod('GET')) {
            return view('admin.cms.staff.generateCredentials', compact('staff'));
        }
        if ($request->isMethod('POST')) {

            $validator = Validator::make(
                $request->only('email', 'password'),
                [
                    'email' => 'required',
                    'password' => 'required|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
                ],
                [
                    'email.required' => 'Email is required',
                    'password.required' => 'Password is required',
                    'password.min' => 'Password must be at least 6 characters',
                    'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'
                ]
            );
            if ($validator->fails()) {
                return  failedValidation($validator);
            }
            try {
                $updated = $staff->update([
                    'status' => true,
                    'password' => Hash::make($request->password)
                ]);
                if ($updated) {
                    return successResponse('Credentials Generated Successfully');
                }
                return failureResponse('Something went wrong');
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }
        abort(403);
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
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('username', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
    }



    private function sort($request, $query, $validColumns = [])
    {

        // if (!empty($request->sortColumn) && in_array($request->sortColumn, $validColumns)) {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, "{$request->sortDirection}");
        } else {
            $query->latest('id');
        }
    }
}
