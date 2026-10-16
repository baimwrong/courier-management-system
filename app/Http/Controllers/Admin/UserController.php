<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('branch')->latest('id');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('phone', 'like', $s);
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,branch_manager,hub_operator,cashier,courier,customer',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:50',
        ]);

        // Only superadmin can create a superadmin account
        if ($validated['role'] === 'superadmin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Administrators can assign the Super Administrator role.');
        }

        // Branch managers can only assign users to their own branch
        if (!auth()->user()->isSuperAdmin() && auth()->user()->branch_id) {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);

        return back()->with('success', "User {$user->name} created successfully.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:superadmin,branch_manager,hub_operator,cashier,courier,customer',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        // Prevent non-superadmin from modifying a superadmin or escalating to superadmin
        if (($user->isSuperAdmin() || $validated['role'] === 'superadmin') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Administrators can modify or grant the Super Administrator role.');
        }

        // Prevent self-deactivation
        if (auth()->id() === $user->id && empty($validated['is_active'])) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return back()->with('success', "User {$user->name} updated successfully.");
    }
}
