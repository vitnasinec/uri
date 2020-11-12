<?php

namespace Vitnasinec\Uri;

/**
 * Uri generator
 *
 * @param ?string $fromString
 *
 * @return App\Utilities\Uri\Uri
 */
if (! function_exists('uri')) {
    function uri(?string $fromString = null)
    {
        return new Uri($fromString);
    }
}
