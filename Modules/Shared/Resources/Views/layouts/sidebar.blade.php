<div class="sidebar d-flex flex-column p-3 bg-gradient" style="min-height: 100vh; background: linear-gradient(to bottom right, #8e44ad, #3498db); color: #fff;">
    
    {{-- Profile Section --}}
    <div class="text-center mb-4">
        <img src="{{ auth()->user()->profile->profile_image }}" alt="Profile" class="rounded-circle mb-2" width="80" height="80">
        <h5 class="mb-0 fw-bold">{{ auth()->user()->name }}</h5>
        <small class="text-light">Welcome back!</small>
    </div>

    @role(['user'])
    {{-- Guest Level & Rewards --}}
    <div class="d-flex justify-content-between mb-4">
        <div class="card text-center border-0 shadow-sm" style="width: 48%; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#guestLevelMapModal">
            <div class="card-body p-2">
                <i class="bi bi-person-badge-fill text-primary fs-4"></i>
                <p class="mb-0 small text-muted">Guest Level</p>
                <strong class="text-pink">{{ auth()->user()->guest_level }}</strong>
            </div>
        </div>
        <div class="card text-center border-0 shadow-sm" style="width: 48%;">
            <div class="card-body p-2">
                <i class="bi bi-star-fill text-warning fs-4"></i>
                <p class="mb-0 small text-muted">Rewards</p>
                <strong class="text-dark">{{auth()->user()->profile->reward_points }} Points</strong>
            </div>
        </div>
    </div>
    @endrole

    {{-- Navigation Links --}}
    <a href="{{ route('dashboard') }}" class="nav-link text-white @activeLink('dashboard')">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    @role('super_admin')
        <a href="{{ route('manage.admins') }}" class="nav-link text-white @activeLink('manage.admins')">
            <i class="bi bi-person-badge-fill me-2"></i> Manage Subadmins
        </a>
    @endrole
    @role(['super_admin', 'admin'])
        <a href="{{ route('manage.properties') }}" class="nav-link text-white @activeLink('manage.properties')">
            <i class="bi bi-house-door-fill me-2"></i> Manage Properties
        </a>
        <a href="{{ route('manage.amenities') }}" class="nav-link text-white @activeLink('manage.amenities')">
            <i class="bi bi-building-fill me-2"></i> Manage Amenities
        </a>
        <a href="{{ route('manage.special.days') }}" class="nav-link text-white @activeLink('manage.special.days')">
            <i class="bi bi-calendar-fill me-2"></i> Manage Special Days
        </a>
        <a href="{{ route('manage.coupons') }}" class="nav-link text-white @activeLink('manage.coupons')">
            <i class="bi bi-ticket-fill me-2"></i> Manage Coupons
        </a>
        <a href="{{ route('manage.testimonials') }}" class="nav-link text-white @activeLink('manage.testimonials')">
            <i class="bi bi-star-fill me-2"></i> Manage Testimonials
        </a>
        <a href="{{ route('manage.faqs') }}" class="nav-link text-white @activeLink('manage.faqs')">
            <i class="bi bi-question-circle-fill me-2"></i> Manage FAQs
        </a>
    @endrole
    <a href="{{ route('manage.bookings') }}" class="nav-link text-white @activeLink('manage.bookings')">
        <i class="bi bi-calendar-event-fill me-2"></i> @role(['super_admin', 'admin']) Manage @endrole @role(['user']) My @endrole Bookings
    </a>
    <a href="{{ route('profile') }}" class="nav-link text-white @activeLink('profile')">
        <i class="bi bi-person-circle me-2"></i> Profile
    </a>
    <a href="{{ route('logout') }}" class="nav-link text-white"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
       <i class="bi bi-box-arrow-left me-2"></i> Logout
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>
