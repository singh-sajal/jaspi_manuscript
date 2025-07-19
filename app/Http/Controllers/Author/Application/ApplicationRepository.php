<?php

namespace App\Http\Controllers\Author\Application;



use Throwable;
use App\Models\Admin;
use App\Models\Service;
use App\Models\Attachment;
use App\Models\Application;
// use App\Models\Application;
use Spatie\Permission\Models\Role;
use App\Models\ApplicationTimeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use SebastianBergmann\CodeUnit\FunctionUnit;

class ApplicationRepository
{


    protected $basePath = 'uploads/author/attachments/';

    private function getApplication($uuid)
    {
        return Application::where('uuid', $uuid)->first();
    }



    public function index($request)
    {

        $perPage = $request->perPage ?? 15;
        // $applications = Application::query();

        // Get the current logged-in author
        $authorId = auth()->id();

        // Filter applications by the current author
        $applications = Application::where('author_id', $authorId)->with('attachments', 'assignee');


        $this->search($request, $applications);
        $this->sort($request, $applications);
        $applications = $applications->paginate($perPage);
        // return $applications;
        if ($request->ajax()) {

            try {
                $datatable = view('author.cms.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('author.cms.index');
    }

    public function create($request)
    {
        $author = authUser()->id;
        $applications = Application::where('author_id', $author)->get();
        return view('author.cms.create', compact('applications'));
    }


    public function store($request)
    {
        try {
            // Generate unique application ID
            $applicationId = generateUniqueId(Application::class, 'APP', 9, 'application_id');

            $artical_type = $request->article_type ?? [];

            if (!empty($request->article_type_other))
                array_push($artical_type, $request->article_type_other);
            // Prepare application data
            $data = [
                'application_id' => $applicationId,
                'submission_type' => $request->submission_type,
                'article_type' => $artical_type,
                'title' => $request->title,
                'author_id' => auth()->id(),
                'author_affiliation' => $request->author_affiliation,
                'author_orcid_id' => $request->author_orcid_id,
                'author_saspi_id' => $request->author_saspi_id ?? null,
                'co_author_data' => null,
                'description' => $request->description,
                'jaspi_id' => $request->submission_type === 'revised_submission' ? $request->jaspi_id : null,
                'status' => 'incomplete',
                'article_check' =>  $request->article_check ?? [],
            ];
            // return $data;
            // Store the application data in the database
            $application = Application::create($data);
            // return $application;

            // Handle file uploads
            if ($application) {

                return successResponse('Manuscript Submitted Successfully', route('author.application.show', $application->uuid));
            }
            return errorResponse();
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function edit($uuid)
    {
        $author = authUser()->id;
        $applications = Application::where('author_id', $author)->get();
        $app_info = $this->getApplication($uuid);
        return view('author.cms.edit', compact('applications', 'app_info'));
    }

    public function update($request, $uuid)
    {
        try {
            // Find the existing application
            $application = $this->getApplication($uuid);
            $artical_type = $request->article_type ?? [];
            if (($request->article_type_other ?? '') != null)
                $artical_type['other_data'] = $request->article_type_other;
            // Prepare the updated application data
            $data = [
                'submission_type' => $request->submission_type,
                'article_type' => $artical_type,
                'title' => $request->title,
                'author_affiliation' => $request->author_affiliation,
                'author_orcid_id' => $request->author_orcid_id,
                'author_saspi_id' => $request->author_saspi_id ?? null,
                'jaspi_id' => $request->submission_type === 'revised_submission' ? $request->jaspi_id : $application->jaspi_id,
                'description' => $request->description,
                'status' => $request->status ?? $application->status,
                'article_check' =>  $request->article_check ?? [],
            ];

            // Update the application data
            $application->update($data);
            return successResponse('Application Updated Successfully', route('author.application.show', $uuid));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function destroy($uuid)
    {
        try {
            // Find the application by ID
            $application = $this->getApplication($uuid);

            // Delete associated attachments
            $attachments = Attachment::where('application_id', $application->id)->get();
            foreach ($attachments as $attachment) {
                // Delete the actual file from the storage
                deleteFile($attachment->file_url);
                // Remove the attachment record from the database
                $attachment->delete();
            }

            // Delete the application
            $application->delete();

            return successResponse('Application deleted successfully');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }




    public function show($uuid)
    {
        try {

            $application = Application::where('uuid', $uuid)->with([
                'attachments',
                'timelines',
            ])->firstOrFail();
            // return $application;
            $attachments = $application->attachments ?? collect(); // fallback to empty collection
            // return $attachments;
            $manuScriptFiles = $attachments
                ->where('attachment_type', 'Upload Manuscript')
                ->sortBy('created_at');

            $otherfiles = $attachments->where('attachment_type', '!=', 'Upload Manuscript');

            return view('author.cms.show', compact('application', 'manuScriptFiles', 'otherfiles'));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }




    private function search($request, $query)
    {
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('application_id', 'like', "%{$searchTerm}%")
                    ->orWhere('article_type', 'like', "%{$searchTerm}%")
                    ->orWhere('author_affiliation', 'like', "%{$searchTerm}%")
                    ->orWhere('author_orcid_id', 'like', "%{$searchTerm}%")
                    ->orWhere('author_saspi_id', 'like', "%{$searchTerm}%");
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

    public function coAuthor($request, $uuid)
    {
        $author = authUser()->id;
        $application = Application::where('author_id', $author)
            ->where('uuid', $uuid)->first();

        if ($request->method() == 'GET') {
            return view('author.cms.co_author_form', compact('application'));
        } elseif ($request->method() == 'POST') {
            $newCoauthor = [
                'coauthor_id' =>    $request->coauthor_id ?? uniqid(),
                'coauthor_name' =>  $request->coauthor_name,
                'coauthor_email' => $request->coauthor_email,
                'coauthor_phone' => $request->coauthor_phone,
                'coauthor_affiliation' =>  $request->author_affiliation,
                'coauthor_orcid_id' => $request->author_orcid_id,
                'coauthor_saspi_id' => $request->author_saspi_id ?? null,
            ];

            // Decode current JSON to array or create a new array if empty
            $existingData = $application->co_author_data ?? [];

            // Append new co-author
            $existingData[] = $newCoauthor;

            // Save updated JSON
            $application->co_author_data = $existingData;
            $update = $application->save();
            if ($update) {
                return successResponse('Co-author added successfully', route('author.application.show', $uuid));
            } else {
                return errorResponse('Something went wrong');
            }
        } else {
            return errorResponse('Invalid request method');
        }
    }

    public function coAuthorUpdate($request, $uuid, $author_id)
    {
        $author = authUser()->id;
        $application = Application::where('author_id', $author)
            ->where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        $all_author_data = $application->co_author_data ?? [];

        // GET Request: Show Edit Form
        if ($request->method() == 'GET') {
            $author_data = collect($all_author_data)->firstWhere('coauthor_id', $author_id);

            if (!$author_data) {
                return errorResponse('Co-author not found');
            }

            return view('author.cms.co_author_edit_form', compact('uuid', 'author_data'));

            // POST Request: Update Co-author
        } elseif ($request->method() == 'POST') {
            foreach ($all_author_data as &$coauthor) {
                if ($coauthor['coauthor_id'] == $author_id) {
                    $coauthor['coauthor_name']  = $request->coauthor_name;
                    $coauthor['coauthor_email'] = $request->coauthor_email;
                    $coauthor['coauthor_phone'] = $request->coauthor_phone;
                    $coauthor['coauthor_affiliation']  = $request->author_affiliation;
                    $coauthor['coauthor_orcid_id'] = $request->author_orcid_id;
                    $coauthor['coauthor_saspi_id'] = $request->author_saspi_id;
                    break;
                }
            }

            $application->co_author_data = $all_author_data;
            $update = $application->save();

            if ($update) {
                return successResponse('Co-author updated successfully', route('author.application.show', $uuid));
            } else {
                return errorResponse('Something went wrong while updating');
            }
        } else {
            return errorResponse('Invalid request method');
        }
    }

    public function coAuthorDelete($request, $uuid, $author_id)
    {
        try {
            $author = authUser()->id;

            $application = Application::where('author_id', $author)
                ->where('uuid', $uuid)
                ->first();

            if (!$application) {
                return errorResponse('Application not found');
            }

            $coAuthors = $application->co_author_data ?? [];

            // Filter out the co-author to be deleted
            $updatedCoAuthors = array_filter($coAuthors, function ($coauthor) use ($author_id) {
                return $coauthor['coauthor_id'] !== $author_id;
            });

            // Re-index the array
            $updatedCoAuthors = array_values($updatedCoAuthors);

            $application->co_author_data = $updatedCoAuthors;
            $application->save();

            return successResponse('Co-author deleted successfully', route('author.application.show', $uuid));
        } catch (\Exception $e) {
            return errorResponse('Error deleting co-author: ' . $e->getMessage());
        }
    }

    public function fileUpload($request, $id, $type, $app_id)
    {
        $author = authUser()->id;

        // Check if the application exists and belongs to the author
        $application = Application::where('author_id', $author)
            ->where('id', $app_id)
            ->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        $attachment_id = Attachment::where('application_id', $id)->first()->id ?? null;

        if ($request->isMethod('GET')) {

            return view('author.cms.file_upload', compact('id', 'type', 'app_id'));
        }

        if ($request->isMethod('POST')) {
            if (!$request->hasFile('file_upload')) {
                return errorResponse('File not found');
            }

            $validator = Validator::make($request->all(), [
                'file_upload' => 'required|mimes:pdf|max:10240', // max size in KB
            ], [
                'file_upload.required' => 'File is required.',
                'file_upload.mimes'    => 'Only PDF files are allowed.',
                'file_upload.max'      => 'The file size must be less than 10 MB.',
            ]);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first()); // or return all errors using ->all()
            }


            $file = $request->file('file_upload');

            // Extract metadata before moving the file
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();

            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $relativePath = 'uploads/applications/';
            $fullPath = public_path($relativePath);

            // Create directory if it doesn't exist
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0775, true);
            }

            // Move the file
            $file->move($fullPath, $fileName);

            $data = [
                'application_id'   => $app_id,
                'file_name'        => $fileName,
                'file_type'        => $mimeType,
                'file_size'        => $size,
                'file_url'         => $relativePath . $fileName,
                'attachment_type'  => $type,
            ];

            // If id == 'na', create new; otherwise update existing
            if ($id === 'na') {
                $attachment = new Attachment();
                $attachment->uuid = (string) Str::uuid();
                $attachment->fill($data)->save();
            } else {
                $attachment = Attachment::find($id);

                if (!$attachment) {
                    return errorResponse('Attachment not found');
                }

                // Delete old file if exists
                if (!empty($attachment->file_url) && File::exists(public_path($attachment->file_url))) {
                    File::delete(public_path($attachment->file_url));
                }

                $attachment->fill($data)->save();
            }

            return successResponse('File uploaded successfully', route('author.application.show', $application->uuid));
        }

        return errorResponse('Invalid request method');
    }

    public function fileDestroy($app_id, $id)
    {
        $author = authUser()->id;

        // Retrieve the attachment by ID and ensure it belongs to the application
        $attachment = Attachment::find($id);
        // return $id;
        if (!$attachment) {
            return errorResponse('Attachment not found');
        }

        // Delete the file from the server
        if (!empty($attachment->file_url) && File::exists(public_path($attachment->file_url))) {
            File::delete(public_path($attachment->file_url));
        }

        // Remove the attachment record from the database
        if ($attachment->delete()) {

            return successResponse('File deleted successfully', route('author.application.show', $app_id));
        }

        return errorResponse('Error deleting file');
    }

    public function readMistake($uuid)
    {
        $application = Application::uuid($uuid)->with('timelines')->first();

        $timeline = $application->timelines
            ->whereIn('status', ['editor-revise', 'editor-reject'])
            ->sortByDesc('created_at')
            ->first();

        return view('admin.application.readMistake', compact('timeline'));
    }

    public function reviewerReadMore($uuid)
    {
        $application = Application::with('timelines')->where('uuid', $uuid)->first();

        $timeline = $application->timelines()
            ->whereIn('status', ['reviewer-accepts', 'reviewer-revise', 'reviewer-rejects'])
            ->latest()
            ->first();


        return view('author.cms.reviewer_readmore', compact('timeline'));
    }

    public function reSubmission($request, $uuid)
    {

        if ($request->isMethod('GET')) {

            return view('author.cms.authorReSubmitRemark', compact('uuid'));
        }

        if ($request->isMethod('POST')) {
            $application = $this->getApplication($uuid);

            $applicationUpdate = $application->update([
                'submission_type' => 'revised_submission',
                'status' => 'resubmitted',
            ]);
            $timelineUpdate = $application->timelines()->create([
                'application_id' => $application->id,
                'status' => 'resubmitted',
                // 'assigned_to_id' => ,
                'remark' => 'Your application have been re-submitted',
                'data' => [
                    'comment' =>  $request->comment,
                ],
            ]);


            if ($applicationUpdate && $timelineUpdate) {
                return successResponse('Re-submission successfully', route('author.application.show', $uuid));
            }
        }
        return errorResponse('Something went wrong while re-submission');
    }

    public function authorUpdate($uuid)
    {
        $application = Application::uuid($uuid)->with('timelines')->first();
        $timeline = $application->timelines->where('status', 'processing')->first();
        // return $timeline;
        return view('author.cms.authorUpdate', compact('timeline'));
    }


    public function toggleStatus($request, $uuid)
    {
        $application = Application::uuid($uuid)->first();
        $data = [
            'application_id' => $application->id,
            'status' => 'submitted',
            'remark' => 'Initial submission by the author',
        ];
        try {
            $updated = $application->update(['status' => $request->status]);
            $timeline = ApplicationTimeline::create($data);
            if ($updated && $timeline) {
                return  successResponse('Submitted SuccessFully');
            }
            return errorResponse();
        } catch (Throwable $th) {
            return throwException($th);
        }
    }
}
