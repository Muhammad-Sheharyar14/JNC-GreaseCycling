<?php

use Illuminate\Support\Facades\Route;

// Home Landing Page Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Privacy Policy Route
Route::get('/privacy-police', function () {
    return view('privacy');
})->name('privacy.police');

// Named login route for Filament redirects
Route::get('/login', function () {
    return redirect('/driver/');
})->name('login');

// Unified API Login route managed strictly by the web middleware group
Route::post('/api/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

// Official Collection Manifest View & Download Routes
Route::get('/manifest/{pickupEvent}', [\App\Http\Controllers\ManifestController::class, 'show'])->name('manifest.show');
Route::get('/manifest/{pickupEvent}/download', [\App\Http\Controllers\ManifestController::class, 'download'])->name('manifest.download');

// Driver App Single Page Application (SPA) Fallback Route
Route::get('/driver/{any?}', function () {
    $indexPath = public_path('driver/index.html');
    if (file_exists($indexPath)) {
        return response()->file($indexPath, ['Content-Type' => 'text/html']);
    }
    return response('Driver app not deployed. Please run npm run build.', 404);
})->where('any', '^(?!assets/|.*\.(js|css|png|jpg|jpeg|svg|ico|json|webmanifest)$).*$');

// Direct Storage File Serving Route (Bypasses webserver symlink & disk mismatch issues)
Route::get('/storage/{filePath}', function ($filePath) {
    $cleanPath = preg_replace('#^/?(storage/)+#', '', $filePath);

    $candidatePaths = [
        storage_path('app/public/' . $cleanPath),
        storage_path('app/' . $cleanPath),
        public_path('storage/' . $cleanPath),
        base_path('storage/app/public/' . $cleanPath),
        base_path('storage/app/' . $cleanPath),
    ];

    foreach ($candidatePaths as $path) {
        if (file_exists($path) && !is_dir($path)) {
            $mimeType = mime_content_type($path) ?: 'application/octet-stream';
            return response()->file($path, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    abort(404);
})->where('filePath', '.*');


