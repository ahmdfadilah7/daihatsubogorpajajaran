<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List all user accounts.
     */
    public function index(): View
    {
        $users = User::orderByDesc('created_at')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $user = new User;

        return view('admin.users.create', compact('user'));
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        // The plain `password` is auto-hashed by the User model's 'hashed' cast.
        User::create($request->validated());

        return redirect()->route('admin.users.index')
            ->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the given user.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // A blank password field leaves the existing password unchanged.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('sukses', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Delete the given user, guarding against self-deletion and lockout.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('gagal', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if (User::count() === 1) {
            return back()->with('gagal', 'Tidak dapat menghapus satu-satunya pengguna yang tersisa.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('sukses', 'Pengguna berhasil dihapus.');
    }
}
