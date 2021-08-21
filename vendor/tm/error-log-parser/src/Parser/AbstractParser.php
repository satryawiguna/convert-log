<?php

namespace TM\ErrorLogParser\Parser;

use TM\ErrorLogParser\Exception\FormatException;

/**
 * Class AbstractParser
 *
 * @package TM\ErrorLogParser\Parser
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * @param string $line
     *
     * @return \stdClass
     * @throws FormatException
     */
    public function parse($line)
    {
        $object = new \stdClass();
        $matches = [];

        foreach ($this->getPatterns() as $key => $pattern) {
            $result = preg_match($pattern, $line, $matches);

            if (1 !== $result) {
                continue;
            }

            $object->$key = $matches[1];
        }

//        if (false === property_exists($object, 'date')) {
//            throw new FormatException;
//        }

        if (false === property_exists($object, 'type')) {
            throw new FormatException;
        }

        return $object;
    }
}
