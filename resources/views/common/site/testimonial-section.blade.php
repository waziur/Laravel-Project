<div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="section-title text-center position-relative pb-3 mb-4 mx-auto" style="max-width: 600px;">
            <h5 class="fw-bold text-primary text-uppercase">Testimonial</h5>
            <h1 class="mb-0">What Our Clients Say About Our Digital Services</h1>
        </div>
        <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.6s">
            @foreach ([['testimonial-1.jpg', 'Client Name', 'CEO'], ['testimonial-2.jpg', 'Client Name', 'Manager'], ['testimonial-3.jpg', 'Client Name', 'Founder'], ['testimonial-4.jpg', 'Client Name', 'Director']] as $testimonial)
                <div class="testimonial-item bg-light my-4">
                    <div class="d-flex align-items-center border-bottom pt-5 pb-4 px-5">
                        <img class="img-fluid rounded" src="{{ asset('img/' . $testimonial[0]) }}" alt="{{ $testimonial[1] }}" style="width: 60px; height: 60px;">
                        <div class="ps-4">
                            <h4 class="text-primary mb-1">{{ $testimonial[1] }}</h4>
                            <small class="text-uppercase">{{ $testimonial[2] }}</small>
                        </div>
                    </div>
                    <div class="pt-4 pb-5 px-5">
                        The team understood our requirements quickly and delivered a practical solution that our staff can actually use every day.
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
