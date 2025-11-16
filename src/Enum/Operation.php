<?php

declare(strict_types=1);

namespace App\Enum;

enum Operation: string
{
    public const DEFAULT_FILE = "test.csv";

    public const DESC_ACTION = "Add arithemtic operation";
    public const DESC_FILE = "Add resource file (no path is needed)";

    public const OPT_ACTION = "action";
    public const OPT_FILE = 'file';

    public const SHORT_ACTION = "a";
    public const SHORT_FILE = "f";

    case DIVISION = "division";
    case MINUS = "minus";
    case MULTIPLY = "multiply";
    case PLUS = "plus";

    /**
     * @return string[]
     */
    public static function toArray(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
