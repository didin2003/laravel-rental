<?php

namespace Modules\Auth\Repositories;

use Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Helpers\CommonHelper;
use Modules\Auth\Models\Otp;
use Modules\Auth\Models\User;
use Modules\Auth\Repositories\Interfaces\UserRepositoryInterface;
use Modules\Shared\Data\GenericFormData;
use Modules\Shared\Services\FileUploadService;
use Yajra\DataTables\Facades\DataTables;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(protected FileUploadService $uploadService) {}

    public function otpSend($request, $handler)
    {
        $otp = CommonHelper::generate();
        $data = GenericFormData::fromRequest($request, ['identifier', 'type'], ['otp' => $otp, 'expires_at' => now()->addMinutes(5)]);
        $otpSend = $handler->updateOrCreate($data, 'Auth', 'Otp', ['identifier' => $request->identifier, 'type' => $request->type]);
        session(['identifier' => Crypt::encrypt($request->identifier)]);
        session(['otp-type' => Crypt::encrypt($request->type)]);

        return redirect()->route('otp.verify.form')->with('success', 'OTP sent successfully!');

    }

    public function verifyOtp($request, $handler)
    {
        $data = GenericFormData::fromRequest($request, ['otp']);

        $identifier = Crypt::decrypt(session('identifier'));
        $type = Crypt::decrypt(session('otp-type'));

        $otp_verify = Otp::where('otp', $data->get('otp'))->where('identifier', $identifier)->first();
        $this->updateOtpVerify($otp_verify);

        session(['otp_verified' => true]);

        throw_if(! $otp_verify, ValidationException::withMessages([
            'otp' => ['Invalid OTP!'],
        ]));

        if ($type === 'register') {
            return redirect()->route('register.form')->with('success', 'OTP verified successfully!');
        } elseif ($type === 'forgot') {
            return redirect()->route('forgot.form')->with('success', 'OTP verified successfully!');
        }

    }

    private function updateOtpVerify($model)
    {
        isset($model) ? $model->update(['is_verified' => true]) : null;
        // $model->update(['is_verified' => true]);
    }

    public function register($request, $handler)
    {
        $data = GenericFormData::fromRequest($request, ['name', 'email', 'password']);
        $user = $handler->create($data, 'Auth', 'User');

        $user->addRole('user');

        $phone = Crypt::decrypt(session('identifier'));

        $data1 = GenericFormData::fromRequest($request, [], ['user_id' => $user->id, 'phone' => $phone]);
        $handler->create($data1, 'Auth', 'Profile');

        session()->forget('otp_verified');

        return redirect()->route('login.form')->with('success', 'Registration successful!');
    }

    public function login($request)
    {
        $data = GenericFormData::fromRequest($request, ['email_phone', 'password']);
        $identifier = $data->get('email_phone');
        $password = $data->get('password');

        // Find user by email or related profile.phone
        $user = $this->emailAndPhoneCheck($identifier);

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $password])) {
            return back()->with('error', 'Invalid credentials.');
        }

        return redirect()->route('dashboard')->with('success', 'Login successful!');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login.form')->with('success', 'Logout successful!');
    }

    public function manageAdmins($request)
    {
        $data = $this->fetchAdmins(); // assuming Spatie role is used

        return DataTables::of($data)
            // ->addIndexColumn()

            // Replace index column with checkbox
            ->addColumn('DT_RowIndex', function ($row) {
                static $i = 0;
                $i++;
                return '
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input admin-check" data-id="'.$row->id.'" id="admin_'.$row->id.'">
                        <label class="form-check-label" for="admin_'.$row->id.'">'.$i.'</label>
                    </div>
                ';
            })
            
            ->addColumn('user_info', function ($row) {
                $name = e($row->name ?? 'N/A');
                $profileImage = $row->profile->profile_image;

                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <img src="'.$profileImage.'" alt="User Image" class="rounded-circle shadow" style="width: 40px; height: 40px; object-fit: cover;">
                        <span>'.$name.'</span>
                    </div>
                ';
            })
            ->addColumn('email', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <span>'.e($row->email ?? 'N/A').'</span>
                    </div>
                ';
            })
            ->addColumn('phone', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
                        <span>'.e(optional($row->profile)->phone ?? 'N/A').'</span>
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="p-2 bg-light border rounded shadow-sm d-flex flex-wrap align-items-center gap-2">
            
                        <a href="#"
                           class="btn btn-sm btn-gradient-primary text-white d-flex align-items-center gap-2 shadow rounded-pill px-3 py-2 edit-admin"
                           data-bs-toggle="modal"
                           data-bs-target="#editAdminModal"
                           title="Edit Admin"
                           data-id="'.$row->id.'"
                           data-name="'.e($row->name).'"
                           data-email="'.e($row->email).'"
                           data-profile-image="'.e($row->profile->profile_image).'"
                           data-name-as-aadhaar="'.e(optional($row->profile->aadhaar)['name_as_in_aadhaar'] ?? '').'"
                           data-phone="'.e(optional($row->profile)->phone ?? '').'"
                           data-password=""
                           data-flat="'.e(optional($row->profile->address)['flat'] ?? '').'"
                           data-street="'.e(optional($row->profile->address)['street'] ?? '').'"
                           data-city="'.e(optional($row->profile->address)['city'] ?? '').'"
                           data-state="'.e(optional($row->profile->address)['state'] ?? '').'"
                           data-postcode="'.e(optional($row->profile->address)['postcode'] ?? '').'"
                           data-aadhaar-no="'.e(optional($row->profile->aadhaar)['aadhaar_no'] ?? '').'"
                           data-pan-no="'.e(optional($row->profile->pan)['pan_number'] ?? '').'"
                           data-gst-no="'.e(optional($row->profile)->gst_number ?? '').'"
                           data-bank-ac-no="'.e(optional($row->profile->bank)['account_number'] ?? '').'"
                           data-bank-name="'.e(optional($row->profile->bank)['bank_name'] ?? '').'"
                           data-ifsc="'.e(optional($row->profile->bank)['ifsc'] ?? '').'"
                           data-branch="'.e(optional($row->profile->bank)['branch'] ?? '').'"
                           data-upi-no="'.e(optional($row->profile->upi)['upi_phone'] ?? '').'">
                            <i class="bi bi-pencil-fill fs-6"></i> <strong>Edit</strong>
                        </a>
            
                        <form action="'.route('delete.admin', $row->id).'" method="POST" class="d-inline-block"
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

            ->rawColumns(['DT_RowIndex', 'user_info', 'email', 'phone', 'action'])
            ->make(true);
    }

    public function fetchAdmins()
    {
        $allAdmins = collect(); // Initialize the collection

        User::whereHasRole('admin')->with('profile')->chunkById(100, function ($adminsChunk) use (&$allAdmins) {
            $allAdmins->push(...$adminsChunk);
        });

        return $allAdmins;
    }

    public function registerAdmin($request, $handler)
    {
        $user_data = GenericFormData::fromRequest($request, ['name', 'email', 'password']);
        $user = $handler->create($user_data, 'Auth', 'User');
        $user->addRole('admin');

        // File Upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $this->uploadService->uploadToPublic(
                $request->file('profile_image'),
                'user'
            );
        }

        $profile_data = GenericFormData::fromRequest($request, [
            'profile_image', 'name_as_in_aadhaar', 'phone', 'street', 'flat', 'city', 'state', 'postcode', 'aadhaar_no',
            'pan_no', 'gst_no', 'bank_ac_no', 'bank_name',
            'ifsc', 'branch', 'upi_phone',
        ], ['user_id' => $user->id]);

        $customize_profile_data = [
            'user_id' => $profile_data->get('user_id'),
            'profile_image' => $imagePath ?? null,
            'phone' => $profile_data->get('phone'),
            'address' => [
                'flat' => $profile_data->get('flat'),
                'street' => $profile_data->get('street'),
                'city' => $profile_data->get('city'),
                'state' => $profile_data->get('state'),
                'postcode' => $profile_data->get('postcode'),
            ],
            'aadhaar' => [
                'name_as_in_aadhaar' => $profile_data->get('name_as_in_aadhaar'),
                'aadhaar_no' => $profile_data->get('aadhaar_no'),
            ],
            'pan' => [
                'pan_number' => $profile_data->get('pan_no'),
            ],
            'gst_number' => $profile_data->get('gst_no'),
            'bank' => [
                'bank_name' => $profile_data->get('bank_name'),
                'branch' => $profile_data->get('branch'),
                'account_number' => $profile_data->get('bank_ac_no'),
                'ifsc' => $profile_data->get('ifsc'),
            ],
            'upi' => [
                'upi_phone' => $profile_data->get('upi_phone'),
            ],
        ];

        // Wrap array in GenericFormData
        $profileGenericData = GenericFormData::fromArray($customize_profile_data);
        $handler->create($profileGenericData, 'Auth', 'Profile');

        return redirect()->route('manage.admins')->with('success', 'Owner registered successfully!');
    }

    public function editAdmin($request, $handler, $id)
    {
        $user = $this->userSole($id);
        $user_data = GenericFormData::fromRequest($request, ['name', 'email', 'password']);
        $handler->update($user_data, $user);

        // File Upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $this->uploadService->uploadToPublic(
                $request->file('profile_image'),
                'user'
            );
        }

        $profile_data = GenericFormData::fromRequest($request, [
            'profile_image', 'name_as_in_aadhaar', 'phone', 'street', 'flat', 'city', 'state', 'postcode', 'aadhaar_no',
            'pan_no', 'gst_no', 'bank_ac_no', 'bank_name',
            'ifsc', 'branch', 'upi_phone',
        ], ['user_id' => $user->id]);

        $customize_profile_data = [
            'user_id' => $profile_data->get('user_id'),
            'profile_image' => $imagePath ?? $this->userImage($id),
            'phone' => $profile_data->get('phone'),
            'address' => [
                'flat' => $profile_data->get('flat'),
                'street' => $profile_data->get('street'),
                'city' => $profile_data->get('city'),
                'state' => $profile_data->get('state'),
                'postcode' => $profile_data->get('postcode'),
            ],
            'aadhaar' => [
                'name_as_in_aadhaar' => $profile_data->get('name_as_in_aadhaar'),
                'aadhaar_no' => $profile_data->get('aadhaar_no'),
            ],
            'pan' => [
                'pan_number' => $profile_data->get('pan_no'),
            ],
            'gst_number' => $profile_data->get('gst_no'),
            'bank' => [
                'bank_name' => $profile_data->get('bank_name'),
                'branch' => $profile_data->get('branch'),
                'account_number' => $profile_data->get('bank_ac_no'),
                'ifsc' => $profile_data->get('ifsc'),
            ],
            'upi' => [
                'upi_phone' => $profile_data->get('upi_phone'),
            ],
        ];

        // Wrap array in GenericFormData
        $profileGenericData = GenericFormData::fromArray($customize_profile_data);
        $handler->update($profileGenericData, $user->profile);

        return redirect()->route('manage.admins')->with('success', 'Owner updated successfully!');
    }

    public function userSole($id)
    {
        return User::where('id', $id)->sole();
    }

    public function userImage($id)
    {
        return User::join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('users.id', $id)
            ->value('profiles.profile_image');
    }

    public function deleteAdmin($id)
    {
        $user = $this->userSole($id);
        $user->delete();

        return redirect()->route('manage.admins')->with('success', 'Owner deleted successfully!');
    }
    
    public function deleteMultipleAdmins($ids)
    {
        foreach ($ids as $id) {
            $user = $this->userSole($id);
            $user->delete();
        }
        // $user = $this->userSole($id);
        // $user->delete();

        return response()->json(['message' => 'Multiple Owners deleted successfully!']);
    }

    public function superAdminAccess()
    {
        return auth()->user()->hasRole('super_admin');
    }

    public function adminAccess()
    {
        return auth()->user()->hasRole('admin');
    }

    public function superAdminAndAdminAccess()
    {
        $user = auth()->user();

        return $user && $user->hasRole(['super_admin', 'admin']);
    }

    public function authUser()
    {
        return auth()->user();
    }

    // Manage Profile

    public function editProfile($request, $handler)
    {
        $user_data = GenericFormData::fromRequest($request, ['name', 'email']);
        $user = $handler->update($user_data, auth()->user());

        // profile Upload
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $this->uploadService->uploadToPublic(
                $request->file('profile_image'),
                'user'
            );
            $this->uploadService->removeImageFromDirectory('assets/images/user/'.auth()->user()->profile->profile_image);
        }

        // Aadhaar Front Upload
        if ($request->hasFile('aadhaar_front')) {
            $aadhaarFrontImagePath = $this->uploadService->uploadToPublic(
                $request->file('aadhaar_front'),
                'user/aadhaar_front'
            );
            $this->uploadService->removeImageFromDirectory('assets/images/user/aadhaar_front/'.auth()->user()->profile->aadhaar['aadhaar_front']);
        }

        // Aadhaar Back Upload
        if ($request->hasFile('aadhaar_back')) {
            $aadharBackImagePath = $this->uploadService->uploadToPublic(
                $request->file('aadhaar_back'),
                'user/aadhaar_back'
            );
            $this->uploadService->removeImageFromDirectory('assets/images/user/aadhaar_back/'.auth()->user()->profile->aadhaar['aadhaar_back']);
        }

        $profile_data = GenericFormData::fromRequest($request, [
            'profile_image', 'name_as_in_aadhaar', 'phone', 'street', 'flat', 'city', 'state', 'postcode', 'aadhaar_no', 'aadhaar_front', 'aadhaar_back',
            'pan_no', 'gst_no', 'bank_ac_no', 'bank_name',
            'ifsc', 'branch', 'upi_phone',
        ]);

        $customize_profile_data = [
            'profile_image' => isset($profileImagePath) ? $profileImagePath : basename(auth()->user()->profile->profile_image),
            'phone' => $profile_data->get('phone'),
            'address' => [
                'flat' => $profile_data->get('flat'),
                'street' => $profile_data->get('street'),
                'city' => $profile_data->get('city'),
                'state' => $profile_data->get('state'),
                'postcode' => $profile_data->get('postcode'),
            ],
            'aadhaar' => [
                'name_as_in_aadhaar' => $profile_data->get('name_as_in_aadhaar'),
                'aadhaar_no' => $profile_data->get('aadhaar_no'),
                // 'aadhaar_front' => $aadhaarFrontImagePath,
                'aadhaar_front' => isset($aadhaarFrontImagePath) ? $aadhaarFrontImagePath : auth()->user()->profile->aadhaar['aadhaar_front'],
                'aadhaar_back' => isset($aadharBackImagePath) ? $aadharBackImagePath : auth()->user()->profile->aadhaar['aadhaar_back'],
                // 'aadhaar_back' => $aadharBackImagePath,
            ],
            'pan' => [
                'pan_number' => $profile_data->get('pan_no'),
            ],
            'gst_number' => $profile_data->get('gst_no'),
            'bank' => [
                'bank_name' => $profile_data->get('bank_name'),
                'branch' => $profile_data->get('branch'),
                'account_number' => $profile_data->get('bank_ac_no'),
                'ifsc' => $profile_data->get('ifsc'),
            ],
            'upi' => [
                'upi_phone' => $profile_data->get('upi_phone'),
            ],
        ];

        // Wrap array in GenericFormData
        $profileGenericData = GenericFormData::fromArray($customize_profile_data);
        $handler->update($profileGenericData, auth()->user()->profile);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    // Reset Password
    public function resetPassword($request, $handler)
    {
        $user_data = GenericFormData::fromRequest($request, ['new_password']);
        $customize_user_data = ['password' => $user_data->get('new_password')];
        $final_data = GenericFormData::fromArray($customize_user_data);
        $user = $handler->update($final_data, auth()->user());

        return redirect()->route('profile')->with('success', 'Password updated successfully!');
    }

    // Forgot Password
    public function forgot($request, $handler)
    {
        $user_data = GenericFormData::fromRequest($request, ['password']);
        $customize_user_data = ['password' => $user_data->get('password')];
        $final_data = GenericFormData::fromArray($customize_user_data);

        $identifier = Crypt::decrypt(session('identifier'));
        // Find user by email or related profile.phone
        $user = $this->emailAndPhoneCheck($identifier);

        $user = $handler->update($final_data, $user);

        return redirect()->route('login')->with('success', 'Password updated successfully!');
    }

    public function emailAndPhoneCheck($identifier)
    {
        $user = User::where('email', $identifier)
        ->orWhereHas('profile', function ($query) use ($identifier) {
            $query->where('phone', $identifier);
        })
        ->first();

        return $user;
    }
}
