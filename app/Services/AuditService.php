<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    /**
     * Catat aksi ke audit log.
     *
     * @param  User|null  $user  null untuk event anonymous (failed login dll).
     * @param  string  $action  Snake_case identifier — mis. 'create_election', 'login_success'.
     * @param  array  $details  Payload tambahan (TIDAK boleh berisi password/token/vote candidate).
     * @param  string|null  $ip  Override IP, default request()->ip().
     */
    public function log(?User $user, string $action, array $details = [], ?string $ip = null): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip ?? request()->ip(),
            'created_at' => now(),
        ]);
    }
}
