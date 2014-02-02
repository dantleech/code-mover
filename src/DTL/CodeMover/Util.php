<?php

namespace DTL\CodeMover;

use Symfony\Component\Finder\Expression\Regex;

class Util
{
    const REGEX_DELIMITER_OPEN = '{';
    const REGEX_DELIMITER_CLOSE = '}';

    public static function delimitRegex($pattern)
    {
        try {
            Regex::create($pattern);

            return $pattern;
        } catch (\InvalidArgumentException $e) {
            return self::REGEX_DELIMITER_OPEN.$pattern.self::REGEX_DELIMITER_CLOSE;
        }
    }

    public static function tokenTypeIntToString($type)
    {
        $constants = get_defined_constants(true);
        $tokenMap = array_flip($constants['tokenizer']);

        if (!isset($tokenMap[$type])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown token type integer "%s"', $type
            ));
        }

        return substr($tokenMap[$type], 2);
    }

    public static function tokenTypeStringToInt($type)
    {
        if (substr($type, 0, 2) != 'T_') {
            $type = 'T_'.$type;
        }

        $constants = get_defined_constants(true);
        $tokenMap = $constants['tokenizer'];

        if (!isset($tokenMap[$type])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown token type string "%s"', $type
            ));
        }

        return $tokenMap[$type];
    }

    public static function tokenNormalizeTypeToString($type)
    {
        if (is_numeric($type)) {
            return self::tokenTypeIntToString($type);
        }

        return $type;
    }
}
