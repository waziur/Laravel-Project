@php
    $approvedQuoteItems = $approvedQuotes ?? collect();
@endphp

@if ($approvedQuoteItems->isNotEmpty())
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 640px;">
                <h5 class="fw-bold text-primary text-uppercase">Approved Quotes</h5>
                <h1 class="mb-0">Recent Requests Reviewed By Our Admin Team</h1>
            </div>

            <div class="row g-4">
                @foreach ($approvedQuoteItems as $quote)
                    <div class="col-lg-4 col-md-6">
                        <div class="approved-quote-card h-100 bg-light p-4">
                            <span class="approved-quote-service">{{ $quote->service }}</span>
                            <p>{{ \Illuminate\Support\Str::limit($quote->message, 180) }}</p>
                            <div class="approved-quote-author">
                                <span>{{ strtoupper(substr($quote->name, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $quote->name }}</strong>
                                    <small>Approved {{ $quote->updated_at?->format('M d, Y') ?? 'recently' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
