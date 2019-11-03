<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Enums\Delimiters;
use Aegisub\Exceptions\FileNotFoundException;
use Aegisub\Logger;

trait Extractor
{
    /**
     * Name of the file.
     *
     * @var string
     */
    public $filename;

    /**
     * Extract file if is a valid file.
     *
     * @param $filename
     *
     * @throws FileNotFoundException
     *
     * @return array
     */
    private function extractFile($filename): array
    {
        if (file_exists($filename) && contains($filename, ['.ass', '.ssa'])) {
            $file = file_get_contents($filename);

            $this->setFilename($filename);

            (new Logger('backup'))->write($file);

            return explode(PHP_EOL, $file);
        }

        throw new FileNotFoundException('File not found');
    }

    /**
     * Establish filename into data object.
     *
     * @param $filename
     *
     * @return void
     */
    private function setFilename($filename): void
    {
        if (contains($filename, Delimiters::SLASH)) {
            $path = explode(Delimiters::SLASH, $filename);
            $filename = end($path);
        }

        $this->filename = $filename;
    }
}
