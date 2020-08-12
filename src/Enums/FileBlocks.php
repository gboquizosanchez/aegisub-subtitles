<?php

declare(strict_types=1);

namespace Aegisub\Enums;

class FileBlocks extends Enum
{
    public const SCRIPT = '﻿[Script Info]';
    public const GARBAGE = '[Aegisub Project Garbage]';
    public const STYLES_V4 = '[V4 Styles]';
    public const STYLES_V4PLUS = '[V4+ Styles]';
    public const EVENTS = '[Events]';
    public const EXTRA_DATA = '[Aegisub Extradata]';
}
