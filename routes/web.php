<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\CompetitionController as AdminCompetitionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FunnelController as AdminFunnelController;
use App\Http\Controllers\Admin\MentorController as AdminMentorController;
use App\Http\Controllers\Admin\SampleController as AdminSampleController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\AppliedController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\ProfileController as MentorProfileController;
use App\Http\Controllers\Mentor\RequestController as MentorRequestController;
use App\Http\Controllers\Mentor\TestimonialController as MentorTestimonialController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\PanelPasswordController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReviewRequestController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Semua route ada di file ini.
| Semua halaman WAJIB login (tamu diarahkan ke /login).
| Peran akun ada di users.role: user (siswa) | mentor | admin
|--------------------------------------------------------------------------
*/

// ============================================================
// LOGIN / DAFTAR (tamu)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Lupa password (link reset dikirim ke email)
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ============================================================
// HALAMAN DEPAN: tamu melihat sambutan, siswa melihat Beranda
// (pemisahan peran ada di HomeController@index)
// ============================================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ============================================================
// SISI SISWA (role: user)
// ============================================================
Route::middleware(['auth', 'role:user'])->group(function () {
    // Info beasiswa, lomba, alumni/mentor
    Route::get('/find-scholarship', [ScholarshipController::class, 'index']);
    Route::get('/find-scholarship/{id}', [ScholarshipController::class, 'show'])->whereNumber('id');
    Route::get('/lomba', [CompetitionController::class, 'index']);
    Route::get('/lomba/{id}', [CompetitionController::class, 'show'])->whereNumber('id');
    Route::get('/alumni', [MentorController::class, 'index']);
    Route::get('/alumni/{id}', [MentorController::class, 'show'])->whereNumber('id');

    // Tombol "Apply Now": catat klik lalu arahkan ke situs resmi
    Route::get('/go/{type}/{id}', [ApplyController::class, 'go'])->whereNumber('id')->name('apply.go');

    // Verifikasi email (hanya siswa yang diwajibkan)
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');

    // "Minta masukan mentor" (butuh email terverifikasi)
    Route::post('/review-requests', [ReviewRequestController::class, 'store'])->middleware('verified')->name('review-requests.store');
    Route::post('/review-requests/{reviewRequest}/cancel', [ReviewRequestController::class, 'cancel'])->whereNumber('reviewRequest')->name('review-requests.cancel');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/riwayat', [AppliedController::class, 'index'])->name('riwayat');
    Route::redirect('/applied', '/riwayat');
    Route::get('/alert', [AlertController::class, 'index']);

    // Settings
    Route::prefix('settings')->group(function () {
        Route::redirect('/', '/settings/personal');

        Route::get('/personal', [SettingsController::class, 'personal']);
        Route::put('/personal', [SettingsController::class, 'updatePersonal'])->name('settings.personal.update');
        Route::post('/photo', [SettingsController::class, 'updatePhoto'])->name('settings.photo.update');
        Route::get('/photo', [SettingsController::class, 'photo'])->name('settings.photo');

        Route::get('/profile', [SettingsController::class, 'profile']);
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');

        Route::get('/social', [SettingsController::class, 'social']);
        Route::put('/social', [SettingsController::class, 'updateSocial'])->name('settings.social.update');

        Route::get('/account', [SettingsController::class, 'account']);
        Route::put('/account/contact', [SettingsController::class, 'updateContact'])->name('settings.contact.update');
        Route::put('/account/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
        Route::delete('/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');
    });

    // CV dan esai (PDF, maks 2 MB, privat)
    // Mengunggah berkas butuh email terverifikasi; membuka dan menghapus berkas sendiri tidak dikunci.
    Route::post('/submissions', [SubmissionController::class, 'store'])->middleware('verified')->name('submissions.store');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'download'])->name('submissions.download');
    Route::delete('/submissions/{submission}', [SubmissionController::class, 'destroy'])->name('submissions.destroy');
});

// ============================================================
// PASSWORD MENTOR & ADMIN
// Akun dengan password sementara dipaksa ke halaman ini oleh middleware 'password.fresh'.
// ============================================================
Route::middleware(['auth', 'role:mentor,admin'])->prefix('panel')->group(function () {
    Route::get('/password', [PanelPasswordController::class, 'edit'])->name('panel.password');
    Route::put('/password', [PanelPasswordController::class, 'update'])->name('panel.password.update');
});

