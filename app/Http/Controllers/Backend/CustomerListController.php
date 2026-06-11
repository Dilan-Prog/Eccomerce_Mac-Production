<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerListDataTable;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerListController extends Controller
{
    public function index(CustomerListDataTable $dataTable)
    {
        return $dataTable->render('admin.customer-list.index');
    }

    public function changeStatus(Request $request)
    {
        $customer = User::findOrFail($request->id);
        $customer->status = $request->status == 'true' ? 'active' : 'inactive';
        $customer->save();

        return response(['message' => 'Status has been updated!']);
    }

    public function uploadCsf(Request $request, User $user)
    {
        $request->validate([
            'csf_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if ($user->csf_path) {
            Storage::delete($user->csf_path);
        }

        $user->csf_path   = $request->file('csf_file')->store('csf');
        $user->b2b_status = 'pending';
        $user->save();

        return response()->json(['message' => 'CSF subido correctamente.']);
    }

    public function viewCsf(User $user)
    {
        abort_unless($user->csf_path && Storage::exists($user->csf_path), 404);

        return Storage::response($user->csf_path, null, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function b2bStatus(Request $request, User $user)
    {
        $request->validate([
            'b2b_status' => ['required', 'in:pending,verified,rejected'],
        ]);

        $user->b2b_status = $request->b2b_status;
        $user->save();

        return response()->json(['message' => 'Estado B2B actualizado.']);
    }
}
