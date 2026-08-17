@extends('master.master')

@section('content')
    @php
        $includedServices = $service->includedServiceList();
        $deliverySteps = $service->deliveryStepList();
    @endphp

    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="section-title position-relative pb-3 mb-5">
                        <h5 class="fw-bold text-primary text-uppercase">Service Details</h5>
                        <h1 class="mb-0">{{ $service->title }}</h1>
                    </div>
                    <p class="mb-4">{{ $service->overview() }}</p>
                    <p class="mb-4">{{ $service->short_description }}</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('booking', ['service' => $service->title]) }}" class="btn btn-primary py-3 px-5">
                            <i class="fa fa-calendar-check me-2"></i>Book This Service
                        </a>
                        <a href="{{ route('service') }}" class="btn btn-outline-primary py-3 px-5">
                            <i class="fa fa-th-large me-2"></i>All Services
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="service-detail-image wow zoomIn" data-wow-delay="0.3s">
                        <img src="{{ $service->imageSrc() }}" alt="{{ $service->title }}">
                    </div>
                </div>
            </div>

            @if ($includedServices !== [])
                <div class="row g-4 mt-5">
                    <div class="col-12">
                        <div class="section-title position-relative pb-3 mb-4">
                            <h5 class="fw-bold text-primary text-uppercase">What We Provide</h5>
                            <h2 class="mb-0">Specific services included in {{ $service->title }}</h2>
                        </div>
                    </div>
                    @foreach ($includedServices as $item)
                        <div class="col-lg-6 wow zoomIn" data-wow-delay="{{ ['0.2s', '0.4s', '0.6s', '0.8s'][$loop->index % 4] }}">
                            <div class="service-detail-card">
                                <span class="service-detail-icon"><i class="fa fa-check"></i></span>
                                <h5 class="mb-0">{{ $item }}</h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if ($deliverySteps !== [])
        <div class="container-fluid bg-light py-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="container py-5">
                <div class="row g-5 align-items-start">
                    <div class="col-lg-5">
                        <div class="section-title position-relative pb-3 mb-4">
                            <h5 class="fw-bold text-primary text-uppercase">Delivery Process</h5>
                            <h1 class="mb-0">How this service is completed</h1>
                        </div>
                        <p class="mb-0">Each project is handled with a clear flow, from understanding the requirement to handover and support.</p>
                    </div>
                    <div class="col-lg-7">
                        <div class="service-process-list">
                            @foreach ($deliverySteps as $step)
                                <div class="service-process-step">
                                    <span>{{ $loop->iteration }}</span>
                                    <p>{{ $step }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('common.site.booking-cta')
    @include('common.site.vendor-carousel')
@endsection
