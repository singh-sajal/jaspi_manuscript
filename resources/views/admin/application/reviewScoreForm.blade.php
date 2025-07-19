    <style>
        body {
            background-color: #f8f9fa;
        }

        .question-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .question-title {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .radio-group {
            margin-bottom: 15px;
        }

        .form-label {
            font-weight: 500;
            color: #34495e;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        #question-7 {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f1f3f5;
            border-radius: 6px;
        }

        .submit-btn {
            background-color: #1e90ff;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #1877f2;
        }
    </style>
    <style>
        .score-display {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Score for the Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.application.reviewScore', ['uuid' => $uuid]) }}" method="post" novalidate
                enctype="multipart/form-data">
                @csrf

                <div class="card-body">
                    {{-- <div class="score-display">Total Score: <span id="totalScore">0</span></div> --}}
                    <div class="row">
                        <h5>Article Review Form</h5>
                        <!-- Q1 Originality -->
                        <div class="question-section">
                            <div class="score-display">Total Score: <span id="totalScore">0</span></div>
                            <h3 class="question-title">Q.1 Originality</h3>
                            <h5 class="form-label">How original is the concept presented in this article?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" id="q1-1"
                                        value="1" required>
                                    <label class="form-check-label" for="q1-1">Not original (1)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" id="q1-2"
                                        value="2" required>
                                    <label class="form-check-label" for="q1-2">Somewhat Original (2)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" id="q1-3"
                                        value="3" required>
                                    <label class="form-check-label" for="q1-3">Moderately Original (3)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" id="q1-4"
                                        value="4" required>
                                    <label class="form-check-label" for="q1-4">Very Original (4)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" id="q1-5"
                                        value="5" required>
                                    <label class="form-check-label" for="q1-5">Highly Original (5)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q1_hidden" id="q1_hidden">
                        </div>

                        <!-- Q2 Significance -->
                        <div class="question-section">
                            <h3 class="question-title">Q.2 Significance</h3>
                            <h5 class="form-label">How significant are the article’s conclusions?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" id="q2-1"
                                        value="1" required>
                                    <label class="form-check-label" for="q2-1">Insignificant (1)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" id="q2-2"
                                        value="2" required>
                                    <label class="form-check-label" for="q2-2">Significant (2)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" id="q2-3"
                                        value="3" required>
                                    <label class="form-check-label" for="q2-3">Moderately significant (3)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" id="q2-4"
                                        value="4" required>
                                    <label class="form-check-label" for="q2-4">Highly significant (4)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" id="q2-5"
                                        value="5" required>
                                    <label class="form-check-label" for="q2-5">Extremely significant (5)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q2_hidden" id="q2_hidden">
                        </div>

                        <!-- Q3 Timeliness -->
                        <div class="question-section">
                            <h3 class="question-title">Q.3 Timeliness</h3>
                            <h5 class="form-label">How relevant is the article for stewardship or infectious diseases
                                in the contemporary landscape?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" id="q3-1"
                                        value="1" required>
                                    <label class="form-check-label" for="q3-1">Irrelevant (1)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" id="q3-2"
                                        value="2" required>
                                    <label class="form-check-label" for="q3-2">Somewhat relevant (2)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" id="q3-3"
                                        value="3" required>
                                    <label class="form-check-label" for="q3-3">Moderately relevant (3)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" id="q3-4"
                                        value="4" required>
                                    <label class="form-check-label" for="q3-4">Highly relevant (4)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" id="q3-5"
                                        value="5" required>
                                    <label class="form-check-label" for="q3-5">Extremely relevant (5)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q3_hidden" id="q3_hidden">
                        </div>

                        <!-- Q4 Logic -->
                        <div class="question-section">
                            <h3 class="question-title">Q.4 Logic`</h3>
                            <h5 class="form-label">How well-reasoned is the article?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" id="q4-1"
                                        value="1" required>
                                    <label class="form-check-label" for="q4-1">Poorly reasoned (1)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" id="q4-2"
                                        value="2" required>
                                    <label class="form-check-label" for="q4-2">Somewhat well-reasoned (2)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" id="q4-3"
                                        value="3" required>
                                    <label class="form-check-label" for="q4-3">Moderately well-reasoned
                                        (3)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" id="q4-4"
                                        value="4" required>
                                    <label class="form-check-label" for="q4-4">Highly well-reasoned (4)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" id="q4-5"
                                        value="5" required>
                                    <label class="form-check-label" for="q4-5">Extremely well-reasoned (5)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q4_hidden" id="q4_hidden">
                        </div>

                        <!-- Q5 Quality -->
                        <div class="question-section">
                            <h3 class="question-title">Q.5 Quality</h3>
                            <h5 class="form-label">What is the quality and clarity of writing in the article?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q5" id="q5-1"
                                        value="1" required>
                                    <label class="form-check-label" for="q5-1">Poorly written (1)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q5" id="q5-2"
                                        value="2" required>
                                    <label class="form-check-label" for="q5-2">Somewhat well-written (2)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q5" id="q5-3"
                                        value="3" required>
                                    <label class="form-check-label" for="q5-3">Moderately well-written (3)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q5" id="q5-4"
                                        value="4" required>
                                    <label class="form-check-label" for="q5-4">Highly well-written (4)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q5" id="q5-5"
                                        value="5" required>
                                    <label class="form-check-label" for="q5-5">Extremely well-written (5)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q5_hidden" id="q5_hidden">
                        </div>

                        <!-- Q6 Interest -->
                        <div class="question-section">
                            <h3 class="question-title">Q.6 Interest</h3>
                            <h5 class="form-label">How interesting is the article?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q6" id="q6-1"
                                        value="1" required>
                                    <label class="form-check-label" for="q6-1">Not Interesting (1)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q6" id="q6-2"
                                        value="2" required>
                                    <label class="form-check-label" for="q6-2">Somewhat interesting (2)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q6" id="q6-3"
                                        value="3" required>
                                    <label class="form-check-label" for="q6-3">Moderately interesting (3)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q6" id="q6-4"
                                        value="4" required>
                                    <label class="form-check-label" for="q6-4">Very interesting (4)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q6" id="q6-5"
                                        value="5" required>
                                    <label class="form-check-label" for="q6-5">Fascinating (5)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q6_hidden" id="q6_hidden">
                        </div>

                        <!-- Q7 Methodology Validity -->
                        {{-- <div class="question-section">
                            <h3 class="question-title">Q.7 Methodology Validity</h3>
                            <h5 class="form-label">Does the manuscript provide RRB/IEC approval? If applicable, how
                                valid is the research design for the stated objectives, and how appropriate are any
                                statistical techniques applied? (for Original articles, Brief communications, Systematic
                                Review and Meta-Analysis ONLY)</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q7" id="q7-yes"
                                        value="yes" onclick="displayoption()" required>
                                    <label class="form-check-label" for="q7-yes">Yes (mandatory for all human/animal
                                        research)</label>
                                </div>
                                <div id="question-7">
                                    <div class="radio-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-1" value="1" required>
                                            <label class="form-check-label" for="q20-1">Inappropriate (1)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-2" value="2" required>
                                            <label class="form-check-label" for="q20-2">Somewhat appropriate
                                                (2)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-3" value="3" required>
                                            <label class="form-check-label" for="q20-3">Moderately appropriate
                                                (3)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-4" value="4" required>
                                            <label class="form-check-label" for="q20-4">Appropriate (4)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-5" value="5" required>
                                            <label class="form-check-label" for="q20-5">Highly Appropriate
                                                (5)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q7" id="q7-no"
                                        value="no" onclick="displayoption2()" required>
                                    <label class="form-check-label" for="q7-no">No</label>
                                </div>
                            </div>
                            <input type="hidden" name="q7_hidden" id="q7_hidden">
                            <input type="hidden" name="q20_hidden" id="q20_hidden">
                        </div> --}}
                        <!-- Q7 Methodology Validity -->
                        <div class="question-section">
                            <h3 class="question-title">Q.7 Methodology Validity</h3>
                            <h5 class="form-label">Does the manuscript provide RRB/IEC approval? If applicable, how
                                valid is the research design for the stated objectives, and how appropriate are any
                                statistical techniques applied? (for Original articles, Brief communications, Systematic
                                Review and Meta-Analysis ONLY)</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q7" id="q7-yes"
                                        value="yes" onclick="displayoption()" required>
                                    <label class="form-check-label" for="q7-yes">Yes (mandatory for all human/animal
                                        research)</label>
                                </div>
                                <div id="question-7" style="display: none;">
                                    <div class="radio-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-1" value="1">
                                            <label class="form-check-label" for="q20-1">Inappropriate (1)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-2" value="2">
                                            <label class="form-check-label" for="q20-2">Somewhat appropriate
                                                (2)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-3" value="3">
                                            <label class="form-check-label" for="q20-3">Moderately appropriate
                                                (3)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-4" value="4">
                                            <label class="form-check-label" for="q20-4">Appropriate (4)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q20"
                                                id="q20-5" value="5">
                                            <label class="form-check-label" for="q20-5">Highly Appropriate
                                                (5)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q7" id="q7-no"
                                        value="no" onclick="displayoption2()" required>
                                    <label class="form-check-label" for="q7-no">No</label>
                                </div>
                            </div>
                            <input type="hidden" name="q7_hidden" id="q7_hidden">
                            <input type="hidden" name="q20_hidden" id="q20_hidden">
                        </div>

                        {{-- <!-- Q19 Confidential Comments to the Admin -->
                        <div class="question-section">
                            <h3 class="question-title">Q.19 Confidential Comments to the Admin</h3>
                            <h5 class="form-label">Confidential Comments to the Admin (will not be shared with authors)
                            </h5>
                            <textarea class="form-control" name="q19" id="question-19" required></textarea>
                            <input type="hidden" name="q19_hidden" id="q19_hidden">
                        </div> --}}

                        <!-- Q8 Recommendation -->
                        <div class="question-section">
                            <h3 class="question-title">Q.8 Recommendation</h3>
                            <h5 class="form-label">What is your recommendation for the article?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q8" id="q8-accept"
                                        value="accept" required disabled>
                                    <label class="form-check-label" for="q8-accept">Accept (28-35)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q8" id="q8-minor"
                                        value="minor" required disabled>
                                    <label class="form-check-label" for="q8-minor">Minor Revision (21-27)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q8" id="q8-major"
                                        value="major" required disabled>
                                    <label class="form-check-label" for="q8-major">Major Revision (14-20)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q8" id="q8-reject"
                                        value="reject" required disabled>
                                    <label class="form-check-label" for="q8-reject">Reject (UNDER 14)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q8_hidden" id="q8_hidden">
                        </div>

                        <!-- Q9 Need for Editorial Commentary -->
                        <div class="question-section">
                            <h3 class="question-title">Q.9 Need for Editorial Commentary</h3>
                            <h5 class="form-label">Do you think this article, if accepted, requires commentary or
                                editorial?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q9" id="q9-yes"
                                        value="yes" required>
                                    <label class="form-check-label" for="q9-yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q9" id="q9-no"
                                        value="no" required>
                                    <label class="form-check-label" for="q9-no">No</label>
                                </div>
                            </div>
                            <input type="hidden" name="q9_hidden" id="q9_hidden">
                        </div>

                        <!-- Q10 Willingness to Write Commentary -->
                        <div class="question-section">
                            <h3 class="question-title">Q.10 Willingness to Write Commentary</h3>
                            <h5 class="form-label">Would you like to write a commentary/editorial when the article is
                                accepted?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q10" id="q10-yes"
                                        value="yes" required>
                                    <label class="form-check-label" for="q10-yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q10" id="q10-no"
                                        value="no" required>
                                    <label class="form-check-label" for="q10-no">No</label>
                                </div>
                            </div>
                            <input type="hidden" name="q10_hidden" id="q10_hidden">
                        </div>

                        <!-- Q11 New Message in Manuscript -->
                        <div class="question-section">
                            <h3 class="question-title">Q.11 New Message in Manuscript</h3>
                            <h5 class="form-label">Is there a new message from the manuscript?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q11" id="q11-yes"
                                        value="yes" required>
                                    <label class="form-check-label" for="q11-yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q11" id="q11-no"
                                        value="no" required>
                                    <label class="form-check-label" for="q11-no">No</label>
                                </div>
                            </div>
                            <input type="hidden" name="q11_hidden" id="q11_hidden">
                        </div>

                        <!-- Q12 Priority for Publication -->
                        <div class="question-section">
                            <h3 class="question-title">Q.12 Priority for Publication</h3>
                            <h5 class="form-label">Please rate this paper’s priority for publication.</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q12" id="q12-high"
                                        value="high" required>
                                    <label class="form-check-label" for="q12-high">High</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q12" id="q12-medium"
                                        value="medium" required>
                                    <label class="form-check-label" for="q12-medium">Medium</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q12" id="q12-low"
                                        value="low" required>
                                    <label class="form-check-label" for="q12-low">Low</label>
                                </div>
                            </div>
                            <input type="hidden" name="q12_hidden" id="q12_hidden">
                        </div>

                        <!-- Q13 Need for Reassessment -->
                        <div class="question-section">
                            <h3 class="question-title">Q.13 Need for Reassessment</h3>
                            <h5 class="form-label">Do you want to reassess this article?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q13" id="q13-yes"
                                        value="yes" required>
                                    <label class="form-check-label" for="q13-yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q13" id="q13-no"
                                        value="no" required>
                                    <label class="form-check-label" for="q13-no">No</label>
                                </div>
                            </div>
                            <input type="hidden" name="q13_hidden" id="q13_hidden">
                        </div>

                        <!-- Q14 Competing Interests -->
                        {{-- <div class="question-section">
                            <h3 class="question-title">Q.14 Competing Interests</h3>
                            <h5 class="form-label">Do you have any competing interests?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q14" id="q14-yes"
                                        value="yes" required>
                                    <label class="form-check-label" for="q14-yes">I declare competing
                                        interests</label>
                                </div>
                                <div class="form-group mt-2">
                                    <label for="question-14" class="form-label">If yes, please specify</label>
                                    <textarea class="form-control" name="q14_text" id="question-14" required></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q14" id="q14-no"
                                        value="no" required>
                                    <label class="form-check-label" for="q14-no">I declare that I have no competing
                                        interests</label>
                                </div>
                            </div>
                            <input type="hidden" name="q14_hidden" id="q14_hidden">
                        </div> --}}

                        <!-- Q14 Competing Interests -->
                        <div class="question-section">
                            <h3 class="question-title">Q.14 Competing Interests</h3>
                            <h5 class="form-label">Do you have any competing interests?</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q14" id="q14-yes"
                                        value="yes" required>
                                    <label class="form-check-label" for="q14-yes">I declare competing
                                        interests</label>
                                </div>
                                <div class="form-group mt-2" id="q14-textarea" style="display: none;">
                                    <label for="question-14" class="form-label">If yes, please specify</label>
                                    <textarea class="form-control" name="q14_text" id="question-14"></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q14" id="q14-no"
                                        value="no" required>
                                    <label class="form-check-label" for="q14-no">I declare that I have no competing
                                        interests</label>
                                </div>
                            </div>
                            <input type="hidden" name="q14_hidden" id="q14_hidden">
                        </div>

                        <!-- Q15 Comments to the Author -->
                        <div class="question-section">
                            <h3 class="question-title">Q.15 Comments to the Author</h3>
                            <h5 class="form-label">Comments to the Author – Manuscript Section Wise (if possible)</h5>
                            <textarea class="form-control" name="q15" id="question-15" required></textarea>
                        </div>

                        <!-- Q16 Confidential Comments to the Editor -->
                        <div class="question-section">
                            <h3 class="question-title">Q.16 Confidential Comments to the Editor</h3>
                            <h5 class="form-label">Confidential Comments to the Editor (will not be shared with
                                authors)</h5>
                            <textarea class="form-control" name="q16" id="question-16" required></textarea>
                        </div>

                        <!-- Q17 Recommendation (Original Articles, etc.) -->
                        <div class="question-section">
                            <h3 class="question-title">Q.17 Recommendation (For Original Articles, Systematic Review
                                and Meta-Analysis, Brief Communications)</h3>
                            <h5 class="form-label">To Decide, Please Add All Scores (In Parentheses) Of Marked
                                Responses For Questions 1 To 7</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q17" id="q17-accept"
                                        value="accept" required>
                                    <label class="form-check-label" for="q17-accept">Accept (28-35)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q17" id="q17-minor"
                                        value="minor" required>
                                    <label class="form-check-label" for="q17-minor">Minor Revision (21-27)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q17" id="q17-major"
                                        value="major" required>
                                    <label class="form-check-label" for="q17-major">Major Revision (14-20)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q17" id="q17-reject"
                                        value="reject" required>
                                    <label class="form-check-label" for="q17-reject">Reject (UNDER 14)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q17_hidden" id="q17_hidden">
                        </div>

                        <!-- Q18 Recommendation (Infectious Diseases Cases, etc.) -->
                        <div class="question-section">
                            <h3 class="question-title">Q.18 Recommendation (For Infectious Diseases Cases/Vignettes,
                                Narrative Reviews)</h3>
                            <h5 class="form-label">To Decide, Please Add All Scores (In Parentheses) Of Marked
                                Responses For Questions 1 To 7</h5>
                            <div class="radio-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q18" id="q18-accept"
                                        value="accept" required>
                                    <label class="form-check-label" for="q18-accept">Accept (28-35)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q18" id="q18-minor"
                                        value="minor" required>
                                    <label class="form-check-label" for="q18-minor">Minor Revision (21-27)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q18" id="q18-major"
                                        value="major" required>
                                    <label class="form-check-label" for="q18-major">Major Revision (14-20)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q18" id="q18-reject"
                                        value="reject" required>
                                    <label class="form-check-label" for="q18-reject">Reject (UNDER 14)</label>
                                </div>
                            </div>
                            <input type="hidden" name="q18_hidden" id="q18_hidden">
                        </div>

                        <!-- Q19 Confidential Comments to the Admin -->
                        <div class="question-section">
                            <h3 class="question-title">Q.19 Confidential Comments to the Admin</h3>
                            <h5 class="form-label">Confidential Comments to the Admin (will not be shared with
                                authors)</h5>
                            <textarea class="form-control" name="q19" id="question-19" required></textarea>
                        </div>

                        <span data-error="review_status" class="text-danger mt-2"></span>
                        <div class="form-group col-12 my-2">
                            <label for="name" class="">
                                <h5>Remark</h5>
                            </label>
                            <textarea name="remark" class="form-control" cols="80" rows="10"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-check m-3">
                    <input type="checkbox" class="form-check-input" id="checkpoint1">
                    <label class="form-check-label" for="checkpoint1">
                        I have not used since I declare strictly prohibition of using AI tools (including ChatGPT or
                        similar models) to analyze, interpret, summarize, or critique the content of any manuscript
                        under review. Using AI to assist in critical analysis violates confidentiality, originality, and
                        professional integrity standards of the peer-review process.
                    </label>
                </div>

                <div class="form-check m-3">
                    <input type="checkbox" class="form-check-input" id="checkpoint2">
                    <label class="form-check-label" for="checkpoint2">
                        I use AI tools only after completing my independent review, and solely for language refinement
                        or grammar polishing as required.
                    </label>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Submit</button>
                </div>
            </form>
        </div>

    </div>



    {{-- Score Calculator at top right --}}
    <script>
        function displayoption() {
            document.getElementById('question-7').style.display = 'block';
        }

        function displayoption2() {
            document.getElementById('question-7').style.display = 'none';
            document.querySelectorAll('input[name="q20"]').forEach((input) => {
                input.checked = false;
            });
        }

        function updateScore() {
            let totalScore = 0;
            const questions = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q20'];
            questions.forEach((q) => {
                const selected = document.querySelector(`input[name="${q}"]:checked`);
                if (selected) {
                    totalScore += parseInt(selected.value) || 0;
                }
            });
            document.getElementById('totalScore').textContent = totalScore;

            // Update recommendation radio buttons based on total score
            const q8Radios = document.querySelectorAll('input[name="q8"]');
            q8Radios.forEach(radio => radio.disabled = false);
            /*   if (totalScore >= 28) {
                   document.getElementById('q8-accept').checked = true;
               } else if (totalScore >= 21) {
                   document.getElementById('q8-minor').checked = true;
               } else if (totalScore >= 14) {
                   document.getElementById('q8-major').checked = true;
               } else {
                   document.getElementById('q8-reject').checked = true;
               }*/
        }

        // Add event listeners to all radio buttons for questions 1 to 7
        document.querySelectorAll(
            'input[type="radio"][name="q1"], input[type="radio"][name="q2"], input[type="radio"][name="q3"], input[type="radio"][name="q4"], input[type="radio"][name="q5"], input[type="radio"][name="q6"], input[type="radio"][name="q20"]'
        ).forEach((input) => {
            input.addEventListener('change', updateScore);
        });

        // Initialize score on page load
        updateScore();
    </script>

    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            $('#checkpoint1, #checkpoint2').on('change', function() {
                if ($('#checkpoint1').is(':checked') && $('#checkpoint2').is(':checked')) {
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#submitBtn').prop('disabled', true);
                }
            });
        });
    </script>
