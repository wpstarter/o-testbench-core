<?php

namespace Orchestra\Testbench\Features;

use Closure;
use WpStarter\Support\Collection;

/**
 * @internal
 */
final class FeaturesCollection extends Collection
{
    /**
     * Handle attribute callbacks.
     *
     * @param  \Closure|null  $callback
     * @return void
     */
    public function handle(?Closure $callback = null): void
    {
        if ($this->isEmpty()) {
            return;
        }

        $this->each($callback ?? static function ($attribute) {
            ws_value($attribute);
        });
    }
}
