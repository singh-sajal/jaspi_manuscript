<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Upload file: Add/Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        @php
            $application = \App\Models\Application::find($app_id);

            $isProofreading = $application && $application->status === 'proofreading';

            $url = $isProofreading
                ? route('author.application.corrected_proof', [
                    'id' => $id ?? 'na',
                    'type' => $type,
                    'app_id' => $app_id,
                    'legend' => $legend,
                ])
                : route('author.application.file_upload', [
                    'id' => $id ?? 'na',
                    'type' => $type,
                    'app_id' => $app_id,
                    'legend' => $legend,
                ]);
        @endphp
        <form novalidate action="{{ $url }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        {{-- <label for="file_upload "
                            class="form-label {{ $type !== 'Supplimentry File' ? 'required' : '' }}">Upload File :
                            {{ $type ?? '' }}</label> --}}
                        <label
                            class="form-label {{ in_array($type, ['Cover Letter', 'First Page', 'Upload Manuscript', 'Upload ICMJE Declaration', 'Plagiarism & AI Check Report', 'Rebuttal Letter', 'Highlighted Manuscript']) ? 'required' : '' }}">Upload
                            File :
                            {{ $type ?? '' }}</label>

                        @if ($type == 'Rebuttal Letter')
                            <input type="file" name="file_upload" id="file_upload" class="form-control" required
                                accept=".pdf,.docx,.doc" data-max-size="10240" data-file-types="pdf,docx">
                            <div class="form-text">List the changes you have made in response to the reviewer comments.</div>
                        @elseif ($type == 'Highlighted Manuscript')
                            <input type="file" name="file_upload" id="file_upload" class="form-control" required
                                accept=".pdf,.docx,.doc" data-max-size="10240" data-file-types="pdf,docx">
                            <div class="form-text">Upload the revised manuscript with the changed areas highlighted.</div>
                        @elseif ($type == 'Upload ICMJE Declaration')
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".pdf"
                                data-max-size="10240" data-file-types="pdf">
                        @elseif ($type == 'Upload Manuscript')
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".docx"
                                data-max-size="10240" data-file-types="docx">
                        @elseif ($type == 'Plagiarism & AI Check Report')
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".pdf"
                                data-max-size="10240" data-file-types="pdf">
                        @else
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".pdf,.docx,.doc"
                                data-max-size="10240" data-file-types="pdf,docx">
                        @endif
                        <span class="text-danger" data-error="file_upload"></span>

                        {{-- <input type="text" name="legend" id="legend" class="form-control mt-3"
                            placeholder="Legend" value="{{ $legend == 'na' ? '' : $legend }}"> --}}
                    </div>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
