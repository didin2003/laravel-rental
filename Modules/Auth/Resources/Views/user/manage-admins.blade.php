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

.btn-gradient-primary {
    background: linear-gradient(45deg, #1d8cf8, #3358f4);
    border: none;
    color: #fff;
}

.btn-gradient-danger {
    background: linear-gradient(45deg, #f5365c, #f56036);
    border: none;
    color: #fff;
}

.btn-gradient-primary:hover,
.btn-gradient-danger:hover {
    filter: brightness(1.1);
    transform: scale(1.03);
    transition: all 0.2s ease-in-out;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
}

#admins-table thead th {
    vertical-align: middle;
    background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
    color: #333;
    font-weight: 600;
    border-bottom: 2px solid #ccc;
}

#admins-table thead .btn-danger {
    font-size: 0.875rem;
    padding: 3px 10px;
    border-radius: 0.35rem;
}


</style>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
@endsection

@section('title', 'Manage Admins')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
            style="background: linear-gradient(135deg, #00b09b, #96c93d);">
            <h4 class="mb-0 fw-bold">Manage Owners</h4>
            <button class="btn btn-add-admin" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="bi bi-plus-lg me-1"></i> Add Owner
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="admins-table" width="100%">
              <thead class="table-light" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                <tr class="align-middle text-center fw-semibold">
                    <th class="rounded-start" style="min-width: 130px;">
                        <input type="checkbox" id="selectAllAdmins" class="form-check-input me-1"> 
                        <span id="selectAllAdminsText">Select All</span>
                    </th>
                    <th style="min-width: 150px;">Name</th>
                    <th style="min-width: 200px;">Email</th>
                    <th style="min-width: 150px;">Phone</th>
                    <th class="rounded-end" style="min-width: 180px;">
                        Action
                        <button id="deleteAllAdmins" class="btn btn-danger btn-sm ms-2 deleteAllAdmins shadow-sm">
                            <i class="bi bi-trash-fill me-1"></i> Delete All
                        </button>
                    </th>
                </tr>
              </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Add Owner Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">

      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(to right, #3a7bd5, #3a6073);">
        <h5 class="modal-title" id="addAdminModalLabel">
          <i class="bi bi-person-plus me-2"></i> Add New Owner
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Form Start -->
      <form action="{{ route('register.admins') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Modal Body -->
        <div class="modal-body px-4 py-3" style="max-height: 75vh; overflow-y: auto;">
          <div class="row g-4">

            <!-- Profile Picture -->
            <div class="col-12 text-center">
              <label class="form-label fw-semibold">Profile Picture</label>
              <div class="mb-2">
                <input type="file" name="profile_image" id="profilePicInput" accept="image/*"
                  class="form-control w-auto mx-auto @error('profile_image') is-invalid @enderror"
                  onchange="previewImage(event)">
                @error('profile_image')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <img id="profilePicPreview" src="{{ asset('assets/images/user/user-3296.png') }}" alt="Preview"
                class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover;">
            </div>

            <!-- Personal Info -->
            <div class="col-md-6">
              <label class="form-label">Owner Name</label>
              <input type="text" name="name" class="form-control rounded-pill shadow-sm" value="{{ old('name') }}" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Name as in Aadhaar</label>
              <input type="text" name="name_as_in_aadhaar" class="form-control rounded-pill shadow-sm" value="{{ old('name_as_in_aadhaar') }}" required>
              @error('name_as_in_aadhaar') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control rounded-pill shadow-sm" value="{{ old('email') }}" required>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control rounded-pill shadow-sm" value="{{ old('phone') }}" required>
              @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control rounded-pill shadow-sm" required>
              @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="password_confirmation" class="form-control rounded-pill shadow-sm" required>
              @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Address Section -->
            <div class="col-12">
              <fieldset class="border p-3 rounded-3">
                <legend class="float-none w-auto px-2 fw-bold text-primary">📍 Address Details</legend>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Flat / Apartment</label>
                    <input type="text" name="flat" class="form-control rounded-pill shadow-sm" value="{{ old('flat') }}" required>
                    @error('flat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Street</label>
                    <input type="text" name="street" class="form-control rounded-pill shadow-sm" value="{{ old('street') }}" required>
                    @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control rounded-pill shadow-sm" value="{{ old('city') }}" required>
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control rounded-pill shadow-sm" value="{{ old('state') }}" required>
                    @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Postcode</label>
                    <input type="text" name="postcode" class="form-control rounded-pill shadow-sm" value="{{ old('postcode') }}" required>
                    @error('postcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </fieldset>
            </div>

            <!-- Document Details -->
            <div class="col-md-4">
                <label class="form-label">Aadhaar Number</label>
                <input type="text" name="aadhaar_no" id="aadhaar_no" class="form-control rounded-pill shadow-sm" value="{{ old('aadhaar_no') }}" required oninput="formatAadhaar(this)">
                @error('aadhaar_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Pan Number</label>
              <input type="text" name="pan_no" class="form-control rounded-pill shadow-sm" value="{{ old('pan_no') }}" required>
              @error('pan_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">GST Number</label>
              <input type="text" name="gst_no" class="form-control rounded-pill shadow-sm" value="{{ old('gst_no') }}" required>
              @error('gst_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Bank Info -->
            <div class="col-md-4">
              <label class="form-label">Bank A/c Number</label>
              <input type="text" name="bank_ac_no" class="form-control rounded-pill shadow-sm" value="{{ old('bank_ac_no') }}" required>
              @error('bank_ac_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Bank Name</label>
              <input type="text" name="bank_name" class="form-control rounded-pill shadow-sm" value="{{ old('bank_name') }}" required>
              @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">IFSC Code</label>
              <input type="text" name="ifsc" class="form-control rounded-pill shadow-sm" value="{{ old('ifsc') }}" required>
              @error('ifsc') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Branch</label>
              <input type="text" name="branch" class="form-control rounded-pill shadow-sm" value="{{ old('branch') }}" required>
              @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">UPI Phone Number</label>
              <input type="text" name="upi_phone" class="form-control rounded-pill shadow-sm" value="{{ old('upi_phone') }}" required>
              @error('upi_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-check-circle me-1"></i> Create Owner
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Owner Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">

      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(to right, #3a7bd5, #3a6073);">
        <h5 class="modal-title" id="addAdminModalLabel">
          <i class="bi bi-person-plus me-2"></i> Edit Owner
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Form Start -->
      <form action="{{ route('edit.admin', ['id' => 0]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Modal Body -->
        <div class="modal-body px-4 py-3" style="max-height: 75vh; overflow-y: auto;">
          <div class="row g-4">

            <!-- Profile Picture -->
            <div class="col-12 text-center">
              <label class="form-label fw-semibold">Profile Picture</label>
              <div class="mb-2">
                <input type="file" name="profile_image" id="profilePicInput" accept="image/*"
                  class="form-control w-auto mx-auto @error('profile_image') is-invalid @enderror"
                  onchange="previewImage(event)">
                @error('profile_image')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <img id="profilePicPreview" src="{{ asset('assets/images/user/user-3296.png') }}" alt="Preview"
                class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover;">
            </div>

            <!-- Personal Info -->
            <div class="col-md-6">
              <label class="form-label">Owner Name</label>
              <input type="text" name="name" class="form-control rounded-pill shadow-sm" value="{{ old('name') }}" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Name as in Aadhaar</label>
              <input type="text" name="name_as_in_aadhaar" class="form-control rounded-pill shadow-sm" value="{{ old('name_as_in_aadhaar') }}" required>
              @error('name_as_in_aadhaar') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control rounded-pill shadow-sm" value="{{ old('email') }}" required>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control rounded-pill shadow-sm" value="{{ old('phone') }}" required>
              @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control rounded-pill shadow-sm" required>
              @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="password_confirmation" class="form-control rounded-pill shadow-sm" required>
              @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Address Section -->
            <div class="col-12">
              <fieldset class="border p-3 rounded-3">
                <legend class="float-none w-auto px-2 fw-bold text-primary">📍 Address Details</legend>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Flat / Apartment</label>
                    <input type="text" name="flat" class="form-control rounded-pill shadow-sm" value="{{ old('flat') }}" required>
                    @error('flat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Street</label>
                    <input type="text" name="street" class="form-control rounded-pill shadow-sm" value="{{ old('street') }}" required>
                    @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control rounded-pill shadow-sm" value="{{ old('city') }}" required>
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control rounded-pill shadow-sm" value="{{ old('state') }}" required>
                    @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Postcode</label>
                    <input type="text" name="postcode" class="form-control rounded-pill shadow-sm" value="{{ old('postcode') }}" required>
                    @error('postcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </fieldset>
            </div>

            <!-- Document Details -->
            <div class="col-md-4">
                <label class="form-label">Aadhaar Number</label>
                <input type="text" name="aadhaar_no" id="aadhaar_no" class="form-control rounded-pill shadow-sm" value="{{ old('aadhaar_no') }}" required oninput="formatAadhaar(this)">
                @error('aadhaar_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Pan Number</label>
              <input type="text" name="pan_no" class="form-control rounded-pill shadow-sm" value="{{ old('pan_no') }}" required>
              @error('pan_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">GST Number</label>
              <input type="text" name="gst_no" class="form-control rounded-pill shadow-sm" value="{{ old('gst_no') }}" required>
              @error('gst_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Bank Info -->
            <div class="col-md-4">
              <label class="form-label">Bank A/c Number</label>
              <input type="text" name="bank_ac_no" class="form-control rounded-pill shadow-sm" value="{{ old('bank_ac_no') }}" required>
              @error('bank_ac_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Bank Name</label>
              <input type="text" name="bank_name" class="form-control rounded-pill shadow-sm" value="{{ old('bank_name') }}" required>
              @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">IFSC Code</label>
              <input type="text" name="ifsc" class="form-control rounded-pill shadow-sm" value="{{ old('ifsc') }}" required>
              @error('ifsc') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Branch</label>
              <input type="text" name="branch" class="form-control rounded-pill shadow-sm" value="{{ old('branch') }}" required>
              @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">UPI Phone Number</label>
              <input type="text" name="upi_phone" class="form-control rounded-pill shadow-sm" value="{{ old('upi_phone') }}" required>
              @error('upi_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-check-circle me-1"></i> Update Owner
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
          var adminsTable = $('#admins-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("manage.admins") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'user_info', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // 🔄 Pagination Click or Any Table Redraw Event
            adminsTable.on('draw', function () {
              $('#selectAllAdmins').prop('checked', false);
              $('#selectAllAdminsText').text('Select All');
            });

              // Delete All Testimonials Button Click
              $('.deleteAllAdmins').click(function () {
              var adminIds = $('.admin-check:checked').map(function() {
                return $(this).data('id');
              }).get();

              if (adminIds.length === 0) {
                Swal.fire({
                  icon: 'warning',
                  title: 'No Selection',
                  text: 'Please select at least one admin to delete.',
                  confirmButtonColor: '#3085d6',
                  confirmButtonText: 'OK'
                });
                return;
              }

              if (confirm("Are you sure you want to delete this admins?")) {
                $.ajax({
                  url: '{{ route("delete.multiple.admins") }}',
                  type: 'DELETE',
                  data: {
                      _token: '{{ csrf_token() }}',
                      ids: adminIds
                  },
                  success: function (response) {
                      toastr.success(response.message);

                      adminsTable.ajax.reload(null, false); // false to keep the current paging

                      $('#selectAllAdmins').prop('checked', false);

                  },
                  error: function () {
                      toastr.error('Failed to update status.');
                  }
                });
              }
            });


            // End Document Ready
        });
    </script>
    <script>
  function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
      document.getElementById('profilePicPreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }

  function formatAadhaar(input) {
    let value = input.value.replace(/\D/g, ''); // Remove all non-digit characters
    
    if (value.length > 12) {
      value = value.substring(0, 12); // Limit to 12 digits
    }

    // Format the value as XXXX XXXX XXXX
    input.value = value.replace(/(\d{4})(\d{1,4})(\d{1,4})?/, '$1 $2 $3').trim();
  }
</script>
<script>
$(document).ready(function () {
  // Use event delegation
  $(document).on('click', '[data-bs-target="#editAdminModal"]', function () {
    const modal = $('#editAdminModal');
    // Debugging (optional)
    // console.log($(this).data());

    // Set values in modal fields using jQuery
    modal.find('[name="name"]').val($(this).data('name') || '');
    modal.find('[name="email"]').val($(this).data('email') || '');
    modal.find('[name="name_as_in_aadhaar"]').val($(this).data('name-as-aadhaar') || '');
    modal.find('[name="phone"]').val($(this).data('phone') || '');
    modal.find('[name="flat"]').val($(this).data('flat') || '');
    modal.find('[name="street"]').val($(this).data('street') || '');
    modal.find('[name="city"]').val($(this).data('city') || '');
    modal.find('[name="state"]').val($(this).data('state') || '');
    modal.find('[name="postcode"]').val($(this).data('postcode') || '');
    modal.find('[name="aadhaar_no"]').val($(this).data('aadhaar-no') || '');
    modal.find('[name="pan_no"]').val($(this).data('pan-no') || '');
    modal.find('[name="gst_no"]').val($(this).data('gst-no') || '');
    modal.find('[name="bank_ac_no"]').val($(this).data('bank-ac-no') || '');
    modal.find('[name="bank_name"]').val($(this).data('bank-name') || '');
    modal.find('[name="ifsc"]').val($(this).data('ifsc') || '');
    modal.find('[name="branch"]').val($(this).data('branch') || '');
    modal.find('[name="upi_phone"]').val($(this).data('upi-no') || '');

    const adminId = $(this).data('id');
  
    // Replace `0` with the correct ID in form action
    const form = modal.find('form');
    let action = form.attr('action');
    // Update the form action URL with the actual admin ID
    action = action.replace(/\/0$/, '/' + adminId);
    form.attr('action', action);

    // Set profile picture
    const profileImage = $(this).data('profile-image');
    // const fullPath = profileImage;

    modal.find('#profilePicPreview').attr('src', profileImage);

    // Clear password fields
    modal.find('[name="password"]').val('');
    modal.find('[name="password_confirmation"]').val('');
  });

  // Select Check Box click To Select All Checkbox
  $("#selectAllAdmins").click(function () {
    $(".admin-check").prop('checked', $(this).prop('checked'));
    if ($('.admin-check:checked').length === $('.admin-check').length) {
      $('#selectAllAdminsText').text('Disable All');
    } else {
      $('#selectAllAdminsText').text('Select All');
    }
  });

    // Individual Admins Click To Trigger Select All Checkbox
    $(document).on('click', '.admin-check', function () {
      $('#selectAllAdmins').prop('checked', $('.admin-check:checked').length === $('.admin-check').length);
      if ($('.admin-check:checked').length === $('.admin-check').length) {
        $('#selectAllAdminsText').text('Disable All');
      } else {
        $('#selectAllAdminsText').text('Select All');
      }
    });

  // End Document Ready
});

</script>


    @if ($errors->any())
        <script>
            $(document).ready(function () {
                const addModal = new bootstrap.Modal(document.getElementById('addAdminModal'));
                addModal.show();

                // Convert Laravel validation errors to a JS object
                const validationErrors = @json($errors->toArray());
                console.log("Validation Errors:", validationErrors);

            });
        </script>
    @endif
@endsection
