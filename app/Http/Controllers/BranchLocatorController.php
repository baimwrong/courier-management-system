<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchLocatorController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::where('is_active', true);

        if ($request->filled('city')) {
            $query->whereRaw('LOWER(city) = ?', [strtolower(trim($request->city))]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('address', 'like', $s)
                    ->orWhere('code', 'like', $s);
            });
        }

        $branches = $query->orderBy('type')->orderBy('name')->paginate(12)->withQueryString();
        $cities = Branch::where('is_active', true)->distinct()->pluck('city')->sort()->values();

        return view('branches.index', compact('branches', 'cities'));
    }
}
