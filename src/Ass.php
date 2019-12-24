<?php

declare(strict_types=1);

namespace Aegisub;

use Aegisub\Contracts\Analyzer;
use Aegisub\Contracts\Extractor;
use Aegisub\Contracts\Processor;
use Aegisub\Contracts\Writer;
use Aegisub\Exceptions\FileNotFoundException;
use Aegisub\Exceptions\FileNotValidException;

class Ass
{
    use Processor, Extractor, Writer, Analyzer;

    /**
     * All the file content.
     *
     * @var array
     */
    private array $file;

    /**
     * Ass constructor.
     *
     * @param $filename
     *
     * @throws FileNotValidException
     * @throws FileNotFoundException
     */
    public function __construct($filename)
    {
        $this->file = $this->extractFile($filename);
        $this->parse();
    }

    /**
     * Parse .ass to an Ass object.
     *
     * @throws FileNotValidException
     *
     * @return Ass
     */
    private function parse(): self
    {
        if ($this->isAValidAss()) {
            return $this->processFile();
        }

        throw new FileNotValidException('It is not an ass valid file.');
    }

    /**
     * Transform the object to json string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) json_encode($this, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
    }
}
