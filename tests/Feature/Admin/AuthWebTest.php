<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('unauthenticated visitor is redirected to login', function (): void {
    $this->get('/')->assertRedirect('/login');
    $this->get('/stations')->assertRedirect('/login');
    $this->get('/sessions')->assertRedirect('/login');
});

test('login page can be rendered', function (): void {
    $this->get('/login')->assertOk()->assertSeeLivewire(Login::class);
});

test('owner can login successfully with correct credentials', function (): void {
    User::factory()->create([
        'email' => 'admin@chargebing.local',
        'password' => Hash::make('password123'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'admin@chargebing.local')
        ->set('password', 'password123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertAuthenticated();
});

test('owner cannot login with incorrect password', function (): void {
    User::factory()->create([
        'email' => 'admin@chargebing.local',
        'password' => Hash::make('password123'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'admin@chargebing.local')
        ->set('password', 'wrong-pass')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('authenticated user can logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});
