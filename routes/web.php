<?php

use App\Http\Middleware\Authenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'login-page')->name('login');
Route::livewire('/register', 'register-page')->name('register');

// Route::livewire('/welcome2', function () {
//     // return redirect()->away(config('services.api.url') . '/welcome2');

//     $url = config('services.api.url').'/welcome2';

//     return Redirect::to($url);

// })->name('welcome2');

// IMAGE SHOW
Route::livewire('/image', 'image')->name('image');

// Map of the Route
Route::livewire('/allroutesmap', 'fullmap')->name('allroutesmap');

// Map of the Route
Route::livewire('/fairgroundsmap', 'fairgrounds')->name('fairgroundsmap');

// Settings Page
// Route::livewire('/settings', 'settings-page')->name('settings');

// Route::get('/welcome2', function () {
//     return redirect()->away(config('services.api.url') . '/welcome2');
// });

Route::get('/auth/callback', function (Request $request) {
    if ($request->filled('token')) {
        session(['auth_token' => $request->get('token'), 'token_verified_at' => now()]);

        return redirect()->route('home');
    }

    return redirect()->route('login');
});

Route::middleware(Authenticated::class)->group(function () {
    Route::livewire('/', 'home-page')->name('home');

    Route::livewire('/movies', 'movie-index')->name('movies');
    Route::livewire('/movies/{slug}', 'movie-show')->name('movies.show');
    Route::livewire('/quiz/{session}', 'quiz-runner')->name('quiz.play');
    Route::livewire('/quiz/{session}/summary', 'quiz-summary')->name('quiz.summary');
    Route::livewire('/progress', 'progress-dashboard')->name('progress');
    Route::livewire('/settings', 'settings-page')->name('settings');
    Route::livewire('/leaderboard', 'leaderboard')->name('leaderboard');

    Route::livewire('/cuesheet', 'cuesheet-index')->name('cuesheet');
    Route::livewire('/merchandise', 'merchandise')->name('merchandise');
    Route::livewire('/notifiy', 'notifiy')->name('notifiy');

});
