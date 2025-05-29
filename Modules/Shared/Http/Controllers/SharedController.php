<?php

namespace Modules\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Amenity\Repositories\Interfaces\AmenityRepositoryInterface;
use Modules\Property\Repositories\PropertyRepository;
use Modules\Shared\Repositories\Interfaces\SharedRepositoryInterface;
use Modules\Shared\Models\Reminder;
use Modules\Shared\Models\Testimonial;
use Modules\Shared\Models\FAQ;
use Modules\Property\Models\Property;
use Modules\Shared\Http\Requests\AddTestimonialRequest;
use Modules\Shared\Http\Requests\EditTestimonialRequest;
use Modules\Shared\Http\Requests\TestimonialToggleRequest;
use Modules\Shared\Http\Requests\TestimonialRemoveRequest;
use Modules\Shared\Http\Requests\MultipleTestimonialRemoveRequest;
use Modules\Shared\Http\Requests\AddFaqRequest;
use Modules\Shared\Http\Requests\EditFaqRequest;
use Modules\Shared\Http\Requests\FaqToggleRequest;
use Modules\Shared\Actions\HandleFormSubmission;



class SharedController extends Controller
{
    protected $sharedRepository;

    protected $amenityRepository;

    protected $propertyRepository;

    public function __construct(SharedRepositoryInterface $sharedRepository, AmenityRepositoryInterface $amenityRepository, PropertyRepository $propertyRepository)
    {
        $this->sharedRepository = $sharedRepository;
        $this->amenityRepository = $amenityRepository;
        $this->propertyRepository = $propertyRepository;
    }

    public function index(Request $request)
    {
        $allFaqs = $this->sharedRepository->allFaq();
        $allAmenities = $this->amenityRepository->fetchAllAmenities();
        $properties = $this->propertyRepository->near_recommended_fromProperties($request);
        $recommendedProperties = $properties['recommendedProperties'];
        $nearbyProperties = $properties['nearfromProperties'];
        $allPropertyTypes = $this->propertyRepository->allPropertyTypes();

        return view('shared::dashboard', compact('allFaqs', 'allAmenities', 'nearbyProperties', 'recommendedProperties', 'allPropertyTypes'));
    }

    // Reminders
    public function userReminders(Request $request)
    {
        return $this->sharedRepository->userReminders($request);
    }

    public function skipReminder(Reminder $reminder)
    {
        return $this->sharedRepository->skipReminder($reminder->id);
    }

    public function remindLater(Reminder $reminder, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->remindLater($reminder, $handler);
    }

    // Testimonials
    public function manageTestimonials(Request $request)
    {
        if ($request->ajax()) {
            return $this->sharedRepository->manageTestimonials($request);
        }

        return view('shared::manage-testimonials');
    }
    public function addTestimonial(AddTestimonialRequest $request, HandleFormSubmission $handler, Property $property)
    {
        return $this->sharedRepository->addTestimonial($request, $handler, $property);
    }

    public function testimonalsToggle(TestimonialToggleRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->testimonialsToggle($request, $handler);
    }

    public function deleteTestimonial(TestimonialRemoveRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->deleteTestimonial($request, $handler);
    }

    public function deleteMultipleTestimonial(MultipleTestimonialRemoveRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->deleteMultipleTestimonial($request, $handler);
    }

    public function enableMultipleTestimonial(MultipleTestimonialRemoveRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->enableMultipleTestimonial($request, $handler);
    }

    public function disableMultipleTestimonial(MultipleTestimonialRemoveRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->disableMultipleTestimonial($request, $handler);
    }

    public function editTestimonial(EditTestimonialRequest $request, HandleFormSubmission $handler, Testimonial $testimonial)
    {
        return $this->sharedRepository->editTestimonial($request, $handler, $testimonial);
    }

    // FAQs
    public function manageFaqs(Request $request)
    {
        if ($request->ajax()) {
            return $this->sharedRepository->manageFaqs($request);
        }
        return view('shared::manage-faqs');
    }

    public function addFaq(AddFaqRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->addFaq($request, $handler);
    }

    public function editFaq(EditFaqRequest $request, HandleFormSubmission $handler, FAQ $faq)
    {
        return $this->sharedRepository->editFaq($request, $handler, $faq);
    }

    public function toggleFaq(FaqToggleRequest $request, HandleFormSubmission $handler)
    {
        return $this->sharedRepository->toggleFaq($request, $handler);
    }

    public function deleteFaq(FAQ $faq)
    {
        return $this->sharedRepository->deleteFaq($faq);
    }

}
