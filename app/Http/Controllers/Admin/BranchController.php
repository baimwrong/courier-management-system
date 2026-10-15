<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::withCount(['users', 'originShipments'])->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('code', 'like', $s)
                    ->orWhere('city', 'like', $s);
            });
        }

        $branches = $query->paginate(15)->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:branches,code',
            'name' => 'required|string|max:150',
            'type' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ]);

        $branch = Branch::create($validated);

        return back()->with('success', "Branch {$branch->name} ({$branch->code}) created successfully.");
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'is_active' => 'required|boolean',
        ]);

        $branch->update($validated);

        return back()->with('success', "Branch {$branch->name} updated successfully.");
    }
}
