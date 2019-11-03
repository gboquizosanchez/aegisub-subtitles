<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Enums\Blocks;
use Aegisub\Enums\FileBlocks;
use ReflectionException;

trait Processor
{
    // This trait is used in order to transform the extracted
    // lines into a valid Ass object.
    use Transformer;

    /**
     * Used to establish the current ass block.
     *
     * @var string
     */
    protected $block;

    /**
     * Process all the Ass file and transform it to a valid object.
     *
     * @return $this
     */
    private function processFile(): self
    {
        // Process all file lines
        foreach ($this->file as $line) {
            $this->processLine($line);
        }

        // Transform them to a valid Ass object and unset the file attribute.
        $this->transform()->cleaner();

        return $this;
    }

    /**
     * Check if is a valid Ass trough the Script block.
     *
     * @return bool
     */
    private function isAValidAss(): bool
    {
        return reset($this->file) === FileBlocks::SCRIPT;
    }

    /**
     * Establish blocks and process lines not empties.
     *
     * @param  string  $line
     * @return void
     */
    private function processLine(string $line): void
    {
        $this->establishBlock($line);

        $this->processNotEmptyLine($line);
    }

    /**
     * Lines not empty are processed only.
     *
     * @param  string|null  $line
     * @retun void
     */
    private function processNotEmptyLine(?string $line): void
    {
        if (!empty($line) && !$this->isFileBlock($line)) {
            $this->fill($line);
        }
    }

    /**
     * Establish block only when blocks lines in file are founded.
     *
     * @param $line
     * @return void
     */
    private function establishBlock($line): void
    {
        if ($this->isFileBlock($line)) {
            $this->block = $line;
        }
    }

    /**
     * Check if is a header file block contained in the file.
     *
     * @param  string|null  $line
     * @return bool
     */
    private function isFileBlock(?string $line): bool
    {
        try {
            return in_array($line, FileBlocks::getValues(), true);
        } catch (ReflectionException $exception) {
            echo $exception->getMessage();
            return false;
        }
    }

    /**
     * Fill the different blocks through $block var.
     *
     * @param string $line
     */
    private function fill(string $line): void
    {
        switch ($this->block) {
            case FileBlocks::SCRIPT:
                $this->setBlock(Blocks::SCRIPT, $line);
                break;
            case FileBlocks::STYLES_V4:
            case FileBlocks::STYLES_V4PLUS:
                $this->setBlock(Blocks::STYLES, $line);
                break;
            case FileBlocks::EVENTS:
                $this->setBlock(Blocks::EVENTS, $line);
                break;
        }
    }

    /**
     * Establish $line into $block attribute array
     * named through param into the object.
     *
     * @param $block
     * @param $line
     * @return void
     */
    private function setBlock($block, $line): void
    {
        $this->$block[] = $line;
    }

    /**
     * Unset file attribute.
     *
     * @return void
     */
    private function cleaner(): void
    {
        unset($this->file);
    }
}
