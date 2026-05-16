<?php

namespace App\Observers;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */

    public function creating(User $user): void
    {
        $tempPassword = Str::random(12);

        $user->password_para_correo = $tempPassword;

        $user->password = Hash::make($tempPassword);
    }
    public function created(User $user): void
    {
        // 1. Generamos una clave aleatoria de 12 caracteres
        $tempPassword = Str::random(12);

        // 2. Actualizamos al usuario con la clave encriptada
        // Usamos 'quietly' para no disparar este mismo observer otra vez

        $user->forceFill([
            'password' => Hash::make($tempPassword),
        ])->saveQuietly();

        // 3. Enviamos el correo (usamos queue para no trabar el proceso de importación)
        Mail::to($user->email)->queue(new WelcomeUserMail($user, $tempPassword));
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
