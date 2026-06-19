<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // 1. Date Range Filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            } elseif (count($dates) == 1) {
                $query->whereDate('created_at', $dates[0]);
            }
        }

        // 2. Multi-User Filter
        if ($request->filled('users') && is_array($request->users)) {
            $query->whereIn('user_id', $request->users);
        }

        // 3. Multi-Action Filter
        if ($request->filled('actions') && is_array($request->actions)) {
            $query->whereIn('action', $request->actions);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Data for filter dropdowns
        $filterUsers = \App\Models\User::whereHas('activityLogs')->orderBy('name')->get(['id', 'name', 'email']);
        $filterActions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('activity_logs.index', compact('logs', 'filterUsers', 'filterActions'));
    }
}
