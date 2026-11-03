<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pages shouldn't need the Vite dev server or a production build to be tested.
        $this->withoutVite();
    }
}
