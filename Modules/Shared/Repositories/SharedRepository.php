<?php

namespace Modules\Shared\Repositories;

use Modules\Shared\Models\FAQ;
use Modules\Shared\Models\Setting;
use Modules\Shared\Repositories\Interfaces\SharedRepositoryInterface;
use Modules\Shared\Models\Reminder;
use Modules\Shared\Models\Testimonial;
use Carbon\Carbon;
use Modules\Shared\Data\GenericFormData;
use Modules\Auth\Repositories\Interfaces\UserRepositoryInterface;
use Modules\Property\Models\Property;
use Yajra\DataTables\Facades\DataTables;

class SharedRepository implements SharedRepositoryInterface
{

    public function __construct(protected UserRepositoryInterface $userRepository) {}

    public function allFaq()
    {
        $allFaqs = collect(); // Initialize an empty collection

        FAQ::Active()->chunkById(100, function ($chunk) use (&$allFaqs) {
            $allFaqs->push(...$chunk);
        });

        return $allFaqs;
    }

    public function settingsValue($key)
    {
        return Setting::where('key', $key)->value('value');
    }

    // Reminder
    public function userReminders() {
        $reminders = auth()->user()->reminders()->with('booking.property')->get();
        return response()->json(['reminders' => $reminders, 'message' => 'Reminders fetched successfully!']);
    }

    public function skipReminder($id) {
        $reminder = $this->reminderSole($id);
        $reminder->delete();
        return response()->json(['message' => 'Reminder skipped successfully!']);
    }

    public function remindLater($reminder, $handler) {
        $reminder = $this->reminderSole($reminder->id);
        $data = [
            'reminder_date' => Carbon::parse($reminder->reminder_date)->addDays(2)->format('Y-m-d'),
            'is_sent' => true
        ];
        $final = GenericFormData::fromArray($data);
        $handler->update($final, $reminder);
        return response()->json(['message' => 'Reminder updated successfully!']);
    }

    public function reminderSole($id) {
        return Reminder::where('id', $id)->sole();
    }

