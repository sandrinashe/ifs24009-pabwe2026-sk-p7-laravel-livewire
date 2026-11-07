<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\AdminLogin;
use App\Features\Auth\Livewire\AdminRegister;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.admin_registration_code' => 'kode-rahasia']);
        RateLimiter::clear('admin-register:127.0.0.1');
    }

    public function test_can_render_admin_login_page(): void
    {
        $this->get('/admin/login')
            ->assertStatus(200)
            ->assertSee('Masuk sebagai Admin')
            ->assertSee('Username atau Email Admin')
            ->assertSee('login-submit');
    }

    public function test_can_render_admin_register_page(): void
    {
        $this->get('/admin/register')
            ->assertStatus(200)
            ->assertSee('Daftar Akun Admin')
            ->assertSee('Kode Akses Admin')
            ->assertDontSee('sedang dinonaktifkan');
    }

    public function test_admin_register_page_shows_notice_when_disabled(): void
    {
        config(['app.admin_registration_code' => '']);

        $this->get('/admin/register')
            ->assertStatus(200)
            ->assertSee('Pendaftaran admin sedang dinonaktifkan');
    }

    public function test_member_pages_link_to_admin_pages(): void
    {
        $this->get('/login')->assertSee(route('admin.login'), false);
        $this->get('/register')->assertSee(route('admin.register'), false);
    }

    public function test_authenticated_user_is_redirected_away_from_admin_login(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/login')
            ->assertRedirect();
    }

    public function test_admin_login_validation(): void
    {
        Livewire::test(AdminLogin::class)
            ->set('login', '')
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['login', 'password']);
    }

    public function test_admin_login_with_invalid_credentials(): void
    {
        User::factory()->admin()->create([
            'username' => 'boss',
            'password' => Hash::make('correct-password'),
        ]);

        Livewire::test(AdminLogin::class)
            ->set('login', 'boss')
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['login']);

        $this->assertGuest();
    }

    public function test_member_cannot_login_through_admin_page(): void
    {
        User::factory()->member()->create([
            'username' => 'just_member',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(AdminLogin::class)
            ->set('login', 'just_member')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertHasErrors(['login']);

        $this->assertGuest();
    }

    public function test_admin_can_login_with_username_or_email(): void
    {
        User::factory()->admin()->create([
            'username' => 'boss',
            'email' => 'boss@test.com',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(AdminLogin::class)
            ->set('login', 'boss')
            ->set('password', 'password123')
            ->set('remember', true)
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));

        $this->assertAuthenticated();
        auth()->logout();

        Livewire::test(AdminLogin::class)
            ->set('login', 'boss@test.com')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));
    }

    public function test_admin_register_validation(): void
    {
        Livewire::test(AdminRegister::class)
            ->call('register')
            ->assertHasErrors(['name', 'username', 'email', 'password', 'access_code']);

        $this->assertGuest();
    }

    public function test_admin_register_is_blocked_when_disabled(): void
    {
        config(['app.admin_registration_code' => '']);

        Livewire::test(AdminRegister::class)
            ->set('name', 'Admin Baru')
            ->set('username', 'adminbaru')
            ->set('email', 'adminbaru@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('access_code', '')
            ->call('register')
            ->assertHasErrors(['access_code']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'adminbaru']);
    }

    public function test_admin_register_rejects_wrong_access_code(): void
    {
        Livewire::test(AdminRegister::class)
            ->set('name', 'Admin Baru')
            ->set('username', 'adminbaru')
            ->set('email', 'adminbaru@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('access_code', 'salah')
            ->call('register')
            ->assertHasErrors(['access_code']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'adminbaru']);
    }

    public function test_admin_register_success_creates_admin_and_logs_in(): void
    {
        Livewire::test(AdminRegister::class)
            ->set('name', 'Admin Baru')
            ->set('username', 'adminbaru')
            ->set('email', 'adminbaru@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('access_code', 'kode-rahasia')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'adminbaru',
            'email' => 'adminbaru@test.com',
            'role' => 'admin',
        ]);
        $this->assertTrue(auth()->user()->isAdmin());
    }

    public function test_admin_register_is_throttled_after_too_many_wrong_codes(): void
    {
        $component = Livewire::test(AdminRegister::class)
            ->set('name', 'Admin Baru')
            ->set('username', 'adminbaru')
            ->set('email', 'adminbaru@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('access_code', 'salah');

        for ($i = 0; $i < 5; $i++) {
            $component->call('register')->assertHasErrors(['access_code']);
        }

        // Percobaan ke-6 diblokir walau kodenya benar
        $component->set('access_code', 'kode-rahasia')
            ->call('register')
            ->assertHasErrors(['access_code']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'adminbaru']);

        RateLimiter::clear('admin-register:127.0.0.1');
    }
}
