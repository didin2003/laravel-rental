@extends('shared::layouts.master') @section('title', 'Dashboard') 

@section('styles')

  <style>
    .section-title {
      font-weight: 700;
      margin-bottom: 1rem;
      color: #212529;
    }

    .property-card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      transition: 0.3s;
    }

    .property-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .property-card img {
      height: 200px;
      object-fit: cover;
    }

    .recommended-card img {
      height: 150px;
      object-fit: cover;
    }

    .amenity-icon {
      font-size: 1.5rem;
      color: #dc3545;
    }

    .accordion-button::after {
      background-image: none;
      content: "+";
      font-size: 1.5rem;
      font-weight: bold;
      color: #dc3545;
    }

    .accordion-button.collapsed::after {
      content: "+";
    }

    .accordion-button:not(.collapsed)::after {
      content: "−";
    }

    .amenity-card:hover {
      background-color: #f8f9fa;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .property-item {
        transition: all 0.5s ease;
        cursor: pointer;
    }

    .recommended-item {
        transition: all 0.5s ease;
        cursor: pointer;
    }

    /* Filter Styles */

    /* MODAL STYLING */
  .modal-content {
    border-radius: 1.25rem;
    padding: 1rem 0;
    border: none;
  }

  .modal-header {
    border-bottom: none;
    padding: 1rem 2rem;
  }

  .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
  }

  .modal-body {
    padding: 0 2rem;
    font-size: 0.95rem;
  }

  .modal-footer {
    border-top: none;
    padding: 1rem 2rem;
  }

  .form-label {
    font-weight: 500;
    margin-bottom: 0.4rem;
  }

  input[type="text"], input[type="date"] {
    border-radius: 0.5rem;
  }

  .btn-outline-secondary {
    font-size: 0.85rem;
  }

  .btn-pill-select {
    padding: 0.4rem 0.9rem;
    font-size: 0.85rem;
    border-radius: 999px;
    transition: 0.3s;
  }

  .btn-pill-select.active {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
  }

  /* Price Slider */
  #priceSlider {
    margin-top: 15px;
    margin-bottom: 5px;
  }

  #priceDisplay {
    font-weight: bold;
    color: #dc3545;
  }

  .noUi-horizontal .noUi-handle {
    top: -6px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: white;
    border: 2px solid #dc3545;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
  }

  .noUi-connect {
    background-color: #dc3545;
  }

  /* Counter Buttons */
  .input-group {
    max-width: 160px;
  }

  .input-group .btn {
    border-radius: 0.4rem !important;
  }

  .input-group input {
    border-radius: 0.4rem !important;
    background-color: #f9f9f9;
    font-weight: 600;
  }

  /* Show Results Button */
  .modal-footer .btn {
    border-radius: 999px;
    font-weight: 600;
    font-size: 1.05rem;
    padding: 0.75rem;
  }

  /* Responsive Touches */
  @media (max-width: 576px) {
    .modal-body, .modal-header, .modal-footer {
      padding: 1rem 1.25rem;
    }

    .input-group {
      max-width: 100%;
    }
  }

  /* No properties Found Blinking */
  /* Styling for the no-properties message */
  .no-properties {
      font-size: 1.5rem;
      font-weight: 600;
      color: #ff4d4d; /* Red color for emphasis, you can change it */
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 15px;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef); /* Subtle gradient background */
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
      display: inline-block; /* Keeps the element centered */
      transition: all 0.3s ease; /* Smooth transition for hover effects */
  }

  /* Blinking animation */
  @keyframes blink {
      0%, 100% {
          opacity: 1;
          transform: scale(1);
      }
      50% {
          opacity: 0.4;
          transform: scale(1.05);
      }
  }

  .blink {
      animation: blink 2s infinite ease-in-out; /* Smooth blinking effect */
  }

  /* Hover effect for interactivity */
  .no-properties:hover {
      transform: translateY(-2px); /* Slight lift on hover */
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
      color: #e63946; /* Slightly darker red on hover */
  }
    
  </style>
@endsection

