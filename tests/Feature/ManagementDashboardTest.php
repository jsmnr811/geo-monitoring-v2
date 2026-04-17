<?php

use App\Models\User;

test('guests are redirected to the login page for management dashboard', function () {
    $response = $this->get(route('management-dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the management dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('management-dashboard'));
    $response->assertOk();
});
