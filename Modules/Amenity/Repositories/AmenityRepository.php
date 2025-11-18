<?php

namespace Modules\Amenity\Repositories;

use Auth;
use Illuminate\Support\Str;
use Modules\Amenity\Models\Amenity;
use Modules\Amenity\Repositories\Interfaces\AmenityRepositoryInterface;
use Modules\Auth\Repositories\Interfaces\UserRepositoryInterface;
use Modules\Property\Models\Property;
use Modules\Shared\Data\GenericFormData;
use Modules\Shared\Services\FileUploadService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class AmenityRepository implements AmenityRepositoryInterface
{
    protected $userRepository;

    public function __construct(protected FileUploadService $uploadService, UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;

    }

    public function manageAmenities($request)
    {
        $data = $this->fetchAmenities(); // assuming Spatie role is used

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('name', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <span>'.e($row->name).'</span>
                    </div>
                ';
            })
            // Custom column for user name + image
            ->addColumn('user_info', function ($row) {
                $name = e(optional($row->user)->name ?? 'N/A');
                $profileImage = $row->user->profile->profile_image;

                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <img src="'.$profileImage.'" alt="User Image" class="rounded-circle shadow" style="width: 40px; height: 40px; object-fit: cover;">
                        <span>'.$name.'</span>
                    </div>
                ';
            })

            ->addColumn('action', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
            
                        <a href="#"
                           class="btn btn-sm btn-gradient-primary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2 edit-admin"
                           data-bs-toggle="modal"
                           data-bs-target="#editAmenityModal"
                           title="Edit Admin"
                           data-id="'.$row->id.'"
                           data-name="'.e($row->name).'"
                           data-icon="'.e($row->icon).'">
                            <i class="bi bi-pencil-fill fs-6"></i> <strong>Edit</strong>
                        </a>
            
                        <form action="'.route('delete.amenity', $row->id).'" method="POST" class="d-inline-block"
                              onsubmit="return confirm(\'Are you sure you want to delete this admin?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit"
                                    class="btn btn-sm btn-gradient-danger text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                                    title="Delete Admin">
                                <i class="bi bi-trash3-fill fs-6"></i> <strong>Delete</strong>
                            </button>
                        </form>
                    </div>
                ';
            })

            ->addColumn('icon', function ($row) {
                $iconPath = Storage::disk('s3')->url($row->icon);

                return '
                <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                <img src="'.$iconPath.'" alt="Icon" style="width: 40px; height: 40px; object-fit: cover;" class="rounded shadow">
                </div>
                ';
            })

             // Status Action Button
            ->addColumn('status_action', function ($row) {
                $checked = $row->is_active ? 'checked' : '';

                return '
                <div class="d-flex justify-content-center p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                    <label class="form-switch">
                        <input type="checkbox" class="form-check-input amenity-toggle visually-hidden"
                               data-id="'.$row->id.'" '.$checked.'>
                        <span class="slider"></span>
                    </label>
                </div>';
            })

            ->rawColumns(['name', 'user_info', 'icon', 'status_action', 'action'])
            ->make(true);
    }

    // Fetch all amenities
    public function fetchAmenities()
    {
        $allAmenities = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        Amenity::with('user')
            ->visibleTo($user)   // local scope
            ->chunkById(100, function ($chunk) use (&$allAmenities) {
                $allAmenities->push(...$chunk);
            });

        return $allAmenities;
    }

    public function fetchAllAmenities()
    {
        $allAmenities = collect(); // Initialize an empty collection

        Amenity::chunkById(100, function ($chunk) use (&$allAmenities) {
            $allAmenities->push(...$chunk);
        });

        return $allAmenities;
    }

    public function fetchAllActiveAmenities()
    {
        $allAmenities = collect(); // Initialize an empty collection

        Amenity::active()->chunkById(100, function ($chunk) use (&$allAmenities) {
            $allAmenities->push(...$chunk);
        });

        return $allAmenities;
    }

    public function addAmenity($request, $handler)
    {

        // File Upload
        if ($request->hasFile('icon')) {
            $imagePath = $this->uploadService->uploadToSS(
                        $request->file('icon'),
                        'amenities'
                        );
        }

        $amenity_data = GenericFormData::fromRequest($request, ['name', 'icon'], ['user_id' => Auth::user()->id]);

        $customize_amenity_data = [
            'user_id' => $amenity_data->get('user_id'),
            'name' => $amenity_data->get('name'),
            'slug' => Str::slug($amenity_data->get('name')),
            'icon' => $imagePath ?? null,
        ];

        // Wrap array in GenericFormData
        $amenityGenericData = GenericFormData::fromArray($customize_amenity_data);
        $handler->create($amenityGenericData, 'Amenity', 'Amenity');

        return redirect()->route('manage.amenities')->with('success', 'Amenity created successfully!');
    }

    public function editAmenity($request, $handler, $id)
    {
        $amenity = $this->amenitySole($id);
        // File Upload
        if ($request->hasFile('icon')) {
            $imagePath = $this->uploadService->uploadToPublic(
                $request->file('icon'),
                'amenities'
            );
        }

        $amenity_data = GenericFormData::fromRequest($request, ['name', 'icon']);

        $customize_amenity_data = [
            'name' => $amenity_data->get('name'),
            'slug' => Str::slug($amenity_data->get('name')),
            'icon' => $imagePath ?? $this->amenityImage($id),
        ];

        // Wrap array in GenericFormData
        $amenityGenericData = GenericFormData::fromArray($customize_amenity_data);
        $handler->update($amenityGenericData, $amenity);

        return redirect()->route('manage.amenities')->with('success', 'Amenity updated successfully!');
    }

    public function amenitySole($id)
    {
        return Amenity::id($id)->sole();
    }

    public function amenityImage($id)
    {
        return Amenity::id($id)->value('icon');
    }

    public function toggleAmenity($request, $handler)
    {
        $amenity_data = GenericFormData::fromRequest($request, ['id', 'is_active']);

        $amenity = $this->amenitySole($amenity_data->get('id'));

        $handler->update($amenity_data, $amenity);

        return response()->json(['message' => 'Amenity status updated successfully.']);
    }

    public function deleteAmenity($id)
    {
        $amenity = $this->amenitySole($id);
        $amenity->delete();

        return redirect()->route('manage.amenities')->with('success', 'Amenity deleted successfully!');
    }

    // Property Amenity
    public function addPropertyAmenityAdd($request, $handler)
    {
        $property_amenity_data = GenericFormData::fromRequest($request, ['property_id', 'amenity_id']);
        $property_amenity = $handler->create($property_amenity_data, 'Amenity', 'PropertyAmenity');
    }

    public function propertyAmenities($request, $propertyId)
    {
        $data = $this->fetchPropertyAmenities($propertyId);

        return DataTables::of($data)
            ->addIndexColumn()

            ->addColumn('amenity_info', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <img src="'.asset('assets/images/amenities/'.$row['icon']).'" alt="Amenity Icon" class="img-thumbnail rounded-circle" width="40" height="40">
                            <div>
                                <p class="mb-0 fw-bold">'.$row['name'].'</p>
                            </div>
                        </div>
                    </div>';
            })

             // Status Action Button
            ->addColumn('action', function ($row) {

                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <form action="'.route('property.amenities.delete', [$row['property_slug'], $row['id']]).'" method="POST" class="d-inline-block">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit"
                                    class="btn btn-sm btn-gradient-danger text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                                    title="Delete Admin">
                                <i class="bi bi-trash3-fill fs-6"></i> <strong>Remove</strong>
                            </button>
                        </form>
                    </div>';
            })
            //

            ->rawColumns(['amenity_info', 'action'])
            ->make(true);
    }

    public function fetchPropertyAmenities($propertyId)
    {
        $property = Property::with('amenities')->findOrFail($propertyId);

        return $property->amenities->map(function ($amenity) use ($property) {
            return [
                'property_slug' => $property->slug,
                'id' => $amenity->id,
                'name' => $amenity->name,
                'icon' => $amenity->icon,
            ];
        });
    }

    public function deletePropertyAmenity($property, $amenityId, $handler)
    {
        $handler->detach($property, 'amenities', [$amenityId]);

        return redirect()->route('property.amenities', $property->slug)->with('success', 'Amenity detached successfully!');
    }

    public function addPropertyAmenity($property, $request, $handler)
    {
        $property_amenity_data = GenericFormData::fromRequest($request, ['amenities']);
        $property_amenity = $handler->sync($property, 'amenities', $property_amenity_data->amenitiesAsInt());

        return redirect()->route('property.amenities', $property->slug)->with('success', 'Amenity added successfully!');
    }
}
