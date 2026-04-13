@extends('layouts.app')

@section('styles')
    <style>
        .pin-input {
            font-family: 'Courier New', Courier, monospace; /* Font mono agar angka sejajar */
            letter-spacing: 15px; /* Jarak antar titik/angka lebih lebar */
            text-indent: 15px;    /* Menyeimbangkan posisi teks agar tetap di tengah */
            font-size: 2rem;      /* Ukuran lebih besar agar mudah dibaca */
            border-radius: 12px;  /* Membuat sudut lebih halus */
            max-width: 300px;     /* Batasi lebar box agar tidak terlalu panjang */
            margin: 0 auto;       /* Ketengahkan elemen */
        }

        /* Menghilangkan panah atas/bawah pada browser Chrome/Safari/Edge */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="text-center text-white mb-4">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" style="max-height: 80px;">
                <h4 class="mt-3 fw-bold">Private Gallery</h4>
                <p>Silahkan masukkan password untuk melihat foto Anda.</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5 text-center">
                    @if(session('error'))
                        <div class="alert alert-danger border-0 small">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('public.gallery.verify', $session->session_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <input type="password"
                                name="password"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                class="form-control form-control-lg text-center fw-bold pin-input"
                                placeholder="······"
                                autofocus
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-unlock-fill me-2"></i> Unlock Photos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
