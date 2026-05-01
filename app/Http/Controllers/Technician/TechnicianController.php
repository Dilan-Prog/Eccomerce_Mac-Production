<?php
// app/Http/Controllers/Technician/TechnicianController.php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\ServiceReport;

class TechnicianController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total'     => ServiceReport::where('user_id', auth()->id())->count(),
            'draft'     => ServiceReport::where('user_id', auth()->id())->where('status', 'draft')->count(),
            'completed' => ServiceReport::where('user_id', auth()->id())->where('status', 'completed')->count(),
        ];

        $recientes = ServiceReport::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('technician.dashboard', compact('stats', 'recientes'));
    }
}
