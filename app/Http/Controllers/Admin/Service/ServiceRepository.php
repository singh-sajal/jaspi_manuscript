<?php

namespace App\Http\Controllers\Admin\Service;

use Throwable;
use App\Models\Service;

class ServiceRepository
{

    private $storagePath = 'uploads/service/';
    private function getService($uuid)
    {
        return Service::uuid($uuid)->first();
    }

    public function index($request)
    {
        $perPage = $request->perPage ?? 15;
        $records = Service::query()->with('parent');
        $this->search($request, $records);
        $this->sort($request, $records);
        $records = $records->paginate($perPage);

        if ($request->ajax()) {

            try {
                $datatable = view('admin.cms.service.datatable', compact('records'))->render();
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
        return view('admin.cms.service.index');
    }

    public function create()
    {
        $services = Service::active()->where('parent_id', null)->select('id', 'name')->get();
        return view('admin.cms.service.create', compact('services'));
    }
    public function store($request)
    {
        try {
            $data = [
                'name' => $request->name,
                'description' => $request->short_description,
                'long_description' => $request->description,
                'parent_id' => $request->parent_id ?? null,
            ];
            if ($request->hasFile('image')) {
                $data['image_url'] = uploadFile($request->file('image'), $this->storagePath);
            }
            $service = Service::create($data);
            if ($service) {
                return successResponse('Service Created Successfully', route('admin.services.index'));
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }
    public function edit($uuid)
    {
        $service = $this->getService($uuid);
        $services = Service::active()->where('parent_id', null)->where('id', '!=', $service->id)->select('id', 'name')->get();
        return view('admin.cms.service.edit', compact('service', 'services'));
    }

    public function update($request, $uuid)
    {
        try {
            $service = $this->getService($uuid);
            $data = [
                'name' => $request->name,
                'description' => $request->short_description,
                'long_description' => $request->description,
                'parent_id' => $request->parent_id ?? null,
            ];
            if ($request->hasFile('image')) {
                $data['image_url'] = replaceFile($request->file('image'), $this->storagePath, $service->image_url);
            }
            $updated = $service->update($data);
            if ($updated) {
                return successResponse('Service Updated Successfully', route('admin.services.index'));
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function destroy($uuid)
    {
        try {
            $service = $this->getService($uuid);
            $image = $service->image_url;
            if ($image) {
                deleteFile($image);
            }
            $deleted = $service->delete();
            if ($deleted) {
                return successResponse('Service Deleted Successfully', route('admin.services.index'));
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function show($uuid)
    {
        $service = $this->getService($uuid);
        return view('admin.cms.service.show', compact('service'));
    }
    
    public function toggleStatus($request, $uuid)
    {
        $service = $this->getService($uuid);
        if (updateStatus($service, $request->status)) {
            // Toggle child status---------Parent is active all childs are active which can be inactive individualy
            $service->subservices()->update(['status' => ($request->status == 'active' ? false : true)]);
            return successResponse('Service Status Updated Successfully');
        } else {
            return errorResponse('Something went wrong');
        }
    }
    private function search($request, $query)
    {
        if (!empty($request->search)) {
            $statusMap = [
                'active' => true,
                'inactive' => false,
            ];
            $statusValue = $statusMap[strtolower($request->search)] ?? null;
            $query->where(function ($subQuery) use ($request, $statusValue) {
                $subQuery->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('description', 'LIKE', "%{$request->search}%")
                    ->orWhereHas('parent', function ($q) use ($request) {
                        $q->where('name', 'LIKE', "%{$request->search}%");
                    })
                    ->orWhere('created_at', 'LIKE', "%{$request->search}%");

                if (!is_null($statusValue)) {
                    $subQuery->orWhere('status', $statusValue);
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
