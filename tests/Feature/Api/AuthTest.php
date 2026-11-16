<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

test('user can login with valid credentials and receive a bearer token', function (): void {
    $user = User::factory()->create([
        'email' => 'driver@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'driver@example.com',
        'password' => 'secret123',
        'device_name' => 'iPhone 16 Pro',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'token_type',
            'user' => ['id', 'name', 'email'],
        ])
        ->assertJson([
            'token_type' => 'Bearer',
            'user' => [
                'email' => 'driver@example.com',
            ],
        ]);
});

test('user cannot login with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'driver@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'driver@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can get own profile via me endpoints', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);

    $this->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
});

test('authenticated user can logout and revoke token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson(['message' => 'Successfully logged out.']);

    expect($user->tokens()->count())->toBe(0);
});

test('unauthenticated request to protected endpoint is rejected with 401', function (): void {
    $this->getJson('/api/v1/stations')
        ->assertUnauthorized();
});
