@extends('author.app')
@section('title', 'Manuscript Submission Checklist')
@section('page-title', 'Manuscript Submission Checklist')
@section('breadcrumb-button')
    <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
        <i class="ri-arrow-go-back-line"></i>
    </a>
@endsection

@section('css')
    <style>
        body {
            background: #f9fafb;
        }

        .checklist-container {
            max-width: 900px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 50px auto;
        }

        .checklist-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .question-label {
            font-weight: 500;
        }

        .required {
            color: red;
        }

        .disclaimer-box {
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-top: 40px;
        }

        .disclaimer-box h5 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .disclaimer-box ol {
            counter-reset: item;
            margin-left: 0px;
            padding-left: 25px;
        }

        .disclaimer-box ol>li {
            display: block;
            position: relative;
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .disclaimer-box ol>li::before {
            content: counter(item) ". ";
            counter-increment: item;
            font-weight: bold;
            position: absolute;
            left: -25px;
        }


        .disclaimer-box p {
            margin-bottom: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="checklist-container" style="padding-left: 50px;">
        <div class="checklist-header">
            <h3>Manuscript Submission Checklist</h3>
            <p>
                To begin with the submission of your manuscript, please complete the following submission checklist
                questions and ensure all requirements are met according to the instructions on the journal website.
            </p>
            <h6>CHECKLIST QUESTIONS</h6>
            <p class="text-muted">* Denotes required field</p>
        </div>

        <form id="checklistForm" action="{{ route('author.application.check_list') }}" noAjax method="post">
            @method('POST')
            @csrf
            <div class="row mb-3">
                <div class="col-8">
                    <label class="form-label question-label">
                        * Please be aware that you will be requested to recommend a minimum of two reviewers during the
                        journal's submission process. Kindly confirm whether you would like to proceed with submitting your
                        recommendations alongside the manuscript.
                    </label>
                </div>
                <div class="col-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="reviewer_recommendation" value="yes"
                            required>
                        <label class="form-check-label">Yes</label>
                    </div>
                </div>
            </div>

            {{-- Checklist Questions --}}
            @php
                $questions = [
                    '1. <b>Author guidelines –</b> Have you read and followed the journal’s author instructions?' =>
                        'guidelines',
                    '2. <b>Authorship approval –</b> Has every listed author seen, reviewed, and approved the final version of this manuscript?' =>
                        'authorship_approval',
                    '3. <b>Ethical compliance & conflicts of interest –</b> Does your manuscript comply with relevant ethical standards and institutional approvals? Have you declared any potential conflicts of interest? Have you documented CTRI number if clinical trial and institutional ethics committee approval number in the manuscript?' =>
                        'ethics',
                    '4. <b>Patient or participant data & consent –</b> Does your submission include data or images from patients or study participants? If yes, have you obtained and documented appropriate informed consent?' =>
                        'patient_consent',
                    '5. <b>Tables –</b> Are all tables numbered and cited in the text in the correct order? Have you indicated whether each table is original or adapted (with proper source details)?' =>
                        'tables',
                    '6. <b>Figures –</b> Are all figures numbered, have legends/headings, and cited correctly in the text? Have you indicated whether each figure is original or reproduced (with source details)?' =>
                        'figures',
                    '7. <b>AI use disclosure –</b> Have you included the statement “Declaration of generative AI and AI-assisted technologies in the writing process” in your manuscript, if any AI tools were used?' =>
                        'ai_disclosure',
                ];
            @endphp

            @foreach ($questions as $question => $name)
                <div class="row mb-3">
                    <div class="col-8">
                        <label class="form-label question-label">{!! $question !!}</label>
                    </div>
                    <div class="col-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="{{ $name }}" value="Yes"
                                required>
                            <label class="form-check-label">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="{{ $name }}" value="No">
                            <label class="form-check-label">No</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="{{ $name }}" value="NA">
                            <label class="form-check-label">N/A</label>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Full Disclaimer (non-scrollable) --}}
            <div class="disclaimer-box">
                <h5>DISCLAIMER</h5>
                <p><strong>On behalf of all contributors, I declare the following:</strong></p>
                <ol>
                    <li><strong>Authorship Contribution:</strong> All listed authors have made significant contributions to
                        the study’s concept, design, data collection, analysis, or interpretation, and have participated in
                        drafting or revising the manuscript. Only faculty/consultant of the concerned original articles
                        including case report are allowed to be first/corresponding author.</li>
                    <li><strong>Approval and Responsibility:</strong> Each author has reviewed and approved the final
                        version of the manuscript and agrees to be publicly accountable for the accuracy and integrity of
                        the work.</li>
                    <li><strong>Originality:</strong> This manuscript is original and has not been published, nor is it
                        under consideration for publication elsewhere, except as disclosed in the cover letter.</li>
                    <li><strong>Data Integrity:</strong> All data collected for this study are included in this manuscript.
                        No part of the data has been or will be published separately. <br>
                        We agree to provide the original data or supporting documents to the editors if requested.
                    </li>
                    <li><strong>Conflicts of Interest and Funding:</strong> All financial or other potential conflicts of
                        interest have been fully disclosed in the cover letter.<br>
                        All sources of financial or material support have also been acknowledged.
                    </li>
                    <li><strong>Copyright and Licensing:</strong> The authors retain copyright of this article. Upon
                        publication, the work will be distributed under the Creative Commons
                        Attribution–NonCommercial–NoDerivatives 4.0 International License (CC BY-NC-ND 4.0).<br>
                        This license allows others to share the work with proper citation, for non-commercial purposes only,
                        and without alteration.<br>
                        The journal holds the right to publish, archive, and disseminate the article in print or electronic
                        formats.
                    </li>
                    <li><strong>Corresponding Author Authority:</strong> All contributors authorize the corresponding author
                        to communicate with the journal, respond to editorial requests, make necessary corrections, and act
                        as guarantor of the manuscript on behalf of all authors.</li>
                    <li><strong>Acknowledgments:</strong> All individuals who contributed substantially to the work but are
                        not listed as authors are named in the Acknowledgment section, with their permission. <br>
                        If no such acknowledgment is included, it indicates no additional contributors were involved.
                    </li>
                </ol>
            </div>


            {{-- Agreement + Submit --}}
            {{-- ... inside your form ... --}}

            <div class="text-center mt-4">
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" id="agreeDisclaimer">
                    <label class="form-check-label" for="agreeDisclaimer">
                        I have read & agree to the above Disclaimer
                    </label>
                </div>
                <br><br>
                {{-- ADDED 'id' AND 'disabled' ATTRIBUTE HERE --}}
                <button type="submit" id="btnSubmit" class="btn btn-primary" disabled>Proceed to Submission</button>
            </div>
        </form>
    </div>
