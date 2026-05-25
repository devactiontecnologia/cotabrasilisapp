<div class="accordion-item">
    <h2 class="accordion-header" id="faq-heading-{{ $faq->id }}">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $faq->id }}" aria-expanded="false" aria-controls="faq-collapse-{{ $faq->id }}">
            {{ $faq->question }}
        </button>
    </h2>
    <div id="faq-collapse-{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion" aria-labelledby="faq-heading-{{ $faq->id }}">
        <div class="accordion-body faq-answer-content">
            {!! $faq->answer !!}
        </div>
    </div>
</div>
