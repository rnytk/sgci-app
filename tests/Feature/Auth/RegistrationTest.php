<?php

namespace Tests\Feature\Auth;

test('public registration is unavailable', function () {
    $this->get('/register')->assertNotFound();
});
