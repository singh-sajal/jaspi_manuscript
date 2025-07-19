<?php

namespace App\Http\Controllers\Admin\Consultant;

use Throwable;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\Consultant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ConsultantRepository
{
    private $storagePath = 'uploads/consultants/avatars/';
    private function getConsultant($uuid)
    {
        return Consultant::uuid($uuid)->first();
    }

    public function index($request)
    {

        $perPage = $request->perPage ?? 15;
        $records = Consultant::query()->where('parent_id', null);
        $this->search($request, $records);
        $this->sort($request, $records);
        $records = $records->paginate($perPage);

        if ($request->ajax()) {

            try {
                $datatable = view('admin.cms.consultant.datatable', compact('records'))->render();
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
        return view('admin.cms.consultant.index');
    }

    public function create($request)
    {
        $services = Service::active()->where('parent_id', null)->pluck('name')->toArray();
        return view('admin.cms.consultant.create', compact('services'));
    }
    public function store($request)
    {

        try {
            $specialization = [];
            foreach (json_decode($request->specialization ?? '', true) as $spec) {
                $specialization[] = $spec['value'];
            }

            $data = [
                'name' => $request->name,
                'username' => generateUniqueId(Consultant::class, 'CAO'),
                'email' => $request->email,
                'phone' => $request->phone,
                'is_conusltant' => true,
                'type' => $request->other_type
                    ? ucwords("Others({$request->other_type})")
                    : ucwords($request->consultant_type),

                'registration_no' => $request->registration_no,
                'specialization' => $specialization,

            ];
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $data['avatar'] = uploadFile($file, $this->storagePath);
            }

            $consultant = Consultant::create($data);
            if ($consultant) {
                $consultant->activities()->create([
                    'action' => 'Account Created',
                    'description' => 'Your Account has been Created',

                ]);
                return successResponse('Consultant Created Successfully', route('admin.consultants.index'));
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }
    public function edit($uuid)
    {

        $consultant = $this->getConsultant($uuid);
        $services = Service::active()->where('parent_id', null)->pluck('name')->toArray();
        return view('admin.cms.consultant.edit', compact('services', 'consultant'));
    }

    public function update($request, $uuid)
    {
        try {
            $consultant = $this->getConsultant($uuid);
            $specialization = [];
            foreach (json_decode($request->specialization ?? '', true) as $spec) {
                $specialization[] = $spec['value'];
            }

            $data = [
                'name' => $request->name,
                'username' => generateUniqueId(Consultant::class, 'CAO'),
                'email' => $request->email,
                'phone' => $request->phone,
                'is_conusltant' => true,
                'type' => $request->other_type
                    ? ucwords("Others({$request->other_type})")
                    : ucwords($request->consultant_type),

                'registration_no' => $request->registration_no,
                'specialization' => $specialization,

            ];
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $data['avatar'] = replaceFile($file, $this->storagePath, $consultant->avatar);
            }

            $consultant = $consultant->update($data);
            if ($consultant) {
                return successResponse('Consultant Updated Successfully', route('admin.consultants.index'));
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function destroy($uuid)
    {
        try {
            $consultant = $this->getConsultant($uuid);
            deleteFile($consultant->avatar);
            $deleted = $consultant->delete();
            if ($deleted) {
                return successResponse('Consultant Deleted Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function show($uuid)
    {
        return "Under Consutruction";
        $checklist = Checklist::Uuid($uuid)->with('creator', 'service')->first();
        return view('admin.cms.checklist.show', compact('checklist'));
    }
    public function toggleStatus($request, $uuid)
    {

        $consultant = $this->getConsultant($uuid);
        if (updateStatus($consultant, $request->status, $request->column)) {
            return successResponse('Status Updated Successfully');
        } else {
            return errorResponse('Something went wrong');
        }
    }

    public function generateCredentials($request, $uuid)
    {

        $consultant = $this->getConsultant($uuid);

        if ($request->isMethod('GET')) {
            return view('admin.cms.consultant.generatecredentials', compact('consultant'));
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
                $updated = $consultant->update([
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
        if (!empty($request->search)) {
            $statusMap = [
                'active' => true,
                'inactive' => false,
            ];
            $enableDisableMap = [
                'enabled' => true,
                'disabled' => false
            ];
            $isFeaturedMap = [
                'yes' => true,
                'no' => false
            ];
            $statusValue = $statusMap[strtolower($request->search)] ?? null;
            $enableDisableValue = $enableDisableMap[strtolower($request->search)] ?? null;
            $isFeaturedValue = $isFeaturedMap[strtolower($request->search)] ?? null;
            $query->where(function ($subQuery) use ($request, $statusValue, $enableDisableValue, $isFeaturedValue) {
                $subQuery->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('username', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%")
                    ->orWhere('type', 'LIKE', "%{$request->search}%")
                    // ->orWhereHas('service', function ($q) use ($request) {
                    //     $q->where('name', 'LIKE', "%{$request->search}%");
                    // })
                    // ->orWhereHas('creator', function ($q) use ($request) {
                    //     $q->where('name', 'LIKE', "%{$request->search}%");
                    // })
                    ->orWhere('created_at', 'LIKE', "%{$request->search}%");

                if (!is_null($statusValue)) {
                    $subQuery->orWhere('status', $statusValue);
                }
                if (!is_null($enableDisableValue)) {
                    $subQuery->orWhere('is_twofactor_enabled', $enableDisableValue);
                }
                if (!is_null($isFeaturedValue)) {
                    $subQuery->orWhere('is_featured', $isFeaturedValue);
                }
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
