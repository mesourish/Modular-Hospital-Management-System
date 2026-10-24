<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('public customer frontend renders at root', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('Apollo Hospitals Healthcare Network');
    $response->assertSee('Instant OPD Token Booking');
});

test('unauthenticated workspace access redirects to login', function () {
    $response = $this->get('/workspace');
    $response->assertRedirect('/login');
});
