<?php

namespace App\Http\Controllers\Admin\Application;


use Throwable;
use App\Models\Admin;
use App\Models\Author;
use App\Models\Service;
use App\Models\Attachment;
// use App\Models\Application;
use App\Models\Application;
use Spatie\Permission\Models\Role;
use App\Models\ApplicationTimeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class ApplicationRepository
{


    protected $basePath = 'uploads/author/file/';

    private function getApplication($uuid)
    {
        return Application::where('uuid', $uuid)->first();
    }



    public function index($request)
    {

        $perPage = $request->perPage ?? 15;

        // $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
        //     $q->where('name', 'Editor');
        // })->get();
        $type = $request->type ?? '';

        $applications = Application::query()->with('assignee');
        if ($request->type === 'published') {

            $applications = $applications->where('status', 'published');
            // return $applications;
        } else {

            $applications = $applications->whereNotIn('status', ['incomplete', 'published']);
        }

        if (!isSuperAdmin()) {
            $applications = $applications->where('assigned_to_id', authUser('admin')->id)
                // ->where('status','!=','incomplete')
                ->orWhereHas('timelines', function ($query) {
                    $query->where('assigned_to_id', authUser('admin')->id);
                });
        }


        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {

            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
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
        return view('admin.application.index', compact('type'));
    }

    // public function create($request)
    // {
    //     return view('admin.application.create');
    // }
    // public function store($request)
    // {
    //     try {
    //         $data = [
    //             'application_id' => generateUniqueId(Application::class, 'MNS', 9, 'application_id'),
    //             'title' => $request->title,
    //             'description' => $request->description,
    //             'author_id' => authUser()->id,
    //         ];
    //         if ($request->hasFile('file')) {
    //             $file = $request->file('file');
    //             $data['file_name'] = $file->getClientOriginalName();
    //             $data['file_type'] = $file->getClientOriginalExtension();
    //             $data['file_size'] = $file->getSize();
    //             $data['file_url'] = uploadFile($file, $this->basePath);
    //         }
    //         // return $data;


    //         $application = Application::create($data);
    //         if ($application) {
    //             return successResponse('Manuscript Submitted Successfully');
    //         }
    //         return errorResponse();
    //     } catch (Throwable $th) {
    //         return throwException($th);
    //     }
    // }


    public function show($uuid)
    {

        $application = Application::where('uuid', $uuid)->with([
            'attachments',
            'timelines'
        ])->firstOrFail();
        // return $application;
        $attachments = $application->attachments ?? collect(); // fallback to empty collection
        // return $attachments;
        $manuScriptFiles = $attachments
            ->where('attachment_type', 'Upload Manuscript')
            ->sortBy('created_at');

        $otherfiles = $attachments->where('attachment_type', '!=', 'Upload Manuscript');

        return view('admin.application.show', compact('application', 'manuScriptFiles', 'otherfiles'));
    }

    public function edit($uuid)
    {

        $application = Application::where('uuid', $uuid)->with('attachments')->first();
        // return $application;

        return view('admin.application.editForm', compact('application'));
    }

    public function update($request, $uuid)
    {
        try {
            // Find the existing application
            $application = $this->getApplication($uuid);

            // Prepare the updated application data
            $data = [
                'submission_type' => $request->submission_type,
                'article_type' => $request->article_type ?? [],
                'title' => $request->title,
                'author_affiliation' => $request->author_affiliation,
                'author_orcid_id' => $request->author_orcid_id,
                'author_saspi_id' => $request->author_saspi_id,
                'jaspi_id' => $request->submission_type === 'revised_submission' ? $request->jaspi_id : $application->jaspi_id,
                'description' => $request->description,
                'status' => $request->status ?? $application->status,
            ];

            // Update the application data
            $application->update($data);
            return successResponse('Application Updated Successfully', route('admin.application.show', $uuid));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function destroy($uuid)
    {
        try {
            // Fetch the application
            $application =  $this->getApplication($uuid);

            // Remove the associated file if it exists
            if ($application->file_url) {
                deleteFile($application->file_url);
            }

            // Delete the application record
            if ($application->delete()) {
                return successResponse('Application deleted successfully');
            }

            return errorResponse('Failed to delete the application');
        } catch (Throwable $th) {
            return throwException($th);
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



    public function coAuthor($request, $uuid)
    {
        // $author = authUser()->id;
        $application = Application::where('uuid', $uuid)->first();

        if ($request->method() == 'GET') {
            return view('admin.application.co_author_form', compact('application'));
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
                return successResponse('Co-author added successfully', route('admin.application.show', $uuid));
            } else {
                return errorResponse('Something went wrong');
            }
        } else {
            return errorResponse('Invalid request method');
        }
    }

    public function coAuthorUpdate($request, $uuid, $author_id)
    {
        // $author = authUser()->id;
        $application = Application::where('uuid', $uuid)->first();

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

            return view('admin.application.co_author_edit_form', compact('uuid', 'author_data'));

            // POST Request: Update Co-author
        } elseif ($request->method() == 'POST') {
            foreach ($all_author_data as &$coauthor) {
                if ($coauthor['coauthor_id'] == $author_id) {
                    $coauthor['coauthor_name']  = $request->coauthor_name;
                    $coauthor['coauthor_email'] = $request->coauthor_email;
                    $coauthor['coauthor_phone'] = $request->coauthor_phone;
                    $coauthor['coauthor_affiliation']  = $request->author_affiliation;
                    $coauthor['coauthor_orcid_id'] = $request->author_orcid_id;
                    $coauthor['coauthor_saspi_id'] = $request->author_saspi_id ?? null;
                    break;
                }
            }

            $application->co_author_data = $all_author_data;
            $update = $application->save();

            if ($update) {
                return successResponse('Co-author updated successfully', route('admin.application.show', $uuid));
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
            // $author = authUser()->id;

            $application = Application::where('uuid', $uuid)->first();

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

            return successResponse('Co-author deleted successfully', route('admin.application.show', $uuid));
        } catch (\Exception $e) {
            return errorResponse('Error deleting co-author: ' . $e->getMessage());
        }
    }

    public function fileUpload($request, $id, $type, $app_id)
    {
        // $author = authUser()->id;

        // Check if the application exists and belongs to the author
        $application = Application::where('id', $app_id)->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        $attachment_id = Attachment::where('application_id', $id)->first()->id ?? null;

        if ($request->isMethod('GET')) {

            return view('admin.application.file_upload', compact('id', 'type', 'app_id'));
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

            return successResponse('File uploaded successfully', route('admin.application.show', $application->uuid));
        }

        return errorResponse('Invalid request method');
    }

    public function fileDestroy($app_id, $id)
    {
        // $author = authUser()->id;

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

            return successResponse('File deleted successfully', route('admin.application.show', $app_id));
        }

        return errorResponse('Error deleting file');
    }


    public function assignApplication($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
                $q->where('name', 'Editor');
            })->get();
            return view('admin.application.assignForm', compact('uuid', 'staffs'));
        }
        if ($request->isMethod('POST')) {
            try {
                $application =  $this->getApplication($uuid);
                $data = [
                    'application_id' => $application->id,
                    'assigned_to_id' => $request->staff_id,
                    'remark' => 'Assigned to Editor',
                    'status' => 'processing'
                ];

                $timelineUpdated = ApplicationTimeline::create($data);

                $applicationUpdated = $application->update([
                    'assigned_to_id' => $request->staff_id,
                    'status' => $data['status'],
                ]);

                if ($timelineUpdated && $applicationUpdated) {

                    return successResponse('Application assigned successfully');
                } else
                    return errorResponse();
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
    }

    public function assignReviewer($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
                $q->where('name', 'Reviewer');
            })->get();
            // return $staffs;
            return view('admin.application.assignReviewerForm', compact('uuid', 'staffs'));
        }

        if ($request->isMethod('POST')) {
            try {
                $application = $this->getApplication($uuid);

                $data = [
                    'application_id' => $application->id,
                    'assigned_to_id' => $request->staff_id,
                    'remark' => 'Assigned to Reviewer',
                    'status' => 'under review',
                ];

                // Create timeline entry
                $timelineCreated = ApplicationTimeline::create($data);

                // Update application (status and assigned staff)
                $applicationUpdated = $application->update([
                    'assigned_to_id' => $request->staff_id,
                    'status' => $data['status'],
                ]);

                if ($timelineCreated && $applicationUpdated) {
                    return successResponse(
                        'Application assigned to reviewer successfully',
                        route('admin.application.show', $application->uuid)
                    );
                } else {
                    return errorResponse();
                }
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
    }



    public function acceptance($uuid)
    {
        $application = Application::where('uuid', $uuid)->first();

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'status' => 'accepted',
            'assigned_to_id' => authUser('admin')->id,
            'remark' => 'Your application has been accepted by the editor',
        ]);
        $updated = $application->update([
            'status' => 'accepted',
            'assigned_to_id' => authUser('admin')->id,
        ]);

        if ($timeline && $updated) {
            return successResponse('Application accepted successfully', route('admin.application.show', $application->uuid));
        }
        return errorResponse('Their was an error accepting your application');
    }

    public function rejection($uuid)
    {

        $application = Application::where('uuid', $uuid)->firstOrFail();

        // $reason = $request->review_status == 'significant_rework_required' ? 'The submission is not acceptable in its current form but may be resubmitted after significant rework.' : 'The submission does not meet the requirements or standards and will not be considered further.';

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => authUser('admin')->id,
            'status' => 'rejected',
            'remark' => 'Your application have been rejected',

        ]);

        $applicationUpdate = $application->update([
            'status' => 'rejected',
            'assigned_to_id' => authUser('admin')->id,
        ]);

        if ($timeline && $applicationUpdate) {
            return successResponse('Application rejected successfully', route('admin.application.show', $uuid));
        }
        return errorResponse('Their was an while processing please try again');
        // return errorResponse('Invalid request method');
    }
    public function revise($uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => null,
            'status' => 'revise',
            'remark' => 'Application revision is requested',
        ]);
        $applicationUpdate = $application->update([
            'status' => 'revise',
            'assigned_to_id' => null,
        ]);

        if ($timeline && $applicationUpdate) {
            return successResponse(
                'Application send to revise successfully',
                route('admin.application.show', $application->uuid)
            );
        }
        return errorResponse('Their was an while processing please try again');

        // return errorResponse('Invalid request method');
    }

    public function reviewed($request, $uuid)
    {
        if ($request->isMethod('GET')) {

            return view('admin.application.reviewForm', compact('uuid'));
        }

        if ($request->isMethod('POST')) {

            $application = Application::where('uuid', $uuid)->first();
            $editor = ApplicationTimeline::where('application_id', $application->id)->where('status', 'processing')->first();
            $statusTexts = [
                'Accept' => 'The submission is approved as-is, with no changes required.',
                'Accept with Minor Changes' => 'Approved with only small revisions needed (e.g., typos, formatting, minor clarifications).',
                'Request Revisions with Major Changes' => 'Substantial changes are required before the work can be accepted (e.g., missing content, reorganization, factual updates).',
                'Reject with Suggestions' => 'The submission is not acceptable in its current form but may be resubmitted after significant rework.',
                'Reject' => 'The submission does not meet the requirements or standards and will not be considered further.',
            ];

            $timelineData = [
                'application_id' => $application->id,
                'assigned_to_id' => $editor->assigned_to_id,
                'status' => 'reviewed',
                'remark' => 'Application has been reviewed',
                'data' => [
                    'review_status' => $statusTexts[$request->review_status] ?? 'Unknown decision',
                    'comment' =>  $request->remark,

                ],
            ];

            $updated = $application->update([
                'status' => 'reviewed',
                'assigned_to_id' => $editor->assigned_to_id,
            ]);

            if (ApplicationTimeline::create($timelineData) && $updated) {
                return successResponse('Application reviewed successfully', route('admin.application.show', $application->uuid));
            }
            return errorResponse('Their was an while processing please try again');
        }
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


        return view('admin.application.reviewer_readmore', compact('timeline'));
    }


    public function authorUpdate($uuid)
    {
        $application = Application::uuid($uuid)->with('timelines')->first();
        $timeline = $application->timelines->where('status', 'processing')->first();
        // return $timeline;
        return view('author.cms.authorUpdate', compact('timeline'));
    }

    public function assignPublisher($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
                $q->where('name', 'Publisher');
            })->get();

            return view('admin.application.assignPublisherForm', compact('uuid', 'staffs'));
        }

        if ($request->isMethod('POST')) {
            $application = Application::where('uuid', $uuid)->first();

            $timeline = ApplicationTimeline::create([
                'application_id' => $application->id,
                'status' => 'prepublished',
                'assigned_to_id' => $request->staff_id,
                'remark' => 'Your application has been prepublished',
            ]);
            $updated = $application->update(['status' => 'prepublished']);

            if ($timeline && $updated) {
                return successResponse('Application accepted successfully', route('admin.application.show', $application->uuid));
            }
            return errorResponse('Their was an error accepting your application');
        }
    }

    public function publish($request, $uuid)
    {

        if ($request->isMethod('GET')) {

            return view('admin.application.publishForm', compact('uuid'));
        }

        if ($request->isMethod('POST')) {

            $application = Application::where('uuid', $uuid)->first();

            if ($request->hasFile('final_script')) {

                $file_url = uploadFile($request->final_script, $this->basePath);
                // return $file_url;
                $timeline = ApplicationTimeline::create([
                    'application_id' => $application->id,
                    'status' => 'published',
                    'assigned_to_id' => authUser('admin')->id,
                    'remark' => 'Application has been publised successfully',
                    'data' => [
                        'comment' =>  $request->comment,
                        'file_url' => $file_url
                    ],
                ]);
                $updated = $application->update([
                    'status' => 'published',
                    'data' => [
                        'comment' =>  $request->comment,
                        'file_url' => $file_url
                    ],
                ]);

                if ($timeline && $updated) {
                    return successResponse('Application published successfully', route('admin.application.show', $application->uuid));
                }
            }
            return errorResponse('Their was an error publishing your application');
        }
    }

    public function reviewScore($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            return view('admin.application.reviewScoreForm', compact('uuid'));
        }

        if ($request->isMethod('POST')) {
            $admin = Admin::where('is_admin', true)->first();
            $application = Application::where('uuid', $uuid)->first();
            // return $request;
            $list = [
                1 => [
                    'heading' => 'Q.1 Originality',
                    'question' => 'How original is the concept presented in this article?',
                    'answer' => $request->q1,
                ],
                2 => [
                    'heading' => 'Q.2 Significance',
                    'question' => 'How significant are the article’s conclusions?',
                    'answer' => $request->q2,
                ],
                3 => [
                    'heading' => 'Q.3 Timeliness',
                    'question' => 'How relevant is the article for stewardship or infectious diseases in the contemporary landscape?',
                    'answer' => $request->q3,
                ],
                4 => [
                    'heading' => 'Q.4 Logic',
                    'question' => 'How well-reasoned is the article?',
                    'answer' => $request->q4,
                ],
                5 => [
                    'heading' => 'Q.5 Quality',
                    'question' => 'What is the quality and clarity of writing in the article?',
                    'answer' => $request->q5,
                ],
                6 => [
                    'heading' => 'Q.6 Interest',
                    'question' => 'How interesting is the article?',
                    'answer' => $request->q6,
                ],
                7 => [
                    'heading' => 'Q.7 Methodology Validity',
                    'question' => 'Does the manuscript provide RRB/IEC approval? If applicable, how valid is the research design for the stated objectives, and how appropriate are any statistical techniques applied? (for Original articles, Brief communications, Systematic Review and Meta-Analysis ONLY)',
                    'answer' => !empty($request->q20_hidden) ?  $request->q20_hidden : 'no',
                ],
                8 => [
                    'heading' => 'Q.8 Recommendation',
                    'question' => 'What is your recommendation for the article?',
                    'answer' => $request->q8,
                ],
                9 => [
                    'heading' => 'Q.9 Need for Editorial Commentary',
                    'question' => 'Do you think this article, if accepted, requires commentary or editorial?',
                    'answer' => $request->q9,
                ],
                10 => [
                    'heading' => 'Q.10 Willingness to Write Commentary',
                    'question' => 'Would you like to write a commentary/editorial when the article is accepted?',
                    'answer' => $request->q10,
                ],
                11 => [
                    'heading' => 'Q.11 New Message in Manuscript',
                    'question' => 'Is there a new message from the manuscript?',
                    'answer' => $request->q11,
                ],
                12 => [
                    'heading' => 'Q.12 Priority for Publication',
                    'question' => 'Please rate this paper’s priority for publication.',
                    'answer' => $request->q12,
                ],
                13 => [
                    'heading' => 'Q.13 Need for Reassessment',
                    'question' => 'Do you want to reassess this article?',
                    'answer' => $request->q13,
                ],
                14 => [
                    'heading' => 'Q.14 Competing Interests',
                    'question' => 'Do you have any competing interests?',
                    'answer' => $request->q14 . ' (' . $request->q14_text . ')',
                ],
                15 => [
                    'heading' => 'Q.15 Comments to the Author',
                    'question' => 'Comments to the Author – Manuscript Section Wise (if possible)',
                    'answer' => $request->q15,
                ],
                16 => [
                    'heading' => 'Q.16 Confidential Comments to the Editor',
                    'question' => 'Confidential Comments to the Editor (will not be shared with authors)',
                    'answer' => $request->q16,
                ],
                17 => [
                    'heading' => 'Q.17 Recommendation (For Original Articles, Systematic Review and Meta-Analysis, Brief Communications)',
                    'question' => 'To Decide, Please Add All Scores (In Parentheses) Of Marked Responses For Questions 1 To 7',
                    'answer' => $request->q17,
                ],
                18 => [
                    'heading' => 'Q.18 Recommendation (For Infectious Diseases Cases/Vignettes, Narrative Reviews)',
                    'question' => 'To Decide, Please Add All Scores (In Parentheses) Of Marked Responses For Questions 1 To 7',
                    'answer' => $request->q18,
                ],
                19 => [
                    'heading' => 'Q.19 Confidential Comments to the Admin',
                    'question' => 'Confidential Comments to the Admin (will not be shared with authors)',
                    'answer' => $request->q19,
                ],
            ];

            // Make sure all values are treated as integers
            $sumQ1toQ7 = (int) $request->q1 +
                (int) $request->q2 +
                (int) $request->q3 +
                (int) $request->q4 +
                (int) $request->q5 +
                (int) $request->q6 +
                (int) (!empty($request->q20_hidden) ? $request->q20_hidden : 0);


            if ($sumQ1toQ7 >= 28) {
                $recommendation = 'Accept';
                $status = 'reviewer-accepts';
            } elseif ($sumQ1toQ7 >= 21) {
                $recommendation = 'Minor Revision';
                $status = 'reviewer-revise';
            } elseif ($sumQ1toQ7 >= 14) {
                $recommendation = 'Major Revision';
                $status = 'reviewer-revise';
            } else {
                $recommendation = 'Reject';
                $status = 'reviewer-rejects';
            }




            $timeline = ApplicationTimeline::create([
                'application_id' => $application->id,
                'status' => $status,
                'assigned_to_id' => $admin->id,
                'remark' => 'Application has been reviewed successfully',
                'data' => [
                    'comment' =>  $request->remark,
                    'score' => $sumQ1toQ7,
                    'recommendation' => $recommendation,
                    'review_score' => $list,

                ],
            ]);


            // return $timeline;

            $updated = $application->update([
                'status' => $status,
                // 'data' => [
                //     'comment' =>  $request->remark,
                // ],
            ]);

            // return $list;

            if ($timeline && $updated) {
                return successResponse('Application reviewed successfully', route('admin.application.show', $application->uuid));
            }
            return errorResponse('Something went wrong!');
        }
    }

    public function editorDecision($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
                $q->where('name', 'Reviewer');
            })->get();
            return view('admin.application.editor_decision', compact('uuid', 'staffs'));
        }

        if ($request->isMethod('POST')) {
            $application = Application::where('uuid', $uuid)->first();

            $data = [];

            $decision = $request->decision;

            // return $request;

            switch ($decision) {

                case 1:
                    $data['status'] = 'under review';
                    break;

                case 2:
                    $data['status'] = 'under review';
                    $data['remark'] = $request->comment;
                    break;

                case 3:
                    $data['status'] = 'editor-revise';
                    $data['remark'] = $request->comment;
                    break;

                case 4:
                    $data['status'] = 'editor-reject';
                    $data['remark'] = $request->comment;
                    break;
            }


            $admin = Admin::where('is_admin', true)->first();


            $timeline = ApplicationTimeline::create([
                'application_id' => $application->id,
                'status' => $data['status'],
                'assigned_to_id' => !empty($request->staff_id) ? $request->staff_id : $admin->id,
                'remark' => 'Application has been edited',
                'data' => [
                    'comment' =>  !empty($data['remark']) ? $data['remark'] : '',
                ],
            ]);



            $updated = $application->update([
                'status' => $data['status'],
                'data' => [
                    'comment' =>  !empty($data['remark']) ? $data['remark'] : '',
                ],
            ]);

            // return [$timeline, $updated];

            if ($timeline && $updated) {
                return successResponse('Application edited', route('admin.application.show', $application->uuid));
            }
            return errorResponse('Something went wrong!');
        }
    }
}