// ============================================================
// SISI MENTOR (role: mentor)
// Mentor hanya melihat nama siswa dan catatan pengajuan, tidak membuka berkas.
// ============================================================
Route::middleware(['auth', 'role:mentor', 'password.fresh'])->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/', [MentorDashboardController::class, 'index'])->name('dashboard');

    Route::get('/requests', [MentorRequestController::class, 'index'])->name('requests');
    Route::get('/requests/{reviewRequest}', [MentorRequestController::class, 'show'])->whereNumber('reviewRequest')->name('requests.show');
    Route::post('/requests/{reviewRequest}/reply', [MentorRequestController::class, 'reply'])->whereNumber('reviewRequest')->name('requests.reply');
    Route::post('/requests/{reviewRequest}/decline', [MentorRequestController::class, 'decline'])->whereNumber('reviewRequest')->name('requests.decline');
    Route::get('/files/{submission}', [MentorRequestController::class, 'file'])->whereNumber('submission')->name('files');

    Route::get('/profile', [MentorProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [MentorProfileController::class, 'update'])->name('profile.update');

    Route::get('/testimonials', [MentorTestimonialController::class, 'index'])->name('testimonials');
    Route::post('/testimonials', [MentorTestimonialController::class, 'store'])->name('testimonials.store');
    Route::delete('/testimonials/{id}', [MentorTestimonialController::class, 'destroy'])->whereNumber('id')->name('testimonials.destroy');
});

// ============================================================
// SISI ADMIN (role: admin)
// Admin TIDAK membuka berkas (CV/esai) siswa dan tidak melihat data pribadi siswa.
// ============================================================
Route::middleware(['auth', 'role:admin', 'password.fresh'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('scholarships', AdminScholarshipController::class)->except('show');
    Route::resource('competitions', AdminCompetitionController::class)->except('show');

    Route::get('/mentors', [AdminMentorController::class, 'index'])->name('mentors.index');
    Route::get('/mentors/create', [AdminMentorController::class, 'create'])->name('mentors.create');
    Route::post('/mentors', [AdminMentorController::class, 'store'])->name('mentors.store');
    Route::get('/mentors/{mentor}/edit', [AdminMentorController::class, 'edit'])->whereNumber('mentor')->name('mentors.edit');
    Route::put('/mentors/{mentor}', [AdminMentorController::class, 'update'])->whereNumber('mentor')->name('mentors.update');
    Route::post('/mentors/{mentor}/toggle-active', [AdminMentorController::class, 'toggleActive'])->whereNumber('mentor')->name('mentors.toggle');
    Route::post('/mentors/{mentor}/reset-password', [AdminMentorController::class, 'resetPassword'])->whereNumber('mentor')->name('mentors.reset');

    Route::get('/funnel', [AdminFunnelController::class, 'index'])->name('funnel');
    Route::post('/funnel/impressions', [AdminFunnelController::class, 'impressions'])->name('funnel.impressions');

    Route::get('/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials');
    Route::post('/testimonials', [AdminTestimonialController::class, 'store'])->name('testimonials.store');
    Route::post('/testimonials/{id}/toggle', [AdminTestimonialController::class, 'toggle'])->whereNumber('id')->name('testimonials.toggle');
    Route::delete('/testimonials/{id}', [AdminTestimonialController::class, 'destroy'])->whereNumber('id')->name('testimonials.destroy');

    Route::get('/students', [AdminStudentController::class, 'index'])->name('students');
    Route::post('/students/{student}/reset-password', [AdminStudentController::class, 'resetPassword'])->whereNumber('student')->name('students.reset');

    Route::get('/announcements', [AdminAnnouncementController::class, 'create'])->name('announcements');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');

    Route::get('/samples', [AdminSampleController::class, 'index'])->name('samples');
    Route::post('/samples/hide', [AdminSampleController::class, 'hide'])->name('samples.hide');
    Route::post('/samples/purge', [AdminSampleController::class, 'purge'])->name('samples.purge');
});
