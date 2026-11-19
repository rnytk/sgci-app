<?php

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;

test('the welcome email reset link opens the password form', function () {
    Mail::fake();

    $user = User::query()->create([
        'name' => 'Colaborador nuevo',
        'email' => 'colaborador-nuevo@example.com',
    ]);

    $welcomeMail = null;

    Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) use (&$welcomeMail, $user): bool {
        $welcomeMail = $mail;

        return $mail->user->is($user)
            && str($mail->resetUrl)->contains('/reset-password/');
    });

    expect($welcomeMail)->not->toBeNull();

    $this->get($welcomeMail->resetUrl)
        ->assertOk()
        ->assertSeeVolt('pages.auth.reset-password')
        ->assertSee('Cambia tu contraseña');
});

test('the welcome email reset link works even with another active session', function () {
    Mail::fake();
    $administrator = User::factory()->create();

    $user = User::query()->create([
        'name' => 'Colaborador nuevo',
        'email' => 'colaborador-con-sesion@example.com',
    ]);

    $welcomeMail = null;

    Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) use (&$welcomeMail, $user): bool {
        $welcomeMail = $mail;

        return $mail->user->is($user);
    });

    $this->actingAs($administrator)
        ->get($welcomeMail->resetUrl)
        ->assertOk()
        ->assertSeeVolt('pages.auth.reset-password');
});

test('the token generated for the welcome email resets the new user password', function () {
    Mail::fake();

    $user = User::query()->create([
        'name' => 'Colaborador con contraseña temporal',
        'email' => 'colaborador-restablecimiento@example.com',
    ]);

    $welcomeMail = null;

    Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) use (&$welcomeMail, $user): bool {
        $welcomeMail = $mail;

        return $mail->user->is($user);
    });

    $path = parse_url($welcomeMail->resetUrl, PHP_URL_PATH);
    $token = basename((string) $path);
    parse_str((string) parse_url($welcomeMail->resetUrl, PHP_URL_QUERY), $query);

    Volt::test('pages.auth.reset-password', ['token' => $token])
        ->set('email', $query['email'])
        ->set('password', 'Nueva-clave-segura-2026')
        ->set('password_confirmation', 'Nueva-clave-segura-2026')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect('/login');

    expect(Hash::check('Nueva-clave-segura-2026', $user->refresh()->password))->toBeTrue();

    $this->get($welcomeMail->resetUrl)
        ->assertRedirect('/login');
});
