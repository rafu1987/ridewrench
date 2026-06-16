@section('title', __('faq.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-circle-question me-1"></i>
                {{ __('faq.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('faq.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('faq.subtitle') }}
            </p>
        </div>
    </div>

    <div class="card card-lift mb-4" itemscope itemtype="https://schema.org/FAQPage">
        <div class="card-body p-3 p-md-4">
            <div class="accordion" id="faqAccordion">
                @foreach (range(1, 8) as $i)
                    <div
                        class="accordion-item"
                        itemscope
                        itemprop="mainEntity"
                        itemtype="https://schema.org/Question"
                    >
                        <h2 class="accordion-header" id="faq-heading-{{ $i }}">
                            <button
                                class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq-collapse-{{ $i }}"
                                aria-expanded="{{ $i === 1 ? 'true' : 'false' }}"
                                aria-controls="faq-collapse-{{ $i }}"
                            >
                                <span itemprop="name">
                                    {{ __('faq.q' . $i) }}
                                </span>
                            </button>
                        </h2>

                        <div
                            id="faq-collapse-{{ $i }}"
                            class="accordion-collapse collapse"
                            aria-labelledby="faq-heading-{{ $i }}"
                            data-bs-parent="#faqAccordion"
                            itemscope
                            itemprop="acceptedAnswer"
                            itemtype="https://schema.org/Answer"
                        >
                            <div class="accordion-body text-muted" itemprop="text">
                                {{ __('faq.a' . $i) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @include('partials.bmc')
</x-app-layout>
