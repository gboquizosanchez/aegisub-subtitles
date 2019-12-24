<?php

declare(strict_types=1);

namespace Aegisub;

use Aegisub\Enums\Delimiters;

class Logger
{
    /**
     * Num of lines that will be printed.
     *
     * @var int
     */
    private int $numOfLines = 120;

    /**
     * File to write.
     *
     * @var resource
     */
    protected $file;

    /**
     * Logger constructor.
     *
     * @param string $filename
     * @param string $extension
     */
    public function __construct(string $filename, string $extension = 'ass')
    {
        $this->file = fopen("debug/{$filename}.{$extension}", 'wb+');
    }

    /**
     * Write a line with EOL.
     *
     * @param string|null $string
     * @param null        $type
     *
     * @return void
     */
    public function write(?string $string = '', $type = null): void
    {
        $type === 'center'
            ? fwrite($this->file, $this->writeCenteredLine($string).PHP_EOL ?? '')
            : fwrite($this->file, $string.PHP_EOL ?? '');
    }

    /**
     * Write a separator with 120 characters by default.
     *
     * @param string $string
     * @param int    $amount
     *
     * @return void
     */
    public function writeSeparator(string $string, int $amount = 120): void
    {
        $amount = $amount <= $this->numOfLines ? $amount : $this->numOfLines;

        $header = str_repeat($string, $amount).PHP_EOL;

        fwrite($this->file, $header);
    }

    /**
     * Write a centered line.
     *
     * @param string $string
     *
     * @return string
     */
    private function writeCenteredLine(string $string): string
    {
        $headerLength = mb_strlen($string);

        $sides = (object) ['right' => 0, 'left' => 0];

        foreach ($sides as $key => $side) {
            $side = ($this->numOfLines - $headerLength) / 2;

            if ($headerLength % 2 !== 0 && $key === 'right') {
                $side++;
            }

            $sides->$key = str_repeat(Delimiters::SPACE, (int) $side);
        }

        return Delimiters::SPACE.$sides->left.$string.$sides->right;
    }

    /**
     * Logger destructor.
     */
    public function __destruct()
    {
        fclose($this->file);
    }
}