    // Testimonials
    public function manageTestimonials() {
            $data = $this->fetchAllPropertyTestimonials();
    
            return DataTables::of($data)
                ->addIndexColumn()
    
                ->addColumn('property_info', function ($row) {
                    $property_type = e(optional($row->propertyType)->name ?? 'N/A');
                    $property_name = e($row->name ?? 'N/A');
                    $address = e($row->address ?? 'N/A');
                    $images = optional($row->images)->pluck('image_path')->toArray();
                
                    $first_pricing = optional($row->pricings->first());
                    $pricing = e($first_pricing->pricing ?? 'N/A');
                    $pricing_type = e($first_pricing->pricing_type ?? 'N/A');
                
                    $carousel_id = 'carousel_'.$row->id;
                    $carousel_items = collect($row->images ?? [])
                        ->pluck('image_path')
                        ->map(function ($image, $index) {
                            $active = $index === 0 ? 'active' : '';
                            $url = asset('assets/images/property_images/'.$image);
                
                            return <<<HTML
                                <div class="carousel-item {$active}">
                                    <img src="{$url}" class="d-block w-100" style="height:160px; object-fit:cover;">
                                </div>
                            HTML;
                        })->implode('');
                
                    $carousel_html = count($images) ? '
                        <div id="'.$carousel_id.'" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                '.$carousel_items.'
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#'.$carousel_id.'" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#'.$carousel_id.'" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>' : '<div class="text-muted">No Images</div>';
                
                    // Add checkbox with unique ID
                    $checkbox_html = '
                    <div class="d-flex justify-content-end align-items-center mb-1 pe-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input property-check" data-id="'.$row->id.'" id="checkbox_'.$row->id.'">
                            <label class="form-check-label small" for="checkbox_'.$row->id.'">Select All Testimonials</label>
                        </div>
                    </div>';

                    $testimonials = $row->testimonials;
                    (isset($testimonials) && $testimonials->isNotEmpty() ? $checkbox_html : $checkbox_html = '');

                
                    return '
                        <div class="card shadow-sm border-0" style="max-width: 450px;">
                            <!-- Top right checkbox -->
                            '.$checkbox_html.'
                            
                            <div class="row g-0 align-items-center">
                                <!-- Image Carousel -->
                                <div class="col-4 ps-2 py-2">
                                    '.$carousel_html.'
                                </div>
                                <!-- Property Info -->
                                <div class="col-8 pe-3 py-2">
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1 text-primary fw-semibold text-truncate" title="'.$property_name.'">'.$property_name.'</h6>
                                        <p class="mb-1 small"><strong>Type:</strong> '.$property_type.'</p>
                                        <p class="mb-1 small text-truncate" title="'.$address.'">
                                            <strong>Address:</strong> '.$address.'
                                        </p>
                                        <p class="mb-0 small">
                                            <strong>Price:</strong> ₹'.$pricing.' <small>/'.$pricing_type.'</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>';

                })
                

                // Testimonials
                ->addColumn('testimonials_info', function ($row) {
                    $propertyId = $row->id;
                    $testimonials = $row->testimonials;
                
                    if ($testimonials->isEmpty()) {
                        return '<div class="text-muted text-center">No Testimonials</div>';
                    }
                
                    $testimonialHtml = '<div class="d-flex flex-column gap-4">';
                
                    foreach ($testimonials as $testimonial) {
                        $user = optional($testimonial->user);
                        $userName = e($user->name ?? 'Anonymous');
                        $profileImage = $user->profile->profile_image;
                    
                        $feedback = e($testimonial->description);
                        $rating = intval($testimonial->ratings);
                        $id = $testimonial->id;
                        $enabled = $testimonial->is_active == 1 ? 'checked' : '';
                    
                        // Star rating HTML
                        $starsHtml = '';
                        for ($i = 1; $i <= 5; $i++) {
                            $starsHtml .= $i <= $rating
                                ? '<i class="bi bi-star-fill text-warning me-1"></i>'
                                : '<i class="bi bi-star text-muted me-1"></i>';
                        }
                    
                        // Testimonial Card HTML with left-side checkbox
                        $testimonialHtml .= <<<HTML
                        <div class="card border-0 shadow-sm testimonial-card" style="transition: 0.3s;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-3">
                                    <!-- Left Checkbox -->
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input testimonial-check" data-property-id="{$propertyId}" data-id="{$id}" id="testimonial_{$id}">
                                    </div>
                    
                                    <!-- Profile and Info -->
                                    <img src="{$profileImage}" alt="Profile" width="60" height="60" class="rounded-circle border" style="object-fit: cover;">
                                    
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1 text-dark fw-bold">{$userName}</h6>
                                                <div class="text-warning mb-2">{$starsHtml}</div>
                                                <p class="mb-0 text-muted" style="white-space: pre-line;">{$feedback}</p>
                                            </div>
                                            <div class="position-absolute top-0 end-0 m-3 d-flex gap-2">
                                                <button class="btn btn-icon btn-sm btn-outline-primary edit-testimonial" data-id="{$id}" data-ratings="{$rating}" data-feedback="{$feedback}" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-icon btn-sm btn-outline-danger delete-testimonial" data-id="{$id}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input toggle-status" type="checkbox" data-id="{$id}" {$enabled}>
                                            <label class="form-check-label small text-secondary">Enabled</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        HTML;
                    }
                    
                
                    $testimonialHtml .= '</div>';
                
                    return $testimonialHtml;
                })
                
                
                
    
            ->rawColumns(['property_info', 'testimonials_info'])
            ->make(true);
    }

    public function fetchAllPropertyTestimonials() {
        $allPropertiesTestimonials = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        Property::with(['testimonials' => function ($query) {
                        $query->withoutGlobalScope('activeTestimonials');
                    }, 'propertyType', 'pricings', 'images'])
            ->visibleTo($user)   // local scope
            ->chunkById(100, function ($chunk) use (&$allPropertiesTestimonials) {
                $allPropertiesTestimonials->push(...$chunk);
            });

        return $allPropertiesTestimonials;
    }

