<?php

declare(strict_types=1);

namespace Aegisub\Enums;

class Monosyllables extends Enum
{
    public const REAL = [
        '[cfglp](r|u)?(í|i)(é|e|á|a|ó|o)i?s?', '[fh](l|r)?uís?', '[fr]r?iáis?(\s+|$)', '[vdfnt](í|i|á|u|ú)(ó|o|í|i)?',
        '[i]ón', '[mr]u(á|ó)n', '[t]ruhán', '[p]rión', '[psg]u?ión',
    ];
    public const POSSIBLE = ['[fr]r?(í|i)(ó|o)', '[m](á|a)s', '[s](í|i)', '[t](ú|u)', '[e|é]l', '[a](ú|u)n'];
}
