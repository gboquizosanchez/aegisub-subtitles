<?php

declare(strict_types=1);

namespace Aegisub;

use Aegisub\Exceptions\FileNotFoundException;
use Aegisub\Exceptions\FileNotValidException;

class Parser
{
    public function make(string $filename, array $arguments): void
    {
        try {
            $ass = new Ass($filename);
            $ass->analyze();
            $ass->compose($arguments);
        } catch (FileNotFoundException | FileNotValidException $exception) {
            echo $exception->getMessage();
        }
    }
}
