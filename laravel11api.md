# Laravel 11 API

## Kernel

The `Kernel.php` file has been removed in Laravel 11. Middleware configuration is now done in `bootstrap/app.php`.

## Middleware

You can create middleware using the `php artisan make:middleware YourMiddlewareName` Artisan command.

To register middleware:

-   **Globally:** Append the middleware to the `bootstrap/app.php` file to be triggered on every call.
-   **Route-specific:** Use the `append` method on the `Middleware` object inside the `withMiddleware` function in route definitions (e.g., in `routes/web.php` or `routes/api.php`).

    Example:

    ```php
    Route::middleware('auth:api')->get('/users', function () {
        // ...
    })->withMiddleware(function (Middleware $middleware) {
        $middleware->append(YourMiddleware::class);
    });
    ```

## Routes

Scheduled tasks can now be defined directly in the `routes/console.php` file.
