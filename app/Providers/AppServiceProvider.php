<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use App\Models\Entreprise;

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
        Schema::defaultStringLength(191);

        // Global Blade helper for entreprise currency formatting.
        Blade::directive('currency', function ($expression) {
            return "<?php echo optional(auth()->user()?->entreprise)->formatAmount($expression) ?? number_format($expression, 0, ',', ' ') . ' $'; ?>";
        });
    }
}
