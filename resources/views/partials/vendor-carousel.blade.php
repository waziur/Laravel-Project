<div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container py-5 mb-5">
        <div class="bg-white">
            <div class="owl-carousel vendor-carousel">
                @foreach (range(1, 9) as $vendor)
                    <img src="{{ asset('img/vendor-' . $vendor . '.jpg') }}" alt="Vendor {{ $vendor }}">
                @endforeach
            </div>
        </div>
    </div>
</div>
