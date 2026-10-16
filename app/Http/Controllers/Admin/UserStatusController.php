<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStatusController extends Controller
{
    /**
     * Daftar user + pencarian + filter.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role   = $request->query('role');
        $status = $request->query('status');

        $users = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query, $role) => $query->where('role', $role))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', (int) $status))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role', 'status'));
    }

    /**
     * Toggle aktif/nonaktif user.
     */
    public function toggle(Request $request, int $id)
    {
        $authUser = $request->user();

        // Pastikan hanya admin
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengubah status user.');
        }

        // Admin tidak boleh mengubah status dirinya sendiri
        if ($authUser->id === $id) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat mengaktifkan/menonaktifkan akun sendiri.');
        }

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $newStatus = $user->is_active ? 0 : 1;

        DB::table('users')
            ->where('id', $id)
            ->update([
                'is_active'  => $newStatus,
                'updated_at' => now(),
            ]);

        $message = $newStatus
            ? "User {$user->name} berhasil diaktifkan."
            : "User {$user->name} berhasil dinonaktifkan.";

        return redirect()->back()->with('success', $message);
    }
}