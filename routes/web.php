<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BikeController;
use App\Http\Controllers\MaintenanceRuleController;
use App\Http\Controllers\StravaController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\StravaWebhookController;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::post('/language', [LanguageController::class, 'update'])->name('language.update');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/cron', CronController::class)->name('cron');

Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/legal-notice', [PublicPageController::class, 'legalNotice'])->name('legal-notice');
Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/feedback', [PublicPageController::class, 'feedback'])->name('feedback');
Route::post('/feedback', [PublicPageController::class, 'feedbackSubmit'])->name('feedback.submit');
Route::get('/feedback/thank-you', [PublicPageController::class, 'feedbackThankYou'])->name('feedback.thank-you');

Route::get('/strava/webhook', [StravaWebhookController::class, 'verify'])->name('strava.webhook.verify');
Route::post('/strava/webhook', [StravaWebhookController::class, 'handle'])->name('strava.webhook.handle');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/sync', [DashboardController::class, 'sync'])->name('sync');
    Route::post('/sync/full', [DashboardController::class, 'fullSync'])->name('sync.full');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');

    Route::get('/strava/connect', [StravaController::class, 'connect'])->name('strava.connect');
    Route::get('/strava/callback', [StravaController::class, 'callback'])->name('strava.callback');
    Route::post('/strava/disconnect', [StravaController::class, 'disconnect'])->name('strava.disconnect');

    Route::get('/bikes', [BikeController::class, 'index'])->name('bikes.index');
    Route::post('/bikes', [BikeController::class, 'update'])->name('bikes.update');

    Route::get('/bikes/{bike}', [BikeController::class, 'show'])->name('bikes.show');
    Route::post('/bikes/{bike}/rules', [BikeController::class, 'storeRule'])->name('bikes.rules.store');

    Route::post('/rules/{rule}/edit', [MaintenanceRuleController::class, 'update'])->name('rules.update');
    Route::post('/rules/{rule}/reset', [MaintenanceRuleController::class, 'reset'])->name('rules.reset');
    Route::post('/rules/{rule}/delete', [MaintenanceRuleController::class, 'delete'])->name('rules.delete');
    Route::post('/rules/{rule}/done', [MaintenanceRuleController::class, 'done'])->name('rules.done');

    Route::post('/rules/{rule}/done', [MaintenanceRuleController::class, 'done'])->name('rules.done');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'password'])->name('settings.password');
    Route::post('/settings/email-reminders', [SettingsController::class, 'emailReminders'])->name('settings.email-reminders');
    Route::post('/settings/language', [SettingsController::class, 'language'])->name('settings.language');
    Route::post('/settings/export-data', [SettingsController::class, 'exportData'])->name('settings.export-data');
    Route::post('/settings/delete-account', [SettingsController::class, 'deleteAccount'])->name('settings.delete-account');

    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');

    Route::post('/settings/test-due-email', [SettingsController::class, 'testDueEmail'])->name('settings.test-due-email');

    Route::middleware('admin')->group(function (): void {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    });
});

require __DIR__ . '/auth.php';

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
