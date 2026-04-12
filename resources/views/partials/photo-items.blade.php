@foreach ($photos as $photo)
    <div class="col-md-4 col-6 photo-item">
        <div class="card h-100 shadow-sm border-0 photo-card overflow-hidden">
            <div class="position-relative">
                <img src="{{ asset('storage/' . $photo->storage_path) }}" class="card-img-top"
                     style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal"
                     data-bs-target="#imageModal" data-bs-src="{{ asset('storage/' . $photo->storage_path) }}">
            </div>

            <div class="card-body p-2">
                <a href="{{ asset('storage/' . $photo->storage_path) }}"
                   download="Photo_{{ Str::slug($session->customer_name) }}_{{ $loop->iteration }}.jpg"
                   class="btn btn-sm btn-outline-primary w-100 fw-bold">
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
@endforeach
