<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $recentLogins = AuditLog::where('action', 'LOGIN_SUCCESS')
            ->latest()
            ->limit(10)
            ->get();

        $loginFailures = AuditLog::where('action', 'LOGIN_FAILURE')
            ->latest()
            ->limit(10)
            ->get();

        $deniedAccess = AuditLog::whereIn('action', ['ACCESS_DENIED', 'PERMISSION_DENIED'])
            ->latest()
            ->limit(10)
            ->get();

        $blockedUploads = AuditLog::where('action', 'blocked_malicious_upload')
            ->latest()
            ->limit(10)
            ->get();

        $criticalEvents = AuditLog::where('severity', 'CRITICAL')
            ->latest()
            ->limit(10)
            ->get();

        $alerts = SecurityAlert::latest()
            ->limit(15)
            ->get();

        $summary = [
            'login_success_24h' => AuditLog::where('action', 'LOGIN_SUCCESS')->where('created_at', '>=', now()->subDay())->count(),
            'login_failure_24h' => AuditLog::where('action', 'LOGIN_FAILURE')->where('created_at', '>=', now()->subDay())->count(),
            'denied_24h' => AuditLog::whereIn('action', ['ACCESS_DENIED', 'PERMISSION_DENIED'])->where('created_at', '>=', now()->subDay())->count(),
            'blocked_uploads_24h' => AuditLog::where('action', 'blocked_malicious_upload')->where('created_at', '>=', now()->subDay())->count(),
            'alerts_24h' => SecurityAlert::where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('admin.security.index', compact(
            'alerts',
            'blockedUploads',
            'criticalEvents',
            'deniedAccess',
            'loginFailures',
            'recentLogins',
            'summary'
        ));
    }
}
