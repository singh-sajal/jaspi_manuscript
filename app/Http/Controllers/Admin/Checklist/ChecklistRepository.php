<?php

namespace App\Http\Controllers\Admin\Checklist;

use Throwable;
use App\Models\Service;
use App\Models\Checklist;


class ChecklistRepository
{


    private function getChecklist($uuid)
    {
        return Checklist::uuid($uuid)->first();
    }

    public function index($request)
    {

        $perPage = $request->perPage ?? 15;
        $records = Checklist::query()->with('creator', 'service');

        // If Requested for specific service checklist
        if (!empty($request->serviceId)) {
            $serviceId = Service::uuid($request->serviceId)->first()->id ?? null;
            $records->where('service_id', $serviceId);
        }
        $this->search($request, $records);
        $this->sort($request, $records);
        $records = $records->paginate($perPage);

        if ($request->ajax()) {

            try {
                $datatable = view('admin.cms.checklist.datatable', compact('records'))->render();
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
        return view('admin.cms.checklist.index');
    }

    public function create($request)
    {
        $serviceId = $request->serviceId ?? null;
        $services = Service::active()->where('parent_id', '!=', null)->orderBy('name')->select('id', 'uuid', 'name')->get();
        return view('admin.cms.checklist.create', compact('services', 'serviceId'));
    }
    public function store($request)
    {
        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'service_id' => $request->service_id ?? null,
            ];

            $checklist = Checklist::create($data);
            if ($checklist) {
                return successResponse('Checklist Created Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }
    public function edit($uuid)
    {
        $serviceId = null;
        $checklist = $this->getChecklist($uuid);
        $services = Service::active()->where('parent_id', '!=', null)->orderBy('name')->select('id', 'uuid', 'name')->get();
        return view('admin.cms.checklist.edit', compact('services', 'checklist', 'serviceId'));
    }

    public function update($request, $uuid)
    {
        try {
            $checklist = $this->getChecklist($uuid);
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'service_id' => $request->service_id ?? null,
            ];

            $updated = $checklist->update($data);
            if ($updated) {
                return successResponse('Service Updated Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function destroy($uuid)
    {
        try {
            $checklist = $this->getChecklist($uuid);
            $deleted = $checklist->delete();
            if ($deleted) {
                return successResponse('Service Deleted Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function show($uuid)
    {
        $checklist = Checklist::uuid($uuid)->with('creator', 'service')->first();
        return view('admin.cms.checklist.show', compact('checklist'));
    }
    public function toggleStatus($request, $uuid)
    {
        $checklist = $this->getChecklist($uuid);
        if (updateStatus($checklist, $request->status)) {
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
                $subQuery->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('type', 'LIKE', "%{$request->search}%")
                    ->orWhereHas('service', function ($q) use ($request) {
                        $q->where('name', 'LIKE', "%{$request->search}%");
                    })
                    ->orWhereHas('creator', function ($q) use ($request) {
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
