<?php

namespace Tests\Feature;

use App\Models\CampaignVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function account(string $role, string $email): User
    {
        $user = new User(['name' => 'Akun Contoh', 'email' => $email, 'password' => 'rahasia123']);
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_guest_sees_welcome_page_with_login_and_register_buttons(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('CITA SISWA')
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false)
            ->assertSee('bukan penyelenggara beasiswa');
    }

    public function test_guest_welcome_page_has_no_forbidden_words_or_template_leftovers(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['gratis', 'Gratis', 'pasti lolos', 'India', 'Delhi', 'pravatar', 'cdn.tailwindcss.com'] as $bad) {
            $this->assertStringNotContainsString($bad, $html);
        }
        // Tidak ada data dari database yang bocor ke tamu.
        $this->assertStringNotContainsString('/find-scholarship/', $html);
        $this->assertStringNotContainsString('whatsapp', strtolower($html));
    }

    public function test_student_still_sees_the_home_page_at_root(): void
    {
        $this->actingAs($this->account(User::ROLE_USER, 's@contoh.test'))->get('/')
            ->assertOk()->assertSee('Empat langkah santai')->assertDontSee('Daftar, yuk');
    }

    public function test_mentor_and_admin_are_redirected_from_root_to_their_own_home(): void
    {
        $this->actingAs($this->account(User::ROLE_MENTOR, 'm@contoh.test'))->get('/')->assertRedirect('/mentor');
        $this->actingAs($this->account(User::ROLE_ADMIN, 'a@contoh.test'))->get('/')->assertRedirect('/admin');
    }

    public function test_deactivated_user_is_logged_out_at_root(): void
    {
        $user = $this->account(User::ROLE_USER, 'off@contoh.test');
        $user->is_active = false;
        $user->save();

        $this->actingAs($user)->get('/')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_other_pages_still_require_login(): void
    {
        foreach (['/dashboard', '/find-scholarship', '/lomba', '/alumni', '/riwayat'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_utm_visit_on_welcome_page_is_recorded_without_ip(): void
    {
        $this->get('/?utm_source=instagram&utm_medium=reels&utm_campaign=podcast-eps1')->assertOk();

        $this->assertSame(1, CampaignVisit::count());
        $this->assertSame('podcast-eps1', CampaignVisit::first()->utm_campaign);
    }

    public function test_login_page_has_student_and_admin_choices(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Masuk sebagai Siswa')->assertSee('Daftar')
            ->assertSee('sebagai=admin', false);

        $this->get('/login?sebagai=admin')->assertOk()
            ->assertSee('Masuk sebagai Admin')->assertSee('name="sebagai" value="admin"', false)
            ->assertDontSee('Belum punya akun?');
    }

    public function test_admin_login_choice_accepts_admin_only(): void
    {
        $admin = $this->account(User::ROLE_ADMIN, 'a@contoh.test');
        $this->post('/login', ['email' => 'a@contoh.test', 'password' => 'rahasia123', 'sebagai' => 'admin'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        auth()->logout();

        $this->account(User::ROLE_USER, 's@contoh.test');
        $this->post('/login', ['email' => 's@contoh.test', 'password' => 'rahasia123', 'sebagai' => 'admin'])
            ->assertRedirect(route('login', ['sebagai' => 'admin']))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_student_login_choice_works_for_student_and_mentor(): void
    {
        $this->account(User::ROLE_USER, 's@contoh.test');
        $this->post('/login', ['email' => 's@contoh.test', 'password' => 'rahasia123', 'sebagai' => 'siswa'])
            ->assertRedirect('/');
        auth()->logout();

        $this->account(User::ROLE_MENTOR, 'm@contoh.test');
        $this->post('/login', ['email' => 'm@contoh.test', 'password' => 'rahasia123', 'sebagai' => 'siswa'])
            ->assertRedirect('/mentor');
    }

    public function test_login_without_choice_field_behaves_as_before(): void
    {
        $this->account(User::ROLE_ADMIN, 'a@contoh.test');
        $this->post('/login', ['email' => 'a@contoh.test', 'password' => 'rahasia123'])->assertRedirect('/admin');
    }
}
