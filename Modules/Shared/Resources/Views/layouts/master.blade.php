<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>@yield('title')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Sweet Aler -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right top, #a18cd1, #fbc2eb);
      background-attachment: fixed;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: linear-gradient(135deg, #ff6a00, #ee0979, #0acffe, #495aff);
      background-size: 400% 400%;
      animation: animateSidebar 15s ease infinite;
      color: white;
      z-index: 1000;
      padding-top: 3rem;
    }

    @keyframes animateSidebar {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .sidebar a {
      color: #ffffffd9;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .sidebar a i {
      margin-right: 12px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      border-left: 4px solid #fff;
    }

    .navbar {
      margin-left: 250px;
      padding: 1rem 2rem;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #fff;
    }

    .profile-img {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid white;
    }

    .main {
      margin-left: 250px;
      padding: 2rem;
      color: #fff;
    }

    .card {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      border-radius: 1rem;
      backdrop-filter: blur(15px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      transition: all 0.3s;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0,0,0,0.2);
    }

    h3, h5 {
      font-weight: bold;
    }

    @media(max-width: 768px) {
      .sidebar {
        position: absolute;
        left: -250px;
        transition: 0.3s ease;
      }

      .sidebar.open {
        left: 0;
      }

      .navbar,
      .main {
        margin-left: 0;
      }

      .toggle-btn {
        font-size: 28px;
        cursor: pointer;
      }
    }

    /* .navbar {
    background: linear-gradient(135deg, #cfd9df, #e2ebf0);
    padding: 15px;
    color: #333;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    }

    .profile-img {
        border: 2px solid #fff;
        transition: transform 0.2s ease-in-out;
    }

    .profile-img:hover {
        transform: scale(1.1);
    }

    .toggle-btn {
        font-size: 24px;
        color: #fff;
        cursor: pointer;
    } */
  </style>
  <style>
  .star-rating i {
    font-size: 1.8rem;
    color: #ccc;
    cursor: pointer;
    transition: color 0.2s;
  }

  .star-rating i.active {
    color: #ffc107;
  }

    .modal-content {
      border-radius: 1rem;
    }

    .modal-header {
      border-bottom: 0;
    }

    .modal-footer {
      border-top: 0;
    }

    /* Guest Level Modal */
    .level-circle {
      transition: transform 0.3s ease;
      box-shadow: 0 0 0 rgba(0, 0, 0, 0.1);
    }

    .glow {
      box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
    }

    .blink {
      animation: blink 1.5s infinite;
    }

    @keyframes blink {
      50% { opacity: 0.5; }
    }


</style>

  @yield('styles')
</head>
<body>

  <!-- Sidebar -->
@include('shared::layouts.sidebar')

<!-- Navbar -->
@include('shared::layouts.navbar')


  <!-- Main Content -->
   @yield('content')

   <!-- Bootstrap Modals -->
    @yield('modals')
    <!-- Reminder Modal -->
    <div class="modal fade" id="reviewReminderModal" tabindex="-1" aria-labelledby="reviewReminderModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header bg-primary text-white rounded-top-4">
            <h5 class="modal-title" id="reviewReminderModalLabel">We value your feedback!</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('add.testimonial',  ['property' => 0]) }}" method="POST" id="reviewForm">
            @csrf
            <div class="modal-body">

              <!-- Hidden field to store selected rating -->
              <input type="hidden" name="ratings" id="rating" value="">

              <!-- Hidden Reminder ID -->
              <input type="hidden" name="reminder_id" id="reminder_id">

              <!-- Star Rating -->
              <div class="mb-3 text-center">
                <div class="star-rating">
                  <i class="bi bi-star" data-value="1"></i>
                  <i class="bi bi-star" data-value="2"></i>
                  <i class="bi bi-star" data-value="3"></i>
                  <i class="bi bi-star" data-value="4"></i>
                  <i class="bi bi-star" data-value="5"></i>
                </div>
              </div>

              <!-- Feedback Textarea -->
              <div class="mb-3">
                <label for="feedback" class="form-label">Your Feedback</label>
                <textarea class="form-control" id="feedback" name="description" rows="4" placeholder="Tell us what you liked or how we can improve..."></textarea>
              </div>

            </div>
            <div class="modal-footer d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary skip_reminder">Skip</button>
              <button type="button" class="btn btn-outline-warning remind_later" id="remindLaterBtn">Remind Me Later</button>
              <button type="submit" class="btn btn-success">Submit Feedback</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Game-Style Guest Level Modal -->
    <div class="modal fade" id="guestLevelMapModal" tabindex="-1" aria-labelledby="guestLevelMapModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="guestLevelMapModalLabel">🚀 Your Guest Journey</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body py-4 px-3" style="max-height: 600px; overflow-y: auto;">

            @php
              $tiers = [
                  1000 => 'HS Explorer',
                  2000 => 'Silver Voyager',
                  3000 => 'Gold Pathfinder',
                  4000 => 'Platinum Nomad',
                  5000 => 'Diamond Trailblazer',
                  PHP_INT_MAX => 'Pro Explorer' // Above all
              ];

              $tierKeys = array_keys($tiers);
              $currentPoints = auth()->user()->profile->reward_points ?? 0;
              $currentLabel = auth()->user()->guest_level;

              // Find the next level
              $nextLevelPoints = null;
              $nextLabel = null;
              foreach ($tiers as $points => $label) {
                  if ($points > $currentPoints) {
                      $nextLevelPoints = $points;
                      $nextLabel = $label;
                      break;
                  }
              }
            @endphp

            <!-- Current Points Display -->
            <div class="text-center mb-4">
              <h5 class="mb-1">🎯 You have <span class="text-success">{{ $currentPoints }}</span> points</h5>
            </div>

            <!-- Journey Map -->
            <div class="d-flex flex-column align-items-center gap-5 position-relative">
              @php
                $isPassed = true;
                $prev = 1;
              @endphp

              @foreach ($tiers as $points => $label)
                @php
                  $rangeStart = $prev;
                  $rangeEnd = $points === PHP_INT_MAX ? '∞' : $points;
                  $isCurrent = $currentLabel === $label;
                  $isUpcoming = !$isCurrent && $isPassed && $points > $currentPoints;
                  if ($isCurrent) $isPassed = false;
                  $prev = $points + 1;
                @endphp

                <div class="text-center">
                  <div class="rounded-circle mx-auto level-circle d-flex justify-content-center align-items-center 
                    {{ $isCurrent ? 'bg-success text-white glow blink' : ($isUpcoming ? 'bg-warning text-dark' : 'bg-light text-muted') }}"
                    style="width: 70px; height: 70px;">
                    <i class="bi bi-star-fill fs-4 text-warning"></i>
                  </div>
                  <div class="mt-2 fw-semibold fs-6 {{ $isCurrent ? 'text-success' : ($isUpcoming ? 'text-warning' : 'text-muted') }}">
                    {{ $label }}
                  </div>
                  <small class="text-muted">
                    {{ $rangeStart }} - {{ is_numeric($rangeEnd) ? $rangeEnd : '∞' }} pts
                  </small>

                  @if ($isUpcoming && is_numeric($rangeEnd))
                    <div class="mt-1">
                      <span class="badge bg-warning text-dark small">Next level at {{ $rangeEnd }} pts</span>
                    </div>
                  @endif
                </div>

                @if (!$loop->last)
                  <div style="width: 3px; height: 40px; background-color: #dee2e6;"></div>
                @endif
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>





    <!-- Toast Message -->
    @if (session('success'))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="commonToast" class="toast align-items-center text-bg-success border-0 shadow show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    ✅ {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="commonToast" class="toast align-items-center text-bg-danger border-0 shadow show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    ❌ {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- Toastr -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    const AUTH_USER_ID = {{ auth()->user()->id }};
</script>

  <script>
    function toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('open');
    }

    //Toaster Message 
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('commonToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            // Optional: Manually hide after delay (if needed extra)
            setTimeout(() => toast.hide(), 4000);
        }
    });
  </script>
  <!-- <script>
    // Disable right-click
    document.addEventListener('contextmenu', event => event.preventDefault());

    // Disable common inspect shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || 
            (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'C' || e.key === 'J')) ||
            (e.ctrlKey && e.key === 'U')) {
            e.preventDefault();
        }
    });
