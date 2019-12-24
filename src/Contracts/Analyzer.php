<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Enums\Delimiters;
use Aegisub\Enums\Tags;
use Aegisub\Logger;
use Tightenco\Collect\Support\Collection;

trait Analyzer
{
    /**
     * This logger is used to write in quality.txt.
     *
     * @var Logger
     */
    private Logger $analyzerLog;

    /**
     * Auxiliary variable to print into quality.txt
     * once .ass is analyzed.
     *
     * @var object
     */
    private object $checked;

    /**
     * Another auxiliary variable that is needed in order to use
     * styles, events... to established as collection instead of array.
     *
     * @var object
     */
    private object $auxiliary;

    /**
     * Start analyzing .ass.
     *
     * @return void
     */
    public function analyze(): void
    {
        $this->analyzerLog = (new Logger('quality', 'txt'));
        $this->run();
    }

    /**
     * Make all needed before print.
     *
     * @return void
     */
    private function run(): void
    {
        $this->auxiliar();
        $this->handleAss();
        $this->printer();
    }

    /**
     * Establish an auxiliar var.
     *
     * @return void
     */
    private function auxiliar(): void
    {
        $this->auxiliary = (object) [];

        foreach (['styles', 'events'] as $type) {
            $this->auxiliary->$type = collect($this->$type);
        }
    }

    /**
     * Establish all needed to be printed.
     *
     * @return void
     */
    private function handleAss(): void
    {
        $this->checked = (object) [];
        $this->checked->unusedStyles = $this->unusedStyles();
        $this->checked->fonts = $this->auxiliary->styles->pluck('fontname')->filter();
        $this->checked->opening = $this->searchOnStyleName('OP');
        $this->checked->ending = $this->searchOnStyleName('ED');
        $this->checked->notBlurred = $this->notBlurred();
    }

    /**
     * Extract unused styles.
     *
     * @return Collection
     */
    private function unusedStyles(): Collection
    {
        $stylesUsed = $this->auxiliary->events->pluck('style')->unique();

        return $this->auxiliary->styles->pluck('name')->diff($stylesUsed)->filter();
    }

    /**
     * Search word on style name and count all of it.
     *
     * @param string $needle
     *
     * @return int|null
     */
    private function searchOnStyleName(string $needle): ?int
    {
        return $this->auxiliary->styles->map(fn ($style): bool => contains($style->name, $needle))->filter()->count();
    }

    /**
     * Check what amount of lines are not blurred.
     *
     * @return int
     */
    private function notBlurred(): int
    {
        $blurred = $this->auxiliary->events->map(fn ($event) => contains($event->text, Tags::BLUR))->filter()->count();

        return $this->auxiliary->events->count() - $blurred;
    }

    /**
     * Print all quality.txt file.
     *
     * @return void
     */
    private function printer(): void
    {
        $this->printSection('Quality Analyzer', Delimiters::EQUALS);
        $this->analyzerLog->writeSeparator(Delimiters::PIPE);
        $this->printSection('Styles', Delimiters::EQUALS);
        $this->printStyles();
        $this->printSection('Lines', Delimiters::EQUALS);
        $this->printLines();
        $this->analyzerLog->writeSeparator(Delimiters::HYPHEN);
        $this->analyzerLog->writeSeparator(Delimiters::EQUALS);
    }

    /**
     * Print a section by a delimiter given.
     *
     * @param string $string
     * @param string $delimiter
     *
     * @return void
     */
    private function printSection(string $string, string $delimiter): void
    {
        $this->analyzerLog->writeSeparator($delimiter);
        $this->analyzerLog->write($string, 'center');
        $this->analyzerLog->writeSeparator($delimiter);
    }

    /**
     * Print all related about styles section.
     *
     * @return void
     */
    private function printStyles(): void
    {
        $this->checked->unusedStyles->isEmpty()
            ? $this->analyzerLog->write('There are no styles', 'center')
            : $this->analyzerLog->write('Unused  ➡ '.$this->checked->unusedStyles->implode(', '));

        $this->checked->fonts->isEmpty()
            ? $this->analyzerLog->write('There are no fonts', 'center')
            : $this->analyzerLog->write('Fonts   ➡ '.$this->checked->fonts->implode(', '));

        $this->analyzerLog->write("Opening ➡ {$this->checked->opening}");
        $this->analyzerLog->write("Ending  ➡ {$this->checked->ending}");
    }

    /**
     * Print all related about lines section.
     *
     * @return void
     */
    private function printLines(): void
    {
        $this->analyzerLog->write("Not blurred ➡ {$this->checked->notBlurred}");
    }
}
