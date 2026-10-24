<?php

use App\Http\Controllers\AdminInvitationController;
use App\Http\Controllers\AiAssessmentController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AssistanceHistoryController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PortalLoginController;
use App\Http\Controllers\Auth\PortalNewPasswordController;
use App\Http\Controllers\Auth\PortalPasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentImportController;
use App\Http\Controllers\DocumentImportStatusController;
use App\Http\Controllers\HadKifayahController;
use App\Http\Controllers\HadKifayahSettingController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportDataController;
use App\Http\Controllers\MoneyAuditController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Portal\AiController as PortalAiController;
use App\Http\Controllers\Portal\ApplicationController as PortalApplicationController;
use App\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Http\Controllers\Portal\BantuanScanController as PortalBantuanScanController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\DocumentController as PortalDocumentController;
use App\Http\Controllers\Portal\HadKifayahController as PortalHadKifayahController;
use App\Http\Controllers\Portal\HouseholdController as PortalHouseholdController;
use App\Http\Controllers\Portal\LetterController as PortalLetterController;
use App\Http\Controllers\Portal\NotificationsController as PortalNotificationsController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\ProfilAsnafController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ViewProfileController;
use App\Services\WelcomePreviewService;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('welcome', app(WelcomePreviewService::class)->getAll());
})->name('welcome');

// Auth pages (public — no guest middleware so logged-in users can also access)
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register');

// Portal User — login & registration (separate guard, separate session)
Route::get('/user/login', [PortalLoginController::class, 'create'])->name('user.login');
Route::post('/user/login', [PortalLoginController::class, 'store'])->middleware('throttle:login');

Route::get('/user/register', [PortalAuthController::class, 'showRegister'])->name('user.register');
Route::post('/user/register', [PortalAuthController::class, 'register'])->middleware('throttle:register');

// Admin logout (web guard)
Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Portal logout (portal guard)
Route::middleware(['auth:portal'])->group(function () {
    Route::post('/user/logout', [PortalLoginController::class, 'destroy'])->name('user.logout');
});

// Portal 2FA challenge (public, gated by session)
Route::get('/user/2fa', [PortalLoginController::class, 'showChallenge'])->name('user.2fa.challenge');
Route::post('/user/2fa', [PortalLoginController::class, 'verifyChallenge'])->middleware('throttle:6,1')->name('user.2fa.verify');
Route::post('/user/2fa/cancel', [PortalLoginController::class, 'cancelChallenge'])->name('user.2fa.cancel');

// View Profile — top-level list (Phase 1)
Route::get('/viewprofile', [ViewProfileController::class, 'index'])->name('viewprofile.index');

// Real-time global search (admin only — returns asnaf PII)
Route::get('/api/search', [SearchController::class, 'global'])->middleware(['auth:web', 'throttle:search'])->name('api.search');
Route::get('/api/household/search', [HouseholdController::class, 'apiSearch'])->middleware(['auth:web', 'throttle:search'])->name('api.household.search');

// API endpoints (shared auth)
Route::middleware(['auth:web,portal'])->prefix('api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

// Password reset (admin — users broker)
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:register')->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');

// Password reset (portal — portal_users broker)
Route::get('/user/forgot-password', [PortalPasswordResetLinkController::class, 'create'])->name('user.password.request');
Route::post('/user/forgot-password', [PortalPasswordResetLinkController::class, 'store'])->middleware('throttle:register')->name('user.password.email');
Route::get('/user/reset-password/{token}', [PortalNewPasswordController::class, 'create'])->name('user.password.reset');
Route::post('/user/reset-password', [PortalNewPasswordController::class, 'store'])->name('user.password.update');

// AI Chat Assistant (full page + endpoints) — admin only (tools query asnaf data)
Route::middleware('auth:web')->group(function () {
    Route::get('/ai', [AiController::class, 'page'])->name('ai.page');
    Route::post('/ai/chat', [AiController::class, 'chat'])->middleware('throttle:ai-chat')->name('ai.chat');
    Route::post('/ai/clear', [AiController::class, 'clear'])->name('ai.clear');
});

// 2FA challenge (post-login, pre-session)
Route::get('/2fa', [AuthenticatedSessionController::class, 'showChallenge'])->name('auth.2fa.challenge');
Route::post('/2fa', [AuthenticatedSessionController::class, 'verifyChallenge'])->middleware('throttle:6,1')->name('auth.2fa.verify');
Route::post('/2fa/cancel', [AuthenticatedSessionController::class, 'cancelChallenge'])->name('auth.2fa.cancel');

