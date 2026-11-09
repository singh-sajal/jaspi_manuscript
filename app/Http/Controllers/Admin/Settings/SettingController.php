<?php

namespace App\Http\Controllers\Admin\Settings;

use Throwable;
use Exception;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Settings;


class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request)
    {
        // $user = Auth::user();
        // return $user;
        // if ($this->canAccessPage('staff')) {

        $perPage = $request->perPage ?? 50;
        $records = Setting::query();


        $this->search($request, $records);
        $this->sort($request, $records);
        $records = $records->paginate($perPage);
        // return $records;
        if ($request->ajax()) {

            try {
                $datatable = view('admin.setting.datatable', compact('records'))->render();
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
        return view('admin.setting.index');
        // }
        // abort(403, 'Access Denied');
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
                    ->orWhere('std_id', 'like', "%{$searchTerm}%")
                    ->orWhere('aadhar_id', 'like', "%{$searchTerm}%")
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
            $query->orderBy('created_at', 'DESC');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.setting.settingscreate');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 'img' => 'file|mimes:png,jpg',
        // 'description' => 'min:3',
        // 'class' => 'min:1',
        // 'alt' => 'min:3',
        // 'link' => 'min:3',

        $validated = Validator::make(
            $request->all(),
            [
                'key' => 'required|min:3',
                'title' => 'required|min:3',
                'img' => 'image|max:2048|mimes:jpeg,png,jpg',
            ],
            [
                'key.required' => 'Please Enter a Key name',
                'key.min' => 'Key Name should be minimum 3 Characters long',
                'title.required' => 'Please Enter a Value',
                'title.min' => 'Value size should be more than 2 character long',
                'img.image' => 'Invalid image selected',
                'img.max' => 'Image size must be less than 2MB',
                'img.mimes' => 'Image type should be jpeg/png/jpg/gif',
            ]
        );
        if ($validated->fails()) {
            return response()->json(['status' => 'fail', 'errors' => $validated->errors()->all()]);
        } else {
            if (!empty($request->img)) {
                $file =  time() . '.' . $request->img->extension();
                $path = 'frontend/uploads/common/' . $file;
            } else {
                $path = null;
            }
            $data = [
                'title' => $request->title,
                'key' => $request->key,
                'img' => $path,
                'description' => $request->description,
                'class' => $request->class,
                'alt' => $request->alt,
                'link' => $request->link,
            ];
            //  dd($data);
            if ($path != null) {
                $request->img->move(public_path('frontend/uploads/common/'), $file);
            }

            $form = Setting::create($data);
            if ($form) {
                // return response()->json(['status' => 'success', 'msg' => 'Added Successfully.']);
                return successResponse('Added Successfully.');
            } else {
                // return response()->json(['status' => 'fail', 'msg' => 'Something Went Wrong.']);
                return errorResponse('Something Went Wrong.');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $form = Setting::where('id', $id)->first();
        return view('admin.setting.settingsshow', ['form' => $form]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (request()->ajax()) {
            // return view('Common.edit', ['form' => $form]);
            $form = Setting::find($id);
            return view('admin.setting.settingsedit', compact('form'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $validated = Validator::make(
        //     $request->all(),
        //     [
        //         'title' => 'required|min:3',
        //         'img' => 'file|mimes:png,jpg',
        //         'description' => 'required|min:2',
        //         'class' => 'required|min:1',
        //         'alt' => 'required|min:3',
        //         'link' => 'required',
        //         'key' => 'required',
        //     ]

        // );

        $validated = Validator::make(
            $request->all(),
            [
                'key' => 'required|min:3',
                'title' => 'required|min:3',
                'img' => 'image|max:2048|mimes:jpeg,png,jpg',
            ],
            [
                'key.required' => 'Please Enter a Key name',
                'key.min' => 'Key Name should be minimum 3 Characters long',
                'title.required' => 'Please Enter a Value',
                'title.min' => 'Value size should be more than 2 character long',
                'img.image' => 'Invalid image selected',
                'img.max' => 'Image size must be less than 2MB',
                'img.mimes' => 'Image type should be jpeg/png/jpg/gif',
            ]
        );

        if ($validated->fails()) {
            //  return $validated->errors()->all();
            // return "fail";
            return response()->json(['status' => 'fail', 'errors' => $validated->errors()->all()]);
        } else {

            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'class' => $request->class,
                'alt' => $request->alt,
                'link' => $request->link,
                'key' => $request->key,

            ];
            //  dd($data);
            $image = $request->hasFile('img');
            if ($image) {

                $file =  time() . '.' . $request->img->extension();
                $path = 'frontend/uploads/common/' . $file;
                $data['img'] = $path;

                $oldfile = Setting::where(['id' => $id])->first()->img;
                if ($oldfile) {
                    if (file_exists(public_path($oldfile))) {
                        // File::delete($path);
                        unlink(public_path($oldfile));
                    }
                }
                $request->img->move(public_path('frontend/uploads/common/'), $file);
            }
            $form = Setting::where(['id' => $id])->update($data);

            if ($form) {
                // return response()->json(['status' => 'success', 'msg' => 'Updated Successfully.']);
                return successResponse('Updated Successfully.');
            } else {
                // return response()->json(['status' => 'fail', 'msg' => 'Something Went Wrong.']);
                return errorResponse('Something Went Wrong.');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $path = Setting::where(['id' => $id])->first()->img;
        if ($path) {
            if (file_exists(public_path($path))) {
                unlink(public_path($path));
            }
        }
        $delete = Setting::find($id)->delete();
        if ($delete) {
            // return response()->json(['status' => 'success', 'msg' => 'Deleted Successfully']);
            return successResponse('Deleted Successfully');
        } else {
            // return response()->json(['status' => 'fail', 'msg' => 'Something went wrong!']);
            return errorResponse('Something went wrong!');
        }
    }
}
