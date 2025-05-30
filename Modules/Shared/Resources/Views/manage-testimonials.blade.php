@extends('shared::layouts.master')

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
.form-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.form-switch .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ced4da;
    transition: 0.4s;
    border-radius: 34px;
}

.form-switch .slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
}

.form-switch input:checked + .slider {
    background-color: #0d6efd;
}

.form-switch input:checked + .slider:before {
    transform: translateX(24px);
}
.form-switch input:checked + .slider {
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
}

.btn-gradient-primary {
    background: linear-gradient(45deg, #007bff, #00bfff);
    color: #fff;
    border: none;
}

.btn-gradient-danger {
    background: linear-gradient(45deg, #ff4b5c, #ff6b6b);
    color: #fff;
    border: none;
}

.btn-gradient-primary:hover,
.btn-gradient-danger:hover {
    filter: brightness(1.1);
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
    transition: all 0.2s ease-in-out;
}


</style>

<style>
.testimonial-card:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08) !important;
    transform: translateY(-2px);
}
.testimonial-card .btn {
    transition: all 0.2s ease-in-out;
}
.testimonial-card .btn:hover {
    opacity: 0.85;
}
</style>

<style>
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.5rem;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    font-size: 1rem;
    transition: all 0.2s ease-in-out;
}

.btn-icon i {
    margin: 0;
}

.btn-icon:hover {
    opacity: 0.9;
    transform: scale(1.05);
}
</style>

<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
@endsection

@section('title', 'Property Testimonials')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
            style="background: linear-gradient(135deg, #00b09b, #96c93d);">
            <h4 class="mb-0 fw-bold">Manage Testimonials</h4>
            <div>
                <!-- <button class="btn btn-add-admin" data-bs-toggle="modal" data-bs-target="#addtestimonialsModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Testimonials
                </button> -->
                <a href="{{ route('manage.properties') }}" class="btn btn-secondary ms-2">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped table-hover text-nowrap" id="testimonials-table" width="100%">
            <thead class="table-light align-middle">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="min-width: 200px;">Property Info</th>
                <th>
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <!-- Left Buttons -->
                    <div class="d-flex flex-wrap gap-2">
                      <button type="button" class="btn btn-sm btn-outline-danger deleteAllTestimonials">
                        <i class="bi bi-trash3-fill me-1"></i> Delete All
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-success enableAllTestimonials">
                        <i class="bi bi-check-circle-fill me-1"></i> Enable All
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-secondary disableAllTestimonials">
                        <i class="bi bi-x-circle-fill me-1"></i> Disable All
                      </button>
                    </div>

                    <!-- Right Select All -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <div class="form-check mb-0">
                        <input class="form-check-input selectAllTestimonials" type="checkbox" id="selectAllTestimonials">
                        <label class="form-check-label small fw-semibold text-muted" for="selectAllTestimonials">
                          Select All Testimonials
                        </label>
                      </div>
                      <span class="fw-semibold text-muted small d-none d-md-inline">| Testimonials Info</span>
                    </div>
                  </div>
                </th>
              </tr>
            </thead>
            <!-- DataTable body -->
          </table>
        </div>


    </div>
</div>
@endsection