// App (Admin — perlu login via web guard)
Route::middleware(['auth:web'])->prefix('app')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profilasnaf', [ProfilAsnafController::class, 'index'])->name('profilasnaf');
    Route::post('/profil-asnaf', [ProfilAsnafController::class, 'store'])->name('profil-asnaf.store');

    Route::get('/profilasnaf/{ic}/edit', [ProfilAsnafController::class, 'edit'])->name('profilasnaf.edit');
    Route::put('/profilasnaf/{ic}', [ProfilAsnafController::class, 'update'])->name('profilasnaf.update');
    Route::delete('/profilasnaf/{ic}', [ProfilAsnafController::class, 'destroy'])->name('profilasnaf.destroy');
    Route::post('/profilasnaf/bulk-delete', [ProfilAsnafController::class, 'bulkDestroy'])->name('profilasnaf.bulk-destroy');

    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    Route::get('/household', [HouseholdController::class, 'dashboard'])->name('household.dashboard');
    Route::get('/household/search', [HouseholdController::class, 'search'])->name('household.search');
    Route::get('/household/{id}', [HouseholdController::class, 'show'])->name('household.show');
    Route::get('/api/household/stats', [HouseholdController::class, 'apiStats'])->name('api.household.stats');
    Route::post('/household/{household}/assistance', [AssistanceHistoryController::class, 'store'])->name('household.assistance.store');
    Route::put('/assistance/{assistance}', [AssistanceHistoryController::class, 'update'])->name('assistance.update');
    Route::delete('/assistance/{assistance}', [AssistanceHistoryController::class, 'destroy'])->name('assistance.destroy');

    Route::get('/viewprofile', [ViewProfileController::class, 'index'])->name('viewprofile.legacy');
    Route::get('/viewprofile/{ic}', [ViewProfileController::class, 'show'])->name('viewprofile');
    Route::post('/viewprofile/{ic}/approve', [ViewProfileController::class, 'approve'])->name('viewprofile.approve');
    Route::post('/viewprofile/{ic}/reject', [ViewProfileController::class, 'reject'])->name('viewprofile.reject');
    Route::get('/viewprofile/{ic}/letter', [ViewProfileController::class, 'downloadLetter'])->name('viewprofile.letter');

    Route::get('/importcsv', [ImportController::class, 'index'])->name('importcsv');
    Route::post('/importcsv', [ImportController::class, 'store'])->middleware('throttle:import')->name('importcsv.store');
    Route::get('/importcsv/template', [ImportController::class, 'template'])->name('importcsv.template');

    Route::get('/import-data', [ImportDataController::class, 'index'])->name('import-data');

    Route::get('/import-pdf', [DocumentImportController::class, 'index'])->name('import-pdf.index');
    Route::get('/import-pdf/template', [DocumentImportController::class, 'template'])->name('import-pdf.template');
    Route::post('/import-pdf/upload', [DocumentImportController::class, 'upload'])->middleware('throttle:pdf-upload')->name('import-pdf.upload');
    Route::get('/import-pdf/status/{id}', [DocumentImportStatusController::class, 'show'])->name('import-pdf.status');
    Route::get('/import-pdf/preflight', [DocumentImportController::class, 'preflight'])->name('import-pdf.preflight');
    Route::get('/import-pdf/review/{id}', [DocumentImportController::class, 'review'])->name('import-pdf.review');
    Route::post('/import-pdf/reprocess/{id}', [DocumentImportController::class, 'reprocess'])->name('import-pdf.reprocess');
    Route::get('/import-pdf/preview/{id}', [DocumentImportController::class, 'preview'])->name('import-pdf.preview');
    Route::post('/import-pdf/validate/{id}', [DocumentImportController::class, 'validate'])->name('import-pdf.validate');
    Route::get('/api/household/lookup', [DocumentImportController::class, 'householdLookup'])->name('api.household.lookup');
    Route::post('/import-pdf/commit/{id}', [DocumentImportController::class, 'commit'])->name('import-pdf.commit');
    Route::get('/import-pdf/history', [DocumentImportController::class, 'history'])->name('import-pdf.history');
    Route::get('/import-pdf/{id}', [DocumentImportController::class, 'show'])->name('import-pdf.show');
    Route::get('/import-pdf/{id}/file', [DocumentImportController::class, 'file'])->name('import-pdf.file');
    Route::delete('/import-pdf/{id}', [DocumentImportController::class, 'destroy'])->name('import-pdf.destroy');

    Route::get('/hadkifayah', [HadKifayahController::class, 'index'])->name('hadkifayah');
    Route::post('/hadkifayah/preview', [HadKifayahController::class, 'preview'])->name('hadkifayah.preview');
    Route::post('/hadkifayah/print', [HadKifayahController::class, 'print'])->name('hadkifayah.print');
    Route::get('/hadkifayah/settings', [HadKifayahSettingController::class, 'index'])->name('hadkifayah.settings');
    Route::post('/hadkifayah/settings', [HadKifayahSettingController::class, 'update'])->name('hadkifayah.settings.update');

    Route::get('/ai-assessment', [AiAssessmentController::class, 'index'])->name('ai-assessment');
    Route::post('/ai-assessment/run', [AiAssessmentController::class, 'run'])->name('ai-assessment.run');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/verify', [ProfileController::class, 'verifyTwoFactor'])->name('profile.2fa.verify');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');
    Route::get('/profile/download', [ProfileController::class, 'downloadData'])->name('profile.download');
    Route::post('/profile/logout-others', [ProfileController::class, 'logoutOtherDevices'])->name('profile.logout-others');

    Route::get('/admin-invitations', [AdminInvitationController::class, 'index'])->name('admin.invitations.index');
    Route::get('/admin-invitations/create', [AdminInvitationController::class, 'create'])->name('admin.invitations.create');
    Route::post('/admin-invitations', [AdminInvitationController::class, 'store'])->name('admin.invitations.store');
    Route::get('/admin-invitations/{invitation}', [AdminInvitationController::class, 'show'])->name('admin.invitations.show');
    Route::delete('/admin-invitations/{invitation}', [AdminInvitationController::class, 'destroy'])->name('admin.invitations.destroy');
    Route::post('/admin-invitations/{invitation}/deactivate', [AdminInvitationController::class, 'deactivate'])->name('admin.invitations.deactivate');
    Route::post('/admin-invitations/{invitation}/reactivate', [AdminInvitationController::class, 'reactivate'])->name('admin.invitations.reactivate');

    Route::post('/profilasnaf/{ic}/request-documents', [ProfilAsnafController::class, 'requestDocuments'])
        ->name('profilasnaf.request-documents');

    Route::middleware('super.admin')->prefix('admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('admin.audit-logs.export');
        Route::get('/money-audit', [MoneyAuditController::class, 'index'])->name('admin.money-audit.index');
        Route::get('/money-audit/export', [MoneyAuditController::class, 'export'])->name('admin.money-audit.export');
    });
});

