<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        // Eager load user dengan roles supaya bisa tampilin role badge per row.
        $query = AuditLog::with(['user.roles'])->latest('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by role (panitia, pemilih, saksi, atau anonymous).
        if ($request->filled('role')) {
            if ($request->role === 'anonymous') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('user.roles', fn ($q) => $q->where('name', $request->role));
            }
        }

        $logs = $query->paginate(20)->withQueryString();

        $actions = AuditLog::distinct()->pluck('action')->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'actions'));
    }
}
