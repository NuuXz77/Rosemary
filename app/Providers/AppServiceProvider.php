<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin bypass all permissions
        Gate::before(function ($user, $ability) {
            if ($user->username === 'admin') {
                return true;
            }
        });

        $this->registerActivityLogging();
    }

    private function registerActivityLogging(): void
    {
        Event::listen(Login::class, function (Login $event) {
            ActivityLogger::log(
                action: 'login',
                description: 'Login berhasil',
                subject: $event->user,
                properties: ['guard' => $event->guard],
                causer: $event->user,
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            ActivityLogger::log(
                action: 'logout',
                description: 'Logout berhasil',
                subject: $event->user,
                properties: ['guard' => $event->guard],
                causer: $event->user,
            );
        });

        Event::listen('eloquent.created: *', function (string $eventName, array $data) {
            $this->logModelCrud('created', $data[0] ?? null);
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data) {
            $this->logModelCrud('updated', $data[0] ?? null);
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data) {
            $this->logModelCrud('deleted', $data[0] ?? null);
        });
    }

    private function logModelCrud(string $action, mixed $model): void
    {
        if (! $model instanceof Model) {
            return;
        }

        if (! str_starts_with($model::class, 'App\\Models\\')) {
            return;
        }

        if ($model instanceof ActivityLog) {
            return;
        }

        $properties = [];

        if ($action === 'updated') {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $properties = [
                'changes' => [
                    'old' => array_intersect_key($model->getOriginal(), $changes),
                    'new' => $changes,
                ],
            ];
        }

        ActivityLogger::log(
            action: $action,
            description: class_basename($model) . ' ' . $action,
            subject: $model,
            properties: $properties,
            causer: auth()->user(),
        );
    }
}
