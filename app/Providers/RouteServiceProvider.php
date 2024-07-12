<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';
    

    /**
     * The path to your application's "unauthenticated" route.
     *
     * Typically, users are redirected here when they are not authenticated.
     *
     * @var string
     */
    public const UNAUTHENTICATED = 'welcome';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Load all route files from specified folders recursively

            foreach ($this->phpFilesInFolder(base_path('/routes/web')) as $route_file) {
                $this->addWebRoutes($route_file, ['auth']);
            }

            foreach ($this->phpFilesInFolder(base_path('/routes/api')) as $route_file) {
                $this->addApiRoutes($route_file, ['auth:sanctum']);
            }

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }

    // ---------------------------------------------------------------------
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function addWebRoutes(string $route_file_path, array $middleware = []): void
    {
        $middleware = array_unique(array_merge(['web', 'group.authorize', 'friend.authorize', 'expense.authorize', 'payment.authorize'], $middleware));
        Route::middleware($middleware)->group($route_file_path);
    }

    protected function addApiRoutes(string $route_file_path, array $middleware = []): void
    {
        Route::prefix('api')
            ->middleware(array_merge(['api'], $middleware))
            ->group($route_file_path);
    }

    // ---------------------------------------------------------------------

    protected function phpFilesInFolder(string $rootPath, array &$route_file_paths = []): array
    {
        $files = scandir($rootPath);

        foreach ($files as $filename) {
            if (! $this->isSpecialDirectory($filename)) {

                $path = $this->absolutePath($rootPath, $filename);

                if (is_dir($path)) {
                    $this->phpFilesInFolder($path, $route_file_paths);
                } else {
                    if ($this->isPHPFile($filename)) {
                        $route_file_paths[] = $path;
                    }
                }
            }
        }

        return $route_file_paths;
    }

    protected function absolutePath(string $root_path, string $filename): string
    {
        return (string) realpath($root_path.DIRECTORY_SEPARATOR.$filename);
    }

    protected function isPHPFile(string $filename): bool
    {
        return pathinfo($filename, PATHINFO_EXTENSION) == 'php';
    }

    protected function isSpecialDirectory(string $filename): bool
    {
        return in_array($filename, ['.', '..']);
    }
}
