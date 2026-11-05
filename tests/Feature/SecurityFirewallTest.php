<?php

use Illuminate\Support\Facades\Log;

it('omits request secrets from suspicious request warnings before routing', function () {
    Log::spy();

    $this->withHeader('User-Agent', 'Browser synthetic-agent-secret')
        ->get('/unknown-activation/synthetic-path-secret?token=synthetic-query-secret&search=%27')
        ->assertNotFound();

    Log::shouldHaveReceived('warning')->with(
        'Security firewall detected suspicious request',
        ['ip' => '127.0.0.1', 'score' => 30, 'method' => 'GET'],
    )->once();
});

it('blocks a scanner and subsequent requests without logging raw request values', function () {
    Log::spy();

    $this->withHeader('User-Agent', 'sqlmap synthetic-agent-secret')
        ->get('/unknown-activation/synthetic-path-secret?token=synthetic-query-secret')
        ->assertForbidden()
        ->assertExactJson(['message' => 'Forbidden']);

    $this->withHeader('User-Agent', 'Browser')
        ->get('/')
        ->assertForbidden()
        ->assertExactJson(['message' => 'Forbidden']);

    Log::shouldHaveReceived('warning')->with(
        'Security firewall detected suspicious request',
        ['ip' => '127.0.0.1', 'score' => 100, 'method' => 'GET'],
    )->once();
    Log::shouldHaveReceived('warning')->with(
        'Security firewall blocked IP',
        ['ip' => '127.0.0.1', 'score' => 100],
    )->once();
});

it('still accumulates suspicious requests until the temporary block expires', function () {
    $this->freezeTime();
    Log::spy();

    foreach ([1, 2, 3] as $attempt) {
        $this->get('/unknown-route?search=%27')->assertNotFound();
    }

    $this->get('/unknown-route?search=%27')->assertForbidden();

    $this->travel(31)->minutes();

    $this->get('/unknown-route')->assertNotFound();

    Log::shouldHaveReceived('warning')->with(
        'Security firewall blocked IP',
        ['ip' => '127.0.0.1', 'score' => 120],
    )->once();
});

it('passes ordinary requests without emitting firewall warnings', function () {
    Log::spy();

    $this->get('/unknown-route?search=ordinary')->assertNotFound();

    Log::shouldNotHaveReceived('warning');
});
