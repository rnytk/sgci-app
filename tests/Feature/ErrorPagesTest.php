<?php

test('the branded not found page is returned for an unknown route', function () {
    $this->get('/esta-ruta-no-existe')
        ->assertNotFound()
        ->assertSee('No encontramos esta página')
        ->assertSee('SGCI');
});

test('all custom error pages include the SGCI visual foundation', function (int $status, string $title) {
    $page = view("errors.{$status}")->render();

    expect($page)
        ->toContain('SGCI')
        ->toContain($title)
        ->toContain('viewport')
        ->toContain('sm:')
        ->toContain('lg:');
})->with([
    [401, 'Necesitas iniciar sesión'],
    [403, 'No tienes permiso para acceder'],
    [404, 'No encontramos esta página'],
    [405, 'Esta acción no está permitida'],
    [419, 'Tu sesión necesita actualizarse'],
    [429, 'Se realizaron demasiadas solicitudes'],
    [500, 'Ocurrió un problema inesperado'],
    [503, 'SGCI estará disponible pronto'],
]);
