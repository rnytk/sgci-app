<?php

test('the collaborator login and error pages use the SGCI favicon', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('rel="icon"', false)
        ->assertSee('/img/logo.png', false);

    $this->get('/favicon-test-not-found')
        ->assertNotFound()
        ->assertSee('rel="icon"', false)
        ->assertSee('/img/logo.png', false);
});

test('the administration panel uses the SGCI favicon', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('/img/logo.png', false);
});