@section('modals')
<!-- Add testimonials Modal -->
<div class="modal fade" id="addtestimonialsModal" tabindex="-1" aria-labelledby="addtestimonialsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow border-0 rounded-4 overflow-hidden">

      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(to right, #4e54c8, #8f94fb);">
        <h5 class="modal-title fw-semibold" id="addAdminModalLabel">
          <i class="bi bi-plus-circle me-2"></i> Add New testimonials
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Form Start -->
      <form action="" method="POST">
        @csrf

        <!-- Modal Body -->
        <div class="modal-body p-4">
          <div class="row g-4">

          <div class="mb-5 p-3 border rounded-3 shadow-sm bg-light">
            <h6 class="fw-bold text-primary mb-3">
              <i class="bi bi-currency-dollar me-1"></i> testimonials
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
                <label class="form-label">testimonials</label>
                <input type="text" class="form-control @error('testimonials') is-invalid @enderror" name="testimonials" value="{{ old('testimonials') }}"> @error('testimonials') <div class="invalid-feedback">
                  {{ $message }}
                </div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">testimonials Type</label>
                <select class="form-select @error('testimonials_type') is-invalid @enderror" name="testimonials_type">
                  <option value="Night" @selected(old('testimonials_type')=='Night' )>Night</option>
                  <option value="Month" @selected(old('testimonials_type')=='Month' )>Month</option>
                  <option value="SpecialDay" @selected(old('testimonials_type')=='SpecialDay' )>Special Day</option>
                </select> @error('testimonials_type') <div class="invalid-feedback">
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

          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer bg-light d-flex justify-content-between rounded-bottom-4 px-4 py-3">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-check-circle me-1"></i> Add testimonials
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Edit Testimonials Modal -->
<div class="modal fade" id="edittestimonialsModal" tabindex="-1" aria-labelledby="edittestimonialsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">

      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(to right, #4e54c8, #8f94fb);">
        <h5 class="modal-title fw-semibold" id="edittestimonialsModalLabel">
          <i class="bi bi-pencil-square me-2"></i> Edit Testimonial
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Form -->
      <form action="{{ route('edit.testimonial', ['testimonial' => 0]) }}" method="POST" id="reviewForm">
        @csrf
        @method('PUT')

        <div class="modal-body py-4 px-5">
          <!-- Hidden Rating Input -->
          <input type="hidden" name="ratings" id="rating" value="">

          <!-- Star Rating -->
          <div class="mb-4 text-center">
            <div class="star-rating fs-3 text-warning">
              <i class="bi bi-star" data-value="1" style="cursor: pointer;"></i>
              <i class="bi bi-star" data-value="2" style="cursor: pointer;"></i>
              <i class="bi bi-star" data-value="3" style="cursor: pointer;"></i>
              <i class="bi bi-star" data-value="4" style="cursor: pointer;"></i>
              <i class="bi bi-star" data-value="5" style="cursor: pointer;"></i>
            </div>
          </div>

          <!-- Feedback Input -->
          <div class="form-floating mb-3">
            <textarea class="form-control rounded-3 shadow-sm" placeholder="Leave your feedback here..." id="feedback" name="description" style="height: 150px;"></textarea>
            <label for="feedback">Your Feedback</label>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-0 px-5 pb-4 pt-0">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-success rounded-pill">
            <i class="bi bi-save me-1"></i> Update Testimonial
          </button>
        </div>
      </form>

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
        $(document).ready(function () {
            var testimonialsTable = $('#testimonials-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("manage.testimonials") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'property_info', name: 'name' },
                    { data: 'testimonials_info', name: 'property.testimonials.user.name', orderable: false },
                    // { data: 'status_action', name: 'status_action', orderable: false, searchable: false, className: 'text-center'},
                    // { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

              // 🔄 Pagination Click or Any Table Redraw Event
              testimonialsTable.on('draw', function () {
                $('#selectAllTestimonials').prop('checked', false);
              });

            $(document).on('click', '.delete-testimonial', function () {
              const id = $(this).data('id');
                if (confirm("Are you sure you want to delete this testimonial?")) {
                  $.ajax({
                    url: '{{ route("delete.testimonials") }}',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function (response) {
                        toastr.success(response.message);

                        testimonialsTable.ajax.reload(null, false); // false to keep the current paging
                    },
                    error: function () {
                        toastr.error('Failed to update status.');
                    }
                });
              }
            });

            // Delete All Testimonials Button Click
            $('.deleteAllTestimonials').click(function () {
              var testimonalIds = $('.testimonial-check:checked').map(function() {
                return $(this).data('id');
              }).get();
              if (testimonalIds.length === 0) {
                toastr.warning('Please select at least one testimonial to delete.');
                return;
              }
              console.log('Checked IDs:', testimonalIds);
              if (confirm("Are you sure you want to delete this testimonials?")) {
                $.ajax({
                  url: '{{ route("delete.multiple.testimonials") }}',
                  type: 'DELETE',
                  data: {
                      _token: '{{ csrf_token() }}',
                      ids: testimonalIds
                  },
                  success: function (response) {
                      toastr.success(response.message);

                      testimonialsTable.ajax.reload(null, false); // false to keep the current paging

                      $('#selectAllTestimonials').prop('checked', false);

                  },
                  error: function () {
                      toastr.error('Failed to update status.');
                  }
                });
              }
            });

            // Enabled All Testimonials Button Click
            $('.enableAllTestimonials').click(function () {
              var testimonalIds = $('.testimonial-check:checked').map(function() {
                return $(this).data('id');
              }).get();
              if (testimonalIds.length === 0) {
                toastr.warning('Please select at least one testimonial to enable.');
                return;
              }
              console.log('Checked IDs:', testimonalIds);
              if (confirm("Are you sure you want to enable this testimonials?")) {
                $.ajax({
                  url: '{{ route("enable.multiple.testimonials") }}',
                  type: 'POST',
                  data: {
                      _token: '{{ csrf_token() }}',
                      ids: testimonalIds
                  },
                  success: function (response) {
                      toastr.success(response.message);

                      testimonialsTable.ajax.reload(null, false); // false to keep the current paging

                      $('#selectAllTestimonials').prop('checked', false);

                  },
                  error: function () {
                      toastr.error('Failed to update status.');
                  }
                });
              }
            });

            // Enabled All Testimonials Button Click
            $('.disableAllTestimonials').click(function () {
              var testimonalIds = $('.testimonial-check:checked').map(function() {
                return $(this).data('id');
              }).get();
              if (testimonalIds.length === 0) {
                toastr.warning('Please select at least one testimonial to disabled.');
                return;
              }
              console.log('Checked IDs:', testimonalIds);
              if (confirm("Are you sure you want to enable this testimonials?")) {
                $.ajax({
                  url: '{{ route("disable.multiple.testimonials") }}',
                  type: 'POST',
                  data: {
                      _token: '{{ csrf_token() }}',
                      ids: testimonalIds
                  },
                  success: function (response) {
                      toastr.success(response.message);

                      testimonialsTable.ajax.reload(null, false); // false to keep the current paging

                      $('#selectAllTestimonials').prop('checked', false);
                  },
                  error: function () {
                      toastr.error('Failed to update status.');
                  }
                });
              }
            });
        });

        $(document).on('change', '.testimonials-toggle', function () {
            const testimonialsId = $(this).data('id');
            const isActive = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route("pricing.toggle") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: testimonialsId,
                    is_active: isActive
                },
                success: function (response) {
                    toastr.success(response.message);
                },
                error: function () {
                    toastr.error('Failed to update status.');
                }
            });
        });
    </script>

    <!-- Edit testimonials -->
     <script>
      $(document).ready(function () {
        // Use event delegation
        $(document).on('click', '[data-bs-target="#edittestimonialsModal"]', function () {
          const modal = $('#edittestimonialsModal');

          // Debugging (optional)
          // console.log($(this).data());

          // Set values in modal fields using jQuery
          modal.find('[name="bedrooms"]').val($(this).data('bedrooms') || '');
          modal.find('[name="bathrooms"]').val($(this).data('bathrooms') || '');
          modal.find('[name="slab"]').val($(this).data('slab') || '');
          modal.find('[name="testimonials"]').val($(this).data('testimonials') || '');
          modal.find('[name="testimonials_type"]').val($(this).data('testimonials_type') || '');
          modal.find('[name="capacity"]').val($(this).data('capacity') || '');
          modal.find('[name="max_capacity"]').val($(this).data('max_capacity') || '');

          const testimonialsId = $(this).data('id');
        
          // Replace `0` with the correct ID in form action
          const form = modal.find('form');
          let action = form.attr('action');
          // Update the form action URL with the actual admin ID
          action = action.replace(/\/0$/, '/' + testimonialsId);
          form.attr('action', action);
        });

        // Select Check Box click To Select All Checkbox
        $("#selectAllTestimonials").click(function () {
            // Individual Property
            $(".property-check").prop('checked', $(this).prop('checked'));
            // Individual Testimonals Checked
            $(".testimonial-check").prop('checked', $(this).prop('checked'));
        });

        // Property Select All Checkbox click Own Testimonials Select
        $(document).on('click', '.property-check', function () {
          $('#selectAllTestimonials').prop('checked', $('.property-check:checked').length === $('.property-check').length);
          $('[data-property-id="' + $(this).data('id') + '"]').prop('checked', $(this).prop('checked'));
        });

        // testimonal Click To Trigger Select All Checkbox
        $(document).on('click', '.testimonial-check', function () {
          $('#selectAllTestimonials').prop('checked', $('.testimonial-check:checked').length === $('.testimonial-check').length);
        });

        // End Document Ready
      });

      $(document).on('click', '.edit-testimonial', function () {
          const id = $(this).data('id');
          const rating = $(this).data('ratings');
          const description = $(this).data('feedback');

          // Existing Values Assign
          const modal = $('#edittestimonialsModal');
          modal.find('[name="description"]').val(description || '');
          modal.find('[name="ratings"]').val(rating || '');
          const form = modal.find('form');
          let action = form.attr('action');
          // Update the form action URL with the actual admin ID
          action = action.replace(/\/0$/, '/' + id);
          form.attr('action', action);

          highlightStars(rating);
          

          $('#edittestimonialsModal').modal('show');
          
      });

      $(document).on('change', '.toggle-status', function () {
          const id = $(this).data('id');
          const isActive = $(this).is(':checked') ? 1 : 0;
          $.ajax({
              url: '{{ route("testimonials.toggle") }}',
              type: 'POST',
              data: {
                  _token: '{{ csrf_token() }}',
                  id: id,
                  is_active: isActive
              },
              success: function (response) {
                  toastr.success(response.message);
              },
              error: function () {
                  toastr.error('Failed to update status.');
              }
          });
      });

     </script>

     <!-- <script>
      let selectedRating = 0;

      document.querySelectorAll('.star-rating i').forEach(star => {
        star.addEventListener('mouseenter', function () {
          const val = this.getAttribute('data-value');
          highlightStars(val);
        });

        star.addEventListener('click', function () {
          selectedRating = this.getAttribute('data-value');
          highlightStars(selectedRating);
          document.getElementById('rating').value = selectedRating;
        });

        star.addEventListener('mouseleave', function () {
          highlightStars(selectedRating);
        });
      });

      function highlightStars(value) {
        document.querySelectorAll('.star-rating i').forEach(star => {
          const val = star.getAttribute('data-value');
          star.classList.toggle('active', val <= value);
        });
      }
     </script> -->
@endsection
