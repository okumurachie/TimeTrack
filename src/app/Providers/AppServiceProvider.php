<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Tests\HasInDatabase;
use Illuminate\Testing\Constraints\HasInDatabase as BaseHasInDatabase;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseHasInDatabase::class, HasInDatabase::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Carbon::macro('toJapaneseDate', function () {
            /** @var \Carbon\Carbon $this */
            return $this->format('Y年m月d日');
        });
    }
}
