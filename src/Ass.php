<?php

namespace Aegisub;

class Ass
{
    protected const SCRIPT_BLOCK = "﻿[Script Info]";
    protected const GARBAGE_BLOCK = "[Aegisub Project Garbage]";
    protected const STYLES_V4 = "[V4 Styles]";
    protected const STYLES_V4PLUS = "[V4+ Styles]";
    protected const EVENTS = "[Events]";
    protected const EXTRA_DATA = "[Aegisub Extradata]";
    protected const HEADERS = "Format: ";
    public $scriptInfoHeaders = [];
    public $scriptInfo = [];
    public $stylesHeaders;
    public $styles = [];
    public $eventsHeaders;
    public $events = [];
    protected $block;
    protected $change;

    public function parse($data)
    {
        if ($this->isASS($data)) {
            foreach ($data as $line) {

                ! $this->isChangeBlock() ?: $this->setBlock($this->checkBlock($line));

                empty($line) ?: $this->extractBlock($line);

                $this->checkChangeBlock($line);
            }
            return $this->cleaner();
        }
        
        throw new \Aegisub\Exceptions\FileNotValidException("It is not a valid file.");
    }

    private function isASS($data)
    {
        if ($data[0] === self::SCRIPT_BLOCK) {
            $this->setBlock($this->checkBlock($data[0]));
            return true;
        }
        return false;
    }

    private function setBlock($block)
    {
        $this->block = $block;
        $this->change = false;
    }

    private function checkBlock($line)
    {
        switch ($line) {
            case self::SCRIPT_BLOCK:
                return 1;
            case self::GARBAGE_BLOCK:
                return 2;
            case self::STYLES_V4:
            case self::STYLES_V4PLUS:
                return 3;
            case self::EVENTS:
                return 4;
            case self::EXTRA_DATA:
                return 5;
            default :
                return -1;
        }
    }

    private function isChangeBlock()
    {
        return $this->change;
    }

    private function extractBlock($line)
    {
        switch ($this->getBlock()) {
            case 1:
                $this->setInfo($line);
                break;
            case 3:
                $this->setStyles($line);
                break;
            case 4:
                $this->setEvents($line);
                break;
        }
    }

    private function getBlock()
    {
        return $this->block;
    }

    private function setInfo($line)
    {
        if ($line !== self::SCRIPT_BLOCK && $line[0] !== ";") {
            $this->setScript($line);
        }
    }

    private function setScript($line)
    {
        foreach ($this->explodeLine($line, ': ') as $key => $value) {
            $key === 0 ? $this->setScriptHeaders($value) : $this->setScriptLine($value);
        }
    }

    private function explodeLine($line, $delimiter, $limit = false)
    {
        return ! $limit ? explode($delimiter, $line) : explode($delimiter, $line, $limit);
    }

    private function setScriptHeaders($line)
    {
        array_push($this->scriptInfoHeaders, $line);
    }

    private function setScriptLine($line)
    {
        array_push($this->scriptInfo, $line);
    }

    private function setStyles($line)
    {
        if ($line !== self::STYLES_V4PLUS && $line !== self::STYLES_V4) {
            str_contains($line, self::HEADERS)
                ? $this->setStylesHeaders($this->explodeLine($this->explodeLine($line, ': ')[1], ', '))
                : $this->setStylesLine($this->explodeLine($this->explodeLine($line, ": ")[1], ','));
        }
    }

    private function setStylesHeaders($line)
    {
        $this->stylesHeaders = $line;
    }

    private function setStylesLine($line)
    {
        array_push($this->styles, $line);
    }

    private function setEvents($line)
    {
        if ($line !== self::EVENTS) {
            str_contains($line, self::HEADERS)
                ? $this->setEventsHeaders($this->explodeLine($this->explodeLine($line, ': ')[1], ', '))
                : $this->setEventsLine($this->explodeLine($this->explodeLine($line, ": ")[1], ',', 10));
        }
    }

    private function setEventsHeaders($line)
    {
        $this->eventsHeaders = $line;
    }

    private function setEventsLine($line)
    {
        array_push($this->events, $line);
    }

    private function checkChangeBlock($line)
    {
        $this->change = empty($line) ? true : false;
    }

    private function cleaner()
    {
        unset($this->block);
        unset($this->change);
        return $this;
    }
}