@endsection

@section('javascripts')
    <script>
        $(document).ready(function() {
            // Elements
            const $form = $('#checklistForm');
            const $submitBtn = $('#btnSubmit');
            const $disclaimer = $('#agreeDisclaimer');

            // This function checks the state of the form
            function validateChecklist() {
                let isValid = true;

                // 1. Check if Disclaimer is checked
                if (!$disclaimer.is(':checked')) {
                    isValid = false;
                }

                // 2. Check if all Radio Groups have a selection
                // We get all unique names of radio buttons inside the form
                let radioNames = [];
                $form.find('input[type="radio"]').each(function() {
                    let name = $(this).attr('name');
                    if (radioNames.indexOf(name) === -1) {
                        radioNames.push(name);
                    }
                });

                // Loop through each group name and check if at least one option is selected
                for (let i = 0; i < radioNames.length; i++) {
                    let groupName = radioNames[i];
                    if ($('input[name="' + groupName + '"]:checked').length === 0) {
                        isValid = false;
                        break; // Stop checking if we find one missing
                    }
                }

                // 3. Enable or Disable button based on result
                if (isValid) {
                    $submitBtn.prop('disabled', false);
                } else {
                    $submitBtn.prop('disabled', true);
                }
            }

            // Listen for changes on any input (radio or checkbox) within the form
            $form.on('change', 'input', function() {
                validateChecklist();
            });

            // Run once on page load (in case of browser back-button or auto-fill)
            validateChecklist();
        });
    </script>
@endsection
