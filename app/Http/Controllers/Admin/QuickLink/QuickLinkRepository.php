<?php

namespace App\Http\Controllers\Admin\QuickLink;

use Throwable;
use App\Models\QuickLink;



class QuickLinkRepository
{


    private function getQuickLink($uuid)
    {
        return QuickLink::uuid($uuid)->first();
    }

    public function index($request)
    {

        $perPage = $request->perPage ?? 15;
        $records = QuickLink::query();


        $this->search($request, $records);
        $this->sort($request, $records);
        $records = $records->paginate($perPage);

        if ($request->ajax()) {

            try {
                $datatable = view('admin.cms.quick-link.datatable', compact('records'))->render();
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
        return view('admin.cms.quick-link.index');
    }

    public function create($request)
    {
        return view('admin.cms.quick-link.create');
    }
    public function store($request)
    {
        try {
            $data = [
                'name' => $request->name,
                'url' => $request->url
            ];

            $link = QuickLink::create($data);
            if ($link) {
                return successResponse('QUick Link Created Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }
    public function edit($uuid)
    {

        $quickLink = $this->getQuickLink($uuid);
        return view('admin.cms.quick-link.edit', compact('quickLink'));
    }

    public function update($request, $uuid)
    {
        try {
            $quickLink = $this->getQuickLink($uuid);
            $data = [
                'name' => $request->name,
                'url' => $request->url
            ];

            $updated = $quickLink->update($data);
            if ($updated) {
                return successResponse('Quick Link Updated Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function destroy($uuid)
    {
        try {
            $quickLink = $this->getQuickLink($uuid);
            $deleted = $quickLink->delete();
            if ($deleted) {
                return successResponse('Quick Link Deleted Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function show($uuid)
    {
        $quickLink = $this->getQuickLink($uuid);
        return view('admin.cms.quick-link.show', compact('quickLink'));
    }
    public function toggleStatus($request, $uuid)
    {
        $quickLink = $this->getQuickLink($uuid);
        if (updateStatus($quickLink, $request->status)) {
            return successResponse('Quick Link Status Updated Successfully');
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
                    ->orWhere('url', 'LIKE', "%{$request->search}%")
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
