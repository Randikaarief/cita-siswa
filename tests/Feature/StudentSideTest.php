<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\ReviewRequest;
use App\Models\Scholarship;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentSideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // tes tidak butuh hasil build npm
        $this->seed();
        Storage::fake('local');
    }

    private function student(string $email = 'siswa@contoh.test'): User
    {
        $user = new User(['name' => 'Siswa Contoh', 'email' => $email, 'password' => 'rahasia123']);
        $user->role = User::ROLE_USER;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /** Siswa mengunggah satu PDF dan mengembalikan modelnya. */
    private function upload(User $user, string $name = 'cv-saya.pdf'): Submission
    {
        $this->actingAs($user)->post('/submissions', ['file' => UploadedFile::fake()->create($name, 50, 'application/pdf')])->assertSessionHasNoErrors();

        return Submission::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    // ---------- Login wajib & peran ----------

    public function test_guests_are_redirected_to_login_on_every_page(): void
    {
        foreach (['/dashboard', '/riwayat', '/alert', '/settings/personal', '/find-scholarship',
            '/find-scholarship/1', '/lomba', '/lomba/1', '/alumni', '/alumni/1', '/go/scholarship/1'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_register_creates_student_and_redirects_to_home(): void
    {
        $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@contoh.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin', // harus diabaikan
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'budi@contoh.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_validation(): void
    {
        $this->student('ada@contoh.test');

        $this->post('/register', ['name' => '', 'email' => 'ada@contoh.test', 'password' => 'pendek', 'password_confirmation' => 'beda'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_student_login_redirects_to_home_and_wrong_password_fails(): void
    {
        $this->student();

        $this->post('/login', ['email' => 'siswa@contoh.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'siswa@contoh.test', 'password' => 'rahasia123'])
            ->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_mentor_and_admin_login_go_to_their_own_home(): void
    {
        $this->post('/login', ['email' => 'admin@berasiswa.test', 'password' => 'ganti-password-admin'])
            ->assertRedirect('/admin');
        $this->post('/logout');

        $this->post('/login', ['email' => 'mentor@berasiswa.test', 'password' => 'ganti-password-mentor'])
            ->assertRedirect('/mentor');
    }

    public function test_mentor_role_is_redirected_away_from_student_pages(): void
    {
        $mentor = User::where('email', 'mentor@berasiswa.test')->first();

        $this->actingAs($mentor)->get('/dashboard')->assertRedirect('/mentor');
        $this->actingAs($mentor)->get('/go/scholarship/1')->assertRedirect('/mentor');
        $this->assertAuthenticatedAs($mentor);
    }

    public function test_role_cannot_be_set_through_settings_forms(): void
    {
        $user = $this->student();

        $this->actingAs($user)->put('/settings/personal', ['name' => 'X', 'role' => 'admin'])->assertSessionHasNoErrors();
        $this->assertSame('user', $user->fresh()->role);
    }

    public function test_logout(): void
    {
        $this->actingAs($this->student())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ---------- Halaman tampil ----------

    public function test_all_student_pages_render(): void
    {
        $user = $this->student();
        $s = Scholarship::first();
        $c = Competition::first();
        $m = MentorProfile::first();

        foreach (['/', '/dashboard', '/riwayat', '/alert', '/settings/personal', '/settings/profile',
            '/settings/social', '/settings/account', '/find-scholarship', "/find-scholarship/{$s->id}",
            '/lomba', "/lomba/{$c->id}", '/alumni', "/alumni/{$m->id}"] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_unknown_ids_return_404(): void
    {
        $user = $this->actingAs($this->student());
        $user->get('/find-scholarship/99999')->assertNotFound();
        $user->get('/lomba/99999')->assertNotFound();
        $user->get('/alumni/99999')->assertNotFound();
        $user->get('/go/scholarship/99999')->assertNotFound();
        $user->get('/go/bukan-jenis/1')->assertNotFound();
    }

    public function test_dashboard_shows_user_name_and_pages_use_database_data(): void
    {
        $user = $this->student();

        $this->actingAs($user)->get('/dashboard')->assertSee('Halo, Siswa Contoh')->assertDontSee('Esther Howard');
        $this->actingAs($user)->get('/find-scholarship')->assertSee('Beasiswa Bank Indonesia (Contoh)');
        $this->actingAs($user)->get('/alumni')->assertSee('Nama Alumni 1 (contoh)');
        $this->actingAs($user)->get('/lomba')->assertSee('Contoh Lomba 1');
    }

    public function test_search_and_filters(): void
    {
        $user = $this->actingAs($this->student());

        $user->get('/find-scholarship?q=LPDP')->assertSee('LPDP Scholarship')->assertDontSee('Beasiswa Bank Indonesia');
        $user->get('/find-scholarship?q=zzzztidakada')->assertDontSee('LPDP Scholarship');
        $user->get('/alumni?q=Alumni 2')->assertSee('Nama Alumni 2 (contoh)')->assertDontSee('Nama Alumni 3 (contoh)');
        $user->get('/lomba?q=Contoh Lomba 4')->assertSee('Contoh Lomba 4')->assertDontSee('Contoh Lomba 5');
    }

    // ---------- Apply Now ----------

    public function test_apply_now_records_click_without_duplicates_and_redirects_to_official_site(): void
    {
        $user = $this->student();
        $s = Scholarship::first();

        $this->actingAs($user)->get("/go/scholarship/{$s->id}")->assertRedirect($s->website);
        $this->actingAs($user)->get("/go/scholarship/{$s->id}")->assertRedirect($s->website);

        $this->assertSame(1, Application::where('user_id', $user->id)->count());
        $this->assertSame('opened', Application::first()->status);

        $this->actingAs($user)->get('/riwayat')->assertSee($s->nama)->assertSee('Dibuka');
    }

    public function test_apply_without_official_link_does_not_redirect_away_or_record(): void
    {
        $user = $this->student();
        $c = Competition::first(); // lomba contoh tidak punya link resmi

        $this->actingAs($user)->get("/go/competition/{$c->id}")->assertSessionHas('status');
        $this->assertSame(0, Application::count());
    }

    public function test_dangerous_url_schemes_are_never_used_for_redirect(): void
    {
        $user = $this->student();
        $s = Scholarship::first();
        $s->update(['website' => 'javascript:alert(1)']);

        $this->actingAs($user)->get("/go/scholarship/{$s->id}")->assertSessionHas('status');
        $this->assertSame(0, Application::count());

        $s->update(['website' => 'www.contoh.go.id']);
        $this->actingAs($user)->get("/go/scholarship/{$s->id}")->assertRedirect('https://www.contoh.go.id');
    }

    // ---------- Minta masukan mentor ----------

    public function test_review_request_to_willing_mentor_shows_on_applied_with_mentor_name(): void
    {
        $user = $this->student();
        $s = Scholarship::first();
        $mentor = MentorProfile::first();
        $file = $this->upload($user);

        $this->actingAs($user)->post('/review-requests', [
            'target_type' => 'scholarship', 'target_id' => $s->id,
            'mentor_profile_id' => $mentor->id, 'note' => 'Tolong cek CV saya', 'submissions' => [$file->id],
        ])->assertRedirect('/riwayat');

        $this->assertDatabaseHas('review_requests', [
            'user_id' => $user->id, 'mentor_profile_id' => $mentor->id,
            'scholarship_id' => $s->id, 'status' => 'pending',
        ]);
        $this->assertSame(1, Application::where('user_id', $user->id)->count());

        $this->actingAs($user)->get('/riwayat')
            ->assertSee($s->nama)->assertSee($mentor->nama)->assertSee('Menunggu')
            ->assertSee('(1)', false);
    }

    public function test_duplicate_pending_request_is_not_created_twice(): void
    {
        $user = $this->student();
        $payload = ['target_type' => 'scholarship', 'target_id' => Scholarship::first()->id,
            'mentor_profile_id' => MentorProfile::first()->id, 'submissions' => [$this->upload($user)->id]];

        $this->actingAs($user)->post('/review-requests', $payload);
        $this->actingAs($user)->post('/review-requests', $payload);

        $this->assertSame(1, ReviewRequest::count());
    }

    public function test_cannot_request_unwilling_mentor(): void
    {
        $user = $this->student();
        $mentor = MentorProfile::first();
        $mentor->update(['is_willing_to_help' => false]);

        $this->actingAs($user)->post('/review-requests', [
            'target_type' => 'scholarship', 'target_id' => Scholarship::first()->id,
            'mentor_profile_id' => $mentor->id, 'submissions' => [$this->upload($user)->id],
        ])->assertSessionHasErrors('mentor_profile_id');

        $this->assertSame(0, ReviewRequest::count());
    }

    public function test_whatsapp_button_only_for_willing_mentor_with_number_and_never_on_public_pages(): void
    {
        $user = $this->student();
        $s = Scholarship::first();
        $mentor = MentorProfile::first();

        $this->actingAs($user)->post('/review-requests', [
            'target_type' => 'scholarship', 'target_id' => $s->id, 'mentor_profile_id' => $mentor->id,
            'submissions' => [$this->upload($user)->id],
        ]);

        // Mentor bersedia tapi belum mengisi nomor: tidak ada tombol.
        $this->actingAs($user)->get('/riwayat')->assertDontSee('Chat WhatsApp mentor')->assertDontSee('wa.me');

        // Mentor mengisi nomor sendiri: tombol muncul (0812... menjadi 62812...).
        $mentor->update(['whatsapp' => '0812-3456-7890']);
        $this->actingAs($user)->get('/riwayat')->assertSee('Chat WhatsApp mentor')->assertSee('https://wa.me/6281234567890', false);
        $this->actingAs($user)->get('/dashboard')->assertSee('https://wa.me/6281234567890', false);

        // Nomor tidak pernah tampil di halaman publik.
        foreach (['/alumni', "/alumni/{$mentor->id}", "/find-scholarship/{$s->id}"] as $url) {
            $this->actingAs($user)->get($url)->assertDontSee('6281234567890')->assertDontSee('0812-3456-7890')->assertDontSee('wa.me');
        }

        // Mentor mematikan "bersedia": tombol hilang.
        $mentor->update(['is_willing_to_help' => false]);
        $this->actingAs($user)->get('/riwayat')->assertDontSee('wa.me');

        // Nomor tidak ikut keluar saat model di-JSON-kan.
        $this->assertArrayNotHasKey('whatsapp', $mentor->fresh()->toArray());
    }

    public function test_students_only_see_their_own_rows(): void
    {
        $a = $this->student('a@contoh.test');
        $b = $this->student('b@contoh.test');
        $s = Scholarship::first();

        $this->actingAs($a)->get("/go/scholarship/{$s->id}");
        $this->actingAs($a)->post('/review-requests', [
            'target_type' => 'scholarship', 'target_id' => $s->id, 'mentor_profile_id' => MentorProfile::first()->id,
            'submissions' => [$this->upload($a, 'a.pdf')->id],
        ]);

        $this->actingAs($b)->get('/riwayat')->assertDontSee($s->nama)->assertSee('Belum ada riwayat.');
        $this->actingAs($b)->get('/dashboard')->assertDontSee($s->nama);
    }

    // ---------- Berkas (PDF) ----------

    public function test_upload_pdf_is_stored_privately_and_only_owner_can_open_it(): void
    {
        $owner = $this->student('pemilik@contoh.test');
        $other = $this->student('lain@contoh.test');

        $this->actingAs($owner)->post('/submissions', [
            'file' => UploadedFile::fake()->create('cv-saya.pdf', 500, 'application/pdf'),
            'type' => 'cv',
        ])->assertSessionHasNoErrors();

        $sub = Submission::first();
        $this->assertSame($owner->id, $sub->user_id);
        Storage::disk('local')->assertExists($sub->path);
        $this->assertStringContainsString('submissions/'.$owner->id, $sub->path);

        $this->actingAs($owner)->get('/settings/personal')->assertSee('cv-saya');
        $this->actingAs($owner)->get("/submissions/{$sub->id}")->assertOk();
        $this->actingAs($other)->get("/submissions/{$sub->id}")->assertNotFound();
        $this->actingAs($other)->delete("/submissions/{$sub->id}")->assertNotFound();
        $this->actingAs($other)->get('/settings/personal')->assertDontSee('cv-saya');

        $this->actingAs($owner)->delete("/submissions/{$sub->id}");
        $this->assertSame(0, Submission::count());
        Storage::disk('local')->assertMissing($sub->path);
    }

    public function test_upload_rejects_non_pdf_and_files_over_2mb(): void
    {
        $user = $this->actingAs($this->student());

        $user->post('/submissions', ['file' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg')])
            ->assertSessionHasErrors('file');
        $user->post('/submissions', ['file' => UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf')])
            ->assertSessionHasErrors('file');
        $user->post('/submissions', ['file' => UploadedFile::fake()->create('skrip.php', 10, 'text/x-php')])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Submission::count());
    }

    public function test_upload_limit_per_student(): void
    {
        $user = $this->student();

        foreach (range(1, 10) as $i) {
            $this->actingAs($user)->post('/submissions', ['file' => UploadedFile::fake()->create("b{$i}.pdf", 10, 'application/pdf')]);
        }
        $this->actingAs($user)->post('/submissions', ['file' => UploadedFile::fake()->create('lebih.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('file');

        $this->assertSame(10, Submission::count());
    }

    // ---------- Settings ----------

    public function test_settings_forms_save_to_database(): void
    {
        $user = $this->student();
        $this->actingAs($user);

        $this->put('/settings/personal', [
            'name' => 'Nama Baru', 'headline' => 'Siswa SMA', 'experience' => 'beginner',
            'education' => 'highschool', 'website' => 'contoh.com',
        ])->assertSessionHasNoErrors();

        $this->put('/settings/profile', [
            'nationality' => 'ID', 'birth_date' => '2008-08-17', 'gender' => 'female', 'education_level' => 'bachelor',
        ])->assertSessionHasNoErrors();

        $this->put('/settings/social', ['facebook' => 'fb.com/saya', 'twitter' => '', 'instagram' => 'ig.com/saya', 'youtube' => ''])
            ->assertSessionHasNoErrors();

        $this->put('/settings/account/contact', ['location' => 'Kediri', 'phone' => '08123', 'email' => 'baru@contoh.test'])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('Siswa SMA', $user->headline);
        $this->assertSame('beginner', $user->experience);
        $this->assertSame('highschool', $user->education);
        $this->assertSame('contoh.com', $user->website);
        $this->assertSame('ID', $user->nationality);
        $this->assertSame('2008-08-17', $user->birth_date->toDateString());
        $this->assertSame('female', $user->gender);
        $this->assertSame('bachelor', $user->education_level);
        $this->assertSame('fb.com/saya', $user->social_links['facebook']);
        $this->assertSame('Kediri', $user->location);
        $this->assertSame('08123', $user->phone);
        $this->assertSame('baru@contoh.test', $user->email);

        // Nilai tersimpan tampil lagi di form.
        $this->get('/settings/profile')->assertSee('2008-08-17');
        $this->get('/settings/social')->assertSee('fb.com/saya');
        $this->get('/settings/account')->assertSee('Kediri')->assertSee('baru@contoh.test');
    }

    public function test_settings_validation(): void
    {
        $user = $this->student();
        $other = $this->student('lain@contoh.test');
        $this->actingAs($user);

        $this->put('/settings/personal', ['name' => ''])->assertSessionHasErrors('name');
        $this->put('/settings/profile', ['birth_date' => '17/08/2008'])->assertSessionHasErrors('birth_date');
        $this->put('/settings/profile', ['birth_date' => '2999-08-17'])->assertSessionHasErrors('birth_date');
        $this->put('/settings/account/contact', ['email' => $other->email])->assertSessionHasErrors('email');
    }

    public function test_change_password_requires_current_password(): void
    {
        $user = $this->student();
        $this->actingAs($user);

        $this->put('/settings/account/password', [
            'current_password' => 'salah', 'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertSessionHasErrors('current_password');

        $this->put('/settings/account/password', [
            'current_password' => 'rahasia123', 'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('passwordbaru1', $user->fresh()->password));
    }

    public function test_photo_upload_is_private(): void
    {
        $user = $this->student();
        $other = $this->student('lain@contoh.test');

        $this->actingAs($user)->post('/settings/photo', ['photo' => UploadedFile::fake()->image('foto.jpg', 600, 600)])
            ->assertSessionHasNoErrors();

        $user->refresh();
        Storage::disk('local')->assertExists($user->photo_path);
        $this->actingAs($user)->get('/settings/photo')->assertOk();
        $this->actingAs($other)->get('/settings/photo')->assertNotFound();
        $this->actingAs($user)->get('/dashboard')->assertSee('/settings/photo?v=', false);

        $this->actingAs($user)->post('/settings/photo', ['photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('photo');
    }

    public function test_delete_account_removes_user_and_files(): void
    {
        $user = $this->student();
        $this->actingAs($user)->post('/submissions', ['file' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]);
        $this->actingAs($user)->get('/go/scholarship/'.Scholarship::first()->id);
        $path = Submission::first()->path;

        $this->delete('/settings/account', ['password' => 'salah'])->assertSessionHasErrors('password');
        $this->assertNotNull(User::find($user->id));

        $this->delete('/settings/account', ['password' => 'rahasia123'])->assertRedirect('/login');
        $this->assertNull(User::find($user->id));
        $this->assertSame(0, Submission::count());
        $this->assertSame(0, Application::count());
        Storage::disk('local')->assertMissing($path);
    }

    // ---------- Alert ----------

    public function test_alert_page_shows_notifications_and_marks_them_read(): void
    {
        $user = $this->student();
        $user->notify(new \App\Notifications\SimpleNotice('Mentor sudah membalas', 'Cek pengajuanmu', '/riwayat'));

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Notifikasi baru');
        $this->actingAs($user)->get('/alert')->assertSee('Mentor sudah membalas')->assertSee('(1 baru)');
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    // ---------- Data contoh ----------

    public function test_samples_purge_hide_and_delete(): void
    {
        $user = $this->student();
        Scholarship::create(['nama' => 'Beasiswa Asli', 'website' => 'https://contoh.go.id', 'is_sample' => false]);

        $this->artisan('samples:purge --hide')->assertSuccessful();
        $this->actingAs($user)->get('/find-scholarship')->assertSee('Beasiswa Asli')->assertDontSee('Bank Indonesia');
        $this->assertSame(9, Scholarship::withoutGlobalScopes()->where('is_sample', true)->count());

        $this->artisan('samples:purge')->assertSuccessful();
        $this->assertSame(0, Scholarship::withoutGlobalScopes()->where('is_sample', true)->count());
        $this->assertSame(1, Scholarship::withoutGlobalScopes()->count());
        $this->assertSame(0, MentorProfile::withoutGlobalScopes()->count());
        $this->assertSame(0, Competition::withoutGlobalScopes()->count());
    }

    public function test_seed_contains_expected_sample_data(): void
    {
        $this->assertSame(9, Scholarship::count());
        $this->assertSame(9, Competition::count());
        $this->assertSame(6, MentorProfile::count());
        $this->assertSame(0, MentorProfile::whereNotNull('whatsapp')->count());
        $this->assertSame(0, Scholarship::whereNotNull('phone')->orWhereNotNull('email')->count());
        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(1, User::where('role', 'mentor')->count());
    }
}
