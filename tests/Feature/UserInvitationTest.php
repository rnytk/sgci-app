<?php

use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Services\UserInvitationService;
use Illuminate\Support\Facades\Mail;

test('an invitation sends a secure password-reset URL instead of a password', function () {
    Mail::fake();
    $user = User::factory()->create();

    app(UserInvitationService::class)->send($user);

    Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) use ($user): bool {
        return $mail->user->is($user)
            && str($mail->resetUrl)->contains('/reset-password/')
            && str($mail->resetUrl)->contains('email='.urlencode($user->email));
    });
});
