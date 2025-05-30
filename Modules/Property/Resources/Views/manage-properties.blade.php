@extends('shared::layouts.master')

@section('title', 'Manage Properties')

@section('styles')
<style>
    .card-header {
        background: linear-gradient(135deg, #6a11cb, #2575fc);
    }
    .btn-add-admin {
        background: linear-gradient(135deg, #ff6a00, #ee0979);
        border: none;
        color: white;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 30px;
        transition: all 0.3s ease;
    }
    .btn-add-admin:hover {
        background: linear-gradient(135deg, #ee0979, #ff6a00);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .btn {
    transition: all 0.2s ease-in-out;
}
.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
}

.modal-body::-webkit-scrollbar {
  width: 6px;
}
.modal-body::-webkit-scrollbar-thumb {
  background-color: #ced4da;
  border-radius: 3px;
}

.carousel {
        max-width: 100%;
        border-radius: 6px;
        overflow: hidden;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0,0,0,0.5);
        border-radius: 50%;
    }

    .btn-gradient-primary {
    background: linear-gradient(45deg, #007bff, #00bfff);
    border: none;
}

.btn-gradient-danger {
    background: linear-gradient(45deg, #ff4b5c, #ff6b6b);
    border: none;
}

.btn-gradient-success {
    background: linear-gradient(45deg, #28a745, #6dd47e);
    border: none;
}

.btn-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #3dd5f3);
    border: none;
}

.btn-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #ffdd57);
    border: none;
    color: #333;
}

.btn-gradient-secondary {
    background: linear-gradient(45deg, #6c757d, #adb5bd);
    border: none;
}

#properties-table thead th {
    vertical-align: middle;
    background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
    color: #333;
    font-weight: 600;
    border-bottom: 2px solid #ccc;
}

</style>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
            style="background: linear-gradient(135deg, #00b09b, #96c93d);">
            <h4 class="mb-0 fw-bold">Manage Properties</h4>
            <button class="btn btn-add-admin" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                <i class="bi bi-plus-lg me-1"></i> Add Property
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="properties-table" width="100%">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Property Info</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Add Property Modal -->
<div class="modal fade" id="addPropertyModal" tabindex="-1" aria-labelledby="addPropertyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(to right, #3a7bd5, #3a6073);">
        <h5 class="modal-title">
          <i class="bi bi-house-add me-2"></i> Add New Property
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Form Start -->
      <form action="{{ route('add.property') }}" method="POST" enctype="multipart/form-data"> @csrf
        <!-- Scrollable Body -->
        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
          <!-- Property Details -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-building me-1"></i> Property Details
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Property Type</label>
                <select class="form-select @error('property_type') is-invalid @enderror" name="property_type">
                  <option disabled selected>-- Select Property Type --</option> @foreach ($property_types as $type) <option value="{{ $type->id }}" {{ old('property_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option> @endforeach
                </select> @error('property_type') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Property Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="e.g. Seaside Villa" value="{{ old('name') }}"> @error('name') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">GPS Location</label>
                <input type="text" class="form-control @error('location_gps') is-invalid @enderror" name="location_gps" placeholder="Latitude, Longitude" value="{{ old('location_gps') }}"> @error('location_gps') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" placeholder="Enter address" value="{{ old('address') }}"> @error('address') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}"> @error('phone') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2" placeholder="Short description...">{{ old('description') }}</textarea> @error('description') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Total Rooms</label>
                <input type="number" class="form-control @error('total_rooms') is-invalid @enderror" name="total_rooms" value="{{ old('total_rooms') }}"> @error('total_rooms') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Total Capacity</label>
                <input type="number" class="form-control @error('total_capacity') is-invalid @enderror" name="total_capacity" value="{{ old('total_capacity') }}"> @error('total_capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Billing Method</label>
                <select class="form-select @error('billing_method') is-invalid @enderror" name="billing_method">
                  <option value="Card-(Visa/Master)" @selected(old('billing_method')=='Card-(Visa/Master)' )>Card (Visa/Master)</option>
                  <option value="Cash" @selected(old('billing_method')=='Cash' )>Cash</option>
                  <option value="UPI" @selected(old('billing_method')=='UPI' )>UPI</option>
                </select> @error('billing_method') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-3 form-check mt-4">
                <input type="checkbox" class="form-check-input @error('is_owned') is-invalid @enderror" name="is_owned" id="is_owned" value="1" {{ old('is_owned') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_owned">Is Owned?</label>
              </div>
              <div class="col-md-3 form-check mt-4">
                <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Is Active?</label>
              </div>
              <div class="col-md-3 form-check mt-4">
                <input type="checkbox" class="form-check-input @error('is_franchise') is-invalid @enderror"
                      name="is_franchise" id="is_franchise"
                      value="1"
                      {{ old('is_franchise') ? 'checked' : '' }}
                      onchange="toggleFranchiseField()">
                <label class="form-check-label" for="is_franchise">Is Franchise?</label>
              </div>

              <div class="col-md-4" id="franchise_chain_name_field" style="display: none;">
                <label class="form-label">Franchise Chain Name</label>
                <input type="text" class="form-control @error('franchise_chain_name') is-invalid @enderror"
                      name="franchise_chain_name"
                      value="{{ old('franchise_chain_name') }}">
                @error('franchise_chain_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Deactivation From And To Date</label>
                <input type="date" class="form-control @error('from_deactivation_date') is-invalid @enderror" name="from_deactivation_date" value="{{ old('from_deactivation_date') }}"> @error('from_deactivation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror <input type="date" class="form-control mt-2 @error('to_deactivation_date') is-invalid @enderror" name="to_deactivation_date" value="{{ old('to_deactivation_date') }}"> @error('to_deactivation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Location Start Date</label>
                <input type="date" class="form-control @error('location_start_date') is-invalid @enderror" name="location_start_date" value="{{ old('location_start_date') }}"> @error('location_start_date') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
          <!-- Amenities -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-check2-square me-1"></i> Amenities
            </h6>
            <label class="form-label">Select Amenities</label>
            <select class="form-select @error('amenities') is-invalid @enderror" name="amenities[]" multiple size="5"> @foreach ($amenities as $amenity) <option value="{{ $amenity->id }}" {{ in_array($amenity->id, old('amenities', [])) ? 'selected' : '' }}>
                {{ $amenity->name }}
              </option> @endforeach </select> @error('amenities') <div class="invalid-feedback">
              {{ $message }}
            </div> @enderror
          </div>
          <!-- Property Images -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-images me-1"></i> Property Images
            </h6>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">Upload Images</label>
                <input type="file" class="form-control @error('images') is-invalid @enderror" name="images[]" multiple> @error('images') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Unit Capacity</label>
                <input type="number" class="form-control @error('unit_capacity') is-invalid @enderror" name="unit_capacity" value="{{ old('unit_capacity') }}"> @error('unit_capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
          <!-- Pricing -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-currency-dollar me-1"></i> Pricing
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Bedrooms</label>
                <input type="number" class="form-control @error('bedrooms') is-invalid @enderror" name="bedrooms" value="{{ old('bedrooms') }}"> @error('bedrooms') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Bathrooms</label>
                <input type="number" class="form-control @error('bathrooms') is-invalid @enderror" name="bathrooms" value="{{ old('bathrooms') }}"> @error('bathrooms') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Slab</label>
                <select class="form-select @error('slab') is-invalid @enderror" name="slab">
                  <option value="Monsoon" @selected(old('slab')=='Monsoon' )>Monsoon</option>
                  <option value="Sunny" @selected(old('slab')=='Sunny' )>Sunny</option>
                  <option value="SuperHot" @selected(old('slab')=='SuperHot' )>SuperHot</option>
                  <option value="Fall" @selected(old('slab')=='Fall' )>Fall</option>
                  <option value="Winter" @selected(old('slab')=='Winter' )>Winter</option>
                </select> @error('slab') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Pricing</label>
                <input type="text" class="form-control @error('pricing') is-invalid @enderror" name="pricing" value="{{ old('pricing') }}"> @error('pricing') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Pricing Type</label>
                <select class="form-select @error('pricing_type') is-invalid @enderror" name="pricing_type">
                  <option value="Night" @selected(old('pricing_type')=='Night' )>Night</option>
                  <option value="Month" @selected(old('pricing_type')=='Month' )>Month</option>
                  <option value="SpecialDay" @selected(old('pricing_type')=='SpecialDay' )>Special Day</option>
                </select> @error('pricing_type') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control @error('capacity') is-invalid @enderror" name="capacity" value="{{ old('capacity') }}"> @error('capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Max Capacity</label>
                <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" name="max_capacity" value="{{ old('max_capacity') }}"> @error('max_capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
          <!-- Nearby -->
          <div class="mb-4 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-geo-alt-fill me-1"></i> Nearby
            </h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Nearby Item</label>
                <select class="form-select @error('nearby_item') is-invalid @enderror" name="nearby_item">
                  <option value="Mahal" @selected(old('nearby_item')=='Mahal' )>Mahal</option>
                  <option value="Hospital" @selected(old('nearby_item')=='Hospital' )>Hospital</option>
                  <option value="University" @selected(old('nearby_item')=='University' )>University</option>
                  <option value="Tech Park" @selected(old('nearby_item')=='Tech Park' )>Tech Park</option>
                  <option value="Bus Stand" @selected(old('nearby_item')=='Bus Stand' )>Bus Stand</option>
                  <option value="Railway Station" @selected(old('nearby_item')=='Railway Station' )>Railway Station</option>
                </select> @error('nearby_item') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Distance</label>
                <input type="text" class="form-control @error('nearby_distance') is-invalid @enderror" name="nearby_distance" value="{{ old('nearby_distance') }}"> @error('nearby_distance') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Image</label>
                <input type="file" class="form-control @error('nearby_image') is-invalid @enderror" name="nearby_image"> @error('nearby_image') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer bg-light border-top rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-check-circle me-1"></i> Create Property </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Property Modal -->
<div class="modal fade" id="editPropertyModal" tabindex="-1" aria-labelledby="editPropertyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(to right, #3a7bd5, #3a6073);">
        <h5 class="modal-title">
          <i class="bi bi-house-add me-2"></i> Edit Property
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Form Start -->
      <form action="{{ route('edit.property', ['property' => 0]) }}" method="POST" enctype="multipart/form-data"> @csrf
        @method('PUT')
        <!-- Scrollable Body -->
        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
          <!-- Property Details -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-building me-1"></i> Property Details
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Property Type</label>
                <select class="form-select @error('property_type') is-invalid @enderror" name="property_type">
                  <option disabled selected>-- Select Property Type --</option> @foreach ($property_types as $type) <option value="{{ $type->id }}" {{ old('property_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option> @endforeach
                </select> @error('property_type') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Property Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="e.g. Seaside Villa" value="{{ old('name') }}"> @error('name') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">GPS Location</label>
                <input type="text" class="form-control @error('location_gps') is-invalid @enderror" name="location_gps" placeholder="Latitude, Longitude" value="{{ old('location_gps') }}"> @error('location_gps') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" placeholder="Enter address" value="{{ old('address') }}"> @error('address') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}"> @error('phone') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2" placeholder="Short description...">{{ old('description') }}</textarea> @error('description') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Total Rooms</label>
                <input type="number" class="form-control @error('total_rooms') is-invalid @enderror" name="total_rooms" value="{{ old('total_rooms') }}"> @error('total_rooms') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Total Capacity</label>
                <input type="number" class="form-control @error('total_capacity') is-invalid @enderror" name="total_capacity" value="{{ old('total_capacity') }}"> @error('total_capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Billing Method</label>
                <select class="form-select @error('billing_method') is-invalid @enderror" name="billing_method">
                  <option value="Card-(Visa/Master)" @selected(old('billing_method')=='Card-(Visa/Master)' )>Card (Visa/Master)</option>
                  <option value="Cash" @selected(old('billing_method')=='Cash' )>Cash</option>
                  <option value="UPI" @selected(old('billing_method')=='UPI' )>UPI</option>
                </select> @error('billing_method') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-3 form-check mt-4">
                <input type="checkbox" class="form-check-input @error('is_owned') is-invalid @enderror" name="is_owned" id="is_owned" value="1" {{ old('is_owned') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_owned">Is Owned?</label>
              </div>
              <div class="col-md-3 form-check mt-4">
                <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Is Active?</label>
              </div>
              <div class="col-md-3 form-check mt-4">
                <input type="checkbox" class="form-check-input @error('is_franchise') is-invalid @enderror"
                      name="is_franchise" id="is_franchise_edit"
                      value="1"
                      {{ old('is_franchise') ? 'checked' : '' }}
                      onchange="toggleEditFranchiseField()">
                <label class="form-check-label" for="is_franchise">Is Franchise?</label>
              </div>

              <div class="col-md-4" id="franchise_chain_name_field_edit" style="display: none;">
                <label class="form-label">Franchise Chain Name</label>
                <input type="text" class="form-control @error('franchise_chain_name') is-invalid @enderror"
                      name="franchise_chain_name"
                      value="{{ old('franchise_chain_name') }}">
                @error('franchise_chain_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Deactivation From And To Date</label>
                <input type="date" class="form-control @error('from_deactivation_date') is-invalid @enderror" name="from_deactivation_date" value="{{ old('from_deactivation_date') }}"> @error('from_deactivation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror <input type="date" class="form-control mt-2 @error('to_deactivation_date') is-invalid @enderror" name="to_deactivation_date" value="{{ old('to_deactivation_date') }}"> @error('to_deactivation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Location Start Date</label>
                <input type="date" class="form-control @error('location_start_date') is-invalid @enderror" name="location_start_date" value="{{ old('location_start_date') }}"> @error('location_start_date') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
          <!-- Amenities -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-check2-square me-1"></i> Amenities
            </h6>
            <label class="form-label">Select Amenities</label>
            <select class="form-select @error('amenities') is-invalid @enderror" name="amenities[]" multiple size="5"> @foreach ($amenities as $amenity) <option value="{{ $amenity->id }}" {{ in_array($amenity->id, old('amenities', [])) ? 'selected' : '' }}>
                {{ $amenity->name }}
              </option> @endforeach </select> @error('amenities') <div class="invalid-feedback">
              {{ $message }}
            </div> @enderror
          </div>
          <!-- Property Images -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-images me-1"></i> Property Images
            </h6>
            <div class="row g-3 align-items-end">
              <div class="col-md-8">
                <label class="form-label">Upload Images</label>
                <input type="file" class="form-control @error('images') is-invalid @enderror" name="images[]" multiple>
                @error('images') 
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div> 
                @enderror
              </div>

              <div class="col-md-4 text-end">
                <label class="form-label d-block invisible">Button</label>
                <button type="button" class="btn btn-outline-primary w-100 shadow-sm" id="manangeOldImages">
                  <i class="bi bi-images me-1"></i> Manage Old Images
                </button>
              </div>

              <div class="col-md-4">
                <label class="form-label">Unit Capacity</label>
                <input type="number" class="form-control @error('unit_capacity') is-invalid @enderror" name="unit_capacity" value="{{ old('unit_capacity') }}">
                @error('unit_capacity') 
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div> 
                @enderror
              </div>
            </div>

          </div>
          <!-- Pricing -->
          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-currency-dollar me-1"></i> Pricing
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Bedrooms</label>
                <input type="number" class="form-control @error('bedrooms') is-invalid @enderror" name="bedrooms" value="{{ old('bedrooms') }}"> @error('bedrooms') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Bathrooms</label>
                <input type="number" class="form-control @error('bathrooms') is-invalid @enderror" name="bathrooms" value="{{ old('bathrooms') }}"> @error('bathrooms') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Slab</label>
                <select class="form-select @error('slab') is-invalid @enderror" name="slab">
                  <option value="Monsoon" @selected(old('slab')=='Monsoon' )>Monsoon</option>
                  <option value="Sunny" @selected(old('slab')=='Sunny' )>Sunny</option>
                  <option value="SuperHot" @selected(old('slab')=='SuperHot' )>SuperHot</option>
                  <option value="Fall" @selected(old('slab')=='Fall' )>Fall</option>
                  <option value="Winter" @selected(old('slab')=='Winter' )>Winter</option>
                </select> @error('slab') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Pricing</label>
                <input type="text" class="form-control @error('pricing') is-invalid @enderror" name="pricing" value="{{ old('pricing') }}"> @error('pricing') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Pricing Type</label>
                <select class="form-select @error('pricing_type') is-invalid @enderror" name="pricing_type">
                  <option value="Night" @selected(old('pricing_type')=='Night' )>Night</option>
                  <option value="Month" @selected(old('pricing_type')=='Month' )>Month</option>
                  <option value="SpecialDay" @selected(old('pricing_type')=='SpecialDay' )>Special Day</option>
                </select> @error('pricing_type') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control @error('capacity') is-invalid @enderror" name="capacity" value="{{ old('capacity') }}"> @error('capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Max Capacity</label>
                <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" name="max_capacity" value="{{ old('max_capacity') }}"> @error('max_capacity') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
          <!-- Nearby -->
          <div class="mb-4 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-geo-alt-fill me-1"></i> Nearby
            </h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Nearby Item</label>
                <select class="form-select @error('nearby_item') is-invalid @enderror" name="nearby_item">
                  <option value="Mahal" @selected(old('nearby_item')=='Mahal' )>Mahal</option>
                  <option value="Hospital" @selected(old('nearby_item')=='Hospital' )>Hospital</option>
                  <option value="University" @selected(old('nearby_item')=='University' )>University</option>
                  <option value="Tech Park" @selected(old('nearby_item')=='Tech Park' )>Tech Park</option>
                  <option value="Bus Stand" @selected(old('nearby_item')=='Bus Stand' )>Bus Stand</option>
                  <option value="Railway Station" @selected(old('nearby_item')=='Railway Station' )>Railway Station</option>
                </select> @error('nearby_item') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Distance</label>
                <input type="text" class="form-control @error('nearby_distance') is-invalid @enderror" name="nearby_distance" value="{{ old('nearby_distance') }}"> @error('nearby_distance') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4 append_image">
                <label class="form-label">Image</label>
                <input type="file" class="form-control @error('nearby_image') is-invalid @enderror" name="nearby_image"> @error('nearby_image') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer bg-light border-top rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-check-circle me-1"></i> Update Property </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Manage Old Images Modal-->
<div class="modal fade" id="manageOldImageModal" tabindex="-1" aria-labelledby="manageOldImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">

            <!-- Modal Header -->
            <div class="modal-header text-white" style="background: linear-gradient(to right, #00c6ff, #0072ff);">
                <h5 class="modal-title" id="manageOldImageModalLabel">
                    <i class="bi bi-pencil-square me-2"></i> Manage Old Images
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="append_images" class="row g-3"></div> <!-- Use Bootstrap grid for images -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var assetUrl = "{{ asset('') }}"; // This will give you the base URL
    </script>
    <script>
        $(document).ready(function () {
          
           // data table
            $('#properties-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("manage.properties") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'property_info', name: 'name, address, propertyType.name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // Use event delegation
            $(document).on('click', '[data-bs-target="#editPropertyModal"]', function () {
              const modal = $('#editPropertyModal');
              // Debugging (optional)
              console.log($(this).data());

              // Set values in modal fields using jQuery
              // Property datas
              modal.find('[name="name"]').val($(this).data('name') || '');
              modal.find('[name="property_type"]').val($(this).data('property_type') || '');
              modal.find('[name="location_gps"]').val($(this).data('location_gps') || '');
              modal.find('[name="address"]').val($(this).data('address') || '');
              modal.find('[name="phone"]').val($(this).data('phone') || '');
              modal.find('[name="description"]').val($(this).data('description') || '');
              modal.find('[name="total_rooms"]').val($(this).data('total_rooms') || '');
              modal.find('[name="total_capacity"]').val($(this).data('total_capacity') || '');
              modal.find('[name="billing_method"]').val($(this).data('billing_method') || '');
              // modal.find('[name="is_owned"]').val($(this).data('is_owned') || '');
              var isOwned = $(this).data('is_owned');
              if (isOwned == 1) {
                  modal.find('[name="is_owned"]').prop('checked', true);
              } else {
                  modal.find('[name="is_owned"]').prop('checked', false);
              }
              modal.find('[name="is_active"]').val($(this).data('is_active') || '');
              // modal.find('[name="is_franchise"]').val($(this).data('is_franchise') || '');
              var isFranchise = $(this).data('is_franchise');
              if (isFranchise == 1) {
                  modal.find('[name="is_franchise"]').prop('checked', true);
              } else {
                  modal.find('[name="is_franchise"]').prop('checked', false);
              }
              modal.find('[name="franchise_chain_name"]').val($(this).data('franchise_chain_name') || '');
              modal.find('[name="from_deactivation_date"]').val($(this).data('from_deactivation_date') || '');
              modal.find('[name="to_deactivation_date"]').val($(this).data('to_deactivation_date') || '');
              modal.find('[name="location_start_date"]').val($(this).data('location_start_date') || '');

              // Amenities Datas
              let amenities = $(this).data('amenities');
              try {
                  amenities = typeof amenities === 'string' ? JSON.parse(amenities) : amenities;
                  modal.find('[name="amenities[]"]').val(amenities).trigger('change');
              } catch (e) {
                  console.error("Invalid JSON amenities", e);
              }


              // Property Images
              $('#manangeOldImages').attr('data-property_images', JSON.stringify($(this).data('property_images')));
              modal.find('[name="unit_capacity"]').val($(this).data('unit_capacity') || '');

              // pricing Data
              modal.find('[name="bedrooms"]').val($(this).data('bedrooms') || '');
              modal.find('[name="bathrooms"]').val($(this).data('bathrooms') || '');
              modal.find('[name="slab"]').val($(this).data('slab') || '');
              modal.find('[name="pricing"]').val($(this).data('pricing') || '');
              modal.find('[name="pricing_type"]').val($(this).data('pricing_type') || '');
              modal.find('[name="capacity"]').val($(this).data('capacity') || '');
              modal.find('[name="max_capacity"]').val($(this).data('max_capacity') || '');

              // nearbys Data
              modal.find('[name="nearby_item"]').val($(this).data('nearby_item') || '');
              modal.find('[name="nearby_distance"]').val($(this).data('nearby_distance') || '');
              let nearbyImage = $(this).data('nearby_image');

              // First, remove any previously appended images inside .append_image
              $('.append_image .old-image-preview').remove(); 

              if (nearbyImage) {
                let imageUrl = assetUrl + 'assets/images/nearby_images/' + nearbyImage;

                let imagePreviewHtml = `
                  <div class="mt-3 old-image-preview">
                    <label class="form-label">Old Image Preview</label>
                    <div class="border rounded shadow-sm p-2 bg-light text-center">
                      <img src="${imageUrl}" alt="Nearby Image" class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                    </div>
                  </div>
                `;

                $('.append_image').append(imagePreviewHtml);
              }

              const propertySlug = $(this).data('slug');
  
              // Replace `0` with the correct ID in form action
              const propertyForm = modal.find('form');
              let propertyAction = propertyForm.attr('action');
              // Update the form action URL with the actual admin ID
              propertyAction = propertyAction.replace(/\/0$/, '/' + propertySlug);
              propertyForm.attr('action', propertyAction);

              // const adminId = $(this).data('id');
            
              // Replace `0` with the correct ID in form action
              exit;
              const form = modal.find('form');
              let action = form.attr('action');
              // Update the form action URL with the actual admin ID
              action = action.replace(/\/0$/, '/' + adminId);
              form.attr('action', action);

              const defaultImagePath = '/assets/images/user/user-3296.png'; // adjust as needed
              const basePath = window.location.origin + '/'; // or use your asset() URL if needed
              const profileImage = 'assets/images/user/' + $(this).data('profile-image');
              const fullPath = profileImage ? basePath + profileImage : defaultImagePath;

              modal.find('#profilePicPreview').attr('src', fullPath);

              // Clear password fields
              modal.find('[name="password"]').val('');
              modal.find('[name="password_confirmation"]').val('');
            });

            // Manage old Images
            $('#manangeOldImages').on('click', function() {
              // Retrieve the property images from the data attribute
              let propertyImages = JSON.parse($(this).attr('data-property_images'));
              var html = '';

              // Check if propertyImages is defined and is an array
              if (Array.isArray(propertyImages)) {
                  $.each(propertyImages, function(key, value) {
                      // Construct the image HTML using assetUrl
                      html += `
                          <div class="col-6 col-md-4 image_main_div">
                              <div class="position-relative">
                                  <img src="${assetUrl}assets/images/property_images/${value.image_path}" class="img-fluid rounded" alt="Property Image">
                                  <button type="button" 
                                          class="position-absolute top-0 end-0 m-2 bg-transparent border-0" 
                                          style="color: darkred; font-size: 1.5rem;" 
                                          onclick="removeImage('${value.id}', this)">
                                      ❌
                                  </button>
                              </div>
                          </div>
                      `;
                  });
              }

              // Append the images to the modal
              $('#append_images').html(html); // Use .html() to replace the content
              $('#manageOldImageModal').modal('show'); // Show the modal
            });

        });
    </script>
  @if ($errors->any())
      <script>
          $(document).ready(function () {
              const addModal = new bootstrap.Modal(document.getElementById('addPropertyModal'));
              addModal.show();

              // Convert Laravel validation errors to a JS object
              const validationErrors = @json($errors->toArray());
              console.log("Validation Errors:", validationErrors);

          });
      </script>
  @endif

  <script>
    // franchise toggle check
    function toggleFranchiseField() {
      const isFranchise = document.getElementById('is_franchise').checked;
      const franchiseField = document.getElementById('franchise_chain_name_field');
      franchiseField.style.display = isFranchise ? 'block' : 'none';
    }

    function toggleEditFranchiseField() {
      const isFranchise = document.getElementById('is_franchise_edit').checked;
      const franchiseField = document.getElementById('franchise_chain_name_field_edit');
      franchiseField.style.display = isFranchise ? 'block' : 'none';
    }

    // Run on page load (to restore old value state)
    window.addEventListener('DOMContentLoaded', toggleFranchiseField);
    window.addEventListener('DOMContentLoaded', toggleEditFranchiseField);

    // Remove Old Images
    function removeImage(imageId, button) {
      // Send an AJAX request to remove the image
      $.ajax({
        url: '{{ url('property/remove-image') }}/' + imageId, // Correct way
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
        },
        success: function (response) {
          $(button).closest('.image_main_div').remove();
          toastr.success(response.message);
        },
        error: function (xhr, status, error) {
          // Handle error, e.g., show an error message
          console.error('Error removing image:', error);
        }
      });
    }
  </script>
@endsection