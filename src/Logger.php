<?php

declare(strict_types=1);

namespace Aegisub;

class Logger
{
    /**
     * File to write.
     *
     * @var bool|resource
     */
    protected $file;

    /**
     * Logger constructor.
     *
     * @param $filename
     * @param string $extension
     */
    public function __construct(string $filename, $extension = 'ass')
    {
        $this->file = fopen("debug/{$filename}.{$extension}", 'wb+');
    }

    /**
     * Write line into the file.
     *
     * @param string|null $string
     */
    public function write(?string $string): void
    {
        fwrite($this->file, $string ?? '');
    }
}
