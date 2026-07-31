<?php

use VmEngine\Queue\QueueServiceProvider;

describe('Queue Module Discovery', function () {
    it('check package module is registered and working', function () {
        // Verify the package is loaded in composer.json
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);

        expect($composerJson['require'] ?? [])
            ->toHaveKey('vm-engine/synmod-queue');

        // Verify module service provider is autoloaded correctly
        // (this module has no Eloquent model to check, unlike synmod-example)
        expect(class_exists(QueueServiceProvider::class))->toBeTrue();

        // Verify routes are registered (just check they exist and return a URL)
        expect(backend_route('queue.failed'))->toBeString()->not->toBeEmpty();
        expect(backend_route('queue.pending'))->toBeString()->not->toBeEmpty();
        expect(backend_route('queue.worker'))->toBeString()->not->toBeEmpty();
    });
});