</script> -->

<!-- Review Reminder Popup Modal Open -->
<script>
  $(document).ready(function () {
      // Check last shown time from localStorage
      const lastShown = localStorage.getItem('lastReviewReminderShown');
      const now = new Date().getTime();
      const FIVE_MINUTES = 5 * 60 * 1000;  // 5 minutes

      if (!lastShown || now - lastShown > FIVE_MINUTES) {
          // Make AJAX request
          $.ajax({
              url: '{{ route('user.reminders') }}',
              method: 'POST',
              data: {
                  _token: '{{ csrf_token() }}'
              },
              success: function (response) {
                  $.each(response.reminders, function (index, reminder) {
                    // alert(reminder.booking?.property?.slug ?? 'No slug found');
                      if (reminder.reminder_date === '{{ date('Y-m-d') }}' && AUTH_USER_ID === reminder.user_id) {
                        const modal = $('#reviewReminderModal');
                        // Skip Reminder code
                        $('.skip_reminder').attr('data-id', reminder.id);
                        $('.remind_later').attr('data-id', reminder.id);
                        $('#reminder_id').val(reminder.id);

                        // Add Testimonial Purpose code
                        // Replace `0` with the correct ID in form action
                        const form = modal.find('form');
                        let action = form.attr('action');
                        // Update the form action URL with the actual admin ID
                        action = action.replace(/\/0$/, '/' + reminder.booking?.property?.slug);
                        form.attr('action', action);

                        // Show the modal
                        modal.modal('show');

                        localStorage.setItem('lastReviewReminderShown', new Date().getTime());  // after modal show New time set for Local storage
                        return false;
                      }
                  });
              }
          });
      }

      // Skip Button click to remove reminder
      $('.skip_reminder').on('click', function () {
        var id = $(this).data('id');
        $.ajax({
              url: '{{ url('skip-reminder') }}/' + id ,
              method: 'DELETE',
              data: {
                  _token: '{{ csrf_token() }}'
              },
              success: function (response) {
                toastr.success(response.message);
                $('#reviewReminderModal').modal('hide');
              }
          });
      });

      // Remind Later click 
      $('.remind_later').on('click', function () {
      var id = $(this).data('id');
      $.ajax({
            url: '{{ url('remind-later') }}/' + id ,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
              toastr.success(response.message);
              $('#reviewReminderModal').modal('hide');
            }
        });
    });
  });
</script>

<script>
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

  // Submit Handler
  // document.getElementById('reviewForm').addEventListener('submit', function (e) {
  //   e.preventDefault();
  //   const feedback = document.getElementById('feedback').value;
  //   console.log('Rating:', selectedRating);
  //   console.log('Feedback:', feedback);
  //   // TODO: Submit via AJAX or form post
  //   // Close modal
  //   const modal = bootstrap.Modal.getInstance(document.getElementById('reviewReminderModal'));
  //   modal.hide();
  // });

  // // Remind Later Handler
  // document.getElementById('remindLaterBtn').addEventListener('click', function () {
  //   // TODO: Store reminder preference (e.g., via AJAX or localStorage)
  //   alert('We’ll remind you later!');
  //   const modal = bootstrap.Modal.getInstance(document.getElementById('reviewReminderModal'));
  //   modal.hide();
  // });
</script>

<!--  Tooltip Activation Code -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
  });
</script>

  @yield('scripts')
</body>
</html>
