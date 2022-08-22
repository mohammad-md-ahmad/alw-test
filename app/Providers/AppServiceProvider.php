<?php

namespace App\Providers;

use App\Contracts\CommentServiceInterface;
use App\PackageWrappers\SpatieDataLaravel\ArraybleNormalizer as SpatieDataLaravelArraybleNormalizer;
use App\Services\CommentService;
use App\Services\DataRequests\DataRequestValidator;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelData\Normalizers\ArraybleNormalizer;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Extending Spatie Laravel Data to include route params
        $this->app->bind(DataValidatorResolver::class, DataRequestValidator::class);
        $this->app->bind(CommentServiceInterface::class, CommentService::class);

        // Override Spatie's ArraybleNormalizer to include route params
        $this->app->bind(ArraybleNormalizer::class, SpatieDataLaravelArraybleNormalizer::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
