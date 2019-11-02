<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Exceptions\FileNotFoundException;
use Aegisub\Logger;

trait Extractor
{
    /**
     * Extract file if is a valid file.
     *
     * @param $filename
     * @return array
     * @throws FileNotFoundException
     */
    private function extractFile($filename): array
    {
        if (file_exists($filename) && contains($filename, ['.ass', '.ssa'])) {
            $file = file_get_contents($filename);

            (new Logger('backup'))->write($file);

            return explode(PHP_EOL, $file);
        }

        throw new FileNotFoundException('File not found');
    }
}