    public function addTestimonial($request, $handler, $property) {
        $testimonial_data = GenericFormData::fromRequest($request, ['ratings', 'description', 'reminder_id'],['user_id' => auth()->id()]);
        $customized[] = [
            'user_id' => $testimonial_data->get('user_id'),
            'ratings' => $testimonial_data->get('ratings'),
            'description' => $testimonial_data->get('description'),
        ];

        $handler->createChildren($property, 'testimonials', $customized);

        // Remove The Reminder
        $this->skipReminder($testimonial_data->get('reminder_id'));

        return redirect()->route('dashboard')->with('success', 'Testimonial added successfully!');
    }

    public function testimonialsToggle($request, $handler) {

        $testimonial_data = GenericFormData::fromRequest($request, ['id', 'is_active']);

        $testimonial = $this->testimonialSole($testimonial_data->get('id'));

        $handler->update($testimonial_data, $testimonial);

        return response()->json(['message' => 'Testimonial status updated successfully.']);
    }

    public function testimonialSole($id) {
        return Testimonial::withoutGlobalScope('activeTestimonials')->where('id', $id)->sole();
    }

    public function deleteTestimonial($request, $handler) {

        $testimonial_data = GenericFormData::fromRequest($request, ['id']);

        $testimonial = $this->testimonialSole($testimonial_data->get('id'));

        $handler->delete($testimonial);

        return response()->json(['message' => 'Testimonial deleted successfully.']);
    }

    public function deleteMultipleTestimonial($request, $handler) {

        $testimonial_data = GenericFormData::fromRequest($request, ['ids']);

        foreach ($testimonial_data->get('ids') as $id) {
            $testimonial = $this->testimonialSole($id);
            $handler->delete($testimonial);
        }

        return response()->json(['message' => 'Testimonial deleted successfully.']);
    }

    public function enableMultipleTestimonial($request, $handler) {

        $testimonial_data = GenericFormData::fromRequest($request, ['ids'], ['is_active' => 1]);
        $data = [
            'is_active' => $testimonial_data->get('is_active'),
        ];
        $testimonial_final = GenericFormData::fromArray($data);

        foreach ($testimonial_data->get('ids') as $id) {
            $testimonial = $this->testimonialSole($id);
            $handler->update($testimonial_final, $testimonial);
        }

        return response()->json(['message' => 'Testimonials enabled successfully.']);
    }

    public function disableMultipleTestimonial($request, $handler) {

        $testimonial_data = GenericFormData::fromRequest($request, ['ids'], ['is_active' => 0]);
        $data = [
            'is_active' => $testimonial_data->get('is_active'),
        ];
        $testimonial_final = GenericFormData::fromArray($data);

        foreach ($testimonial_data->get('ids') as $id) {
            $testimonial = $this->testimonialSole($id);
            $handler->update($testimonial_final, $testimonial);
        }

        return response()->json(['message' => 'Testimonials disabled successfully.']);
    }

    public function editTestimonial($request, $handler, $testimonial) {
        $testimonial_data = GenericFormData::fromRequest($request, ['ratings', 'description']);

        // $testimonial = $this->testimonialSole($testimonial_data->get('id'));

        $handler->update($testimonial_data, $testimonial);

        return redirect()->route('manage.testimonials')->with('success', 'Testimonial updated successfully!');

    }

