<?php

namespace App\Http\Controllers\Admin\WebQuery;

use Throwable;
use App\Models\Admin;

use App\Models\Author;
use App\Models\Service;
use App\Models\Webquery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class QueryRepository
{

    protected $basePath = 'uploads/author/avatar/';

    private function getAuthor($uuid)
    {
        return Webquery::where('uuid', $uuid)->first();
    }

    public function index($request)
    {

        $perPage = $request->perPage ?? 15;
        $rows = Webquery::query();

        // If Requested for specific service Admin
        // if (!empty($request->serviceId)) {
        //     $serviceId = Service::uuid($request->serviceId)->first()->id ?? null;
        //     $rows->where('service_id', $serviceId);
        // }
        $this->search($request, $rows);
        $this->sort($request, $rows);
        $queryRows = $rows->paginate($perPage);
        // return $queryRows;
        if ($request->ajax()) {

            try {
                $datatable = view('admin.cms.query.datatable', compact('queryRows'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($queryRows)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.cms.query.index');
    }

    public function create($request)
    {
        return view('admin.cms.query.create');
    }
    public function store($request)
    {
        try {
            $data = [

                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => generateUniqueId(Webquery::class),
                'dob' => $request->dob,
                'gender' => $request->gender,
                'country' => $request->country,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'address' => $request->address,
                'registration_type' => 'system',
                'is_email_verified' => true,
                'is_phone_verified' => true,
                'status' => false,

            ];


            if ($request->hasFile('avatar')) {
                $data['avatar'] = uploadFile($request->file('avatar'), $this->basePath);
            }

            $created = Webquery::create($data);
            if ($created) {

                return successResponse('Author Created Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function edit($uuid)
    {
        try {
            $author = $this->getAuthor($uuid);
            return view('admin.cms.query.edit', compact('author'));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function update($request, $uuid)
    {
        try {
            // Fetch the existing author author
            $author = $this->getAuthor($uuid);

            // Prepare the data for update
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'country' => $request->country,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'address' => $request->address,
                'status' => $request->status ?? $author->status, // Preserve current status if not provided
            ];

            // Handle avatar update
            if ($request->hasFile('avatar')) {
                $data['avatar'] = replaceFile($request->file('avatar'), $this->basePath, $author->avatar);
            }

            // Attempt to update the author author
            $updated = $author->update($data);

            // Sync the role if the update was successful
            if ($updated) {
                return successResponse('Author updated successfully');
            }

            // Return error if the update failed
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            // Handle exceptions gracefully
            return throwException($th);
        }
    }


    public function destroy($uuid)
    {
        try {

            $author = $this->getAuthor($uuid);
            if ($author->avatar && file_exists(public_path($author->avatar))) {
                unlink(public_path($author->avatar));
            }
            $deleted = $author->delete();
            if ($deleted) {
                return successResponse('Author deleted successfully');
            }
            errorResponse('Something went wrong');
        } catch (Throwable $th) {
            // Handle exceptions gracefully
            return throwException($th);
        }
    }



    public function show($id)
    {
        // $author = Webquery::uuid($uuid)->with('creator', 'service')->first();
        // return view('admin.cms.query.show', compact('author'));

        $row = Webquery::where('id', $id)->first();
    //    return $row;
        if($row && $row->status == 'unread'){
            Webquery::where('id', $id)->update(['status' => 'read']);
        }
        return view('admin.cms.query.show', compact('row'));
    }

    public function toggleStatus($request, $uuid)
    {
        $author = $this->getAuthor($uuid);
        if (updateStatus($author, $request->status, $request->column)) {
            return successResponse('Author Status Updated Successfully');
        } else {
            return errorResponse('Something went wrong');
        }
    }

    public function generateCredentials($request, $uuid)
    {
        $author = $this->getAuthor($uuid);

        if ($request->isMethod('GET')) {
            return view('admin.cms.query.generatecredentials', compact('author'));
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
                $updated = $author->update([
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
