@extends('shared::layouts.master')

@section('title', 'Manage Bookings')

@section('styles')
    <style>
        .booking-card {
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.75rem 1.25rem rgba(0, 0, 0, 0.1);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #38b000, #70e000);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f9d923, #ffea00);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #ff4d4d, #ff758f);
        }
    </style>

    <style>
        .glass-effect {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.2);
            border-color: #86b7fe;
        }

        .modal-content {
            transition: all 0.3s ease-in-out;
        }

        .btn-close:focus {
            box-shadow: none;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
@endsection
@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            @role(['super_admin', 'admin'])
            <h4 class="fw-bold text-dark mb-0">Manage Bookings ({{ $allBookings->count() }})</h4> @endrole @role('user')
            <h4 class="fw-bold text-dark mb-0">My Bookings ({{ $allBookings->count() }})</h4> @endrole
            <button class="btn btn-outline-dark rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @if ($allBookings->count() == 0)
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark text-center">No Bookings Found</h5>
                        </div>
                    </div>
                </div>
            @endif
            @foreach($allBookings as $booking) @php $status = strtolower($booking->status); $badgeClass = match($status) { 'confirmed' => 'bg-gradient-success', 'pending' => 'bg-gradient-warning text-dark', 'cancelled', 'canceled' => 'bg-gradient-danger', default
            => 'bg-secondary' }; $user = $booking->user; @endphp
            <div class="col">
                <div class="card booking-card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="position-relative">
                        <img src="{{ asset('assets/images/property_images/' . $booking->property->images->first()->image_path) }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Property Image">
                        <span class="position-absolute top-0 end-0 m-3 badge {{ $badgeClass }} rounded-pill px-3 py-1 text-white shadow-sm text-capitalize">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title fw-bold text-dark mb-0">
                                {{ $booking->property->name }}
                            </h5>
                            <div class="d-flex align-items-center small" data-bs-toggle="tooltip" data-bs-placement="top" title="You earned {{ $booking->reward_points }} Reward Points">
                                <i class="bi bi-star-fill fs-5 me-1 text-warning"></i>
                                <span class="fw-bold">@if ($booking->status == 'confirmed') {{ $booking->reward_points }} @else 0 @endif</span>
                            </div>
                        </div>

                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt-fill me-1"></i>{{ $booking->property->address }}
                        </p>

                        <div class="d-flex flex-wrap gap-2 text-muted small mb-2">
                            <span><i class="bi bi-bucket me-1"></i>{{ $booking->property->pricings->first()->unit['bathrooms'] ?? 'N/A' }} Bathrooms</span>
                            <span><i class="bi bi-door-closed me-1"></i>{{ $booking->property->pricings->first()->unit['bedrooms'] ?? 'N/A' }} Bedrooms</span>
                        </div>

                        <div class="d-flex justify-content-between text-muted small mb-3">
                            <div><i class="bi bi-calendar-check me-1"></i>Check-in: <strong>@date($booking->check_in)</strong></div>
                            <div><i class="bi bi-calendar-x me-1"></i>Checkout: <strong>@date($booking->check_out)</strong></div>
                        </div>

                        @role(['super_admin', 'admin'])
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $user->profile->profile_image }}" class="rounded-circle me-2" width="36" height="36" style="object-fit: cover;">
                            <div class="small text-dark fw-medium">{{ $user->name }}</div>
                        </div>
                        @endrole

                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-primary fw-bold mb-0 text-truncate">
                                ₹{{ number_format($booking->total, 2) }} / {{ $booking->duration ?? 'N/A' }}
                            </h6>
                            <a href="{{ route('view.booking', [$booking->property->slug, $booking->id]) }}" class="btn btn-sm btn-outline-dark rounded-pill">View</a>
                        </div>
                    </div>

                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection
@section('modals')
    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 rounded-4 bg-white shadow-lg glass-effect">

                <!-- Modal Header -->
                <div class="modal-header border-0 p-4 pb-2 d-flex align-items-center justify-content-between">
                    <h5 class="modal-title fw-bold text-primary" id="filterModalLabel">
            <i class="bi bi-funnel-fill me-2"></i> Filter Bookings
            </h5>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Clear Filters Button -->
                        <a href="{{ route('manage.bookings') }}" class="btn btn-outline-danger btn-sm rounded-pill shadow-sm">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </a>

                        <!-- Close Button -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Modal Form -->
                <form method="GET" action="{{ route('manage.bookings') }}">
                    <div class="modal-body px-4 pt-0 pb-3">

                        <!-- Property Name -->
                        <div class="mb-4">
                            <label for="property_name" class="form-label fw-semibold">🏠 Property Name</label>
                            <input type="text" class="form-control rounded-pill shadow-sm border-light" id="property_name" name="property_name" placeholder="E.g. Sudarshan Illam" value="{{ request('property_name') }}">
                        </div>

                        <!-- Check-in Date -->
                        <div class="mb-4">
                            <label for="checkin" class="form-label fw-semibold">📅 Check-in Date</label>
                            <input type="date" class="form-control rounded-pill shadow-sm border-light" id="checkin" name="checkin" value="{{ request('checkin') }}">
                        </div>

                        <!-- Check-out Date -->
                        <div class="mb-4">
                            <label for="checkout" class="form-label fw-semibold">📅 Check-out Date</label>
                            <input type="date" class="form-control rounded-pill shadow-sm border-light" id="checkout" name="checkout" value="{{ request('checkout') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">📌 Booking Status</label>
                            <select class="form-select rounded-pill shadow-sm border-light" id="status" name="status">
                                <option value="">-- Select Status --</option>
                                <option value="pending" {{ request( 'status')=='pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="confirmed" {{ request( 'status')=='confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                                <option value="cancelled" {{ request( 'status')=='cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            </select>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 px-4 pt-0 pb-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm fw-semibold">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection