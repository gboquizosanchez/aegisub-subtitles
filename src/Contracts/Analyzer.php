<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Enums\Delimiters;
use Aegisub\Enums\Monosyllables;
use Aegisub\Enums\Tags;
use Aegisub\Logger;
use ReflectionException;
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
     * Auxiliary variable that is needed in order to use styles, events...
     * In order to established as collection instead of array.
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
        $this->printer();
    }

    /**
     * Establish an auxiliar var.
     *
     * @return void
     */
    private function auxiliar(): void
    {
        $this->auxiliary = (object)[];

        foreach (['styles', 'events'] as $type) {
            $this->auxiliary->$type = collect($this->$type);
        }
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
     * @param  string  $string
     * @param  string  $delimiter
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
        $unused = $this->unusedStyles();
        $fonts = $this->fonts();

        $unused->isEmpty()
            ? $this->analyzerLog->write('There are no styles', 'center')
            : $this->analyzerLog->write("Unused  ➡ {$unused->implode(', ')}");

        $fonts->isEmpty()
            ? $this->analyzerLog->write('There are no fonts', 'center')
            : $this->analyzerLog->write("Fonts   ➡ {$fonts->implode(', ')}");

        $this->analyzerLog->write("Opening ➡ {$this->searchOnStyleName('OP')}");
        $this->analyzerLog->write("Ending  ➡ {$this->searchOnStyleName('ED')}");
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
     * Extract font names.
     *
     * @return Collection
     */
    private function fonts(): Collection
    {
        return $this->auxiliary->styles->pluck('fontname')->filter();
    }

    /**
     * Search word on style name and count all of it.
     *
     * @param  string  $needle
     *
     * @return int|null
     */
    private function searchOnStyleName(string $needle): ?int
    {
        return $this->auxiliary->styles->map(fn ($style): bool => contains($style->name, $needle))->filter()->count();
    }

    /**
     * Print all related about lines section.
     *
     * @return void
     */
    private function printLines(): void
    {
        $this->analyzerLog->write("Commented      ➡ {$this->commented()}");
        $this->analyzerLog->write("Not blurred    ➡ {$this->notBlurred()}");
        $this->analyzerLog->write("Unsynchronized ➡ {$this->unsynchronized()}");
        $this->analyzerLog->write("Monosyllables  ➡ {$this->searchOnEvents('monosyllable')}");
        $this->analyzerLog->write("Stuttering     ➡ {$this->searchOnEvents('stuttering')}");
    }

    /**
     * Give all commented lines.
     *
     * @return int
     */
    private function commented(): int
    {
        return $this->auxiliary->events->map(fn ($event) => $event->format === 'Comment')->filter()->count();
    }

    /**
     * Check what amount of lines are not blurred.
     *
     * @return int
     */
    private function notBlurred(): int
    {
        return $this->auxiliary->events->count() - $this->searchOnEvents('blurred');
    }

    /**
     * Search on events lines.
     *
     * @param $function
     *
     * @return int
     */
    private function searchOnEvents(string $function): int
    {
        $function = 'is' . ucfirst($function);

        return $this->auxiliary->events->map(fn ($event) => $this->$function($event->text))->filter()->count();
    }

    /**
     * Check if a line is blurred.
     *
     * @param $text
     *
     * @return bool
     * @throws ReflectionException
     *
     */
    private function isBlurred($text): bool
    {
        return $this->search(Tags::getValues(), $text);
    }

    /**
     * Check if all lines are in order.
     *
     * @return int
     */
    private function unsynchronized(): int
    {
        $prev = (object)[];

        $counter = 0;

        foreach ($array = $this->events as $key => $event) {
            if (($key !== array_key_first($array)) && strtotime($event->start) < strtotime($prev->end)) {
                $counter++;
            }
            $prev = $event;
        }

        return $counter;
    }

    /**
     * Search a pattern in a text.
     *
     * @param  array|string  $values
     * @param  string  $text
     *
     * @return bool
     */
    private function search($values, string $text): bool
    {
        if (is_string($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if (preg_match("/{$value}/", $text, $matches)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if is a monosyllable with unnecessary accent.
     *
     * @param $text
     *
     * @return bool
     */
    private function isMonosyllable($text): bool
    {
        $values = array_map(
            static function ($value): string {
                $modifiers = '|\'|"|,|\.|}|—|-|';

                return "(?:\s+{$modifiers}^)(?<monosyllable>{$value})(?:\s+$modifiers$)";
            },
            Monosyllables::REAL
        );

        foreach ($values as $value) {
            if (preg_match("/{$value}/i", $text, $matches)
                && preg_match('/[áéíóúÁÉÍÓÚ]/u', $matches['monosyllable'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a line is stuttering.
     *
     * @param $text
     *
     * @return bool
     */
    private function isStuttering($text): bool
    {
        $upperCase = 'A-ZAÁÉÍÓÚ';

        return $this->search("[{$upperCase}]u?-[{$upperCase}]u?", $text);
    }
}