// Portal (User — perlu login via portal guard)
Route::middleware(['auth:portal', 'portal.active'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [PortalProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [PortalProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/2fa/enable', [PortalProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/verify', [PortalProfileController::class, 'verifyTwoFactor'])->name('profile.2fa.verify');
    Route::post('/profile/2fa/disable', [PortalProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');
    Route::get('/hadkifayah', [PortalHadKifayahController::class, 'index'])->name('hadkifayah');
    Route::post('/hadkifayah/calculate', [PortalHadKifayahController::class, 'calculate'])->name('hadkifayah.calculate');
    Route::get('/applications', [PortalApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [PortalApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [PortalApplicationController::class, 'store'])->name('applications.store');

    Route::get('/applications/scan', [PortalBantuanScanController::class, 'index'])->name('applications.scan.index');
    Route::post('/applications/scan', [PortalBantuanScanController::class, 'upload'])->middleware('throttle:pdf-upload')->name('applications.scan.upload');
    Route::get('/applications/scan/{id}', [PortalBantuanScanController::class, 'preview'])->name('applications.scan.preview');
    Route::get('/applications/scan/{id}/status', [PortalBantuanScanController::class, 'status'])->name('applications.scan.status');
    Route::post('/applications/scan/{id}/reprocess', [PortalBantuanScanController::class, 'reprocess'])->name('applications.scan.reprocess');
    Route::post('/applications/scan/{id}/commit', [PortalBantuanScanController::class, 'commit'])->name('applications.scan.commit');

    Route::get('/applications/status', [PortalApplicationController::class, 'status'])->name('applications.status');
    Route::get('/applications/{id}', [PortalApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/submit', [PortalApplicationController::class, 'submit'])->name('applications.submit');
    Route::get('/applications/{id}/letter', [PortalApplicationController::class, 'downloadLetter'])->name('applications.letter');
    Route::get('/applications/{id}/receipt', [PortalApplicationController::class, 'receipt'])->name('applications.receipt');

    Route::get('/household', [PortalHouseholdController::class, 'show'])->name('household');
    Route::get('/household/assistance', [PortalHouseholdController::class, 'assistance'])->name('household.assistance');
    Route::get('/household/finance', [PortalHouseholdController::class, 'finance'])->name('household.finance');

    Route::get('/documents', [PortalDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [PortalDocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{id}/download', [PortalDocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{id}/reprocess', [PortalDocumentController::class, 'reprocess'])->name('documents.reprocess');

    Route::get('/letters', [PortalLetterController::class, 'index'])->name('letters.index');

    Route::get('/notifications', [PortalNotificationsController::class, 'index'])->name('notifications.index');

    Route::get('/ai', [PortalAiController::class, 'page'])->name('ai.page');
    Route::post('/ai/chat', [PortalAiController::class, 'chat'])->name('ai.chat');
});

// Backward-compat redirects (old URLs from previous version)
Route::redirect('/dashboard', '/app/dashboard');
Route::redirect('/profilasnaf', '/app/profilasnaf');
Route::redirect('/profil-asnaf', '/app/profilasnaf');
Route::redirect('/history', '/app/history');
Route::redirect('/importcsv', '/app/import-data');
Route::redirect('/import-pdf', '/app/import-data');
Route::redirect('/hadkifayah', '/app/hadkifayah');
Route::redirect('/settings', '/app/settings');
Route::redirect('/ai-assessment', '/app/ai-assessment');
