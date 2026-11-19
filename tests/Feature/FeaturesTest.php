<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Campaign;
use App\Models\CampaignVisit;
use App\Models\MentorProfile;
use App\Models\ReviewRequest;
use App\Models\Scholarship;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Kurasi berkas, batas pengajuan, pelacakan UTM, dan halaman corong. */
class FeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
        Storage::fake('local');
    }

    private function user(string $email, string $role = 'user'): User
    {
        $user = new User(['name' => ucfirst($role).' '.$email, 'email' => $email, 'password' => 'rahasia123']);
        $user->role = $role;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /** @return array{0: User, 1: MentorProfile} */
    private function mentor(string $email = 'm@contoh.test', ?int $max = null): array
    {
        $user = $this->user($email, 'mentor');
        $profile = MentorProfile::withoutGlobalScopes()->create([
            'user_id' => $user->id, 'nama' => 'Mentor '.$email, 'is_willing_to_help' => true, 'max_active_requests' => $max,
        ]);

        return [$user, $profile];
    }

    private function file(User $student, string $name = 'cv.pdf'): Submission
    {
        $this->actingAs($student)->post('/submissions', ['file' => UploadedFile::fake()->create($name, 50, 'application/pdf')])->assertSessionHasNoErrors();

        return Submission::where('user_id', $student->id)->latest('id')->firstOrFail();
    }

    private function ask(User $student, MentorProfile $mentor, array $files, ?Scholarship $s = null)
    {
        $s ??= Scholarship::first();

        return $this->actingAs($student)->post('/review-requests', [
            'target_type' => 'scholarship', 'target_id' => $s->id, 'mentor_profile_id' => $mentor->id,
            'note' => 'Tolong dicek', 'submissions' => array_map(fn ($f) => $f->id, $files),
        ]);
    }

    private function lastRequest(): ReviewRequest
    {
        return ReviewRequest::latest('id')->firstOrFail();
    }

    // ---------- Akses mentor ke berkas ----------

    public function test_only_the_chosen_mentor_can_open_shared_files_and_only_the_shared_ones(): void
    {
        [$mentorA, $profileA] = $this->mentor('a@contoh.test');
        [$mentorB] = $this->mentor('b@contoh.test');
        $student = $this->user('s@contoh.test');
        $shared = $this->file($student, 'dibagikan.pdf');
        $secret = $this->file($student, 'rahasia.pdf');

        $this->ask($student, $profileA, [$shared])->assertSessionHasNoErrors();

        $this->actingAs($mentorA)->get("/mentor/files/{$shared->id}")->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($mentorA)->get("/mentor/files/{$secret->id}")->assertNotFound();   // tidak dibagikan
        $this->actingAs($mentorB)->get("/mentor/files/{$shared->id}")->assertNotFound();   // bukan mentor yang dipilih

        // halaman detail menampilkan berkas yang dibagikan saja
        $this->actingAs($mentorA)->get('/mentor/requests/'.$this->lastRequest()->id)
            ->assertSee('dibagikan')->assertDontSee('rahasia');
    }

    public function test_mentor_access_closes_after_reply_decline_cancel_and_expiry(): void
    {
        [$mentor, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $file = $this->file($student);

        // dibalas
        $this->ask($student, $profile, [$file]);
        $req = $this->lastRequest();
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertOk();
        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/reply", ['feedback' => 'Bagus, tambahkan prestasimu.'])->assertSessionHasNoErrors();
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertNotFound();

        // ditolak
        $this->ask($student, $profile, [$file]);
        $req = $this->lastRequest();
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertOk();
        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/decline", ['reason' => 'Sedang ujian']);
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertNotFound();

        // dibatalkan siswa
        $this->ask($student, $profile, [$file]);
        $req = $this->lastRequest();
        $this->actingAs($student)->post("/review-requests/{$req->id}/cancel")->assertSessionHas('status');
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertNotFound();

        // kedaluwarsa
        $this->ask($student, $profile, [$file]);
        $req = $this->lastRequest();
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertOk();
        $req->forceFill(['created_at' => now()->subDays(15)])->save();
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertNotFound();
    }

    public function test_deleting_a_file_removes_mentor_access(): void
    {
        [$mentor, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $file = $this->file($student);
        $this->ask($student, $profile, [$file]);

        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertOk();
        $this->actingAs($student)->delete("/submissions/{$file->id}");
        $this->actingAs($mentor)->get("/mentor/files/{$file->id}")->assertNotFound();
    }

    public function test_admin_and_other_students_cannot_open_files_through_mentor_route(): void
    {
        [, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $file = $this->file($student);
        $this->ask($student, $profile, [$file]);

        $admin = $this->user('adm@contoh.test', 'admin');
        $this->actingAs($admin)->get("/mentor/files/{$file->id}")->assertRedirect('/admin');
        $this->actingAs($this->user('lain@contoh.test'))->get("/mentor/files/{$file->id}")->assertRedirect('/');
    }

    // ---------- Aturan pengajuan ----------

    public function test_request_needs_one_to_three_own_files(): void
    {
        [, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $other = $this->user('o@contoh.test');
        $files = [$this->file($student, '1.pdf'), $this->file($student, '2.pdf'), $this->file($student, '3.pdf'), $this->file($student, '4.pdf')];
        $foreign = $this->file($other, 'milik-orang.pdf');

        $this->ask($student, $profile, [])->assertSessionHasErrors('submissions');
        $this->ask($student, $profile, $files)->assertSessionHasErrors('submissions');            // 4 berkas
        $this->ask($student, $profile, [$foreign])->assertSessionHasErrors('submissions');        // bukan milik sendiri
        $this->assertSame(0, ReviewRequest::count());

        $this->ask($student, $profile, array_slice($files, 0, 3))->assertSessionHasNoErrors();
        $this->assertSame(3, $this->lastRequest()->submissions()->count());
    }

    public function test_mentor_can_decline_and_student_sees_reason_and_can_try_another_mentor(): void
    {
        [$mentorA, $profileA] = $this->mentor('a@contoh.test');
        [, $profileB] = $this->mentor('b@contoh.test');
        $student = $this->user('s@contoh.test');
        $file = $this->file($student);

        $this->ask($student, $profileA, [$file]);
        $req = $this->lastRequest();
        $this->actingAs($mentorA)->post("/mentor/requests/{$req->id}/decline", ['reason' => 'Sedang sibuk ujian'])->assertSessionHasNoErrors();

        $this->assertSame('declined', $req->fresh()->status);
        $this->assertSame(1, $student->notifications()->count());
        $this->actingAs($student)->get('/riwayat')->assertSee('Belum bisa membantu')->assertSee('Sedang sibuk ujian')->assertSee('mentor lain');

        // mentor yang sama juga boleh dicoba lagi, dan mentor lain tentu saja
        $this->ask($student, $profileB, [$file])->assertSessionHasNoErrors();
        $this->assertSame(2, ReviewRequest::count());
    }

    public function test_feedback_is_required_and_limited(): void
    {
        [$mentor, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $this->ask($student, $profile, [$this->file($student)]);
        $req = $this->lastRequest();

        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/reply", ['feedback' => ''])->assertSessionHasErrors('feedback');
        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/reply", ['feedback' => str_repeat('a', 2001)])->assertSessionHasErrors('feedback');
        $this->assertSame('pending', $req->fresh()->status);

        $this->actingAs($mentor)->post("/mentor/requests/{$req->id}/reply", ['feedback' => str_repeat('a', 2000)])->assertSessionHasNoErrors();
        $this->assertSame('replied', $req->fresh()->status);
    }

    public function test_student_can_cancel_only_own_open_request(): void
    {
        [, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $this->ask($student, $profile, [$this->file($student)]);
        $req = $this->lastRequest();

        $this->actingAs($this->user('o@contoh.test'))->post("/review-requests/{$req->id}/cancel")->assertNotFound();
        $this->assertSame('pending', $req->fresh()->status);

        $this->actingAs($student)->post("/review-requests/{$req->id}/cancel");
        $this->assertSame('cancelled', $req->fresh()->status);
        $this->actingAs($student)->get('/riwayat')->assertSee('Dibatalkan');
    }

    public function test_unanswered_request_becomes_no_reply_after_14_days_and_can_be_retried(): void
    {
        [, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $file = $this->file($student);
        $this->ask($student, $profile, [$file]);
        $this->lastRequest()->forceFill(['created_at' => now()->subDays(15)])->save();

        $this->actingAs($student)->get('/riwayat')->assertSee('Belum ada balasan');

        // pengajuan lama sudah tidak dianggap terbuka, jadi boleh mengajukan lagi
        $this->ask($student, $profile, [$file])->assertSessionHasNoErrors();
        $this->assertSame(2, ReviewRequest::count());
    }

    public function test_mentor_capacity_limit_hides_mentor_from_choices(): void
    {
        [, $profile] = $this->mentor('a@contoh.test', 1);
        $s1 = $this->user('s1@contoh.test');
        $s2 = $this->user('s2@contoh.test');
        $scholarship = Scholarship::first();
        $file2 = $this->file($s2);   // daftar mentor hanya tampil kalau siswa punya berkas

        $this->actingAs($s2)->get("/find-scholarship/{$scholarship->id}")->assertSee('Mentor a@contoh.test');

        $this->ask($s1, $profile, [$this->file($s1)])->assertSessionHasNoErrors();   // slot penuh

        $this->actingAs($s2)->get("/find-scholarship/{$scholarship->id}")->assertDontSee('Mentor a@contoh.test');
        $this->ask($s2, $profile, [$file2])->assertSessionHasErrors('mentor_profile_id');

        // max 0 = berhenti sementara
        $profile->update(['max_active_requests' => 0]);
        $this->actingAs($s2)->get("/find-scholarship/{$scholarship->id}")->assertDontSee('Mentor a@contoh.test');
    }

    public function test_request_card_is_hidden_without_available_mentors_but_upload_stays(): void
    {
        MentorProfile::withoutGlobalScopes()->update(['is_willing_to_help' => false]);
        $student = $this->user('s@contoh.test');
        $scholarship = Scholarship::first();

        $this->actingAs($student)->get("/find-scholarship/{$scholarship->id}")->assertOk()->assertDontSee('Minta masukan mentor');
        $this->actingAs($student)->get('/settings/personal')->assertOk()->assertSee('CV dan esai')->assertSee('Unggah');
        $this->file($student); // unggah tetap bisa tanpa mentor

        MentorProfile::withoutGlobalScopes()->first()->update(['is_willing_to_help' => true]);
        $this->actingAs($student)->get("/find-scholarship/{$scholarship->id}")->assertSee('Minta masukan mentor')->assertSee('balasan tidak dijamin');
    }

    public function test_sample_mentors_are_labelled_in_the_choices(): void
    {
        $student = $this->user('s@contoh.test');
        $this->file($student);

        $this->actingAs($student)->get('/find-scholarship/'.Scholarship::first()->id)->assertSee('contoh, belum ada balasan');
    }

    public function test_request_form_asks_to_upload_first_when_student_has_no_files(): void
    {
        $student = $this->user('s@contoh.test');

        $this->actingAs($student)->get('/find-scholarship/'.Scholarship::first()->id)->assertSee('Unggah CV atau esai dulu');
    }

    // ---------- UTM ----------

    public function test_utm_visit_is_recorded_once_per_visitor_on_the_guest_landing_page(): void
    {
        $first = $this->get('/?utm_source=Instagram&utm_medium=reels&utm_campaign=Podcast-Eps1')->assertOk();
        $visitor = $first->getCookie('cs_visitor', false)->getValue();

        // kunjungan ulang oleh pengunjung yang sama (membawa cookie)
        $this->withUnencryptedCookie('cs_visitor', $visitor)->get('/?utm_source=instagram&utm_medium=reels&utm_campaign=podcast-eps1');

        $this->assertSame(1, CampaignVisit::count());
        $visit = CampaignVisit::first();
        $this->assertSame('instagram', $visit->utm_source);
        $this->assertSame('podcast-eps1', $visit->utm_campaign);
        $this->assertSame(36, strlen($visit->visitor_id));
        $this->assertSame(['id', 'visitor_id', 'utm_source', 'utm_medium', 'utm_campaign', 'landing_path', 'created_at'], array_keys($visit->getAttributes()));
    }

    public function test_utm_is_sanitized_and_ignored_when_missing(): void
    {
        $this->get('/login');
        $this->get('/login?utm_source=');
        $this->assertSame(0, CampaignVisit::count());

        $this->get('/login?utm_source=ig<script>&utm_campaign=a b c');
        $this->assertSame('igscript', CampaignVisit::first()->utm_source);
        $this->assertSame('a-b-c', CampaignVisit::first()->utm_campaign);

        $this->post('/login?utm_source=hack', ['email' => 'x@x.test', 'password' => 'x']);   // hanya GET yang dicatat
        $this->assertSame(1, CampaignVisit::count());
    }

    public function test_registration_remembers_the_campaign_from_the_cookie(): void
    {
        $visit = $this->get('/?utm_source=instagram&utm_medium=reels&utm_campaign=podcast-eps1');
        $utm = $visit->getCookie('cs_utm', false)->getValue();

        $this->withUnencryptedCookie('cs_utm', $utm)->post('/register', [
            'name' => 'Siswa IG', 'email' => 'ig@contoh.test', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'ig@contoh.test')->firstOrFail();
        $this->assertSame('instagram', $user->utm_source);
        $this->assertSame('reels', $user->utm_medium);
        $this->assertSame('podcast-eps1', $user->utm_campaign);
    }

    public function test_registration_without_campaign_has_no_utm(): void
    {
        $this->post('/register', ['name' => 'Siswa', 'email' => 'a@contoh.test', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123']);

        $this->assertNull(User::where('email', 'a@contoh.test')->first()->utm_source);
    }

    // ---------- Corong admin ----------

    public function test_funnel_counts_clicks_registrations_uploaders_and_acr(): void
    {
        $admin = $this->user('adm@contoh.test', 'admin');

        // 4 pengunjung dari kampanye yang sama (cookie berbeda), 2 mendaftar, 1 mengunggah, 1 membuka situs resmi
        foreach (range(1, 4) as $i) {
            CampaignVisit::create(['visitor_id' => \Illuminate\Support\Str::uuid(), 'utm_source' => 'instagram', 'utm_medium' => 'reels', 'utm_campaign' => 'eps1', 'created_at' => now()]);
        }
        foreach ([1, 2] as $i) {
            $u = $this->user("ig{$i}@contoh.test");
            $u->utm_source = 'instagram';
            $u->utm_campaign = 'eps1';
            $u->save();
        }
        $uploader = User::where('email', 'ig1@contoh.test')->first();
        $this->file($uploader);
        $this->actingAs($uploader)->get('/go/scholarship/'.Scholarship::first()->id);
        $this->user('organik@contoh.test'); // tanpa kampanye: tidak ikut tabel kampanye

        $response = $this->actingAs($admin)->get('/admin/funnel')->assertOk()->assertSee('eps1')->assertSee('25,0%');
        $response->assertSee('Pendaftaran')->assertSee('email yang sudah terverifikasi', false);
    }

    public function test_funnel_ctr_uses_manual_impressions_and_validates_input(): void
    {
        $admin = $this->user('adm@contoh.test', 'admin');
        foreach (range(1, 5) as $i) {
            CampaignVisit::create(['visitor_id' => \Illuminate\Support\Str::uuid(), 'utm_source' => 'instagram', 'utm_medium' => '', 'utm_campaign' => 'eps2', 'created_at' => now()]);
        }

        $this->actingAs($admin)->get('/admin/funnel')->assertSee('eps2')->assertDontSee('2,5%');

        $this->actingAs($admin)->post('/admin/funnel/impressions', ['campaign' => 'eps2', 'impressions' => 200])->assertSessionHasNoErrors();
        $this->assertSame(200, (int) Campaign::where('utm_campaign', 'eps2')->value('impressions'));
        $this->actingAs($admin)->get('/admin/funnel')->assertSee('2,5%');   // 5 / 200

        $this->actingAs($admin)->post('/admin/funnel/impressions', ['campaign' => 'eps2', 'impressions' => -5])->assertSessionHasErrors('impressions');
        $this->actingAs($admin)->post('/admin/funnel/impressions', ['campaign' => '', 'impressions' => 5])->assertSessionHasErrors('campaign');
    }

    public function test_funnel_is_admin_only_and_shows_no_personal_data(): void
    {
        $this->actingAs($this->user('s@contoh.test'))->get('/admin/funnel')->assertRedirect('/');
        [$mentor] = $this->mentor();
        $this->actingAs($mentor)->get('/admin/funnel')->assertRedirect('/mentor');
        $this->actingAs($this->user('s2@contoh.test'))->post('/admin/funnel/impressions', ['campaign' => 'x', 'impressions' => 1])->assertRedirect('/');

        $u = $this->user('pribadi@contoh.test');
        $u->utm_source = 'instagram';
        $u->utm_campaign = 'eps1';
        $u->save();
        $this->actingAs($this->user('adm@contoh.test', 'admin'))->get('/admin/funnel')->assertDontSee('pribadi@contoh.test');
    }

    // ---------- Riwayat ----------

    public function test_riwayat_lists_opened_and_requested_with_old_url_redirecting(): void
    {
        [, $profile] = $this->mentor();
        $student = $this->user('s@contoh.test');
        $s1 = Scholarship::orderBy('id')->first();
        $s2 = Scholarship::orderBy('id')->skip(1)->first();

        $this->actingAs($student)->get("/go/scholarship/{$s1->id}");
        $this->ask($student, $profile, [$this->file($student)], $s2);

        $this->actingAs($student)->get('/applied')->assertRedirect('/riwayat');
        $this->actingAs($student)->get('/riwayat')
            ->assertSee($s1->nama)->assertSee('Dibuka')
            ->assertSee($s2->nama)->assertSee('Menunggu')->assertSee('Mentor m@contoh.test')->assertSee('cv')
            ->assertSee('bukan bukti sudah mendaftar');
    }

    public function test_pages_do_not_load_template_leftovers(): void
    {
        $student = $this->user('s@contoh.test');

        foreach (['/', '/find-scholarship', '/lomba', '/alumni', '/dashboard', '/riwayat', '/settings/personal'] as $url) {
            $html = $this->actingAs($student)->get($url)->assertOk()->getContent();
            foreach (['India', 'Delhi', 'Pune', 'welloption', 'ScholarPath', 'Jobpilot', 'pravatar', 'Esther', 'cdn.tailwindcss.com'] as $bad) {
                $this->assertStringNotContainsString($bad, $html, "{$url} masih memuat '{$bad}'");
            }
            $this->assertStringContainsString('CITA SISWA', $html);
        }

        foreach (['/login', '/register'] as $url) {
            auth()->logout();
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('pravatar', $html);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
            $this->assertStringContainsString('CITA SISWA', $html);
        }
    }

    public function test_footer_states_it_is_not_the_organizer_and_contact_comes_from_config(): void
    {
        config(['site.contact' => 'kontak-uji']);

        $this->actingAs($this->user('s@contoh.test'))->get('/')
            ->assertSee('bukan penyelenggara beasiswa')->assertSee('kontak-uji');
    }

    public function test_ga4_snippet_only_when_configured(): void
    {
        $student = $this->user('s@contoh.test');

        $this->actingAs($student)->get('/')->assertDontSee('googletagmanager');
        config(['site.ga4_id' => 'G-TEST12345']);
        $this->actingAs($student)->get('/')->assertSee('googletagmanager.com/gtag/js?id=G-TEST12345', false);
    }
}
