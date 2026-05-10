<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    protected function audit(string $action, array $details = []): void
    {
        app(AuditService::class)->log(
            auth()->user(),
            $action,
            $details,
            request()->ip()
        );
    }
}
