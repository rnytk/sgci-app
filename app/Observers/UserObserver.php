<?php

namespace App\Observers;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */

    public function creating(User $user): void
    {
        if (filled($user->getAttribute('password'))) {
            return;
        }

        $temporaryPassword = Str::password(16);

        $user->password_para_correo = $temporaryPassword;
        $user->password = Hash::make($temporaryPassword);
    }

    public function created(User $user): void
    {
        if (blank($user->password_para_correo)) {
            return;
        }

        $resetUrl = route('password.reset', [
            'token' => Password::broker()->createToken($user),
            'email' => $user->email,
        ]);

        Mail::to($user->email)->queue(new WelcomeUserMail(
            $user,
            $user->password_para_correo,
            $resetUrl,
        ));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
