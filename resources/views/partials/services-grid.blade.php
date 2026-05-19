@php
    $services = $activeServices ?? collect();
@endphp

<div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
            <h5 class="fw-bold text-primary text-uppercase">Our Services</h5>
            <h1 class="mb-0">Custom IT Solutions for Your Successful Business</h1>
        </div>
        <div class="row g-5">
            @forelse ($services as $service)
                @php
                    $delay = ['0.3s', '0.5s', '0.7s'][$loop->index % 3];
                @endphp

                <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="{{ $delay }}">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-photo">
                            <img src="{{ $service->imageSrc() }}" alt="{{ $service->title }}">
                        </div>
                        <h4 class="mb-3">{{ $service->title }}</h4>
                        <p class="m-0">{{ $service->short_description }}</p>
                        <a class="btn btn-lg btn-primary rounded" href="{{ route('quote') }}"><i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="bg-light rounded text-center p-5">
                        <h4 class="mb-2">No active services yet</h4>
                        <p class="mb-0">Please check back soon for updated services.</p>
                    </div>
                </div>
            @endforelse

            <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="0.9s">
                <div class="position-relative bg-primary rounded h-100 d-flex flex-column align-items-center justify-content-center text-center p-5">
                    <h3 class="text-white mb-3">Call Us For Quote</h3>
                    <p class="text-white mb-3">Tell us what you want to build or improve. We will help you shape the next move.</p>
                    <h2 class="text-white mb-0">+012 345 6789</h2>
                </div>
            </div>
        </div>
    </div>
</div>
