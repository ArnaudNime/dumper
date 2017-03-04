<?php

namespace DebugHandler\utility;

class Metadata
{
    public static function get($var, $objectInArray = false)
    {
        $type = gettype($var);
        switch ($type) {
            case "boolean":
                return self::getBoolean($type, $var);
                break;
            case "integer":
                return self::getNum($type, $var);
                break;
            case "double":
                return self::getNum($type, $var);
                break;
            case "string":
                return self::getString($var, $objectInArray);
                break;
            case "array":
                return self::getArray($type, $var);
                break;
            case "object":
                return self::getObject($type, $var);
                break;
            case "NULL":
                return $type;
                break;
            default:
                return 'Inconnu';
                break;
        }
    }
    public function caseAssociatedArray($key, $metadata, $indentation, $objectInArray = false)
    {
        if ($objectInArray) {
            return "$indentation $metadata";
        }

        if (is_string($key)) {
            return $indentation.self::colorQuotedData($key).self::colorData(" => "). $metadata;
        }

        return "$indentation ".self::colorData($key." => ")." $metadata";
    }
    public static function PHPDocs($docs)
    {
        if (!empty($docs)) {
            $html = "";
            $docs = substr($docs, 4, -2);
            $listDocs = explode('*', $docs);
            foreach ($listDocs as $doc) {
                $html .= "<div> $doc </div>";
            }

            return AddHTML::injectPhpDoc($html);
        }
    }
    private static function getBoolean($type, $var)
    {
        $value = ($var) ? "TRUE" : "FALSE";
        return " ($type)" . self::colorData($value);
    }
    private static function getNum($type, $var)
    {
        return " ($type)" . self::colorData($var);
    }
    private static function getString($var, $objectInArray = false)
    {
        if ($objectInArray) {
            return self::colorData($var);
        }

        return self::colorQuotedData($var);
    }
    private static function getArray($type, $var)
    {
        $count = count($var);
        return "$type ($count)";
    }
    private static function getObject($type, $var)
    {
        $class = get_class($var);
        return "($type) ".self::colorData($class);
    }
    private static function colorData($value)
    {
        return "<span class='dataDebug'>$value</span>";
    }
    private static function colorQuotedData($value)
    {
        return "<span class='dataDebug'>'".$value."'</span>";
    }
}
