<?php

namespace App\Http\Controllers\Admin\RolePermission;



use Throwable;
use App\Models\CustomRole;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RoleRepository
{


    // Managing ROles---------------
    public function getRole($roleId)
    {
        return Role::find($roleId);
    }
    public function index($request)
    {

        $perPage = $request->perPage ?? 15;
        $records = Role::where('name', '!=', 'superadmin')->with('permissions');
        $this->search($request, $records);
        $this->sort($request, $records);
        $records = $records->paginate($perPage);


        if ($request->ajax()) {
            try {
                $datatable = view('admin.cms.role-permission.datatable', compact('records'))->render();
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
        return view('admin.cms.role-permission.index');
    }

    public function create()
    {
        return view('admin.cms.role-permission.create');
    }

    public function store($request)
    {
        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);
            return successResponse('Added Successfully');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function edit($id)
    {
        $role = Role::find($id);
        return view('admin.cms.role-permission.edit', compact('role'));
    }
    public function update($request, $id)
    {
        try {
            $role = Role::find($id);
            $role->update(['name' => $request->name]);
            return successResponse('Updated Successfully');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function show($id)
    {
        $role = $this->getRole($id);
        $permissions = Permission::orderBy('id', 'desc')->get();
        $categories = [];

        foreach ($permissions as $permission) {
            if (str_contains($permission->name, '.')) {
                [$category, $action] = explode('.', $permission->name, 2);
                $categories[$category][] = $permission;
            } else {

                $categories['Other'][] = $permission;
            }
        }
        $role_permissions = $role->permissions;

        return view('admin.cms.role-permission.role-has-permissions', compact('role', 'categories', 'role_permissions'));
    }
    public function destroy($id)
    {
        try {
            $role = Role::find($id);
            $usersWithRole = $role->users()->count();
            if ($usersWithRole > 0) {
                return errorResponse('Cannot delete role. It is associated with ' . $usersWithRole . ' user(s).', 403);
            }
            $deleted = $role->delete();
            if ($deleted) {
                return successResponse('Deleted Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    // Assign Permission to a ROle
    public function assignPermission($request, $roleId)
    {

        try {
            $validator = Validator::make($request->all(), [
                'permission' => 'required|array|min:1',
            ]);
            if ($validator->fails()) {
                return failedValidation($validator);
            }

            $role = Role::find($roleId);
            $role->update(['updated_at' => now()]);
            $permissions = Permission::whereIn('id', $request->permission)->pluck('name')->toArray();
            $role->syncPermissions($permissions);
            return successResponse('Updated Successfully', route('admin.roles.index'));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    // Managing Permissions--------------

    public function permissionManager($request)
    {

        if ($request->has('dev')) {

            if ($request->isMethod('get')) {
                $perPage = $request->perPage ?? 15;
                $records = Permission::query();
                $this->search($request, $records);
                $this->sort($request, $records);
                $records = $records->paginate($perPage);


                if ($request->ajax()) {
                    try {
                        $datatable = view('admin.cms.role-permission.permission-datatable', compact('records'))->render();
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

                return view('admin.cms.role-permission.permissions');
            }
            if ($request->isMethod('post')) {
                return $this->createPermission($request);
            }
        }
        abort(404);
    }


    public function createPermission($request)
    {

        $validator = Validator::make(
            $request->all(),
            ['name' => 'required'],
            ['name.required' => "Please Enter Permission Name"]
        );
        if ($validator->fails()) {
            return failedValidation($validator);
        }
        try {
            $permission = Permission::create(
                [
                    'name' => $request->name,
                    'guard_name' => 'admin'
                ]
            );
            if ($permission) {
                return successResponse('Created Successfully.');
            }
            return errorResponse('500', 'Something Went Wrong.');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }

    public function destroyPermission($id)
    {
        try {
            $permission = Permission::find($id);
            $deleted = $permission->delete();
            if (!$deleted) {
                return errorResponse('500', 'Something Went Wrong.');
            }
            return successResponse('Deleted Successfully.');
        } catch (\Throwable $th) {
            return throwException($th);
        }
    }
    private function search($request, $query)
    {
        if (!empty($request->search)) {

            $query->where(function ($subQuery) use ($request,) {
                $subQuery->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('created_at', 'LIKE', "%{$request->search}%");
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
