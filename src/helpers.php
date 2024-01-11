<?php

use Vitnasinec\Uri\Uri;

if (! function_exists('uri')) {
    function uri(string $fromString = null): Uri
    {
        return new Uri($fromString);
    }
}
