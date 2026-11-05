<?php

test('mobile navigation starts closed before JavaScript runs', function () {
    $response = $this->get('/');

    $response->assertOk();

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $navigation = (new DOMXPath($document))->query('//nav[@id="landing-mobile-navigation"]')->item(0);

    expect($navigation)->not->toBeNull()
        ->and($navigation->hasAttribute('hidden'))->toBeTrue();
});

test('mobile navigation keeps account actions available', function () {
    $response = $this->get('/');

    $response->assertOk();

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $actions = (new DOMXPath($document))->query('//nav[@id="landing-mobile-navigation"]/*[contains(concat(" ", normalize-space(@class), " "), " landing-mobile-navigation-actions ")]')->item(0);

    expect($actions)->not->toBeNull()
        ->and($actions->getElementsByTagName('a')->length)->toBe(2);
});
