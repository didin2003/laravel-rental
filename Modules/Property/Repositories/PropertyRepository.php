<?php

namespace Modules\Property\Repositories;

use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Amenity\Models\Amenity;
use Modules\Auth\Repositories\Interfaces\UserRepositoryInterface;
use Modules\Property\Models\Booking;
use Modules\Property\Models\Coupon;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\Property\Models\SpecialDay;
use Modules\Property\Repositories\Interfaces\PropertyRepositoryInterface;
use Modules\Shared\Data\GenericFormData;
use Modules\Shared\Repositories\Interfaces\SharedRepositoryInterface;
use Modules\Shared\Services\FileUploadService;
use Redirect;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Modules\Shared\Helpers\CommonHelper;

class PropertyRepository implements PropertyRepositoryInterface
{
    public function __construct(protected FileUploadService $uploadService, protected UserRepositoryInterface $userRepository, protected SharedRepositoryInterface $sharedRepository) {}

    public function manageProperties($request)
    {

        $data = $this->fetchProperties(); // assuming Spatie role is used

        return DataTables::of($data)
            ->addIndexColumn()
            // Custom column for All Property Details in one column
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

                return '
                            <div class="card shadow-sm border-0" style="max-width: 450px;">
                                <div class="row g-0 align-items-center">
                                    <!-- Image Carousel on the LEFT -->
                                    <div class="col-4 ps-2 py-2">
                                        '.$carousel_html.'
                                    </div>
                                    <!-- Property Details on the RIGHT -->
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
                            </div>
                        ';

            })

            ->addColumn('action', function ($row) {
                // property datas
                $id = $row->id;
                $property_type = e(optional($row->propertyType)->id ?? 'N/A');
                $name = e($row->name);
                $slug = e($row->slug);
                $address = e($row->address);
                $location_lat = e($row->location_gps['lat']);
                $location_lng = e($row->location_gps['lng']);
                $location_gps = $location_lat.', '.$location_lng;
                $phone = e($row->phone);
                $description = e($row->description);
                $total_rooms = e($row->total_rooms);
                $total_capacity = e($row->total_capacity);
                $franchise_chain_name = e($row->franchise_chain_name);
                $billing_method = e($row->billing_method);
                $is_owned = e($row->is_owned);
                $is_active = e($row->is_active);
                $is_franchise = e($row->is_franchise);
                $from_deactivation_date = e($row->deactivated_date['from']);
                $to_deactivation_date = e($row->deactivated_date['to']);
                $location_start_date = e($row->location_start_date);

                // Amenities Data
                $amenities = $row->amenities()->pluck('amenities.id')->toArray();

                // Property Images Data
                $activeImages = $row->images()->get(['id', 'image_path', 'unit_capacity'])->toArray();
                $firstImage = $row->images()->first(); // Get first image model (NOT array)
                $data_unit_capacity = $firstImage->unit_capacity;

                // Pricing Data
                $first_pricing = optional($row->pricings->first());
                $bedrooms = e($first_pricing->unit['bedrooms'] ?? 'N/A');
                $bathrooms = e($first_pricing->unit['bathrooms'] ?? 'N/A');
                $slab = e($first_pricing->slab ?? 'N/A');
                $pricing = e($first_pricing->pricing ?? 'N/A');
                $pricing_type = e($first_pricing->pricing_type ?? 'N/A');
                $capacity = e($first_pricing->capacity ?? 'N/A');
                $max_capacity = e($first_pricing->max_capacity ?? 'N/A');

                // Nearby Data
                $first_nearby = $row->nearbies->first();
                $nearby_item = e($first_nearby->item ?? 'N/A');
                $nearby_Image = e($first_nearby->image_kms['image_path'] ?? 'N/A');
                $nearby_distance = e($first_nearby->image_kms['distance'] ?? 'N/A');

                return '
                        <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap gap-2 justify-content-start" style="max-width: 100%;">
                
                            <a href="#" 
                               class="btn btn-sm btn-gradient-primary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2 edit-property"
                               data-bs-toggle="modal"
                               data-bs-target="#editPropertyModal"
                               title="Edit Property"

                               data-id="'.$id.'"
                               data-name="'.$name.'"
                               data-slug="'.$slug.'"
                               data-property_type="'.$property_type.'"
                               data-address="'.$address.'"
                               data-location_gps="'.$location_gps.'"
                               data-phone="'.$phone.'"
                               data-description="'.$description.'"
                               data-total_rooms="'.$total_rooms.'"
                               data-total_capacity="'.$total_capacity.'"
                               data-franchise_chain_name="'.$franchise_chain_name.'"
                               data-billing_method="'.$billing_method.'"
                               data-is_owned="'.$is_owned.'"
                               data-is_active="'.$is_active.'"
                               data-is_franchise="'.$is_franchise.'"
                               data-from_deactivation_date="'.$from_deactivation_date.'"
                               data-to_deactivation_date="'.$to_deactivation_date.'"
                               data-location_start_date="'.$location_start_date.'"

                               data-amenities="'.e(json_encode($amenities)).'"

                               data-property_images="'.e(json_encode($activeImages)).'"
                               data-unit_capacity = "'.$data_unit_capacity.'"

                               data-bedrooms="'.$bedrooms.'"
                               data-bathrooms="'.$bathrooms.'"
                               data-slab="'.$slab.'"
                               data-pricing="'.$pricing.'"
                               data-pricing_type="'.$pricing_type.'"
                               data-capacity="'.$capacity.'"
                               data-max_capacity="'.$max_capacity.'"

                               data-nearby_item="'.$nearby_item.'"
                               data-nearby_Image="'.$nearby_Image.'"
                               data-nearby_distance="'.$nearby_distance.'"

                                <i class="bi bi-pencil-fill fs-6"></i> <strong>Edit</strong>
                            </a>
                
                            <form action="'.route('delete.property', $row->slug).'" method="POST" class="d-inline-block" onsubmit="return confirm(\'Are you sure you want to delete '.addslashes($row->name).' property?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" 
                                        class="btn btn-sm btn-gradient-danger text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                                        title="Delete Admin">
                                    <i class="bi bi-trash3-fill fs-6"></i> <strong>Delete</strong>
                                </button>
                            </form>
                
                            <a href="'.route('property.amenities', $row->slug).'"
                               class="btn btn-sm btn-gradient-secondary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                               title="Manage Amenities">
                                <i class="bi bi-list-check fs-6"></i> <strong>Amenities</strong>
                            </a>
                
                            <a href="'.route('property.images', $row->slug).'"
                               class="btn btn-sm btn-gradient-info text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                               title="Manage Images">
                                <i class="bi bi-images fs-6"></i> <strong>Images</strong>
                            </a>
                
                            <a href="'.route('property.pricings', $row->slug).'"
                               class="btn btn-sm btn-gradient-success text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                               title="Manage Pricings">
                                <i class="bi bi-currency-rupee fs-6"></i> <strong>Pricing</strong>
                            </a>
                
                            <a href="'.route('property.nearbies', $row->slug).'"
                               class="btn btn-sm btn-gradient-warning text-dark d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2"
                               title="Manage Nearby">
                                <i class="bi bi-geo-alt-fill fs-6"></i> <strong>Nearby</strong>
                            </a>
                        </div>
                    ';
            })

            ->rawColumns(['property_info', 'action'])
            ->make(true);

    }

    public function fetchProperties()
    {
        $allProperties = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        Property::with(['images', 'pricings', 'propertyType', 'amenities', 'nearbies'])
            ->visibleTo($user)   // local scope
            ->chunkById(100, function ($chunk) use (&$allProperties) {
                $allProperties->push(...$chunk);
            });

        return $allProperties;
    }

    public function allPropertyTypes()
    {
        $allPropertyTypes = collect(); // Initialize an empty collection

        PropertyType::chunkById(100, function ($chunk) use (&$allPropertyTypes) {
            $allPropertyTypes->push(...$chunk);
        });

        return $allPropertyTypes;
    }

    public function near_recommended_fromProperties($request)
    {

        $nearfromProperties = collect();

        $default_location = config('locations.default');
        $secondary_location = config('locations.secondary');

        // Adult Calculation
        $adults = $request->adults;
        $childrens = $request->children;
        $adult_children = $childrens / 2;
        $final_adults = $adults + $adult_children;
        
        Property::with(['pricings', 'images'])
            ->withAvg('testimonials as avg_testimonial_ratings', 'ratings')
            ->where(function ($query) use ($default_location, $secondary_location) {
                $query->like('address', $default_location)
                    ->orLike('address', $secondary_location);
            })
            // Location
            ->when($request->location, function ($query) use ($request) {
                $query->Like('address', $request->location);
            })
            // Booking from and to
            ->when($request->booking_from && $request->booking_to, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(deactivated_date, '$.to'))"), '<', $request->booking_from)
                      ->orWhere(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(deactivated_date, '$.from'))"), '>', $request->booking_to);
                });
            })
            // Property Type
            ->when($request->property_types, function ($query) use ($request) {
                $query->whereIn('property_type_id', $request->property_types);
            })
            // Price Range
            ->when(!is_null($request->price_min) && !is_null($request->price_max), function ($query) use ($request) {
                $query->whereHas('pricings', function ($q) use ($request) {
                    $q->whereBetween('pricing', [(float) $request->price_min, (float) $request->price_max]);
                });
            })
            // Bedrooms
            ->when($request->bedrooms, function ($query) use ($request) {
                $query->whereHas('pricings', function ($q) use ($request) {
                    $q->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(unit, '$.bedrooms'))"), '>=', $request->bedrooms);
                });
            })
            // Adults + Childrens
            ->when($request->adults, function ($query) use ($final_adults) {
                $query->whereHas('pricings', function ($q) use ($final_adults) {
                    $q->where('max_capacity', '>=', $final_adults);
                });
            })
            // Amenities
            ->when($request->amenities, function ($query) use ($request) {
                $query->whereHas('amenities', function ($q) use ($request) {
                    $q->whereIn('amenities.id', $request->amenities);
                });
            })
            ->whereHas('pricings', function ($query) {
                $query->where('pricing_type', 'Month');
            })
            ->whereHas('pricings', function ($query) {
                $query->where('pricing_type', 'Night');
            })
            ->whereHas('pricings', function ($query) {
                $query->where('pricing_type', 'SpecialDay');
            })
            ->chunkById(100, function ($chunk) use (&$nearfromProperties) {
                $nearfromProperties->push(...$chunk);
            });

        $nearfromProperties_ids = $nearfromProperties->pluck('id')->toArray();

        // Fetch all Recommended Properties
        $recommendedProperties = collect(); // Initialize an empty collection

        $default_location = config('locations.default');
        $secondary_location = config('locations.secondary');

        Property::with(['pricings', 'images'])
            ->withAvg('testimonials as avg_testimonial_ratings', 'ratings')
            ->whereNotIn('id', $nearfromProperties_ids)
            ->whereHas('pricings', function ($query) {
                $query->where('pricing_type', 'Month');
            })
            ->whereHas('pricings', function ($query) {
                $query->where('pricing_type', 'Night');
            })
            ->whereHas('pricings', function ($query) {
                $query->where('pricing_type', 'SpecialDay');
            })
            ->chunkById(100, function ($chunk) use (&$recommendedProperties) {
                $recommendedProperties->push(...$chunk);
            });

        return ['nearfromProperties' => $nearfromProperties, 'recommendedProperties' => $recommendedProperties];
    }

    // public function recommendedProperties($request)
    // {
    //     $recommendedProperties = collect(); // Initialize an empty collection

    //     $default_location = config('locations.default');
    //     $secondary_location = config('locations.secondary');

    //     Property::with(['pricings', 'images'])
    //         ->when(!$request->location, function ($query) use ($default_location, $secondary_location) {
    //             $query->notLike('address', $default_location)->notLike('address', $secondary_location);
    //         })
    //         ->when($request->location, function ($query) use ($request) {
    //             $query->notLike('address', $request->location);
    //         })
    //         ->whereHas('pricings', function ($query) {
    //             $query->where('pricing_type', 'Month');
    //         })
    //         ->whereHas('pricings', function ($query) {
    //             $query->where('pricing_type', 'Night');
    //         })
    //         ->whereHas('pricings', function ($query) {
    //             $query->where('pricing_type', 'SpecialDay');
    //         })
    //         ->chunkById(100, function ($chunk) use (&$recommendedProperties) {
    //             $recommendedProperties->push(...$chunk);
    //         });

    //     return $recommendedProperties;
    // }


    public function addProperty($request, $handler)
    {

        // Property Table Add
        $property_data = GenericFormData::fromRequest($request, [
            'property_type', 'name', 'address', 'location_gps', 'phone', 'description', 'total_rooms', 'total_capacity', 'franchise_chain_name', 'billing_method', 'is_owned', 'is_active', 'is_franchise', 'from_deactivation_date', 'to_deactivation_date', 'location_start_date', 'billing_method', 'franchise_chain_name',
        ]);

        // split location gps
        $location_gps = explode(',', $property_data->get('location_gps'));

        $property_customize_data = [
            'user_id' => Auth::user()->id,
            'name' => $property_data->get('name'),
            'slug' => Str::slug($property_data->get('name')),
            'property_type_id' => $property_data->get('property_type'),
            'location_gps' => [
                'lat' => $location_gps[0],
                'lng' => $location_gps[1],
            ],
            'address' => $property_data->get('address'),
            'phone' => $property_data->get('phone'),
            'description' => $property_data->get('description'),
            'total_rooms' => $property_data->get('total_rooms'),
            'total_capacity' => $property_data->get('total_capacity'),
            'is_owned' => $property_data->get('is_owned'),
            'is_active' => $property_data->get('is_active'),
            'is_franchise' => $property_data->get('is_franchise'),
            'deactivated_date' => [
                'from' => $property_data->get('from_deactivation_date'),
                'to' => $property_data->get('to_deactivation_date'),
            ],
            'location_start_date' => $property_data->get('location_start_date'),
            'billing_method' => $property_data->get('billing_method'),
            'franchise_chain_name' => $property_data->get('franchise_chain_name'),
        ];
        $property_customize_data_final = GenericFormdata::fromArray($property_customize_data);

        $property = $handler->create($property_customize_data_final, 'Property', 'Property');

        // Property Amenity Table Add
        $amenitie_data = GenericFormData::fromRequest($request, ['amenities']);
        $handler->attach($property, 'amenities', $amenitie_data->get('amenities'));

        // Property Image Table Add
        $property_image_data = GenericFormData::fromRequest($request, ['images', 'unit_capacity']);
        // File Upload
        if ($request->hasFile('images')) {
            $uploadedFileNames = $this->uploadService->uploadMultipleToPublic(
                $request->file('images'),
                'property_images'
            );
        }

        // Prepare children data (1 entry per image)
        $property_images = [];

        foreach ($uploadedFileNames as $fileName) {
            $property_images[] = [
                'image_path' => $fileName,
                'unit_capacity' => $property_image_data->get('unit_capacity'),
            ];
        }

        $handler->createChildren($property, 'images', $property_images);

        // Property Pricing Table Add
        $pricing_data = GenericFormData::fromRequest($request, ['bedrooms', 'bathrooms', 'slab', 'pricing', 'pricing_type', 'capacity', 'max_capacity']);

        $pricing_customize_data = [
            'property_id' => $property->id,
            'unit' => [
                'bedrooms' => $pricing_data->get('bedrooms'),
                'bathrooms' => $pricing_data->get('bathrooms'),
            ],
            'slab' => $pricing_data->get('slab'),
            'pricing' => $pricing_data->get('pricing'),
            'pricing_type' => $pricing_data->get('pricing_type'),
            'capacity' => $pricing_data->get('capacity'),
            'max_capacity' => $pricing_data->get('max_capacity'),
        ];

        $handler->createChildren($property, 'pricings', [$pricing_customize_data]);

        // Property Nearby Table Add
        $nearby_data = GenericFormData::fromRequest($request, ['nearby_item', 'nearby_distance', 'nearby_image']);

        // File Upload
        if ($request->hasFile('nearby_image')) {
            $imagePath = $this->uploadService->uploadToPublic(
                $request->file('nearby_image'),
                'nearby_images'
            );
        }

        $nearby_customize_data = [
            'property_id' => $property->id,
            'item' => $nearby_data->get('nearby_item'),
            'image_kms' => [
                'image_path' => $imagePath ?? null,
                'distance' => $nearby_data->get('nearby_distance'),
            ],
        ];

        $handler->createChildren($property, 'nearbies', [$nearby_customize_data]);

        return redirect()->route('manage.properties')->with('success', 'Property created successfully!');
    }

    public function editProperty($property, $request, $handler)
    {
        // Property Table Add
        $property_data = GenericFormData::fromRequest($request, [
            'property_type', 'name', 'address', 'location_gps', 'phone', 'description', 'total_rooms', 'total_capacity', 'franchise_chain_name', 'billing_method', 'is_owned', 'is_active', 'is_franchise', 'from_deactivation_date', 'to_deactivation_date', 'location_start_date', 'billing_method', 'franchise_chain_name',
        ]);

        // split location gps
        $location_gps = explode(',', $property_data->get('location_gps'));

        $property_customize_data = [
            'user_id' => Auth::user()->id,
            'name' => $property_data->get('name'),
            'slug' => Str::slug($property_data->get('name')),
            'property_type_id' => $property_data->get('property_type'),
            'location_gps' => [
                'lat' => $location_gps[0],
                'lng' => $location_gps[1],
            ],
            'address' => $property_data->get('address'),
            'phone' => $property_data->get('phone'),
            'description' => $property_data->get('description'),
            'total_rooms' => $property_data->get('total_rooms'),
            'total_capacity' => $property_data->get('total_capacity'),
            'is_owned' => $property_data->get('is_owned'),
            'is_active' => $property_data->get('is_active'),
            'is_franchise' => $property_data->get('is_franchise'),
            'deactivated_date' => [
                'from' => $property_data->get('from_deactivation_date'),
                'to' => $property_data->get('to_deactivation_date'),
            ],
            'location_start_date' => $property_data->get('location_start_date'),
            'billing_method' => $property_data->get('billing_method'),
            'franchise_chain_name' => $property_data->get('franchise_chain_name'),
        ];
        $property_customize_data_final = GenericFormdata::fromArray($property_customize_data);

        $property = $handler->update($property_customize_data_final, $property);

        // Property Amenity Table Add
        $amenitie_data = GenericFormData::fromRequest($request, ['amenities']);
        $handler->attach($property, 'amenities', $amenitie_data->get('amenities'));

        // Property Image Table Add
        $property_image_data = GenericFormData::fromRequest($request, ['images', 'unit_capacity']);
        // File Upload
        if ($request->hasFile('images')) {
            $uploadedFileNames = $this->uploadService->uploadMultipleToPublic(
                $request->file('images'),
                'property_images'
            );
        }

        // Prepare children data (1 entry per image)
        $property_images = [];

        if (! empty($uploadedFileNames)) {
            foreach ($uploadedFileNames as $fileName) {
                $property_images[] = [
                    'image_path' => $fileName,
                    'unit_capacity' => $property_image_data->get('unit_capacity'),
                ];
            }
        }

        $handler->createChildren($property, 'images', $property_images);

        // Property Pricing Table Add
        $pricing_data = GenericFormData::fromRequest($request, ['bedrooms', 'bathrooms', 'slab', 'pricing', 'pricing_type', 'capacity', 'max_capacity']);

        $pricing_customize_data = [
            'property_id' => $property->id,
            'unit' => [
                'bedrooms' => $pricing_data->get('bedrooms'),
                'bathrooms' => $pricing_data->get('bathrooms'),
            ],
            'slab' => $pricing_data->get('slab'),
            'pricing' => $pricing_data->get('pricing'),
            'pricing_type' => $pricing_data->get('pricing_type'),
            'capacity' => $pricing_data->get('capacity'),
            'max_capacity' => $pricing_data->get('max_capacity'),
        ];

        $handler->updateChildren($property, 'pricings', [$pricing_customize_data], 'property_id');

        // Property Nearby Table Add
        $nearby_data = GenericFormData::fromRequest($request, ['nearby_item', 'nearby_distance', 'nearby_image']);

        // File Upload
        if ($request->hasFile('nearby_image')) {
            $imagePath = $this->uploadService->uploadToPublic(
                $request->file('nearby_image'),
                'nearby_images'
            );
        }

        $nearby_customize_data = [
            'property_id' => $property->id,
            'item' => $nearby_data->get('nearby_item'),
            'image_kms' => [
                'image_path' => $imagePath ?? null,
                'distance' => $nearby_data->get('nearby_distance'),
            ],
        ];

        $handler->updateChildren($property, 'nearbies', [$nearby_customize_data], 'property_id');

        return redirect()->route('manage.properties')->with('success', 'Property Updated successfully!');
    }

    public function deleteProperty($property, $handler)
    {
        $handler->delete($property);

        return redirect()->route('manage.properties')->with('success', 'Property Deleted successfully!');
    }

    public function PropertyRemoveImage($propertyImage, $handler)
    {
        // existing image delete codes start
        $image_path = 'assets/images/property_images/'.$propertyImage->image_path;
        $this->uploadService->removeImageFromDirectory($image_path);
        // existing image delete codes end

        $handler->delete($propertyImage);

        return response()->json(['message' => 'Image deleted successfully!']);
    }

    // Property images
    public function propertyImages($request, $propertyId)
    {
        $data = $this->fetchPropertyImages($propertyId); // assuming Spatie role is used

        return DataTables::of($data)
            ->addIndexColumn()

            ->addColumn('images_path', function ($row) {
                return '
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden d-flex align-items-center" style="width: 120px;">
                        <img src="'.asset('assets/images/property_images/'.$row['image_path']).'" 
                             alt="Property Image" 
                             class="img-fluid object-fit-cover" 
                             style="height: 100px; width: 100px; border-radius: 10px;">
                    </div>';
            })

             // Status Action Button
            ->addColumn('action', function ($row) {

                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <form action="'.route('property.images.delete', [$row['property_slug'], $row['id']]).'" method="POST" class="d-inline-block">
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

            ->rawColumns(['images_path', 'action'])
            ->make(true);
    }

    public function fetchPropertyImages($propertyId)
    {
        $property = Property::with('images')->findOrFail($propertyId);

        return $property->images->map(function ($images) use ($property) {
            return [
                'property_slug' => $property->slug,
                'id' => $images->id,
                'image_path' => $images->image_path,
            ];
        });
    }

    public function addPropertyImage($property, $request, $handler)
    {
        $property_image_data = GenericFormData::fromRequest($request, [], ['unit_capacity' => 0]);

        // File Upload
        if ($request->hasFile('images')) {
            $uploadedFileNames = $this->uploadService->uploadMultipleToPublic(
                $request->file('images'),
                'property_images'
            );
        }

        // Combine each uploaded image with static values
        $property_images = collect($uploadedFileNames)->map(function ($fileName) use ($property_image_data) {
            return [
                'image_path' => $fileName,
                'unit_capacity' => $property_image_data->get('unit_capacity'),
            ];
        })->all();

        $handler->createChildren($property, 'images', $property_images);

        return redirect()->route('property.images', $property->slug)->with('success', 'Image added successfully!');

    }

    public function deletePropertyImage($property, $imageId, $handler)
    {
        $handler->deleteChildren($property, 'images', [$imageId]);

        return redirect()->route('property.images', $property->slug)->with('success', 'Image deleted successfully!');

    }

    // Special Days

    public function manageSpecialDays()
    {

        $data = $this->fetchSpecialDays(); // assuming Spatie role is used

        return DataTables::of($data)
            ->addIndexColumn()
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

            ->addColumn('date', function ($row) {

                return '
                        <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                            <span>'.($row->date).'</span>
                        </div>
                    ';
            })

            ->addColumn('description', function ($row) {

                return '
                        <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                            <span>'.($row->description).'</span>
                        </div>
                    ';
            })

            ->addColumn('property', function ($row) {
                return '
                        <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                            <span>'.($row->property->name).'</span>
                        </div>
                    ';
            })

            ->addColumn('action', function ($row) {
                return '
                        <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                
                            <a href="#"
                               class="btn btn-sm btn-gradient-primary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2 edit-special-day"
                               data-bs-toggle="modal"
                               data-bs-target="#editSpecialDayModal"
                               title="Edit Admin"
                               data-id="'.$row->id.'"
                               data-date="'.e($row->date).'"
                               data-description="'.e($row->description).'"
                               data-property-id="'.$row->property->id.'"
                                <i class="bi bi-pencil-fill fs-6"></i> <strong>Edit</strong>
                            </a>
                
                            <form action="'.route('delete.special.day', $row->id).'" method="POST" class="d-inline-block"
                                  onsubmit="return confirm(\'Are you sure you want to delete this special day?\')">
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

             // Status Action Button
            ->addColumn('status_action', function ($row) {
                $checked = $row->is_active ? 'checked' : '';

                return '
                    <div class="d-flex justify-content-center p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <label class="form-switch">
                            <input type="checkbox" class="form-check-input speacial-day-toggle visually-hidden"
                                   data-id="'.$row->id.'" '.$checked.'>
                            <span class="slider"></span>
                        </label>
                    </div>';
            })

            ->rawColumns(['user_info', 'date', 'description', 'property', 'status_action', 'action'])
            ->make(true);

    }

    // Fetch all Special Days
    public function fetchSpecialDays()
    {
        $allSpecialDays = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        SpecialDay::with(['user', 'property'])
            ->visibleTo($user)   // local scope
            ->chunkById(100, function ($chunk) use (&$allSpecialDays) {
                $allSpecialDays->push(...$chunk);
            });

        return $allSpecialDays;
    }

    public function addSpecialDay($request, $handler)
    {
        $specialDayData = GenericFormData::fromRequest($request, [
            'property_id', 'date', 'description',
        ], [
            'user_id' => auth()->id(),
        ]);

        $propertyIds = (array) $specialDayData->get('property_id');

        foreach ($propertyIds as $propertyId) {
            $singleData = GenericFormData::fromArray([
                'user_id' => $specialDayData->get('user_id'),
                'property_id' => $propertyId,
                'date' => $specialDayData->get('date'),
                'description' => $specialDayData->get('description'),
            ]);

            $handler->create($singleData, 'Property', 'SpecialDay');
        }

        return redirect()->route('manage.special.days')->with('success', 'Special day added successfully!');
    }

    public function toggleSpecialDay($request, $handler)
    {
        $special_day_data = GenericFormData::fromRequest($request, ['id', 'is_active']);

        $specialDay = $this->specialDaySole($special_day_data->get('id'));

        $handler->update($special_day_data, $specialDay);

        return response()->json(['message' => 'Special day status updated successfully.']);
    }

    public function specialDaySole($id)
    {
        return SpecialDay::where('id', $id)->sole();
    }

    public function deleteSpecialDay($id)
    {

        $specialDay = $this->specialDaySole($id);
        $specialDay->delete();

        return redirect()->route('manage.special.days')->with('success', 'Special day deleted successfully!');
    }

    public function editSpecialDay($specialDay, $request, $handler)
    {
        $specialDayData = GenericFormData::fromRequest($request, [
            'property_id', 'date', 'description',
        ]);

        $handler->update($specialDayData, $specialDay);

        return redirect()->route('manage.special.days')->with('success', 'Special day updated successfully!');
    }

    // Coupons

    public function manageCoupons($request)
    {
        $data = $this->fetchCoupons();

        return DataTables::of($data)
            ->addIndexColumn()

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

            ->addColumn('code', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <span>'.e($row->code).'</span>
                    </div>
                ';
            })

            ->addColumn('description', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <span>'.e($row->description).'</span>
                    </div>
                ';
            })

            ->addColumn('properties', function ($row) {
                if ($row->properties->isEmpty()) {
                    return '<span class="badge bg-secondary text-light fw-semibold px-3 py-2 rounded-pill shadow-sm">Global Coupon</span>';
                }

                $html = '<div class="d-flex flex-wrap gap-2">';
                foreach ($row->properties as $property) {
                    $html .= '
                        <div id="property-'.$property->id.'" class="property-pill position-relative remove_div">
                            <span class="property-badge d-inline-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-house-door-fill"></i> '.e($property->name).'
                            </span>
                            <button type="button" class="btn btn-sm btn-remove-property" title="Remove"
                                onclick="removeProperty('.$row->id.', '.$property->id.')">
                                &times;
                            </button>
                        </div>
                    ';
                }
                $html .= '</div>';

                return $html;
            })

            ->addColumn('action', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
            
                        <a href="#"
                           class="btn btn-sm btn-gradient-primary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2 edit-coupon"
                           data-bs-toggle="modal"
                           data-bs-target="#editCouponModal"
                           title="Edit Coupon"
                           data-id="'.$row->id.'"
                           data-code="'.e($row->code).'"
                           data-description="'.e($row->description).'"
                           data-type="'.e($row->type).'"
                           data-value="'.e($row->value).'"
                           data-start_date="'.e($row->start_date).'"
                           data-end_date="'.e($row->end_date).'"
                           data-properties=\''.json_encode($row->properties->pluck('id')).'\'
                            <i class="bi bi-pencil-fill fs-6"></i> <strong>Edit</strong>
                        </a>

                        <a href="#"
                           class="btn btn-sm btn-gradient-primary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2 edit-admin"
                           data-bs-toggle="modal"
                           data-bs-target="#viewCouponModal"
                           title="View Coupon"
                           data-code="'.e($row->code).'"
                           data-description="'.e($row->description).'"
                           data-type="'.e($row->type).'"
                           data-value="'.e($row->value).'"
                           data-start_date="'.e($row->start_date).'"
                           data-end_date="'.e($row->end_date).'"
                           data-properties="'.e($row->properties).'"
                           onclick="loadCouponView(this)">
                            <i class="bi bi-eye-fill fs-6"></i> <strong>View</strong>
                        </a>
            
                        <form action="'.route('delete.coupon', $row->id).'" method="POST" class="d-inline-block"
                              onsubmit="return confirm(\'Are you sure you want to delete this coupon?\')">
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

             // Status Action Button
            ->addColumn('status_action', function ($row) {
                $checked = $row->is_active ? 'checked' : '';

                return '
                <div class="d-flex justify-content-center p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                    <label class="form-switch">
                        <input type="checkbox" class="form-check-input coupon-toggle visually-hidden"
                               data-id="'.$row->id.'" '.$checked.'>
                        <span class="slider"></span>
                    </label>
                </div>';
            })

            ->rawColumns(['code', 'user_info', 'description', 'type', 'value', 'properties', 'start_date', 'end_date', 'status_action', 'action'])
            ->make(true);
    }

    public function fetchCoupons()
    {
        $allCoupons = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        Coupon::with(['user', 'properties'])
            ->visibleTo($user)   // local scope
            ->chunkById(100, function ($chunk) use (&$allCoupons) {
                $allCoupons->push(...$chunk);
            });

        return $allCoupons;
    }

    public function addCoupon($request, $handler)
    {
        $couponData = GenericFormData::fromRequest($request, [
            'code', 'value', 'description', 'type', 'start_date', 'end_date', 'property_ids',
        ],
            [
                'user_id' => auth()->id(),
            ]);

        $coupon = $handler->create($couponData, 'Property', 'Coupon');

        $propertyIds = (array) $couponData->get('property_ids');

        if (! empty($propertyIds)) {
            $handler->attach($coupon, 'properties', $couponData->get('property_ids'));
        }

        return redirect()->route('manage.coupons')->with('success', 'Coupon added successfully!');
    }

    public function removeCouponProperty($request, $coupon, $handler)
    {
        $handler->detach($coupon, 'properties', (array) $request->property_id);

        return response()->json(['message' => 'Property removed successfully!']);
    }

    public function toggleCoupon($request, $handler)
    {
        $coupon_data = GenericFormData::fromRequest($request, ['id', 'is_active']);

        $coupon = $this->couponSole($coupon_data->get('id'));

        $handler->update($coupon_data, $coupon);

        return response()->json(['message' => 'Coupon status updated successfully.']);
    }

    public function couponSole($id)
    {
        return Coupon::where('id', $id)->sole();
    }

    public function deleteCoupon($coupon)
    {

        $coupon->delete();

        return redirect()->route('manage.coupons')->with('success', 'Coupon deleted successfully!');
    }

    public function editCoupon($coupon, $request, $handler)
    {
        $couponData = GenericFormData::fromRequest($request, [
            'code', 'value', 'description', 'type', 'start_date', 'end_date', 'property_ids',
        ]);

        $handler->update($couponData, $coupon);

        $propertyIds = (array) $couponData->get('property_ids');

        if (! empty($propertyIds)) {
            $handler->sync($coupon, 'properties', $propertyIds);
        }

        return redirect()->route('manage.coupons')->with('success', 'Coupon updated successfully!');
    }

    // Bookings

    public function couponCodeSole($code)
    {
        return Coupon::where('code', $code)->sole();
    }

    // public function applyCoupon($request, $property)
    // {
    //     $code = $request->code;
    //     $check_in = Carbon::parse($request->check_in);
    //     $check_out = Carbon::parse($request->check_out);

    //     $total_days = $check_in->diffInDays($check_out) + 1;

    //     $months = $check_in->diffInMonths($check_out);
    //     $remaining_days = $check_in->addMonths($months)->diffInDays($check_out);

    //     // For example, for display or billing
    //     $total_month_days = $months . ' Month(s)';
    //     if ($remaining_days > 0) {
    //         $total_month_days .= ' + ' . $remaining_days . ' Day(s)';
    //     }

    //     $coupon = $this->couponCodeSole($code);

    //     // Special Dates Count
    //     $specialDatesCount = $this->SpecialDayCalculate($property, $check_in, $check_out)['specialDayCount'];
    //     $specialDates = $this->SpecialDayCalculate($property, $check_in, $check_out)['specialDays'];

    //     // Special Day Amount Fetch
    //     $specialDay = $this->specialDayPrice($property);

    //     $specialDateCalculate = max($total_days - $specialDatesCount, 0);   // Prevent negative calculation

    //     $night = $this->nightPrice($property) ?? (object)['pricing' => 0];
    //     $month = $this->monthPrice($property) ?? (object)['pricing' => 0];
    //     $specialDay = $this->specialDayPrice($property) ?? (object)['pricing' => 0];

    //     if ($total_days < 31) {  // 30 days varaikkum iruntha one night pricing
    //         $stay_duration = $total_days . ' Nights' . ' + ' . isset($specialDatesCount) ? $specialDatesCount . ' Special Days' : '' . '(' . implode(', ', $specialDates) . ')';
    //         dd($stay_duration);
    //         $total_amount = ($night->pricing * $specialDateCalculate) + ($specialDay->pricing * $specialDatesCount);
    //     } else {  // 31 days kku mela iruntha one month pricing
    //         $stay_duration = $total_month_days . isset($specialDatesCount) ? ' + ' . $specialDatesCount . ' Special Days' : '' . '(' . implode(', ', $specialDates) . ')';
    //         dd($stay_duration);
    //         $total_amount = ($month->pricing * $months) + ($night->pricing * $remaining_days) + ($specialDay->pricing * $specialDatesCount);
    //     }

    //     $total_amount = round($total_amount, 2);
    //     $stay_amount = $total_amount;

    //     // Tax fee Calculate
    //     $taxRate = $this->sharedRepository->settingsValue('tax');
    //     $taxFee = ($total_amount * $taxRate / 100);
    //     $total_amount = $total_amount + $taxFee;

    //     // Coupon Disacount Calculate
    //     if ($coupon->type == 'Percentage') {
    //         $couponDiscount = ($total_amount * $coupon->value / 100);
    //         $total_amount = $total_amount - ($total_amount * $coupon->value / 100);
    //     } else {
    //         $couponDiscount = $coupon->value;
    //         $total_amount = $total_amount - $coupon->value;
    //     }

    //     // Special Dates Calculate

    //     return response()->json(['stay' => ['duration' => $stay_duration, 'amount' => $stay_amount],'total_amount' => $total_amount, 'coupon_discount' => $couponDiscount, 'tax_fee' => $taxFee, 'message' => 'Coupon applied successfully!']);

    // }

    public function applyCoupon($request, $property)
    {
        $code = $request->code;
        $check_in = Carbon::parse($request->check_in);
        $check_out = Carbon::parse($request->check_out);

        // Total Days
        $total_days = $check_in->diffInDays($check_out) + 1;

        // Months and remaining days
        $months = $check_in->diffInMonths($check_out);
        $remaining_days = $check_in->copy()->addMonths($months)->diffInDays($check_out);
        $total_month_days = $months.' Month(s)';
        if ($remaining_days > 0) {
            $total_month_days .= ' + '.$remaining_days.' Day(s)';
        }

        // Coupon Fetch
        $coupon = $this->couponCodeSole($code);

        // Special Days
        $specialData = $this->SpecialDayCalculate($property, $check_in, $check_out);
        $specialDatesCount = $specialData['specialDayCount'];
        $specialDates = $specialData['specialDays'];
        $specialDateCalculate = max($total_days - $specialDatesCount, 0);

        // Pricing Fetch
        $night = $this->nightPrice($property) ?? (object) ['pricing' => 0];
        $month = $this->monthPrice($property) ?? (object) ['pricing' => 0];
        $specialDay = $this->specialDayPrice($property) ?? (object) ['pricing' => 0];

        // Stay Calculation
        if ($total_days < 31) {
            $stay_duration = $total_days.' Nights';
            if ($specialDatesCount > 0) {
                $stay_duration .= ' + '.$specialDatesCount.' Special Days ('.implode(', ', $specialDates).')';
            }

            $total_amount = ($night->pricing * $specialDateCalculate) + ($specialDay->pricing * $specialDatesCount);
        } else {
            $stay_duration = $total_month_days;
            if ($specialDatesCount > 0) {
                $stay_duration .= ' + '.$specialDatesCount.' Special Days ('.implode(', ', $specialDates).')';
            }

            $total_amount = ($month->pricing * $months) + ($night->pricing * $remaining_days) + ($specialDay->pricing * $specialDatesCount);
        }

        $total_amount = round($total_amount, 2);
        $stay_amount = $total_amount;

        // Tax
        $taxRate = $this->sharedRepository->settingsValue('tax') ?? 0;
        $taxFee = round(($total_amount * $taxRate / 100), 2);
        $total_amount += $taxFee;

        // Coupon Discount
        $couponDiscount = 0;
        if ($coupon) {
            if ($coupon->type == 'percentage') {
                $couponDiscount = round(($total_amount * $coupon->value / 100), 2);
            } else {
                $couponDiscount = $coupon->value;
            }
            $total_amount -= $couponDiscount;
        }

        $total_amount = round($total_amount, 2);

        return response()->json([
            'stay' => [
                'duration' => $stay_duration,
                'amount' => $stay_amount,
            ],
            'total_amount' => $total_amount,
            'coupon_discount' => $couponDiscount,
            'tax_fee' => $taxFee,
            'message' => 'Coupon applied successfully!',
        ]);
    }

    public function nightPrice($property)
    {
        $price = $property->pricings->where('pricing_type', 'Night')->first();

        return (object) ['pricing' => $price?->pricing ?? 0]; // default to 0
    }

    public function monthPrice($property)
    {
        $price = $property->pricings->where('pricing_type', 'Month')->first();

        return (object) ['pricing' => $price?->pricing ?? 0];
    }

    public function specialDayPrice($property)
    {
        $price = $property->pricings->where('pricing_type', 'SpecialDay')->first();

        return (object) ['pricing' => $price?->pricing ?? 0];
    }

    public function SpecialDayCalculate($property, $check_in, $check_out)
    {

        $specialDay = SpecialDay::where('property_id', $property->id)->where('date', '>=', $check_in)->where('date', '<=', $check_out)->where('is_active', 1)->pluck('date')->toArray();
        $specialDayCount = count($specialDay);

        return ['specialDayCount' => $specialDayCount, 'specialDays' => $specialDay];

    }

    // Payment
    // public function initiatePayment($request, $handler, $property) {
    //     // Add Booking
    //     $this->addBooking($request, $handler, $property);

    //     $orderId = uniqid('ORD');
    //     $amount = 1000 * 100; // ₹1000 in paise

    //     $merchantId = env('PHONEPE_MERCHANT_ID');
    //     $saltKey = env('PHONEPE_SALT_KEY');
    //     $saltIndex = env('PHONEPE_SALT_INDEX');
    //     $baseUrl = env('PHONEPE_BASE_URL');

    //     $callbackUrl = route('phonepe.callback');

    //     $payload = [
    //         "merchantId" => $merchantId,
    //         "merchantTransactionId" => $orderId,
    //         "merchantUserId" => "USER123",
    //         "amount" => $amount,
    //         "redirectUrl" => $callbackUrl,
    //         "redirectMode" => "POST",
    //         "callbackUrl" => $callbackUrl,
    //         "mobileNumber" => "9999999999",
    //         "paymentInstrument" => [
    //             "type" => "PAY_PAGE"
    //         ],
    //     ];

    //     $encoded = base64_encode(json_encode($payload));
    //     $hash = hash('sha256', $encoded . "/pg/v1/pay" . $saltKey) . "###" . $saltIndex;

    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'X-VERIFY' => $hash,
    //         'accept' => 'application/json',
    //     ])->post($baseUrl . '/pg/v1/pay', [
    //         'request' => $encoded
    //     ]);

    //     $res = $response->json();

    //     if (isset($res['success']) && $res['success'] === true) {
    //         $redirectUrl = $res['data']['instrumentResponse']['redirectInfo']['url'];
    //         return redirect($redirectUrl);
    //     }

    //     Log::error('PhonePe Payment Initiation Failed', ['response' => $res]);

    //     return back()->with('error', 'Payment initiation failed.');
    // }
    public function initiatePayment($request, $handler, $property)
    {
        // Add Booking and get total amount
        $bookingResponse = $this->addBooking($request, $handler, $property);

        return redirect()->route('dashboard')->with('success', 'Booking added successfully!');
        // below codes are payment codes
        // $total = $bookingResponse->getData()->total;

        // Generate a unique merchant transaction ID
        $merchantTransactionId = 'TXN_'.time().'_'.Str::random(6);

        // Convert amount to paise
        $amount = 10 * 100; // Use booking total

        // Fetch PhonePe credentials from .env
        $merchantId = env('PHONEPE_MERCHANT_ID');
        $saltKey = env('PHONEPE_SALT_KEY');
        $saltIndex = env('PHONEPE_SALT_INDEX');
        $baseUrl = env('PHONEPE_BASE_URL');
        $callbackUrl = env('PHONEPE_CALLBACK_URL');

        // Validate required credentials
        if (! $merchantId || ! $saltKey || ! $saltIndex || ! $baseUrl || ! $callbackUrl) {
            Log::error('PhonePe Configuration Error', [
                'merchantId' => $merchantId,
                'saltKey' => $saltKey,
                'saltIndex' => $saltIndex,
                'baseUrl' => $baseUrl,
                'callbackUrl' => $callbackUrl,
            ]);

            return back()->with('error', 'Payment gateway configuration is incomplete.');
        }

        // Validate callback URL accessibility
        if (! filter_var($callbackUrl, FILTER_VALIDATE_URL) || ! str_starts_with($callbackUrl, 'https://')) {
            Log::error('Invalid PhonePe Callback URL', [
                'callbackUrl' => $callbackUrl,
            ]);

            return back()->with('error', 'Invalid callback URL configuration.');
        }

        // Log credentials for debugging
        Log::info('PhonePe Credentials', [
            'merchantId' => $merchantId,
            'saltKey' => $saltKey,
            'saltIndex' => $saltIndex,
            'baseUrl' => $baseUrl,
            'callbackUrl' => $callbackUrl,
        ]);

        // Prepare payload for PhonePe
        $payload = [
            'merchantId' => $merchantId,
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => auth()->check() ? 'USER_'.auth()->user()->id : 'GUEST_'.Str::random(10),
            'amount' => $amount,
            'redirectUrl' => $callbackUrl,
            'redirectMode' => 'POST',
            'callbackUrl' => $callbackUrl,
            'mobileNumber' => $request->mobile_number ?? '9999999999',
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        // // Store payment record
        // $payment = \App\Models\Payment::create([
        //     'merchant_transaction_id' => $merchantTransactionId,
        //     'user_id' => auth()->id(),
        //     'property_id' => $property->id,
        //     'amount' => $amount / 100,
        //     'status' => 'pending',
        // ]);

        // Retry logic for rate limit handling
        $maxRetries = 3;
        $retryDelay = 5; // Initial delay in seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Encode payload to base64
                $encodedPayload = base64_encode(json_encode($payload));

                // Generate X-VERIFY header
                $hashInput = $encodedPayload.'/pg/v1/pay'.$saltKey;
                $xVerify = hash('sha256', $hashInput).'###'.$saltIndex;

                // Log payload and X-VERIFY inputs for debugging
                Log::info('PhonePe Request Payload', [
                    'attempt' => $attempt,
                    'payload' => $payload,
                    'encoded' => $encodedPayload,
                    'hash_input' => $hashInput,
                    'x_verify' => $xVerify,
                ]);

                // Make HTTP request to PhonePe
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-VERIFY' => $xVerify,
                    'accept' => 'application/json',
                ])->post($baseUrl.'/pg/v1/pay', [
                    'request' => $encodedPayload,
                ]);

                // Log raw response for debugging
                Log::info('PhonePe Raw Response', [
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ]);

                // Check for 429 Too Many Requests
                if ($response->status() === 429) {
                    if ($attempt < $maxRetries) {
                        $retryAfter = $response->header('Retry-After') ?? $retryDelay;
                        $retryDelay *= 2; // Exponential backoff
                        Log::warning('PhonePe Rate Limit Hit, Retrying', [
                            'attempt' => $attempt,
                            'retry_after' => $retryAfter,
                            'merchantTransactionId' => $merchantTransactionId,
                        ]);
                        sleep((int) $retryAfter);

                        continue;
                    }
                    Log::error('PhonePe Rate Limit Exceeded After Max Retries', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'merchantTransactionId' => $merchantTransactionId,
                    ]);

                    return back()->with('error', 'Too many requests to payment gateway. Please try again later.');
                }

                // Check for 400 KEY_NOT_CONFIGURED
                if ($response->status() === 400 && str_contains($response->body(), 'KEY_NOT_CONFIGURED')) {
                    Log::error('PhonePe Key Not Configured', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'merchantTransactionId' => $merchantTransactionId,
                    ]);

                    return back()->with('error', 'Payment gateway key configuration error. Please contact support.');
                }

                // Check if response is successful
                if ($response->successful()) {
                    $res = $response->json();

                    if (isset($res['success']) && $res['success'] === true) {
                        // Update payment with redirect URL
                        $payment->update(['response_data' => json_encode($res)]);
                        $redirectUrl = $res['data']['instrumentResponse']['redirectInfo']['url'];

                        return redirect()->away($redirectUrl);
                    }

                    // Log failure details
                    Log::error('PhonePe Payment Initiation Failed', [
                        'response' => $res,
                        'merchantTransactionId' => $merchantTransactionId,
                    ]);

                    return back()->with('error', 'Payment initiation failed: '.($res['message'] ?? 'Unknown error'));
                }

                // Log HTTP failure
                Log::error('PhonePe HTTP Request Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'merchantTransactionId' => $merchantTransactionId,
                ]);

                return back()->with('error', 'Payment gateway error: '.($response->json()['message'] ?? 'Unknown error'));
            } catch (\Exception $e) {
                // Log exception details
                Log::error('PhonePe Payment Initiation Exception', [
                    'error' => $e->getMessage(),
                    'merchantTransactionId' => $merchantTransactionId,
                ]);

                return back()->with('error', 'An unexpected error occurred: '.$e->getMessage());
            }
        }
    }

    // Booking
    public function addBooking($request, $handler, $property)
    {

        // Fetch price
        $price_details = $this->applyCoupon($request, $property);
        $duration = $price_details->getData()->stay->duration;
        $subtotal = $price_details->getData()->stay->amount;
        $discount = $price_details->getData()->coupon_discount;
        $tax = $price_details->getData()->tax_fee;
        $total = $price_details->getData()->total_amount;

        // Add rewards
        $this->addRewards($total, $handler);

        // add booking
        $bookingData = GenericFormData::fromRequest($request, [
            'check_in', 'check_out', 'code', 'bedrooms', 'adults', 'children',
        ],
            [
                'property_id' => $property->id,
                'user_id' => auth()->id(),
                'duration' => $duration,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'confirmed',
            ]);

        $customized_booking_data = [
            'user_id' => $bookingData->get('user_id'),
            'property_id' => $bookingData->get('property_id'),
            'check_in' => $bookingData->get('check_in'),
            'check_out' => $bookingData->get('check_out'),
            'duration' => $bookingData->get('duration'),
            'bedrooms' => $bookingData->get('bedrooms'),
            'adults' => $bookingData->get('adults'),
            'children' => $bookingData->get('children'),
            'coupon_code' => $bookingData->get('code'),
            'discount' => $discount,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => $bookingData->get('status'),
        ];

        $final_booking_data = GenericFormData::fromArray($customized_booking_data);

        $booking = $handler->create($final_booking_data, 'Property', 'Booking');

        // Add Reminders
        if ($bookingData->get('status') == 'confirmed') {
            $data = [
                'reminder_date' => Carbon::parse($bookingData->get('check_out'))->addDays(1)->format('Y-m-d'),
            ];
    
            $this->addReminder($data, $handler, $booking);
        }

        return $booking;

    }

    // Add Rewards
    public function addRewards($total, $handler) {
        $points = CommonHelper::calculateRewardPoints($total);
        $genericData = GenericFormData::fromArray(['reward_points' => $points + auth()->user()->profile->reward_points]);
        $handler->update($genericData, auth()->user()->profile);
    }

    public function allBookings($request)
    {

        $allBookings = collect(); // Initialize an empty collection

        $user = $this->userRepository->authUser();

        // Dates
        $checkInDate = $request->checkin;
        $checkOutDate = $request->checkout;

        Booking::with('property')
        ->visibleTo($user)
        ->when($request->property_name, function ($query) use ($request) {
            $query->whereHas('property', function ($q) use ($request) {
                $q->like('name', $request->property_name);
            });
        })
        ->when($request->checkin && $request->checkout, function ($query) use ($request) {
            $checkInDate = $request->checkin;
            $checkOutDate = $request->checkout;
    
            $query->where(function ($q) use ($checkInDate, $checkOutDate) {
                $q->whereBetween('check_in', [$checkInDate, $checkOutDate])
                  ->orWhereBetween('check_out', [$checkInDate, $checkOutDate])
                  ->orWhere(function ($subQ) use ($checkInDate, $checkOutDate) {
                      $subQ->where('check_in', '<=', $checkInDate)
                           ->where('check_out', '>=', $checkOutDate);
                  });
            });
        })
        ->when($request->status, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->chunkById(100, function ($chunk) use (&$allBookings) {
            $allBookings->push(...$chunk);
        });
    

        return $allBookings;
    }

    public function addReminder($request, $handler, $booking) {
        $customized[] = [
            'user_id' => auth()->id(),
            'reminder_date' => $request['reminder_date'],
        ];
        // $final = GenericFormData::fromArray($customized);
        return $handler->createChildren($booking, 'reminder', $customized);
    }
}