    // FAQ
    public function manageFaqs($request)
    {
        $data = $this->fetchFaqs(); // assuming Spatie role is used

        return DataTables::of($data)
        ->addIndexColumn()
    
        // User info (name + image)
        ->addColumn('user_info', function ($row) {
            $name = e(optional($row->user)->name ?? 'N/A');
            $profileImage = $row->user->profile->profile_image ?? asset('default-user.png');
    
            return '
                <div class="d-flex align-items-center gap-3 p-2 bg-white rounded-3 shadow-sm">
                    <img src="'.$profileImage.'" alt="User Image"
                         class="rounded-circle border shadow-sm"
                         style="width: 42px; height: 42px; object-fit: cover;">
                    <div class="fw-semibold text-dark">'.$name.'</div>
                </div>
            ';
        })
    
        // Question
        ->addColumn('question', function ($row) {
            return '
                <div class="bg-white border rounded-3 p-3 shadow-sm">
                    <i class="bi bi-question-circle me-2 text-primary"></i> '.e($row->question).'
                </div>
            ';
        })
    
        // Answer
        ->addColumn('answer', function ($row) {
            return '
                <div class="bg-white border rounded-3 p-3 shadow-sm">
                    <i class="bi bi-chat-right-text me-2 text-success"></i> '.e($row->answer).'
                </div>
            ';
        })
    
        // Status Toggle
        ->addColumn('status_action', function ($row) {
            $checked = $row->is_active ? 'checked' : '';
    
            return '
            <div class="d-flex justify-content-center p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                <label class="form-switch">
                    <input type="checkbox" class="form-check-input faq-toggle visually-hidden"
                           data-id="'.$row->id.'" '.$checked.'>
                    <span class="slider"></span>
                </label>
            </div>';
        })
    
        // Action Buttons (Edit + Delete)
        ->addColumn('action', function ($row) {
            return '
                <div class="d-flex gap-2 flex-wrap justify-content-center">
    
                    <button type="button"
                            class="btn btn-sm btn-gradient-primary text-white rounded-pill d-flex align-items-center gap-2 shadow edit-faq"
                            data-bs-toggle="modal"
                            data-bs-target="#editFaqModal"
                            data-id="'.$row->id.'"
                            data-question="'.e($row->question).'"
                            data-answer="'.e($row->answer).'"
                            title="Edit">
                        <i class="bi bi-pencil-square fs-6"></i> Edit
                    </button>
    
                    <form action="'.route('delete.faq', $row->id).'" method="POST" class="d-inline-block"
                          onsubmit="return confirm(\'Are you sure you want to delete this item?\')">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit"
                                class="btn btn-sm btn-gradient-danger text-white rounded-pill d-flex align-items-center gap-2 shadow"
                                title="Delete">
                            <i class="bi bi-trash3-fill fs-6"></i> Delete
                        </button>
                    </form>
                </div>
            ';
        })
    
        ->rawColumns(['user_info', 'question', 'answer', 'status_action', 'action'])
        ->make(true);
    
    }

    // Fetch all Faqs
    public function fetchFaqs()
    {
        $allFaqs = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        FAQ::with('user')
            ->visibleTo($user)   // local scope
            ->chunkById(100, function ($chunk) use (&$allFaqs) {
                $allFaqs->push(...$chunk);
            });

        return $allFaqs;
    }

    public function addFaq($request,$handler)
    {
        $daq_data = GenericFormData::fromRequest($request, ['question', 'answer'], ['user_id' => auth()->id()]);
        $customized = [
            'user_id' => $daq_data->get('user_id'),
            'question' => $daq_data->get('question'),
            'answer' => $daq_data->get('answer'),
        ];

        $final = GenericFormData::fromArray($customized);

        $handler->create($final, 'Shared', 'FAQ');

        return redirect()->route('manage.faqs')->with('success', 'FAQ added successfully!');
    }

    public function editFaq($request, $handler, $faq) {
        $faq_data = GenericFormData::fromRequest($request, ['question', 'answer']);
        $customized = [
            'question' => $faq_data->get('question'),
            'answer' => $faq_data->get('answer'),
        ];

        $final = GenericFormData::fromArray($customized);

        $handler->update($final, $faq);

        return redirect()->route('manage.faqs')->with('success', 'FAQ updated successfully!');
    }

    public function deleteFaq($faq) {
        $faq->delete();
        return redirect()->route('manage.faqs')->with('success', 'FAQ deleted successfully!');
    }

    public function toggleFaq($request, $handler) {
        $faq_data = GenericFormData::fromRequest($request, ['id', 'is_active']);

        $faq = $this->faqSole($faq_data->get('id'));

        $handler->update($faq_data, $faq);

        return response()->json(['message' => 'FAQ updated successfully!']);
    }

    public function faqSole($id) {
        return FAQ::where('id', $id)->sole();
    }

}
