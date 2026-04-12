@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
    /* Clean Table Styling */
    .table-container {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .custom-table thead {
        background: #f1f3f5;
    }

    .custom-table thead th {
        color: #6c757d !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        font-size: 0.7rem;
        padding: 1rem 1.25rem;
        border: none;
    }

    .custom-table tbody td {
        padding: 1rem 1.25rem;
        color: #495057;
        border-bottom: 1px solid #f1f3f5;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    .custom-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Package Cards Customization */
    .package-card {
        transition: transform 0.2s ease;
    }
    .package-card:hover {
        transform: translateY(-3px);
    }

    .package-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold mb-0">
            <i class="bi bi-speedometer2 text-primary me-2"></i> Dashboard
        </h2>
        <a href="{{ route('admin.gallery') }}" class="btn btn-primary px-4 shadow-sm" style="border-radius: 10px;">
            <i class="bi bi-images me-1"></i> Open Gallery
        </a>
    </div>

    <h6 class="text-muted mb-3 fw-bold text-uppercase small">Package Performance</h6>
    <div class="row g-3 mb-5">
        @php
            $packages = [
                ['label' => 'BASIC', 'count' => $stats['pkg_basic'], 'color' => 'info', 'icon' => 'bi-person'],
                ['label' => 'BESTIE', 'count' => $stats['pkg_bestie'], 'color' => 'primary', 'icon' => 'bi-people'],
                ['label' => 'RAMEAN', 'count' => $stats['pkg_ramean'], 'color' => 'warning', 'icon' => 'bi-people-fill']
            ];
        @endphp

        @foreach($packages as $pkg)
        <div class="col-md-4">
            <div class="card package-card border-0 shadow-sm border-top border-{{ $pkg['color'] }} border-4 bg-white">
                <div class="card-body d-flex align-items-center py-4">
                    <div class="package-icon bg-{{ $pkg['color'] }} bg-opacity-10 text-{{ $pkg['color'] }} me-3">
                        <i class="bi {{ $pkg['icon'] }} fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-bold small">{{ $pkg['label'] }}</small>
                        <h3 class="mb-0 fw-bold text-dark">{{ $pkg['count'] }} <span class="fs-6 fw-normal text-muted">Sessions</span></h3>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <h6 class="text-muted mb-3 fw-bold text-uppercase small">Recent Daily Activity</h6>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table custom-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Date</th>
                                <th class="text-center">Usage</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daily_stats as $day)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light text-primary p-2 rounded-circle me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-calendar3 small"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($day->date)->format('l, d M Y') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                        {{ $day->count }} Sessions
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.gallery', ['search' => $day->date]) }}" class="btn btn-sm btn-outline-primary px-3 fw-bold" style="border-radius: 6px;">
                                        Details <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <i class="bi bi-calendar-x display-4 text-light d-block mb-3"></i>
                                    <span class="text-muted">No sessions recorded in the last 7 days.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
