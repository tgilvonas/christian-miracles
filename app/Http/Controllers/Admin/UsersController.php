<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        $this->ensureSuperAdmin();

        return Inertia::render('admin/Users');
    }

    public function jsonList()
    {
        $this->ensureSuperAdmin();

        return User::query()
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function edit($userId)
    {
        $this->ensureSuperAdmin();

        $user = User::findOrFail($userId);

        return response()->json($user);
    }

    public function save(UserRequest $request, $userId = null)
    {
        $this->ensureSuperAdmin();

        $data = $request->validated();
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'roles' => $data['roles'] ?? [],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user = $userId ? User::findOrFail($userId) : null;

        if ($user) {
            $user->update($payload);
        } else {
            $user = User::create($payload);
        }

        return response()->json([
            'message' => __('admin.record_saved_successfully'),
            'user' => $user,
        ]);
    }

    public function delete($userId)
    {
        $this->ensureSuperAdmin();

        $user = User::findOrFail($userId);
        $user->delete();

        return response()->json([
            'message' => __('admin.record_deleted_successfully'),
        ]);
    }

    private function ensureSuperAdmin(): void
    {
        if (!Auth::check() || !Auth::user()?->hasRole('ROLE_SUPERADMIN')) {
            abort(403);
        }
    }
}
