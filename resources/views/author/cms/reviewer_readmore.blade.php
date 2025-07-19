@php
    $reviewScore = $timeline->data['review_score'] ?? [];
    $remark = $timeline->data['comment'] ?? null;
    $score = $timeline->data['score'] ?? null;
@endphp

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reviewer's Remark</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            @if (!empty($reviewScore))
                <div class="mb-3">
                    <h6><strong>Total Score:</strong> {{ $score }}</h6>
                </div>

                <div class="mb-3">
                    <h6><strong>Message to Author</strong></h6>
                    <p>{{ $reviewScore[15]['answer'] ?? 'N/A' }}</p>
                </div>




                <hr>

                {{-- <div class="accordion" id="reviewDetailsAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFullReview">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFullReview" aria-expanded="false"
                                aria-controls="collapseFullReview">
                                Full Review Details
                            </button>
                        </h2>
                        <div id="collapseFullReview" class="accordion-collapse collapse"
                            aria-labelledby="headingFullReview" data-bs-parent="#reviewDetailsAccordion">
                            <div class="accordion-body">
                                <ul class="list-group">
                                    @foreach ($reviewScore as $item)
                                        <li class="list-group-item">
                                            <strong>{{ $item['heading'] }}</strong><br>
                                            <small>{{ $item['question'] }}</small><br>
                                            <span class="text-muted">Answer: {{ $item['answer'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> --}}
            @else
                <p class="text-muted">No review score data available.</p>
            @endif
        </div>
    </div>
</div>
