<?php

namespace Tests\Feature;

use App\Models\CampaignVisit;
use App\Models\Scholarship;
use App\Models\User;
use App\Notifications\ResetPasswordId;
use App\Notifications\VerifyEmailId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function account(string $email, string $role = 'user', bool $verified = false): User
    {
        $user = new User(['name' => 'Akun '.$email, 'email' => $email, 'password' => 'rahasia123']);
        $user->role = $role;
        $user->email_verified_at = $verified ? now() : null;
        $user->save();

        return $user;
    }

    private function link(User $user, ?string $hash = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    // ---------- Daftar & kirim email ----------

    public function test_register_sends_verification_email_and_leaves_student_unverified(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@contoh.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'budi@contoh.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailId::class);
        $this->get(route('verification.notice'))->assertOk()->assertSee('Cek emailmu')->assertSee('budi@contoh.test');
    }

    public function test_register_still_works_when_mail_cannot_be_sent(): void
    {
        $this->app['config']->set('mail.default', 'smtp');
        $this->app['config']->set('mail.mailers.smtp.host', '127.0.0.1');
        $this->app['config']->set('mail.mailers.smtp.port', 1);

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@contoh.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('verification.notice'));

        $this->assertNotNull(User::where('email', 'budi@contoh.test')->first());
        $this->assertAuthenticated();
    }

    public function test_verification_email_is_in_indonesian_and_has_signed_link(): void
    {
        $user = $this->account('budi@contoh.test');
        $mail = (new VerifyEmailId)->toMail($user);

        $this->assertSame('Verifikasi email CITA SISWA', $mail->subject);
        $this->assertSame('Halo, Akun budi@contoh.test!', $mail->greeting);
        $this->assertSame('Verifikasi email', $mail->actionText);
        $this->assertStringContainsString('/email/verify/'.$user->id.'/', $mail->actionUrl);
        $this->assertStringContainsString('signature=', $mail->actionUrl);
    }

    // ---------- Pembatasan fitur ----------

    public function test_unverified_student_can_browse_but_not_upload_or_request_feedback(): void
    {
        $student = $this->account('s@contoh.test');

        foreach (['/', '/find-scholarship', '/lomba', '/alumni', '/dashboard', '/riwayat', '/settings/personal'] as $url) {
            $this->actingAs($student)->get($url)->assertOk();
        }

        $this->actingAs($student)
            ->post('/submissions', ['file' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf')])
            ->assertRedirect(route('verification.notice'));
        $this->assertSame(0, $student->submissions()->count());

        $this->actingAs($student)->post('/review-requests', [])->assertRedirect(route('verification.notice'));
    }

    public function test_verified_student_can_upload(): void
    {
        $student = $this->account('s@contoh.test', 'user', true);

        $this->actingAs($student)
            ->post('/submissions', ['file' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf')])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $student->submissions()->count());
    }

    public function test_mentor_and_admin_are_never_blocked_by_verification(): void
    {
        $this->assertTrue($this->account('m@contoh.test', 'mentor')->hasVerifiedEmail());
        $this->assertTrue($this->account('a@contoh.test', 'admin')->hasVerifiedEmail());
        $this->assertFalse($this->account('s@contoh.test')->hasVerifiedEmail());
    }

    public function test_banner_shows_only_for_unverified_students(): void
    {
        $this->actingAs($this->account('s@contoh.test'))->get('/dashboard')
            ->assertSee('Email kamu belum diverifikasi')->assertSee('Kirim ulang email');

        $this->actingAs($this->account('v@contoh.test', 'user', true))->get('/dashboard')
            ->assertDontSee('Email kamu belum diverifikasi');
    }

    // ---------- Link verifikasi & kirim ulang ----------

    public function test_signed_link_verifies_the_student(): void
    {
        $student = $this->account('s@contoh.test');

        $this->actingAs($student)->get($this->link($student))->assertRedirect('/');
        $this->assertNotNull($student->fresh()->email_verified_at);
    }

    public function test_link_with_wrong_hash_or_for_another_user_is_rejected(): void
    {
        $student = $this->account('s@contoh.test');
        $other = $this->account('o@contoh.test');

        $this->actingAs($student)->get($this->link($student, sha1('lain@contoh.test')))->assertForbidden();
        $this->actingAs($student)->get($this->link($other))->assertForbidden();
        $this->actingAs($student)->get('/email/verify/'.$student->id.'/'.sha1($student->email))->assertForbidden(); // tanpa tanda tangan
        $this->assertNull($student->fresh()->email_verified_at);
        $this->assertNull($other->fresh()->email_verified_at);
    }

    public function test_guest_opening_the_link_logs_in_first_then_returns_to_it(): void
    {
        $student = $this->account('s@contoh.test');
        $link = $this->link($student);

        $this->get($link)->assertRedirect('/login');

        $this->post('/login', ['email' => 's@contoh.test', 'password' => 'rahasia123'])->assertRedirect($link);
    }

    public function test_resend_sends_a_new_email_and_verified_student_is_sent_home(): void
    {
        Notification::fake();
        $student = $this->account('s@contoh.test');

        $this->actingAs($student)->post(route('verification.send'))->assertSessionHas('status');
        Notification::assertSentTo($student, VerifyEmailId::class);

        $verified = $this->account('v@contoh.test', 'user', true);
        $this->actingAs($verified)->post(route('verification.send'))->assertRedirect('/');
        Notification::assertNotSentTo($verified, VerifyEmailId::class);
    }

    public function test_notice_page_redirects_verified_student_home_and_is_student_only(): void
    {
        $this->actingAs($this->account('v@contoh.test', 'user', true))->get(route('verification.notice'))->assertRedirect('/');
        $this->actingAs($this->account('a@contoh.test', 'admin'))->get(route('verification.notice'))->assertRedirect('/admin');
        auth()->logout();
        $this->get(route('verification.notice'))->assertRedirect('/login');
    }

    public function test_changing_email_requires_verification_again(): void
    {
        Notification::fake();
        $student = $this->account('s@contoh.test', 'user', true);

        $this->actingAs($student)->put('/settings/account/contact', ['email' => 'baru@contoh.test'])->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertSame('baru@contoh.test', $student->email);
        $this->assertNull($student->email_verified_at);
        Notification::assertSentTo($student, VerifyEmailId::class);

        // ganti nomor telepon saja tidak mengubah status verifikasi
        $student->email_verified_at = now();
        $student->save();
        $this->actingAs($student)->put('/settings/account/contact', ['email' => 'baru@contoh.test', 'phone' => '0812'])->assertSessionHasNoErrors();
        $this->assertNotNull($student->fresh()->email_verified_at);
    }

    // ---------- Lupa password ----------

    public function test_login_page_links_to_forgot_password(): void
    {
        $this->get('/login')->assertSee('Lupa password?')->assertSee(route('password.request'), false);
        $this->get('/login?sebagai=admin')->assertSee(route('password.request'), false);
        $this->get('/forgot-password')->assertOk()->assertSee('Lupa password?');
    }

    public function test_forgot_password_sends_link_and_gives_same_answer_for_unknown_email(): void
    {
        Notification::fake();
        $student = $this->account('s@contoh.test', 'user', true);

        $known = $this->post('/forgot-password', ['email' => 's@contoh.test']);
        $unknown = $this->post('/forgot-password', ['email' => 'tidak-ada@contoh.test']);

        Notification::assertSentTo($student, ResetPasswordId::class);
        Notification::assertCount(1);
        $this->assertSame(session('status'), $known->getSession()->get('status'));
        $unknown->assertSessionHas('status', $known->getSession()->get('status'));
    }

    public function test_reset_password_email_is_in_indonesian(): void
    {
        $user = $this->account('s@contoh.test');
        $mail = (new ResetPasswordId('tokenabc'))->toMail($user);

        $this->assertSame('Atur ulang password CITA SISWA', $mail->subject);
        $this->assertSame('Atur ulang password', $mail->actionText);
        $this->assertStringContainsString('/reset-password/tokenabc', $mail->actionUrl);
    }

    public function test_reset_password_changes_password_and_verifies_student(): void
    {
        $student = $this->account('s@contoh.test');
        $token = Password::createToken($student);

        $this->get('/reset-password/'.$token.'?email=s@contoh.test')->assertOk()->assertSee('Buat password baru');

        $this->post('/reset-password', [
            'token' => $token, 'email' => 's@contoh.test',
            'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $student->refresh();
        $this->assertTrue(password_verify('passwordbaru1', $student->password));
        $this->assertNotNull($student->email_verified_at);

        $this->post('/login', ['email' => 's@contoh.test', 'password' => 'passwordbaru1'])->assertRedirect('/');
    }

    public function test_reset_clears_forced_password_change_for_admin(): void
    {
        $admin = $this->account('a@contoh.test', 'admin');
        $admin->must_change_password = true;
        $admin->save();

        $this->post('/reset-password', [
            'token' => Password::createToken($admin), 'email' => 'a@contoh.test',
            'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertRedirect('/login');

        $this->assertFalse($admin->fresh()->must_change_password);
    }

    public function test_reset_with_bad_token_or_weak_password_fails(): void
    {
        $student = $this->account('s@contoh.test', 'user', true);
        $old = $student->password;

        $this->post('/reset-password', [
            'token' => 'salah', 'email' => 's@contoh.test',
            'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertSessionHasErrors('email');

        $this->post('/reset-password', [
            'token' => Password::createToken($student), 'email' => 's@contoh.test',
            'password' => 'pendek', 'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->assertSame($old, $student->fresh()->password);
    }

    // ---------- Corong ----------

    public function test_funnel_counts_only_verified_registrations(): void
    {
        $admin = $this->account('a@contoh.test', 'admin');
        CampaignVisit::create(['visitor_id' => 'v1', 'utm_source' => 'instagram', 'utm_medium' => 'reels', 'utm_campaign' => 'eps1', 'landing_path' => '/']);

        foreach (['u1', 'u2', 'u3'] as $i => $name) {
            $user = $this->account($name.'@contoh.test', 'user', $i < 2); // 2 terverifikasi, 1 belum
            $user->utm_source = 'instagram';
            $user->utm_medium = 'reels';
            $user->utm_campaign = 'eps1';
            $user->save();
        }

        $html = $this->actingAs($admin)->get('/admin/funnel')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<td>2<\/td>/', $html);   // pendaftaran terverifikasi
        $this->assertStringContainsString('email yang sudah terverifikasi', $html);
    }

    public function test_mail_footer_text_is_in_indonesian(): void
    {
        $html = (string) (new VerifyEmailId)->toMail($this->account('s@contoh.test'))->render();

        $this->assertStringContainsString('Kalau tombol', $html);
        $this->assertStringNotContainsString("having trouble clicking", $html);
        $this->assertStringContainsString('Salam hangat, CITA SISWA', $html);
    }
}
