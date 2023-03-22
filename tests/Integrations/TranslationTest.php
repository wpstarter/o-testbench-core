<?php

namespace Orchestra\Testbench\Tests\Integrations;

use Orchestra\Testbench\TestCase;

class TranslationTest extends TestCase
{
    /** @test */
    public function it_can_resolve_default_language_path()
    {
        $this->assertSame(ws_base_path('resources'.DIRECTORY_SEPARATOR.'lang'), $this->app->langPath());
    }

    /** @test */
    public function it_can_resolve_validation_language_string()
    {
        $this->assertSame('The name field is required.', ws___('validation.required', ['attribute' => 'name']));
    }
}
