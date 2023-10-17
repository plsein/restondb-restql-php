<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::listen(function (QueryExecuted $query) {
            $debug = env('APP_DEBUG', FALSE);
            if ($debug) {
                Log::channel('stack')->info('{time}: {bindings} > {sql}', ['time' => $query->time, 'bindings' => $query->bindings, 'sql' => $query->sql]);
            }
            Log::info('{time}: {bindings} > {sql}', ['time' => $query->time, 'bindings' => '', 'sql' => $query->sql]);
        });

        DB::whenQueryingForLongerThan(500, function (Connection $connection, QueryExecuted $event) {
            Log::info('Query took more than 500 ms: {event}', ['event' => $event]);
        });
    }
}
