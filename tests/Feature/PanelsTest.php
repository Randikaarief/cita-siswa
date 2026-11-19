<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\ReviewRequest;
use App\Models\Scholarship;
use App\Models\Submission;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        Storage::fake('local');
    }

    private function user(string $email, string $role = 'user', array $extra = []): User
    {
        $user = new User(['name' => ucfirst($role).' '.$email, 'email' => $email, 'password' => 'rahasia123']);
        $user->role = $role;
        $user->email_verified_at = now();
        foreach ($extra as $k => $v) {
            $user->{$k} = $v;
        }
        $user->save();

        return $user;
    }

    /** Mentor siap pakai (password sudah diganti) lengkap dengan profil. */
    private function mentor(string $email = 'mentor1@contoh.test', bool $willing = true, ?string $whatsapp = null): array
    {
        $user = $this->user($email, 'mentor');
        $profile = MentorProfile::withoutGlobalScopes()->create([
            'user_id' => $user->id, 'nama' => 'Mentor '.$email,
            'is_willing_to_help' => $willing, 'whatsapp' => $whatsapp,
        ]);

        return [$user, $profile];
    }

    private function admin(): User
    {
        return $this->user('admin1@contoh.test', 'admin');
    }

    private function requestFrom(User $student, MentorProfile $profile, ?Scholarship $s = null, string $note = 'Tolong cek CV', ?Submission $file = null): ReviewRequest
    {
        $s ??= Scholarship::first();
        $file ??= Submission::where('user_id', $student->id)->first() ?? $this->makeFile($student);

        $this->actingAs($student)->post('/review-requests', [
            'target_type' => 'scholarship', 'target_id' => $s->id,
            'mentor_profile_id' => $profile->id, 'note' => $note, 'submissions' => [$file->id],
        ])->assertSessionHasNoErrors();

        return ReviewRequest::where('user_id', $student->id)->where('mentor_profile_id', $profile->id)->latest('id')->firstOrFail();
    }

    private function makeFile(User $student, string $name = 'cv-saya.pdf'): Submission
    {
        $this->actingAs($student)->post('/submissions', ['file' => UploadedFile::fake()->create($name, 50, 'application/pdf')])->assertSessionHasNoErrors();

        return Submission::where('user_id', $student->id)->latest('id')->firstOrFail();
    }

    // ---------- Akses per peran ----------

    public function test_guests_are_sent_to_login_for_mentor_and_admin_pages(): void
    {
        foreach (['/mentor', '/mentor/requests', '/mentor/profile', '/admin', '/admin/scholarships', '/admin/mentors', '/panel/password'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_each_role_is_kept_to_its_own_area(): void
    {
        $student = $this->user('s@contoh.test');
        [$mentor] = $this->mentor();
        $admin = $this->admin();

        foreach (['/mentor', '/admin', '/admin/students', '/mentor/requests'] as $url) {
            $this->actingAs($student)->get($url)->assertRedirect('/');
        }
        foreach (['/admin', '/admin/mentors', '/admin/samples', '/dashboard', '/riwayat'] as $url) {
            $this->actingAs($mentor)->get($url)->assertRedirect('/mentor');
        }
        foreach (['/mentor', '/mentor/profile', '/dashboard', '/alumni'] as $url) {
            $this->actingAs($admin)->get($url)->assertRedirect('/admin');
        }

        // Aksi (bukan cuma halaman) juga dibatasi.
        $this->actingAs($student)->post('/admin/mentors', ['name' => 'X', 'email' => 'x@x.test', 'password' => 'abcdefgh'])->assertRedirect('/');
        $this->assertSame(0, User::where('email', 'x@x.test')->count());
        $this->actingAs($student)->post('/admin/samples/purge', ['confirm' => 'HAPUS'])->assertRedirect('/');
        $this->assertSame(9, Scholarship::withoutGlobalScopes()->where('is_sample', true)->count());
    }

    public function test_panel_pages_render(): void
    {
        [$mentor] = $this->mentor();
        $admin = $this->admin();
        $m = MentorProfile::first();
        $sId = Scholarship::first()->id;
        $cId = Competition::first()->id;

        foreach (['/mentor', '/mentor/requests', '/mentor/profile', '/mentor/testimonials', '/panel/password'] as $url) {
            $this->actingAs($mentor)->get($url)->assertOk();
        }
        foreach (['/admin', '/admin/scholarships', '/admin/scholarships/create', "/admin/scholarships/{$sId}/edit",
            '/admin/competitions', '/admin/competitions/create', "/admin/competitions/{$cId}/edit",
            '/admin/mentors', '/admin/mentors/create', "/admin/mentors/{$m->id}/edit",
            '/admin/testimonials', '/admin/students', '/admin/announcements', '/admin/samples', '/panel/password'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    // ---------- Password sementara ----------

    public function test_seeded_accounts_must_change_password_before_anything_else(): void
    {
        $this->post('/login', ['email' => 'mentor@berasiswa.test', 'password' => 'ganti-password-mentor'])->assertRedirect('/mentor');

        $this->get('/mentor')->assertRedirect(route('panel.password'));
        $this->get('/mentor/requests')->assertRedirect(route('panel.password'));
        $this->get('/panel/password')->assertOk();

        // password sementara dipakai lagi = ditolak
        $this->put('/panel/password', [
            'current_password' => 'ganti-password-mentor', 'password' => 'ganti-password-mentor', 'password_confirmation' => 'ganti-password-mentor',
        ])->assertSessionHasErrors('password');

        $this->put('/panel/password', [
            'current_password' => 'ganti-password-mentor', 'password' => 'passwordbaru99', 'password_confirmation' => 'passwordbaru99',
        ])->assertRedirect('/mentor');

        $this->get('/mentor')->assertOk();
        $user = User::where('email', 'mentor@berasiswa.test')->first();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('passwordbaru99', $user->password));
    }

    public function test_admin_seeded_account_is_also_forced(): void
    {
        $this->post('/login', ['email' => 'admin@berasiswa.test', 'password' => 'ganti-password-admin'])->assertRedirect('/admin');
        $this->get('/admin/scholarships')->assertRedirect(route('panel.password'));
    }

    public function test_deactivated_account_cannot_login_and_active_session_is_dropped(): void
    {
        [$mentor] = $this->mentor('off@contoh.test');
        $mentor->is_active = false;
        $mentor->save();

        $this->post('/login', ['email' => 'off@contoh.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['email' => 'Akun ini dinonaktifkan.']);
        $this->assertGuest();

        // sesi yang sedang berjalan ikut dikeluarkan
        $this->actingAs($mentor)->get('/mentor')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ---------- Mentor ----------

    public function test_mentor_sees_only_own_requests_and_only_student_names(): void
    {
        [$mentorA, $profileA] = $this->mentor('a@contoh.test');
        [, $profileB] = $this->mentor('b@contoh.test');
        $siswa1 = $this->user('siswa1@contoh.test', 'user', ['name' => 'Siti Pengaju', 'phone' => '08111222333', 'location' => 'Desa Rahasia']);
        $siswa2 = $this->user('siswa2@contoh.test', 'user', ['name' => 'Budi Lain']);

        $this->requestFrom($siswa1, $profileA, null, 'Mohon cek esai LPDP saya');
        $this->requestFrom($siswa2, $profileB, null, 'Catatan untuk mentor B');

        $this->actingAs($mentorA)->get('/mentor/requests')
            ->assertSee('Siti Pengaju')->assertDontSee('Budi Lain')
            // data pribadi siswa tidak bocor
            ->assertDontSee('siswa1@contoh.test')->assertDontSee('08111222333')->assertDontSee('Desa Rahasia');

        $this->actingAs($mentorA)->get('/mentor')->assertSee('Siti Pengaju');

        // halaman detail: catatan terlihat, tapi tetap tanpa data pribadi
        $req = ReviewRequest::where('mentor_profile_id', $profileA->id)->firstOrFail();
        $this->actingAs($mentorA)->get("/mentor/requests/{$req->id}")
            ->assertOk()->assertSee('Siti Pengaju')->assertSee('Mohon cek esai LPDP saya')
            ->assertDontSee('siswa1@contoh.test')->assertDontSee('08111222333')->assertDontSee('Desa Rahasia');
    }

    public function test_mentor_reply_marks_replied_notifies_student_and_updates_applied(): void
    {
        [$mentor, $profile] = $this->mentor('a@contoh.test');
        $student = $this->user('siswa@contoh.test');
        $req = $this->requestFrom($student, $profile);

        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/reply", ['feedback' => 'Masukan dari mentor untuk esaimu.'])->assertSessionHas('status');

        $req->refresh();
        $this->assertSame('replied', $req->status);
        $this->assertNotNull($req->replied_at);

        $this->assertSame(1, $student->unreadNotifications()->count());
        $this->assertStringContainsString('sudah membalas', $student->notifications()->first()->data['title']);

        $this->actingAs($student)->get('/riwayat')->assertSee('Sudah dibalas')->assertSee('Masukan dari mentor untuk esaimu.');
        $this->actingAs($student)->get('/alert')->assertSee('sudah membalas');

        // balasan kedua tidak membuat notifikasi ganda
        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/reply", ['feedback' => 'Masukan kedua yang tidak boleh masuk.']);
        $this->assertSame(1, $student->notifications()->count());
    }

    public function test_mentor_cannot_reply_to_someone_elses_request(): void
    {
        [, $profileA] = $this->mentor('a@contoh.test');
        [$mentorB] = $this->mentor('b@contoh.test');
        $req = $this->requestFrom($this->user('siswa@contoh.test'), $profileA);

        $this->actingAs($mentorB)->post("/mentor/requests/{$req->id}/reply", ['feedback' => 'Masukan dari mentor untuk esaimu.'])->assertNotFound();
        $this->assertSame('pending', $req->fresh()->status);
    }

    public function test_requests_filter_by_status(): void
    {
        [$mentor, $profile] = $this->mentor('a@contoh.test');
        $s1 = $this->user('s1@contoh.test', 'user', ['name' => 'Siswa Menunggu']);
        $s2 = $this->user('s2@contoh.test', 'user', ['name' => 'Siswa Selesai']);
        $this->requestFrom($s1, $profile);
        $done = $this->requestFrom($s2, $profile);
        $this->actingAs($mentor)->post("/mentor/requests/{$done->id}/reply", ['feedback' => 'Masukan dari mentor untuk esaimu.']);

        $this->actingAs($mentor)->get('/mentor/requests?status=pending')->assertSee('Siswa Menunggu')->assertDontSee('Siswa Selesai');
        $this->actingAs($mentor)->get('/mentor/requests?status=replied')->assertSee('Siswa Selesai')->assertDontSee('Siswa Menunggu');
    }

    public function test_mentor_profile_update_drives_student_whatsapp_button(): void
    {
        [$mentor, $profile] = $this->mentor('a@contoh.test', true, null);
        $student = $this->user('siswa@contoh.test');
        $this->requestFrom($student, $profile);

        $this->actingAs($student)->get('/riwayat')->assertDontSee('wa.me');

        $this->actingAs($mentor)->put('/mentor/profile', [
            'nama' => 'Kak Mentor', 'whatsapp' => '0812 3456 7890', 'is_willing_to_help' => '1',
            'lokasi' => 'Kediri', 'youtube' => 'https://youtu.be/dQw4w9WgXcQ?t=5',
        ])->assertSessionHasNoErrors();

        $profile->refresh();
        $this->assertSame('Kak Mentor', $profile->nama);
        $this->assertSame('dQw4w9WgXcQ', $profile->youtube);
        $this->assertTrue($profile->is_willing_to_help);

        $this->actingAs($student)->get('/riwayat')->assertSee('https://wa.me/6281234567890', false);
        // tidak tampil di halaman publik
        $this->actingAs($student)->get("/alumni/{$profile->id}")->assertDontSee('6281234567890')->assertDontSee('wa.me');

        // saklar dimatikan: tombol hilang dan tidak bisa dipilih lagi
        $this->actingAs($mentor)->put('/mentor/profile', ['nama' => 'Kak Mentor', 'whatsapp' => '0812 3456 7890'])->assertSessionHasNoErrors();
        $this->assertFalse($profile->fresh()->is_willing_to_help);
        $this->actingAs($student)->get('/riwayat')->assertDontSee('wa.me');
        $this->actingAs($student)->get('/find-scholarship/'.Scholarship::first()->id)->assertOk()->assertDontSee('Kak Mentor');
    }

    public function test_mentor_profile_validation(): void
    {
        [$mentor] = $this->mentor('a@contoh.test');

        $this->actingAs($mentor)->put('/mentor/profile', ['nama' => ''])->assertSessionHasErrors('nama');
        $this->actingAs($mentor)->put('/mentor/profile', ['nama' => 'X', 'whatsapp' => 'abc'])->assertSessionHasErrors('whatsapp');
        $this->actingAs($mentor)->put('/mentor/profile', ['nama' => 'X', 'whatsapp' => '12'])->assertSessionHasErrors('whatsapp');
    }

    public function test_mentor_without_profile_gets_one_created_on_first_save(): void
    {
        $user = $this->user('baru@contoh.test', 'mentor');

        $this->actingAs($user)->get('/mentor/profile')->assertOk();
        $this->assertSame(1, MentorProfile::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_mentor_testimonials_add_and_delete_only_own(): void
    {
        [$mentorA, $profileA] = $this->mentor('a@contoh.test');
        [$mentorB, $profileB] = $this->mentor('b@contoh.test');

        $this->actingAs($mentorA)->post('/mentor/testimonials', ['quote' => 'Sangat membantu', 'youtube' => ''])->assertSessionHasNoErrors();
        $this->actingAs($mentorB)->post('/mentor/testimonials', ['youtube' => 'https://youtu.be/abcdefghijk'])->assertSessionHasNoErrors();
        $this->actingAs($mentorA)->post('/mentor/testimonials', ['quote' => '', 'youtube' => 'bukan link'])->assertSessionHasErrors('quote');

        $a = Testimonial::where('mentor_profile_id', $profileA->id)->first();
        $b = Testimonial::where('mentor_profile_id', $profileB->id)->first();
        $this->assertSame('abcdefghijk', $b->youtube);

        $this->actingAs($mentorA)->get('/mentor/testimonials')->assertSee('Sangat membantu');
        $this->actingAs($mentorA)->delete("/mentor/testimonials/{$b->id}")->assertNotFound();
        $this->actingAs($mentorA)->delete("/mentor/testimonials/{$a->id}");
        $this->assertNull(Testimonial::find($a->id));
        $this->assertNotNull(Testimonial::find($b->id));
    }

    // ---------- Admin: beasiswa & lomba ----------

    public function test_admin_scholarship_crud_and_visibility(): void
    {
        $admin = $this->admin();
        $student = $this->user('s@contoh.test');

        $this->actingAs($admin)->post('/admin/scholarships', [
            'nama' => 'Beasiswa Uji Asli', 'website' => 'www.contoh.go.id', 'kategori' => 'Pemerintah',
            'tipe' => 'Dalam negeri', 'requirements' => 'Syarat A, Syarat B', 'is_visible' => '1',
        ])->assertRedirect('/admin/scholarships');

        $s = Scholarship::withoutGlobalScopes()->where('nama', 'Beasiswa Uji Asli')->firstOrFail();
        $this->assertSame('https://www.contoh.go.id', $s->website);   // dinormalkan
        $this->assertFalse($s->is_sample);                              // bukan data contoh

        $this->actingAs($student)->get('/find-scholarship')->assertSee('Beasiswa Uji Asli');
        $this->actingAs($student)->get("/go/scholarship/{$s->id}")->assertRedirect('https://www.contoh.go.id');

        // sembunyikan: hilang dari siswa
        $this->actingAs($admin)->put("/admin/scholarships/{$s->id}", ['nama' => 'Beasiswa Uji Asli', 'website' => 'https://www.contoh.go.id'])->assertSessionHasNoErrors();
        $this->assertFalse($s->fresh()->is_visible);
        $this->actingAs($student)->get('/find-scholarship')->assertDontSee('Beasiswa Uji Asli');
        $this->actingAs($student)->get("/find-scholarship/{$s->id}")->assertNotFound();
        $this->actingAs($admin)->get('/admin/scholarships?q=Uji')->assertSee('Beasiswa Uji Asli')->assertSee('Disembunyikan');

        $this->actingAs($admin)->delete("/admin/scholarships/{$s->id}");
        $this->assertNull(Scholarship::withoutGlobalScopes()->find($s->id));
    }

    public function test_admin_validation_for_links_and_names(): void
    {
        $admin = $this->actingAs($this->admin());

        $admin->post('/admin/scholarships', ['nama' => ''])->assertSessionHasErrors('nama');
        $admin->post('/admin/scholarships', ['nama' => 'X', 'website' => 'javascript:alert(1)'])->assertSessionHasErrors('website');
        $admin->post('/admin/scholarships', ['nama' => 'X', 'email' => 'bukan-email'])->assertSessionHasErrors('email');
        $admin->post('/admin/competitions', ['nama' => 'Y', 'website' => 'ftp://contoh.com'])->assertSessionHasErrors('website');

        $this->assertSame(0, Scholarship::withoutGlobalScopes()->where('nama', 'X')->count());
    }

    public function test_admin_competition_create_and_edit(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/competitions', [
            'nama' => 'Lomba Uji', 'tipe' => 'Akademik', 'website' => 'https://lomba.contoh.id',
            'youtube' => 'https://youtu.be/abcdefghijk', 'is_visible' => '1',
        ])->assertRedirect('/admin/competitions');

        $c = Competition::withoutGlobalScopes()->where('nama', 'Lomba Uji')->firstOrFail();
        $this->assertSame('abcdefghijk', $c->youtube);

        $this->actingAs($this->user('s@contoh.test'))->get('/lomba')->assertSee('Lomba Uji');
        $this->actingAs($admin)->put("/admin/competitions/{$c->id}", ['nama' => 'Lomba Uji 2', 'is_visible' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('Lomba Uji 2', $c->fresh()->nama);
    }

    // ---------- Admin: mentor ----------

    public function test_admin_creates_mentor_account_who_must_change_password_then_can_work(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/mentors', [
            'name' => 'Kak Baru', 'email' => 'kakbaru@contoh.test', 'password' => 'sementara123',
            'beasiswa' => 'LPDP 2022', 'lokasi' => 'Kediri', 'kategori' => 'Pemerintah',
        ])->assertRedirect('/admin/mentors');

        $user = User::where('email', 'kakbaru@contoh.test')->firstOrFail();
        $this->assertSame('mentor', $user->role);
        $this->assertTrue($user->must_change_password);
        $profile = MentorProfile::withoutGlobalScopes()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Kak Baru', $profile->nama);
        $this->assertFalse($profile->is_willing_to_help);   // mentor sendiri yang menyalakan

        auth()->logout();
        $this->post('/login', ['email' => 'kakbaru@contoh.test', 'password' => 'sementara123'])->assertRedirect('/mentor');
        $this->get('/mentor/profile')->assertRedirect(route('panel.password'));
        $this->put('/panel/password', ['current_password' => 'sementara123', 'password' => 'passwordku88', 'password_confirmation' => 'passwordku88']);
        $this->get('/mentor/profile')->assertOk();
    }

    public function test_admin_cannot_create_mentor_with_duplicate_email_or_weak_password(): void
    {
        $this->user('ada@contoh.test');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/mentors', ['name' => 'X', 'email' => 'ada@contoh.test', 'password' => 'sementara123'])
            ->assertSessionHasErrors('email');
        $this->actingAs($admin)->post('/admin/mentors', ['name' => 'X', 'email' => 'baru@contoh.test', 'password' => 'pendek'])
            ->assertSessionHasErrors('password');
    }

    public function test_admin_deactivate_mentor_blocks_login_hides_profile_and_reactivates(): void
    {
        $admin = $this->admin();
        [$mentor, $profile] = $this->mentor('a@contoh.test');
        $student = $this->user('s@contoh.test');

        $this->actingAs($student)->get('/alumni')->assertSee($profile->nama);

        $this->actingAs($admin)->post("/admin/mentors/{$profile->id}/toggle-active");
        $this->assertFalse($mentor->fresh()->is_active);
        $profile->refresh();
        $this->assertFalse($profile->is_visible);
        $this->assertFalse($profile->is_willing_to_help);

        auth()->logout();
        $this->post('/login', ['email' => 'a@contoh.test', 'password' => 'rahasia123'])->assertSessionHasErrors('email');
        $this->actingAs($student)->get('/alumni')->assertDontSee($profile->nama);

        $this->actingAs($admin)->post("/admin/mentors/{$profile->id}/toggle-active");
        $this->assertTrue($mentor->fresh()->is_active);
        $this->assertTrue($profile->fresh()->is_visible);
    }

    public function test_admin_resets_mentor_password_and_forces_change(): void
    {
        $admin = $this->admin();
        [$mentor, $profile] = $this->mentor('a@contoh.test');

        $this->actingAs($admin)->post("/admin/mentors/{$profile->id}/reset-password", ['password' => 'sementara-baru'])->assertSessionHasNoErrors();

        $mentor->refresh();
        $this->assertTrue(Hash::check('sementara-baru', $mentor->password));
        $this->assertTrue($mentor->must_change_password);

        $this->actingAs($admin)->post("/admin/mentors/{$profile->id}/reset-password", ['password' => 'pendek'])->assertSessionHasErrors('password');
    }

    public function test_admin_can_edit_sample_mentor_without_account(): void
    {
        $admin = $this->admin();
        $m = MentorProfile::first();

        $this->actingAs($admin)->put("/admin/mentors/{$m->id}", [
            'nama' => 'Nama Asli', 'kategori' => 'Pemerintah', 'is_visible' => '1', 'is_willing_to_help' => '1',
        ])->assertSessionHasNoErrors();

        $m->refresh();
        $this->assertSame('Nama Asli', $m->nama);
        $this->assertFalse($m->is_sample);   // centang contoh dihilangkan = data asli

        $this->actingAs($admin)->put("/admin/mentors/{$m->id}", ['nama' => 'X', 'kategori' => 'Ngawur'])->assertSessionHasErrors('kategori');
    }

    // ---------- Admin: testimoni ----------

    public function test_admin_testimonials_add_toggle_delete(): void
    {
        $admin = $this->admin();
        $m = MentorProfile::first();

        $this->actingAs($admin)->post('/admin/testimonials', ['mentor_profile_id' => $m->id, 'quote' => 'Bagus'])->assertSessionHasNoErrors();
        $t = Testimonial::firstOrFail();

        $this->actingAs($admin)->post("/admin/testimonials/{$t->id}/toggle");
        $this->assertFalse(Testimonial::withoutGlobalScopes()->find($t->id)->is_visible);
        $this->actingAs($admin)->get('/admin/testimonials')->assertSee('Disembunyikan');

        $this->actingAs($admin)->delete("/admin/testimonials/{$t->id}");
        $this->assertSame(0, Testimonial::withoutGlobalScopes()->count());

        $this->actingAs($admin)->post('/admin/testimonials', ['mentor_profile_id' => 99999, 'quote' => 'x'])->assertSessionHasErrors('mentor_profile_id');
    }

    // ---------- Admin: siswa ----------

    public function test_admin_students_list_shows_only_name_and_email(): void
    {
        $admin = $this->admin();
        $student = $this->user('siswa@contoh.test', 'user', [
            'name' => 'Siswa Terlihat', 'phone' => '08123456789', 'location' => 'Desa Pribadi', 'headline' => 'Judul Pribadi',
        ]);
        $this->actingAs($student)->post('/submissions', ['file' => UploadedFile::fake()->create('cv-rahasia.pdf', 10, 'application/pdf')]);
        $this->actingAs($student)->get('/go/scholarship/'.Scholarship::first()->id);
        $this->assertSame(1, Application::count());

        $this->actingAs($admin)->get('/admin/students')
            ->assertSee('Siswa Terlihat')->assertSee('siswa@contoh.test')
            ->assertDontSee('08123456789')->assertDontSee('Desa Pribadi')->assertDontSee('Judul Pribadi')->assertDontSee('cv-rahasia');

        $this->actingAs($admin)->get('/admin/students?q=Terlihat')->assertSee('siswa@contoh.test');
        $this->actingAs($admin)->get('/admin/students?q=tidakada')->assertDontSee('siswa@contoh.test');
        // mentor/admin tidak muncul di daftar siswa
        $this->actingAs($admin)->get('/admin/students')->assertDontSee('admin@berasiswa.test');
    }

    public function test_admin_cannot_open_student_files(): void
    {
        $admin = $this->admin();
        $student = $this->user('siswa@contoh.test');
        $this->actingAs($student)->post('/submissions', ['file' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]);
        $sub = Submission::firstOrFail();

        $this->actingAs($admin)->get("/submissions/{$sub->id}")->assertRedirect('/admin');
        $this->actingAs($admin)->delete("/submissions/{$sub->id}")->assertRedirect('/admin');
        $this->assertNotNull(Submission::find($sub->id));

        [$mentor] = $this->mentor('a@contoh.test');
        $this->actingAs($mentor)->get("/submissions/{$sub->id}")->assertRedirect('/mentor');
    }

    public function test_admin_resets_student_password(): void
    {
        $admin = $this->admin();
        $student = $this->user('siswa@contoh.test');

        $this->actingAs($admin)->post("/admin/students/{$student->id}/reset-password", ['password' => 'baru-12345'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('baru-12345', $student->fresh()->password));

        $this->actingAs($admin)->post("/admin/students/{$student->id}/reset-password", ['password' => 'pendek'])->assertSessionHasErrors('password');

        // hanya akun siswa yang bisa direset lewat sini (bukan mentor/admin)
        [$mentor] = $this->mentor('a@contoh.test');
        $this->actingAs($admin)->post("/admin/students/{$mentor->id}/reset-password", ['password' => 'baru-12345'])->assertNotFound();
    }

    // ---------- Admin: pengumuman ----------

    public function test_admin_announcement_to_all_students(): void
    {
        $admin = $this->admin();
        $a = $this->user('a@contoh.test');
        $b = $this->user('b@contoh.test');
        [$mentor] = $this->mentor('m@contoh.test');
        $off = $this->user('off@contoh.test', 'user', ['is_active' => false]);

        $this->actingAs($admin)->post('/admin/announcements', [
            'title' => 'Cek jadwal beasiswa', 'subtitle' => 'Selalu cek situs resmi', 'url' => '/find-scholarship', 'target' => 'all',
        ])->assertSessionHas('status');

        $this->assertSame(1, $a->notifications()->count());
        $this->assertSame(1, $b->notifications()->count());
        $this->assertSame(0, $mentor->notifications()->count());
        $this->assertSame(0, $off->notifications()->count());

        $this->actingAs($a)->get('/alert')->assertSee('Cek jadwal beasiswa')->assertSee('Selalu cek situs resmi');
    }

    public function test_admin_announcement_to_one_student_and_validation(): void
    {
        $admin = $this->admin();
        $a = $this->user('a@contoh.test');
        $b = $this->user('b@contoh.test');

        $this->actingAs($admin)->post('/admin/announcements', ['title' => 'Khusus A', 'target' => 'email', 'email' => 'a@contoh.test'])->assertSessionHas('status');
        $this->assertSame(1, $a->notifications()->count());
        $this->assertSame(0, $b->notifications()->count());

        $this->actingAs($admin)->post('/admin/announcements', ['title' => 'X', 'target' => 'email', 'email' => 'nobody@contoh.test'])->assertSessionHasErrors('email');
        $this->actingAs($admin)->post('/admin/announcements', ['title' => 'X', 'target' => 'email'])->assertSessionHasErrors('email');
        $this->actingAs($admin)->post('/admin/announcements', ['title' => '', 'target' => 'all'])->assertSessionHasErrors('title');
        $this->actingAs($admin)->post('/admin/announcements', ['title' => 'X', 'target' => 'all', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
        $this->actingAs($admin)->post('/admin/announcements', ['title' => 'X', 'target' => 'all', 'url' => '//evil.example.com'])->assertSessionHasErrors('url');
    }

    // ---------- Admin: data contoh ----------

    public function test_admin_samples_hide_then_purge_requires_confirmation(): void
    {
        $admin = $this->admin();
        $student = $this->user('s@contoh.test');

        $this->actingAs($admin)->get('/admin/samples')->assertSee('Beasiswa')->assertSee('9');

        $this->actingAs($admin)->post('/admin/samples/hide');
        $this->actingAs($student)->get('/find-scholarship')->assertDontSee('Bank Indonesia');
        $this->assertSame(9, Scholarship::withoutGlobalScopes()->where('is_sample', true)->count());

        $this->actingAs($admin)->post('/admin/samples/purge', ['confirm' => 'hapus'])->assertSessionHasErrors('confirm');
        $this->actingAs($admin)->post('/admin/samples/purge')->assertSessionHasErrors('confirm');
        $this->assertSame(9, Scholarship::withoutGlobalScopes()->where('is_sample', true)->count());

        $this->actingAs($admin)->post('/admin/samples/purge', ['confirm' => 'HAPUS'])->assertSessionHas('status');
        $this->assertSame(0, Scholarship::withoutGlobalScopes()->where('is_sample', true)->count());
        $this->assertSame(0, MentorProfile::withoutGlobalScopes()->where('is_sample', true)->count());
    }

    public function test_admin_dashboard_counts_exclude_samples(): void
    {
        $admin = $this->admin();
        Scholarship::create(['nama' => 'Asli 1', 'is_sample' => false]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()->assertSee('Masih ada')->assertSee('data contoh');
    }
}
