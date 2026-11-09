<?php

namespace App\Http\Controllers\Admin\Integration;

use Throwable;
use App\Models\Integration;
use App\Traits\PermissionGuard;


class IntegrationRepository
{
    use PermissionGuard;

    private function getIntegration($id)
    {
        return Integration::where('id', $id)->first();
    }

    public function index($request)
    {
        if ($this->canAccessPage('integration')) {

            $perPage = $request->perPage ?? 50;
            $records = Integration::query();

            $this->search($request, $records);
            $this->sort($request, $records);
            $records = $records->paginate($perPage);

            if ($request->ajax()) {
                try {
                    $datatable = view('admin.cms.integration.datatable', compact('records'))->render();
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
            return view('admin.cms.integration.index');
        }
        abort(403, 'Access Denied');
    }

    public function create($request)
    {
        return view('admin.cms.integration.create');
    }

    public function store($request)
    {
        try {
            $data = [
                'name' => $request->name,
            ];

            $integration = Integration::create($data);
            if ($integration) {
                return successResponse('Integration Created Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function edit($id)
    {
        $integration = $this->getIntegration($id);
        return view('admin.cms.integration.edit', compact('integration'));
    }

    public function update($request, $id)
    {
        try {
            $integration = $this->getIntegration($id);
            $data = [
                'name' => $request->name,
            ];

            $updated = $integration->update($data);
            if ($updated) {
                return successResponse('Integration Updated Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function destroy($id)
    {
        try {
            $integration = $this->getIntegration($id);
            $deleted = $integration->delete();
            if ($deleted) {
                return successResponse('Integration Deleted Successfully');
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function show($id)
    {
        $integration = $this->getIntegration($id);
        return view('admin.cms.integration.show', compact('integration'));
    }

    private function search($request, $query)
    {
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('created_at', 'LIKE', "%{$searchTerm}%");
            });
        }
    }

    private function sort($request, $query, $validColumns = [])
    {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, "{$request->sortDirection}");
        } else {
            $query->latest('id');
        }
    }
}