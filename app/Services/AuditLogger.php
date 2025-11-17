<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Log financial actions to the financial log channel
     *
     * @param string $action
     * @param int $userId
     * @param array $data
     * @return void
     */
    public static function logFinancialAction($action, $userId, $data)
    {
        Log::channel('financial')->info($action, [
            'user_id' => $userId,
            'ip' => request()->ip(),
            'data' => $data,
        ]);
    }
    
    /**
     * Log security events to the security log channel
     *
     * @param string $event
     * @param string $severity
     * @param array $data
     * @return void
     */
    public static function logSecurityEvent($event, $severity, $data = [])
    {
        Log::channel('security')->{$severity}($event, array_merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data));
    }
}