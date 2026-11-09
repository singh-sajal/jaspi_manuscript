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
            'perPage' => $data->perPage() ?? 50,
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

// Counting manuscripts per status (mirrors the application index visibility rules)
if (!function_exists('manuscriptCounts')) {
    function manuscriptCounts()
    {
        $user = authUser('admin');

        $isStaff = $user && $user->is_admin == 0;

        $query = \App\Models\Application::query()->selectRaw('status, COUNT(*) as total')->groupBy('status');

        if ($isStaff) {
            // Same rule as the list scopes so the sidebar badge and the list
            // can never disagree.
            $query->involvedWith($user);
        }

        $counts = $query->pluck('total', 'status')->map(fn ($total) => (int) $total);

        $excluded = collect(['incomplete', 'published', 'rejected', 'withdrawn', 'duplicate']);

        // Mirrors ApplicationRepository::index(): acceptance ends a handling
        // editor's / reviewer's active work before the manuscript is published.
        if ($isStaff) {
            $excluded = $excluded->merge(['pre-accepted', 'accepted']);
        }

        $all = $isStaff
            ? \App\Models\Application::query()->activeFor($user)->count()
            : $counts->reject(fn ($total, $status) => $excluded->contains($status))->sum();

        $pending = 0;
        if ($user) {
            $pending = \App\Models\Application::query()->pendingFor($user)->count();
        }

        $completed = 0;
        $closed = 0;
        if ($user) {
            $completed = \App\Models\Application::query()->completedFor($user)->count();
            $closed = \App\Models\Application::query()->closedFor($user)->count();
        }

        $mine = 0;
        if ($user && canViewMyManuscripts()) {
            $mine = \App\Models\Application::query()->myManuscripts($user)->count();
        }

        // Inactive is only surfaced when the admin has the explicit permission,
        // so only pay for the query when it will actually be rendered. This
        // helper runs on every admin page render.
        $inactive = 0;
        if ($user && canViewInactiveApplications()) {
            $inactive = \App\Models\Application::query()->inactive()->count();
        }

        // Chief approval queue: only surfaced for the superadmin (chief) role.
        $chief = 0;
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
            $chief = \App\Models\Application::query()
                ->whereIn('status', ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects', 'editor-revise'])
                ->where('data->status', 'sended to chief approval')
                ->count();
        }

        // Review Deadlines: admins + handling editors only.
        $reviewDeadline = 0;
        if ($user && canViewReviewDeadlines()) {
            $reviewDeadline = \App\Http\Controllers\Admin\Application\ReviewDeadlineRepository::overdueCountFor($user);
        }

        return [
            'all' => $all,
            'pending' => $pending,
            'completed' => $completed,
            'closed' => $closed,
            'mine' => $mine,
            'published' => $counts->get('published', 0),
            'rejected' => $counts->get('rejected', 0),
            'withdrawn' => $counts->get('withdrawn', 0),
            'duplicate' => $counts->get('duplicate', 0),
            'inactive' => $inactive,
            'chief' => $chief,
            'review_deadline' => $reviewDeadline,
        ];
    }
}

// Whether the current admin may see the Inactive section. This is an
// admin-only tool, and the role must also carry the explicit permission so a
// technical editor cannot see it merely by being flagged as an admin.
if (!function_exists('canViewInactiveApplications')) {
    function canViewInactiveApplications()
    {
        $user = authUser('admin');

        return $user && $user->is_admin == 1 && $user->can('application.inactive');
    }
}

// Whether the current admin may see the "Review Deadlines" section: the role
// must carry the explicit permission, and the user must be an admin or a
// handling editor.
if (!function_exists('canViewReviewDeadlines')) {
    function canViewReviewDeadlines()
    {
        return \App\Http\Controllers\Admin\Application\ReviewDeadlineRepository::canView(authUser('admin'));
    }
}

/**
 * Normalise a stored DOI into a working link target.
 *
 * The `published_doi` column is free text: some rows hold a full URL
 * (https://doi.org/10.xxxx/yyy), others hold a bare DOI (10.xxxx/yyy) and a
 * few hold something that is not a DOI at all. Only the first two can be
 * turned into a working link, so null is returned for anything else and the
 * caller should render plain text instead of an anchor.
 */
if (!function_exists('doiLinkUrl')) {
    function doiLinkUrl($doi)
    {
        $doi = trim((string) $doi);

        if ($doi === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $doi)) {
            return $doi;
        }

        // Bare DOI, e.g. 10.62541/jaspi001
        if (preg_match('#^10\.\d{4,9}/\S+$#i', $doi)) {
            return 'https://doi.org/' . $doi;
        }

        return null;
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
// Admin-style manuscript queue. Assistant Editors are not flagged as
// `is_admin` in the database, but they still need the personal queue.
if (!function_exists('canViewMyManuscripts')) {
    function canViewMyManuscripts()
    {
        $user = authUser('admin');

        return $user && (
            $user->is_admin == 1
            || (method_exists($user, 'hasRole') && $user->hasRole('assitantEditor'))
        );
    }
}

if(!function_exists('isPublisher')){
    function isPublisher()
    {
        $user = authUser('admin');
        return $user->hasRole('Publisher');
    }
}
if(!function_exists('isAssistantEditor')){
    function isAssistantEditor()
    {
        $user = authUser('admin');
        return $user->hasRole('assitantEditor');
    }
}
if(!function_exists('isTechnicalEditor')){
    function isTechnicalEditor()
    {
        $user = authUser('admin');
        return $user->hasRole('technicalEditor');
    }
}




