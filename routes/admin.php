<?php

use App\Enums\ControllerRating;
use App\Http\Controllers\Admin\AbsenceController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DossierController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventPositionController;
use App\Http\Controllers\Admin\EventRegistrationController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorApplicationController;
use App\Models\ControllerSession;
use App\Models\Feedback;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->name('admin.')->middleware(['role.group:staff'])->group(function () {
    Route::resource('absences', AbsenceController::class)->middleware(['role.group:manager'])
        ->except(['show', 'edit', 'update']);

    Route::get('activity', [ActivityController::class, 'index'])->middleware(['role.any:atm,datm,ta,wm'])->name('activity.index');

    Route::resource('articles', ArticleController::class)->except('show');

    Route::resource('documents', DocumentController::class)->except('show');

    Route::resource('dossiers', DossierController::class)->only(['index']);

    Route::resource('downloads', DownloadController::class)->except('show');

    Route::resource('events', EventController::class)->middleware(['role.any:atm,datm,ta,ec'])->except('show');

    Route::controller(EventPositionController::class)->middleware(['role.any:atm,datm,ta,ec'])->group(function () {
        Route::get('events/{event}/assign', 'show')->name('events.assign');
        Route::post('events/{event}/assign', 'assign')->name('events.assign.store');
        Route::post('events/{event}/notify', 'notify')->name('events.notify');
    });

    Route::controller(EventRegistrationController::class)->middleware(['role.any:atm,datm,ta,ec'])->group(function () {
        Route::post('events/{event}/registrations', 'store')->name('events.registrations.store');
        Route::delete('events/{event}/registrations/{user}', 'destroy')->name('events.registrations.destroy');
    });

    Route::controller(FeedbackController::class)->middleware(['role.any:atm,datm,ta,ec'])->group(function () {
        Route::get('feedbacks', 'index')->name('feedbacks.index');
        Route::post('feedbacks/{feedback}/accept', 'accept')->name('feedbacks.accept');
        Route::post('feedbacks/{feedback}/reject', 'reject')->name('feedbacks.reject');
    });

    Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);

    Route::controller(VisitorApplicationController::class)->middleware(['role.group:manager'])->group(function () {
        Route::get('visitor-applications', 'index')->name('visitor-applications.index');
        Route::post('visitor-applications/{visitor_application}/accept', 'accept')->name('visitor-applications.accept');
        Route::post('visitor-applications/{visitor_application}/reject', 'reject')->name('visitor-applications.reject');
    });

    Route::get('/', function () {
        /*
        * We want to get the following statistics and pass them as props to the page:
        * 1. The total duration of all controller sessions for the current quarter
        * 2. The total number of controller sessions for the current quarter.
        * 3. The total number of home and visitor users.
        * 4. The total number of users by ControllerRating.
        * 5. The total number of feedback items with a not null approved_at date for the last 13 months.
        * 6. The total number of controller hours for the last 13 months.
        * */

        // Get the start and end dates for the current quarter
        $quarterStart = Carbon::now()->startOfQuarter();
        $quarterEnd = Carbon::now()->endOfQuarter();

        // 1. Total duration of all controller sessions for the current quarter
        $controllerSessions = ControllerSession::whereBetween('connected_at', [$quarterStart, $quarterEnd])
            ->whereNotNull('disconnected_at')
            ->get();

        $sessionDurations = $controllerSessions->sum(function ($session) {
            return $session->connected_at->diffInSeconds($session->disconnected_at);
        });

        // 2. Total number of controller sessions for the current quarter
        $sessionCount = $controllerSessions->count();

        // 3. Total number of home and visitor users
        $userCounts = [
            'home' => User::whereMember(true)->whereVisitor(false)->count(),
            'visitor' => User::whereVisitor(true)->count(),
        ];

        // 4. Total number of users by ControllerRating
        $ratingCounts = [];
        foreach (ControllerRating::cases() as $rating) {
            $ratingCounts[] = [
                'rating' => $rating->getDisplayName(),
                'count' => User::whereRating($rating->value)->count(),
            ];
        }

        // 5. Feedback items with not null approved_at date for the last 13 months
        $feedbackByMonth = [];
        // 6. Controller hours for the last 13 months
        $sessionsByMonth = [];

        // Get stats for the last 13 months
        for ($i = 0; $i < 13; $i++) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M');
            $yearLabel = $monthStart->format('Y');

            // Feedback stats
            $feedbackCount = Feedback::whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereNotNull('approved_at')
                ->count();

            $feedbackByMonth[] = [
                'month' => $monthLabel,
                'year' => $yearLabel,
                'total' => $feedbackCount,
            ];

            // Controller hours stats
            $monthlySessionsTime = ControllerSession::whereBetween('connected_at', [$monthStart, $monthEnd])
                ->whereNotNull('disconnected_at')
                ->get()
                ->sum(function ($session) {
                    return $session->connected_at->diffInSeconds($session->disconnected_at);
                });

            $sessionsByMonth[] = [
                'month' => $monthLabel,
                'year' => $yearLabel,
                'total' => $monthlySessionsTime,
            ];
        }

        // Reverse arrays so they're in chronological order (oldest to newest)
        $feedbackByMonth = array_reverse($feedbackByMonth);
        $sessionsByMonth = array_reverse($sessionsByMonth);

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'sessionDurations' => $sessionDurations,
                'sessionCount' => $sessionCount,
                'counts' => [
                    'home' => $userCounts['home'],
                    'visitor' => $userCounts['visitor'],
                    'byRating' => $ratingCounts,
                ],
                'feedback' => $feedbackByMonth,
                'hours' => $sessionsByMonth,
            ],
        ]);
    })->name('dashboard');
});
