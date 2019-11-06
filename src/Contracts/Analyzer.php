<?php

declare(strict_types=1);

namespace Aegisub\Contracts;

use Aegisub\Logger;

trait Analyzer
{
    private $analyzerLog;

    public function analyze()
    {
        $this->analyzerLog = (new Logger('quality', 'txt'));
        $this->fontsAndStyles();
    }

    private function fontsAndStyles()
    {
        $styles = collect($this->styles)->map(static function ($style) {
            return ['fontname' => $style->fontname, 'stylename' => $style->name];
        });

        $uses = collect($this->events)->pluck('style')->unique();

        $this->analyzerLog->write("Styles never used\n¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯");

        foreach ($styles->pluck('stylename')->diff($uses) as $style) {
            $this->analyzerLog->write($style);
        }
    }
}
