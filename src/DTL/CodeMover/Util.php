<?php

namespace DTL\CodeMover;

class Util
{
    const REGEX_DELIMITER = '/';

    public static function delimitRegex($pattern)
    {
        if (substr($pattern, 0, 1) == self::REGEX_DELIMITER ) {
            return $pattern;
        }

        return self::REGEX_DELIMITER.$pattern.self::REGEX_DELIMITER;
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
