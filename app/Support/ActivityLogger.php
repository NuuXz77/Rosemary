<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    private static function getFirstFilledAttribute(Model $model, array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            $value = $model->getAttribute($attribute);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private static function resolveCauserMeta(?Model $causer): array
    {
        $sessionStudentName = session('pos_student_name');
        $sessionStudentId = session('pos_student_id');

        $causerName = $causer ? self::getFirstFilledAttribute($causer, ['name', 'full_name', 'nama']) : null;
        $causerUsername = $causer ? self::getFirstFilledAttribute($causer, ['username', 'email']) : null;

        // For PIN cashier flow, preserve the actual student name as actor label.
        if (is_string($sessionStudentName) && trim($sessionStudentName) !== '') {
            $causerName = trim($sessionStudentName);
        }

        return array_filter([
            'causer_name' => $causerName,
            'causer_username' => $causerUsername,
            'cashier_student_id' => $sessionStudentId,
        ], fn($value) => !is_null($value) && $value !== '');
    }

    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?Model $causer = null
    ): void {
        try {
            if (app()->runningInConsole()) {
                return;
            }

            $request = request();
            $causerMeta = self::resolveCauserMeta($causer);

            ActivityLog::create([
                'action' => $action,
                'description' => $description,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'causer_type' => $causer?->getMorphClass(),
                'causer_id' => $causer?->getKey(),
                'properties' => empty(array_merge($properties, $causerMeta)) ? null : array_merge($properties, $causerMeta),
                'ip_address' => $request?->ip(),
                'method' => $request?->method(),
                'url' => $request?->fullUrl(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity log gagal disimpan: ' . $e->getMessage());
        }
    }
}
