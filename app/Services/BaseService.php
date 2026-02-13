<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    /**
     * Execute a database transaction
     */
    protected function executeTransaction(callable $callback, string $errorMessage = 'Transaction failed')
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($errorMessage, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Log service action
     */
    protected function logAction(string $action, array $data = []): void
    {
        Log::channel('service')->info($action, array_merge([
            'service' => static::class,
            'timestamp' => now()->toISOString()
        ], $data));
    }

    /**
     * Log error
     */
    protected function logError(string $action, \Exception $e, array $data = []): void
    {
        Log::channel('service')->error($action, array_merge([
            'service' => static::class,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], $data));
    }
}