@section('content') 
  <!-- Near From You -->
  <div class="container py-5">
    <!-- Location and Search -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
      <div class="mb-3 mb-md-0">
        <p class="mb-1 text-muted small">Find your place in</p>
        <h4 class="fw-bold">
          <i class="bi bi-geo-alt-fill text-danger"></i> Urapakkam, Chennai
        </h4>
      </div>
      <div class="d-flex align-items-center">
        <input type="text" class="form-control me-2" placeholder="Search by Location" id="searchInput" value="{{ request('location') }}">
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#filterModal">
          <i class="bi bi-funnel-fill"></i>
        </button>
      </div>
    </div>
    <!-- Hero Section -->
    <div class="text-center mb-5">
      <h2 class="section-title">Exquisite Living Every Time</h2>
      <p class="text-muted w-75 mx-auto"> Feel Home Stay is a home rental platform that connects renters with property owners. Browse listings and secure your next home. </p>
    </div>

    <!-- Near from you -->
    <div class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title">Near from you</h4>
      </div>
      <div class="row g-4" id="property-list">
        @if (count($nearbyProperties) != 0 && !empty($nearbyProperties))
          @foreach ($nearbyProperties as $index => $nearbyProperty)
            <div class="col-md-4 property-item" style="{{ $index >= 6 ? 'display: none;' : '' }}" data-slug="{{ $nearbyProperty->slug }}">
              <div class="card property-card h-100 shadow-sm border-0">
                      
                <!-- Bootstrap Carousel (Top Image Slider) -->
                <div id="propertyCarousel-{{ $nearbyProperty->id }}" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                      @if ($nearbyProperty->images)
                        @foreach ($nearbyProperty->images as $index => $image)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                <img src="{{ asset('assets/images/property_images/' . $image->image_path) }}" 
                                    class="d-block w-100" 
                                    alt="Property Image" 
                                    style="height: 220px; object-fit: cover;">
                            </div>
                        @endforeach
                      @endif

                    </div>

                    @if($nearbyProperty->images && count($nearbyProperty->images) > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel-{{ $nearbyProperty->id }}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel-{{ $nearbyProperty->id }}" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>

                @php
                  $rating = number_format($nearbyProperty->avg_testimonial_ratings ?? 0, 1);
                  $reviewCount = $nearbyProperty->testimonials->count();
                @endphp

                <!-- Property Details -->
                <div class="card-body">

                  <!-- Property Title -->
                  <h5 class="card-title fw-semibold text-primary mb-2">
                    {{ $nearbyProperty->name }}
                  </h5>

                  <!-- Ratings Section -->
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                      <div class="me-2">
                        @for ($i = 1; $i <= 5; $i++)
                          @if ($i <= floor($rating))
                            <i class="bi bi-star-fill text-warning"></i>
                          @elseif ($i - $rating < 1)
                            <i class="bi bi-star-half text-warning"></i>
                          @else
                            <i class="bi bi-star text-warning"></i>
                          @endif
                        @endfor
                      </div>
                      <span class="fw-medium text-dark small">
                        {{ $rating }}/5
                      </span>
                    </div>

                    <span class="badge bg-light text-muted border border-1 rounded-pill px-3 py-1 small"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="{{ $reviewCount }} {{ Str::plural('Review', $reviewCount) }}">
                      <i class="bi bi-chat-left-text me-1"></i> {{ $reviewCount }}
                    </span>
                  </div>

                  <!-- Address -->
                  <p class="text-muted mb-2 text-truncate" style="max-width: 95%;">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $nearbyProperty->address }}
                  </p>

                  <!-- Room Info -->
                  <div class="d-flex justify-content-between small text-secondary mb-2">
                    <span>
                      🛏️
                      {{ $nearbyProperty->pricings->isNotEmpty() ? optional($nearbyProperty->pricings->first())->unit['bedrooms'] ?? 'N/A' : 'N/A' }}
                      Bedroom
                    </span>
                    <span>
                      🚿
                      {{ $nearbyProperty->pricings->isNotEmpty() ? optional($nearbyProperty->pricings->first())->unit['bathrooms'] ?? 'N/A' : 'N/A' }}
                      Bathroom
                    </span>
                  </div>

                  <!-- Price -->
                  <div class="fw-bold text-danger fs-5">
                    ₹{{ $nearbyProperty->pricings->isNotEmpty() ? optional($nearbyProperty->pricings->first())->pricing : 'N/A' }}
                    <small class="text-muted fs-6">/ {{ $nearbyProperty->pricings->isNotEmpty() ? optional($nearbyProperty->pricings->first())->pricing_type : 'N/A' }}</small>
                  </div>

                </div>


              </div>
            </div> 
          @endforeach 
        @else
          <h5 class="text-center no-properties blink">No Near From You Properties Found</h5>
        @endif 

      </div>
      <!-- Load More Button -->
      <div class="text-center mt-4">
        <button id="loadMoreBtn" class="btn btn-danger px-4">View More</button>
      </div>

    </div>
    
    <!-- Recommended for you -->
    <div class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title">Recommended for you</h4>
      </div>

      <div class="row g-4" id="recommended-list">
        @if (count($recommendedProperties) != 0 && !empty($recommendedProperties))
          @foreach ($recommendedProperties as $index => $property)
            <div class="col-md-6 recommended-item" style="{{ $index >= 4 ? 'display: none;' : '' }}" data-slug="{{ $property->slug }}">
              <div class="card h-100 flex-row shadow-sm border-0 overflow-hidden">

                <!-- Image Section (carousel if multiple) -->
                <div class="position-relative" style="width: 200px; min-height: 150px; min-width: 200px;">
                  @if ($property->images && $property->images->count())
                    <div id="recommendedCarousel-{{ $property->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                      <div class="carousel-inner h-100">
                        @foreach ($property->images as $imgIndex => $img)
                          <div class="carousel-item h-100 {{ $imgIndex == 0 ? 'active' : '' }}">
                            <img src="{{ asset('assets/images/property_images/' . $img->image_path) }}"
                                class="d-block w-100 h-100"
                                style="object-fit: cover;"
                                alt="Property Image">
                          </div>
                        @endforeach
                      </div>
                      @if ($property->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#recommendedCarousel-{{ $property->id }}" data-bs-slide="prev">
                          <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#recommendedCarousel-{{ $property->id }}" data-bs-slide="next">
                          <span class="carousel-control-next-icon"></span>
                        </button>
                      @endif
                    </div>
                  @else
                    <img src="https://via.placeholder.com/200x150"
                        class="img-fluid h-100 w-100"
                        style="object-fit: cover;"
                        alt="Default Property">
                  @endif
                </div>

                @php
                  $rating = number_format($property->avg_testimonial_ratings ?? 0, 1);
                  $reviewCount = $property->testimonials->count();
                @endphp

                <!-- Content Section -->
                <div class="card-body d-flex flex-column justify-content-between">

                  <!-- Top: Title, Address, and Rooms -->
                  <div>
                    <h5 class="card-title mb-1 fw-semibold text-primary">{{ $property->name }}</h5>

                    <!-- Review Section -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <div class="d-flex align-items-center">
                        @for ($i = 1; $i <= 5; $i++)
                          @if ($i <= floor($rating))
                            <i class="bi bi-star-fill text-warning"></i>
                          @elseif ($i - $rating < 1)
                            <i class="bi bi-star-half text-warning"></i>
                          @else
                            <i class="bi bi-star text-warning"></i>
                          @endif
                        @endfor
                        <span class="ms-2 text-dark small">{{ $rating }}/5</span>
                      </div>
                      <span class="badge bg-light text-muted border rounded-pill px-2 small"
                            data-bs-toggle="tooltip"
                            title="{{ $reviewCount }} {{ Str::plural('Review', $reviewCount) }}">
                        <i class="bi bi-chat-left-text me-1"></i> {{ $reviewCount }}
                      </span>
                    </div>

                    <!-- Address -->
                    <p class="text-muted mb-2">
                      <i class="bi bi-geo-alt-fill text-danger"></i>
                      {{ Str::limit($property->address, 80) }}
                    </p>

                    <!-- Room Info -->
                    <div class="d-flex justify-content-between small mb-2 text-secondary">
                      <span>🛏️
                        {{ $property->pricings->isNotEmpty() ? optional($property->pricings->first())->unit['bedrooms'] ?? 'N/A' : 'N/A' }}
                        Bedrooms
                      </span>
                      <span>🚿
                        {{ $property->pricings->isNotEmpty() ? optional($property->pricings->first())->unit['bathrooms'] ?? 'N/A' : 'N/A' }}
                        Bathrooms
                      </span>
                    </div>
                  </div>

                  <!-- Price -->
                  <div class="fw-bold text-danger">
                    ₹{{ $property->pricings->isNotEmpty() ? optional($property->pricings->first())->pricing : 'N/A' }}
                    <small class="text-muted">/ {{ $property->pricings->isNotEmpty() ? optional($property->pricings->first())->pricing_type : 'N/A' }}</small>
                  </div>

                </div>


              </div>
            </div>
          @endforeach
        @else
          <h5 class="text-center no-properties blink">No Recommended for You Properties Found</h5>
        @endif 
      </div>

      <!-- View More Button -->
      <div class="text-center mt-4">
        <button id="loadMoreRecommendedBtn" class="btn btn-danger px-4">View More</button>
      </div>
    </div>

    <!-- Amenities -->
    <div class="mb-5">
      <h4 class="section-title">Amenities</h4>
      <div class="row g-3">
        @foreach ($allAmenities as $allAmenity)
          <div class="col-6 col-md-3">
            <div class="d-flex align-items-center p-2 border rounded shadow-sm h-100 amenity-card">
              <img src="{{ asset('assets/images/amenities/' . $allAmenity->icon) }}" alt="{{ $allAmenity->name }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
              <span class="fw-semibold">{{ $allAmenity->name }}</span>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="mb-5">
      <h4 class="section-title">FAQ</h4>
      <div class="accordion" id="faqAccordion">
        @foreach ($allFaqs as $index => $allFaq)
          <div class="accordion-item">
            <h2 class="accordion-header" id="faqHeading{{ $index }}">
              <button class="accordion-button fw-bold {{ $index != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $index }}">
                {{ $allFaq->question }}
              </button>
            </h2>
            <div id="faqCollapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="faqHeading{{ $index }}" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                {{ $allFaq->answer }}
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- noUiSlider CSS -->
  <link href="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.css" rel="stylesheet">
  <!-- noUiSlider JS -->
  <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.js"></script>
@endsection

@section('modals')
<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content shadow">
      <div class="modal-header px-4 pt-4 border-bottom-0">
        <h5 class="modal-title fw-semibold">Filter</h5>
        <button type="button" class="btn btn-link text-danger fw-medium clear_filter">Clear Filters</button>
        <button type="button" class="btn btn-link text-danger fw-medium reset_filter">Reset</button>
      </div>
      <form action="{{ route('dashboard') }}" method="GET">
        <div class="modal-body px-4 pb-0">

          <!-- Search -->
          <div class="mb-4">
            <input type="text" class="form-control rounded-pill search_location" name="location" placeholder="Search address, city, location" value="{{ request('location') }}">
          </div>

          <!-- Dates -->
          <div class="d-flex gap-3 mb-4">
            <div class="flex-fill">
              <label class="form-label small">Booking from</label>
              <input type="date" class="form-control shadow-sm search_booking_from" name="booking_from" value="{{ request('booking_from') }}">
            </div>
            <div class="flex-fill">
              <label class="form-label small">Booking to</label>
              <input type="date" class="form-control shadow-sm search_booking_to" name="booking_to" value="{{ request('booking_to') }}">
            </div>
          </div>

          <!-- Property Type -->
          <div class="mb-4">
            <label class="form-label small">Property type</label>
            <div class="d-flex flex-wrap gap-2" id="propertyTypes">
              @foreach ($allPropertyTypes as $propertyType)
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-pill-select" data-name="property_types[]" data-value="{{ $propertyType->id }}" onclick="toggleSelect(this)">
                  {{ $propertyType->name }}
                </button>
              @endforeach
            </div>
          </div>

          <!-- Hidden input for selected property types -->
          <div id="propertyTypeInputs"></div>

          <!-- Price Range -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Price range</label>
            <div id="priceSlider"></div>
            <div class="text-danger mt-3 fw-bold" id="priceDisplay">₹200 – ₹10,00,000</div>
            <input type="hidden" name="price_min" id="price_min">
            <input type="hidden" name="price_max" id="price_max">
          </div>

          <!-- Bedrooms -->
          <div class="mb-4">
            <label class="form-label small">Bedrooms</label>
            <div class="input-group w-50">
              <button type="button" class="btn btn-outline-secondary" onclick="decrement('bedrooms', 1)">−</button>
              <input type="text" id="bedrooms" class="form-control text-center search_bedrooms" name="bedrooms" value="{{ (request('bedrooms') != null) ? request('bedrooms') : 1 }}" readonly>
              <button type="button" class="btn btn-outline-secondary" onclick="increment('bedrooms')">+</button>
            </div>
          </div>

          <!-- Adults -->
          <div class="mb-4">
            <label class="form-label small">Adults</label>
            <div class="input-group w-50">
              <button type="button" class="btn btn-outline-secondary" onclick="decrement('adults', 1)">−</button>
              <input type="text" id="adults" class="form-control text-center search_adults" name="adults" value="{{ (request('adults') != null) ? request('adults') : 1 }}" readonly>
              <button type="button" class="btn btn-outline-secondary" onclick="increment('adults')">+</button>
            </div>
          </div>

          <!-- Children -->
          <div class="mb-4">
            <label class="form-label small">Children <span class="text-muted">(under 9 years)</span></label>
            <div class="input-group w-50">
              <button type="button" class="btn btn-outline-secondary" onclick="decrement('children', 0)">−</button>
              <input type="text" id="children" class="form-control text-center search_children" name="children" value="{{ (request('children') != null) ? request('children') : 0 }}" readonly>
              <button type="button" class="btn btn-outline-secondary" onclick="increment('children')">+</button>
            </div>
          </div>


          <!-- Amenities -->
          <div class="mb-4">
            <label class="form-label small">Amenities</label>
            <div class="d-flex flex-wrap gap-2" id="amenities">
              @foreach ($allAmenities as $allAmenity)
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-pill-select" data-name="amenities[]" data-value="{{ $allAmenity->id }}" onclick="toggleSelect(this)">
                  {{ $allAmenity->name }}
                </button>
              @endforeach
            </div>
          </div>

          <!-- Hidden input for selected amenities -->
          <div id="amenitiesInputs"></div>
        </div>

        <div class="modal-footer border-top-0 px-4 pb-4">
          <button type="submit" class="btn btn-danger w-100 py-2 fw-bold rounded-pill">Show Results</button>
        </div>
      </form>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const propertyItems = document.querySelectorAll('.property-item');
    let visibleCount = 6; // First 6 items shown

      // Hide the button if items are less than or equal to visibleCount
      if (propertyItems.length <= visibleCount) {
        loadMoreBtn.style.display = 'none';
      }

      propertyItems.forEach((item, index) => {
        item.style.display = index < visibleCount ? 'block' : 'none';
      });

    loadMoreBtn.addEventListener('click', function () {
      visibleCount += 6; // Increase by 6 each click

      propertyItems.forEach((item, index) => {
        if (index < visibleCount) {
          item.style.display = 'block';
        }
      });

      // Hide button if all properties are visible
      if (visibleCount >= propertyItems.length) {
        loadMoreBtn.style.display = 'none';
      }
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('loadMoreRecommendedBtn');
    const items = document.querySelectorAll('.recommended-item');
    let visible = 4;

    // Initial visibility setup
    items.forEach((item, index) => {
      item.style.display = index < visible ? 'block' : 'none';
    });

    // Hide the button if all items are already visible
    if (items.length <= visible) {
      btn.style.display = 'none';
    }

    btn.addEventListener('click', function () {
      visible += 4;
      items.forEach((item, index) => {
        if (index < visible) item.style.display = 'block';
      });
      if (visible >= items.length) btn.style.display = 'none';
    });
  });
</script>

<script>
  $(document).ready(function(){
    $(".property-item, .recommended-item").click(function(){
      window.location.href = '{{ route('property.details', ':slug') }}'.replace(':slug', $(this).data('slug'));
    });

    // reset filter button click to remove all filters
    $(".reset_filter").click(function(){
      $('.search_location').val('');
      $('.search_booking_from').val('');
      $('.search_booking_to').val('');
      $('.search_bedrooms').val('1');
      $('.search_adults').val('1');
      $('.search_children').val('0');

      // Reset property type buttons
      $('#propertyTypes .btn-pill-select').each(function () {
        $(this).removeClass('active btn-secondary text-white').addClass('btn-outline-secondary');
      });

      // Reset amenity type buttons
      $('#amenities .btn-pill-select').each(function () {
        $(this).removeClass('active btn-secondary text-white').addClass('btn-outline-secondary');
      });

      // ✅ Reset price slider
      priceSlider.noUiSlider.set([1200, 35000]);

    });

    // Clear Filters
    $(".clear_filter").click(function(){
      window.location.href = '{{ route('dashboard') }}';
    });

  });

  
</script>

<!-- Common Search -->
<script>
  let debounceTimer;

  $('#searchInput').on('keyup', function () {
    clearTimeout(debounceTimer); // clear previous timer

    const query = $(this).val();
    
    debounceTimer = setTimeout(function () {
      const url = new URL("{{ route('dashboard') }}", window.location.origin);
      url.searchParams.set('location', query);
      window.location.href = url.toString();
    }, 1000); // debounce delay in ms
  });
</script>

<!-- Filter Scripts -->
<script>
  // Price Range Slider
  const priceSlider = document.getElementById('priceSlider');
  noUiSlider.create(priceSlider, {
    start: [1200, 35000],
    connect: true,
    step: 500,
    range: {
      'min': 200,
      'max': 1000000
    },
    tooltips: true,
    format: {
      to: value => `₹${parseInt(value).toLocaleString()}`,
      from: value => Number(value.replace(/[^\d]/g, ''))
    }
  });

  priceSlider.noUiSlider.on('update', function (values) {
    document.getElementById('priceDisplay').innerText = `${values[0]} – ${values[1]}`;
  });

  // // Toggle Select Button
  // function toggleSelect(btn) {
  //   btn.classList.toggle('active');
  //   btn.classList.toggle('btn-outline-secondary');
  //   btn.classList.toggle('btn-secondary');
  //   btn.classList.toggle('text-white');
  // }

  // Increment / Decrement Logic
  function increment(id) {
    const input = document.getElementById(id);
    input.value = parseInt(input.value) + 1;
  }

  function decrement(id, min) {
    const input = document.getElementById(id);
    const current = parseInt(input.value);
    if (current > min) input.value = current - 1;
  }

  // Fetch Request Url Params
  function getQueryParams() {
    const params = new URLSearchParams(window.location.search);
    console.log(params);
    const result = {};
    for (const [key, value] of params.entries()) {
      if (key.endsWith('[]')) {
        const baseKey = key.slice(0, -2);
        if (!result[baseKey]) result[baseKey] = [];
        result[baseKey].push(value);
      } else {
        result[key] = value;
      }
    }
    return result;
  }

  // Fetch Request Url Params and active set buttons
  document.addEventListener('DOMContentLoaded', function () {
    const query = getQueryParams();

    // Handle property_types[]
    if (query.property_types) {
      query.property_types.forEach(function (id) {
        const btn = document.querySelector(`[data-name="property_types[]"][data-value="${id}"]`);
        if (btn) toggleSelect(btn);
      });
    }

    // Handle amenities[]
    if (query.amenities) {
      query.amenities.forEach(function (id) {
        const btn = document.querySelector(`[data-name="amenities[]"][data-value="${id}"]`);
        if (btn) toggleSelect(btn);
      });
    }

    // Price Range Slider Set
    priceSlider.noUiSlider.set([query.price_min, query.price_max] || [1200, 35000]);

    // Optional: highlight other fields like bedrooms, adults, etc. if you have toggle buttons for them
  });

  function toggleSelect(btn) {
    btn.classList.toggle('active');
    const inputName = btn.dataset.name;
    const value = btn.dataset.value;
    const container = inputName === 'property_types[]' ? '#propertyTypeInputs' : '#amenitiesInputs';

    // Remove if already exists
    $(`${container} input[value="${value}"]`).remove();

    // Add if now active
    if (btn.classList.contains('active')) {
      $(container).append(`<input type="hidden" name="${inputName}" value="${value}">`);
    }
  }

  // Capture price range values before submit
  $('form').on('submit', function () {
    const [min, max] = priceSlider.noUiSlider.get().map(v => Number(v.replace(/[^\d]/g, '')));
    $('#price_min').val(min);
    $('#price_max').val(max);
  });
</script>


@endsection