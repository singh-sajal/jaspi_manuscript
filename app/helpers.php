<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;


// Json Response Handler
if (!function_exists('throwException')) {
    function throwException($th, $showTrac = false)
    {
        $exception = [
            'status' => '500',
            'msg' => $message ?? 'An error occured',
            'exception' => $th->getMessage()
        ];
        if ($showTrac) {
            $exception['trace'] = $th->getTraceAsString();
        }
        return response()->json($exception, 500);
    }
}

if (!function_exists('successResponse')) {
    function successResponse(
        $message = "Created Successfully",
        $redirectUrl = null,
        $jsFunction = null,
        $parameters = null
    ) {
        $data = [
            'status' => '200',
            'msg' => $message,
        ];
        if ($jsFunction) {
            $data['jsFunction'] = $jsFunction;
        }
        if ($parameters) {
            $data['parameters'] = $parameters;
        }
        if ($redirectUrl) {
            $data['redirect'] = $redirectUrl;
        }
        return response()->json($data, 200);
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse($message = "Something went wrong", $statusCode = 500)
    {
        return response()->json([
            'status' => '500',
            'msg' => $message
        ], $statusCode);
    }
}


// -----------------Paginator helpers-----------------------------
if (!function_exists('getPaginationInfo')) {
    function getPaginationInfo($data)
    {
        return [
            'currentPage' => $data->currentPage(),
            'lastPage' => $data->lastPage(),
            'from' => $data->firstItem() ?? 0,
            'to' => $data->lastItem() ?? 0,
            'total' => $data->total(),
            'perPage' => $data->perPage() ?? 15,
            'previousPageUrl' => $data->previousPageUrl() ?? null,
            'nextPageUrl' => $data->nextPageUrl() ?? null,
            'hasPreviousPage' => $data->currentPage() > 1,
            'hasNextPage' => $data->hasMorePages(),
        ];
    }
}
// Validators

if (!function_exists('failedValidation')) {
    function failedValidation($validator)
    {
        return response()->json(['status' => '400', 'errors' => $validator->errors()], 400);
    }
}
// ------------Form Request helpers---------------------

if (!function_exists('failureResponse')) {
    function failureResponse($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => '400',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}


// Getting authenticated USer

if (!function_exists('authUser')) {
    function authUser($guard = null)
    {
        if ($guard) {
            return Auth::guard($guard)->user();
        }
        return Auth::guard('web')->user();
    }
}

if (!function_exists('canAccess')) {
    function canAccess($menuItem)
    {
        $user = authUser('admin');

        $can_access_manu = false;
        $permissions = $user->getAllPermissions();
        if (isSuperAdmin()) {
            return true;
        } else {
            foreach ($permissions as $permission) {
                if (Str::startsWith($permission->name, "$menuItem.")) {
                    $can_access_manu = true;
                    break;
                }
            }
            return $can_access_manu;
        }
    }
}

// Generating Unique ID
// Generate the unique username for the user
if (!function_exists('generateUniqueId')) {
    function generateUniqueId($model, $prefix = 'MNS', $length = 8, $field = 'username')
    {
        $maxAttempts = 10;
        $uniqueId = $prefix . str_pad(random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            if (!$model::where($field, $uniqueId)->exists()) {
                return $uniqueId;
            }

            // If the generated ID already exists, try generating a new one
            $uniqueId = $prefix . str_pad(random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
        }

        throw new \RuntimeException("Unable to generate a unique ID after {$maxAttempts} attempts.");
    }
}


// Generate a slug for a model


// File Upload Handlers---------------
if (!function_exists('uploadFile')) {
    function uploadFile($file, $destinationPath)
    {
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = substr($baseName, 0, 15) . "_" . time() . "." . $file->getClientOriginalExtension();
        $file->move(public_path($destinationPath), $filename);
        return $destinationPath . $filename;
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($path)
    {
        if (file_exists(public_path($path))) {
            unlink(public_path($path));
        }
    }
}

if (!function_exists('replaceFile')) {
    function replaceFile($file, $destinationPath, $oldFilePath = null)
    {
        if ($oldFilePath) {
            deleteFile($oldFilePath);
        }
        return uploadFile($file, $destinationPath);
    }
}
if (!function_exists('getFileExtension')) {
    function getFileExtension($fileUrl)
    {
        $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);
        return $extension;
    }
}
if (!function_exists('isOfficeFile')) {
    function isOfficeFile($fileUrl)
    {
        $extension = getFileExtension($fileUrl);
        return in_array(strtolower($extension), [
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
        ]);
    }
}
if (!function_exists('getFileUrl')) {
    function getFileUrl($fileUrl)
    {
        // Check if the URL has a hostname (absolute URL)
        if (!preg_match('/^(http|https):\/\//', $fileUrl)) {
            $fileUrl = asset($fileUrl); // Convert relative URL to absolute
        }

        return isOfficeFile($fileUrl)
            ? 'https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($fileUrl)
            : $fileUrl;
    }
}
// Enable Disable the Status Column
if (!function_exists('updateStatus')) {
    function updateStatus($model,  $status, $column = 'status')
    {
        $toStatus = $status == 'active' ? false : true;
        return $model->update([$column => $toStatus]);
    }
}

// Check if the request is ajax or not
if (!function_exists('isAjax')) {
    function isAjax($request)
    {
        return $request->ajax() && $request->header('X-Requested-With') === 'XMLHttpRequest';
    }
}


// ---------------------Authorization Helpers---------------
// Checking if the user is a superadmin or not
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin()
    {
        $user = authUser('admin');
        return $user->hasRole('superadmin');
    }
    if (!function_exists('getStateList')) {
        function getStateList()
        {
            // Write Indian State names
            return [
                'Andhra Pradesh',
                'Arunachal Pradesh',
                'Assam',
                'Bihar',
                'Chhattisgarh',
                'Goa',
                'Gujarat',
                'Haryana',
                'Himachal Pradesh',
                'Jammu and Kashmir',
                'Jharkhand',
                'Karnataka',
                'Kerala',
                'Madhya Pradesh',
                'Maharashtra',
                'Manipur',
                'Meghalaya',
                'Mizoram',
                'Nagaland',
                'Odisha',
                'Punjab',
                'Rajasthan',
                'Sikkim',
                'Tamil Nadu',
                'Telangana',
                'Tripura',
                'Uttar Pradesh',
                'Uttarakhand',
                'West Bengal',

            ];
        }
    }
}

if(!function_exists('isEditor')){
    function isEditor()
    {
        $user = authUser('admin');
        return $user->hasRole('Editor');
    }
}
if(!function_exists('isReviewer')){
    function isReviewer()
    {
        $user = authUser('admin');
        return $user->hasRole('Reviewer');
    }
}
if(!function_exists('isPublisher')){
    function isPublisher()
    {
        $user = authUser('admin');
        return $user->hasRole('Publisher');
    }
}


