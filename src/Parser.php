<?php

namespace Aegisub;

use Aegisub\Exceptions\FileNotFoundException;

class Parser
{
    public function make($filename): void
    {
        try {
            print_r(new Ass($filename));
        } catch (FileNotFoundException $exception) {
            print_r($exception->getMessage());
        }
    }


}
