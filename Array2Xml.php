<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

/**
 * Class Array2Xml
 * @package conquer\helpers
 * @author Andrey Borodulin
 */
class Array2Xml
{
    private static function toXml($array)
    {
        $xml = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_int($key) || is_numeric($key)) {
                    $key = 'element';
                }
                static::checkName($key);
                if (is_array($value)) {
                    $xml .= "<$key>" . static::toXml($value) . "</$key>";
                } elseif (strlen(trim((string)$value)) === 0) {
                    $xml .= "<$key/>";
                } else {
                    $xml .= "<$key>" . htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8') . "</$key>";
                }
            }
        }
        return $xml;
    }

    /**
     * Ensures the string is a valid XML element name, so that array keys cannot inject markup.
     * @param string $name
     * @throws \InvalidArgumentException
     */
    protected static function checkName($name)
    {
        if (!is_string($name) || !preg_match('/^[\p{L}_][\p{L}\p{N}_.\-]*$/u', $name)) {
            throw new \InvalidArgumentException('Invalid XML element name: ' . var_export($name, true));
        }
    }

    /**
     * Converts PHP array to xml.
     * Numeric keys become `<element>` tags; any other key must be a valid XML element name,
     * otherwise an \InvalidArgumentException is thrown.
     * @param array $array
     * @param string $rootTag
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function encodeXml($array, $rootTag = 'root')
    {
        static::checkName($rootTag);
        $xml = static::toXml($array);
        return "<$rootTag>$xml</$rootTag>";
    }
}