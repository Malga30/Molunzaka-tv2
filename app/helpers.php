<?php

namespace App;

use Illuminate\Foundation\Application;

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the installation.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath(($path ? DIRECTORY_SEPARATOR.$path : $path));
    }
}
