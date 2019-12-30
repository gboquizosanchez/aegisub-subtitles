<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Enums\Blocks;
use Aegisub\Enums\Delimiters;
use Aegisub\Enums\FileBlocks;
use Aegisub\Enums\Lines;
use RuntimeException;

trait Writer
{
    /**
     * A new file opened.
     *
     * @var resource
     */
    private $newFile;

    /**
     * The name of the path to move the converted files.
     *
     * @var string
     */
    private string $path = 'conversion';

    /**
     * Write a new file.
     *
     * @param array $arguments
     *
     * @return void
     */
    public function compose(array $arguments = null): void
    {
        $this->openFile();

        $this->writeScriptBlock();

        $this->writeBlock(Blocks::STYLES);

        if (isset($arguments['b'])) {
            $this->deletedBrackets();
        }

        $this->writeBlock(Blocks::EVENTS);

        $this->closer();
    }

    /**
     * Write a new script block.
     *
     * @return void
     */
    private function writeScriptBlock(): void
    {
        $this->write(FileBlocks::SCRIPT.PHP_EOL);
        $this->write('; Script modified by assparser'.PHP_EOL);
        $this->write('; https://github.com/gboquizosanchez/aegisub-subtitles'.PHP_EOL);

        foreach ($this->script as $key => $value) {
            if (end($this->script) === $value) {
                $key = substr((string) $key, 0, 5).' '.substr((string) $key, 5, 10);
            }
            $this->write(ucfirst((string) $key).Delimiters::COLON_WITH_SPACE.$value.PHP_EOL);
        }

        $this->write(PHP_EOL);
    }

    /**
     * Write a block with the name of it given through param.
     *
     * Only admits: Style and events blocks.
     *
     * @param string $block
     */
    private function writeBlock(string $block): void
    {
        $firstBlockLine = reset($this->$block);

        $this->write($this->headerBlock($block).PHP_EOL);
        $this->write('Format: '.$this->keysCommaSeparated((array) $firstBlockLine).PHP_EOL);

        foreach ($this->$block as $line) {
            $this->write($this->valuesCommaSeparated((array) $line).PHP_EOL);
        }

        $this->write(PHP_EOL);
    }

    /**
     * Return the header block line.
     *
     * @param string $string
     *
     * @return string
     */
    private function headerBlock(string $string): string
    {
        switch ($string) {
            case Blocks::EVENTS:
                return FileBlocks::EVENTS;
            case Blocks::STYLES:
                return FileBlocks::STYLES_V4PLUS;
            default:
                return '';
        }
    }

    /**
     * Write in the new file.
     *
     * @param string $string
     */
    private function write(string $string): void
    {
        fwrite($this->newFile, $string);
    }

    /**
     * Glue keys of a line with commas.
     *
     * @param array $values
     *
     * @return string
     */
    private function keysCommaSeparated(array $values): string
    {
        array_shift($values);

        return implode(Delimiters::COMMA_WITH_SPACE, array_map('ucfirst', array_keys($values)));
    }

    /**
     * Glue values of a line with commas.
     *
     * @param array $values
     *
     * @return string
     */
    private function valuesCommaSeparated(array $values): string
    {
        $firstLine = reset($values);

        array_shift($values);

        $line = implode(Delimiters::COMMA, array_map('ucfirst', array_values($values)));

        return $firstLine.Delimiters::COLON_WITH_SPACE.$line;
    }

    /**
     * Open the new file and create directory if not exists.
     *
     * @return void
     */
    private function openFile(): void
    {
        if (!is_dir($this->path) && (!mkdir($this->path) && !is_dir($this->path))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->path));
        }

        $this->newFile = fopen("{$this->path}/{$this->filename}", 'wb+');
    }

    /**
     * Delete all lines with {} inside.
     *
     * @return void
     */
    private function deletedBrackets(): void
    {
        foreach ($this->events as $event) {
            $event->text = preg_replace('/{.+}/', '', $event->text);
        }
    }

    /**
     * Close resource and unset variables.
     *
     * @return void
     */
    private function closer(): void
    {
        fclose($this->newFile);
        unset($this->newFile, $this->path);
    }
}
