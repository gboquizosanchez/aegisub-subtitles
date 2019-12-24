<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Enums\Blocks;
use Aegisub\Enums\Delimiters;
use Aegisub\Enums\Lines;

trait Transformer
{
    /**
     * Empty object only for fill purposes.
     *
     * @var object
     */
    private object $object;

    /**
     * Empty array only for fill purposes.
     *
     * @var array
     */
    private array $array = [];

    /**
     * Script block.
     *
     * @var array
     */
    public array $script;

    /**
     * Style block.
     *
     * @var array
     */
    public array $styles;

    /**
     * Event block.
     *
     * @var array
     */
    public array $events;

    /**
     * Transform file into an valid Ass object.
     *
     * @return $this
     */
    private function transform(): self
    {
        // An empty object is establish in this method
        // because PHP not admit empty object in declaration.
        $this->object = (object) [];

        $this->scriptBlock();

        $this->extractBlock(Blocks::STYLES);

        $this->extractBlock(Blocks::EVENTS);

        $this->clean();

        return $this;
    }

    /**
     * Process script block to ass attribute object.
     *
     * @return void
     */
    private function scriptBlock(): void
    {
        foreach ($this->{Blocks::SCRIPT} as $line) {
            // Delete dummy lines with this condition.
            if (!contains($line, Delimiters::SEMICOLON)) {
                $this->setScriptAttribute($line);
            }
        }

        $this->{Blocks::SCRIPT} = (array) $this->object;

        $this->reset();
    }

    /**
     * Split script line and establish into an empty object.
     *
     * @param string $line
     */
    private function setScriptAttribute(string $line): void
    {
        $splitLine = explode(Delimiters::COLON_WITH_SPACE, $line);

        $this->object->{$this->formatAttribute($splitLine[0])} = $splitLine[1];
    }

    /**
     * Fill all block and establish it into an object attribute.
     * The name of the block is given by the block.
     *
     * @param string $block
     */
    private function extractBlock(string $block): void
    {
        $this->block = $block;

        foreach ($this->$block as $item) {
            if ($this->isNotFirstLine($item)) {
                foreach ($this->extractValues($item) as $index => $style) {
                    $this->object->{$this->extractAttributes()[$index]} = $style;
                }
                $this->array[] = $this->object;
                $this->reset();
            }
        }
        $this->$block = $this->array;
        $this->array = [];

        $this->reset();
    }

    /**
     * Handle the first line of the block and
     * extract all attributes contains on it.
     *
     * @return array
     */
    private function extractAttributes(): array
    {
        $firstLine = reset($this->{$this->block});

        $erasedLine = str_replace(Lines::HEADERS, '', $firstLine);

        $splitLine = explode(Delimiters::COMMA_WITH_SPACE, $erasedLine);

        return array_map([$this, 'formatAttribute'], $splitLine);
    }

    /**
     * Format the attribute line without spaces
     * and with the first letter into lowercase.
     *
     * @param string $string
     *
     * @return string
     */
    private function formatAttribute(string $string): string
    {
        return str_replace(' ', '', lcfirst($string));
    }

    /**
     * Check if is the line is the first
     * in the array block.
     *
     * @param string $line
     *
     * @return bool
     */
    private function isNotFirstLine(string $line): bool
    {
        return reset($this->{$this->block}) !== $line;
    }

    /**
     * Extract all values contained into the line.
     *
     * @param string $line
     *
     * @return array
     */
    private function extractValues(string $line): array
    {
        $header = '';
        $limit = -1;

        switch ($this->block) {
            case Blocks::STYLES:
                $header = Lines::STYLE;
                break;
            case Blocks::EVENTS:
                $header = Lines::DIALOGUE;
                $limit = 10;
                break;
        }

        return explode(Delimiters::COMMA, str_replace($header, '', $line), $limit);
    }

    /**
     * Reset the object attribute
     * in a new empty object.
     *
     * @return void
     */
    private function reset(): void
    {
        $this->object = (object) [];
    }

    /**
     * Destroy aux variables.
     *
     * @return void
     */
    private function clean(): void
    {
        unset($this->object, $this->array);
    }
}
