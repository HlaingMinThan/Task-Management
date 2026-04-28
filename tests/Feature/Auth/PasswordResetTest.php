<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('reset password link screen can be rendered', function () {
    $response = get('/forgot-password');
    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    $user = User::factory()->create();

    $response = post('/forgot-password', ['email' => $user->email]);

    $response->assertSessionHasNoErrors();
});

test('reset password screen can be rendered', function () {
    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = get('/reset-password/'.$token.'?email='.$user->email);
    $response->assertStatus(200);
});

test('password can be reset with valid token', function () {
    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/login');

    $this->assertTrue(Hash::check('password', $user->fresh()->password));
});
