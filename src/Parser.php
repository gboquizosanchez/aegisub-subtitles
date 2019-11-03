<?php

declare(strict_types=1);

namespace Aegisub;

use Aegisub\Exceptions\FileNotFoundException;
use Aegisub\Exceptions\FileNotValidException;

class Parser
{
    public function make($filename): void
    {
        try {
            echo (string) (new Ass($filename));
        } catch (FileNotFoundException | FileNotValidException $exception) {
            echo $exception->getMessage();
        }
    }
}
