<?php

if (!function_exists('path2plugin')) {
    /**
     * @param string|null $path
     * @return string
     */
    function path2plugin(?string $path = null): string
    {
        return path2platform('plugins' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('path2platform')) {
    /**
     * @param string|null $path
     * @return string
     */
    function path2platform(?string $path = null): string
    {
        return base_path('platform/' . $path);
    }
}

if (!function_exists('path2core')) {
    /**
     * @param string|null $path
     * @return string
     */
    function path2core(?string $path = null): string
    {
        return path2platform('core/' . $path);
    }
}

if (!function_exists('path2package')) {
    /**
     * @param string|null $path
     * @return string
     */
    function path2package(?string $path = null): string
    {
        return path2platform('packages/' . $path);
    }
}
