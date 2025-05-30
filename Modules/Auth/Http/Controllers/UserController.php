<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\AdminRegisterRequest;
use Modules\Auth\Http\Requests\EditAdminRequest;
use Modules\Auth\Http\Requests\MultipleAdminDeleteRequest;
use Modules\Auth\Repositories\Interfaces\UserRepositoryInterface;
use Modules\Shared\Actions\HandleFormSubmission;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function manageAdmins(Request $request)
    {
        if ($request->ajax()) {
            return $this->userRepository->manageAdmins($request);
        }

        return view('auth::user.manage-admins');
    }

    public function registerAdmin(AdminRegisterRequest $request, HandleFormSubmission $handler)
    {
        return $this->userRepository->registerAdmin($request, $handler);
    }

    public function editAdmin(EditAdminRequest $request, HandleFormSubmission $handler, $id)
    {
        return $this->userRepository->editAdmin($request, $handler, $id);
    }

    public function deleteAdmin($id)
    {
        return $this->userRepository->deleteAdmin($id);
    }

    public function deleteMultipleAdmins(MultipleAdminDeleteRequest $request)
    {
        return $this->userRepository->deleteMultipleAdmins($request->ids);
    }
}
