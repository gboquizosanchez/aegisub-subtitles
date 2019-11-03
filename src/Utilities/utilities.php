<?php

declare(strict_types=1);

if (!function_exists('contains')) {
    function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('dump')) {
    function dump($data)
    {
        ob_start();
        var_dump($data);
        $output = ob_get_clean();

        $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
        $output = "\e[0;32mDump\e[0m \e[1;31m=>\e[0m \e[0;34m$output\e[0m";

        echo $output;
    }
}

if (!function_exists('dd')) {
    function dd($data)
    {
        dump($data);
        exit(0);
    }
}
