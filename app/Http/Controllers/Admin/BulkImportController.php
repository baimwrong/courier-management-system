<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BulkImport;
use App\Services\BulkImportService;
use Illuminate\Http\Request;

class BulkImportController extends Controller
{
    public function __construct(protected BulkImportService $bulkImportService)
    {
    }

    public function index()
    {
        $imports = BulkImport::with('user')->latest('id')->paginate(10);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.bulk_import.index', compact('imports', 'branches'));
    }

    public function importShipments(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB
        ]);

        $file = $request->file('csv_file');
        $originalName = $file->getClientOriginalName();

        $result = $this->bulkImportService->importShipments($file, auth()->user(), $originalName);

        $msg = "Bulk shipment import processed: {$result['successful']} successful, {$result['failed']} failed.";

        if ($result['failed'] > 0) {
            return redirect()->route('admin.bulk-import.index')
                ->with('warning', $msg)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.bulk-import.index')->with('success', $msg);
    }

    public function importStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
            'location_city' => 'nullable|string',
            'notes' => 'nullable|string',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:10240',
            'awb_text' => 'nullable|string',
        ]);

        $user = auth()->user();
        $status = $request->status;
        $city = $request->location_city ?: ($user->branch?->city ?? 'Central Hub');
        $notes = $request->notes ?: "Bulk status scan update to {$status}";

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $originalName = $file->getClientOriginalName();
            $result = $this->bulkImportService->importStatusUpdates($file, $user, $originalName, $status, $city, $notes);
        } elseif ($request->filled('awb_text')) {
            $awbList = preg_split('/[\s,]+/', trim($request->awb_text));
            $awbList = array_filter(array_unique(array_map('trim', $awbList)));
            $originalName = 'manual_text_batch_' . date('Ymd_His') . '.txt';
            $result = $this->bulkImportService->importStatusUpdates($awbList, $user, $originalName, $status, $city, $notes);
        } else {
            return back()->with('error', 'Please provide either a CSV file or paste tracking numbers.');
        }

        $msg = "Bulk status update processed: {$result['successful']} tracking milestones updated, {$result['failed']} failed.";

        if ($result['failed'] > 0) {
            return redirect()->route('admin.bulk-import.index')
                ->with('warning', $msg)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.bulk-import.index')->with('success', $msg);
    }

    public function importTariffs(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $originalName = $file->getClientOriginalName();

        $result = $this->bulkImportService->importTariffs($file, auth()->user(), $originalName);

        $msg = "Bulk tariff rates processed: {$result['successful']} rate entries updated/created, {$result['failed']} failed.";

        if ($result['failed'] > 0) {
            return redirect()->route('admin.bulk-import.index')
                ->with('warning', $msg)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.bulk-import.index')->with('success', $msg);
    }

    public function downloadTemplate(string $type)
    {
        $filename = match ($type) {
            'shipments' => 'shipments_sample_template.csv',
            'status' => 'status_update_sample_template.csv',
            'tariffs' => 'tariffs_sample_template.csv',
            default => abort(404),
        };

        $path = public_path("templates/{$filename}");

        if (!file_exists($path)) {
            abort(404, "Template file not found.");
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
