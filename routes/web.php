<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\IndexStaffUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\VisitorApplicationController;
use App\Models\Article;
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::controller(SocialiteController::class)->name('socialite.')->group(function () {
    Route::get('oauth/{provider}', 'redirectToProvider')->name('redirect');
    Route::get('oauth/callback/{provider}', 'handleProviderCallback')->name('callback');
});

Route::get('login', fn () => to_route('socialite.redirect', 'vatsim'))->name('login');
Route::post('logout', fn () => auth()->logout())->name('logout');

Route::get('/', function () {
    return Inertia::render('Home', [
        'events' => Event::whereFuture('starts_at')->latest()->get(),
        'articles' => Article::latest()->take(3)->get(),
    ]);
})->name('home');

Route::resource('articles', ArticleController::class)->only(['index', 'show']);

Route::resource('documents', DocumentController::class)->only(['index', 'show']);
Route::resource('downloads', DownloadController::class)->only(['index']);

Route::resource('events', EventController::class)->only(['index', 'show']);
Route::post('events/{event}/registrations',
    [EventRegistrationController::class, 'store'])->name('events.registrations.store');
Route::delete('events/{event}/registrations/{registration}',
    [EventRegistrationController::class, 'destroy'])->name('events.registrations.destroy');

Route::get('staff', IndexStaffUserController::class)->name('staff.index');

Route::resource('users', UserController::class)->only(['index', 'show']);

Route::middleware(['auth'])->group(function () {
    Route::resource('visitor-applications', VisitorApplicationController::class)->only(['create', 'store']);
    Route::resource('feedback', FeedbackController::class)->only(['create', 'store']);

    Route::controller(UserDashboardController::class)->name('dashboard.')->group(function () {
        Route::get('dashboard', 'index')->name('index');
        Route::get('dashboard/edit', 'edit')->name('edit');
        Route::put('dashboard/profile', 'update')->name('update');
        Route::get('dashboard/feedback', 'feedback')->name('feedback');
    });

    // Notification routes
    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function (
    ) {
        Route::get('/', 'index')->name('index');
        Route::put('{id}/read', 'markAsRead')->name('read');
        Route::put('read-all', 'markAllAsRead')->name('read.all');
        Route::delete('/', 'deleteAll')->name('delete.all');
        Route::delete('{id}', 'destroy')->name('destroy');
    });
});

require __DIR__ . '/admin.php';
