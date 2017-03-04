<?php

namespace DebugHandler\utility;

class AddHTML
{
    public static function classSpan($variable, string $cssClass = null)
    {
        if (!empty($variable)) {
             $class = (empty($cssClass) ? "" : "class='$cssClass'");
             return "<span $class>$variable</span>";
        }
        return "";
    }
    public static function classDiv($variable, string $cssClass = null)
    {
        if (!empty($variable)) {
             $class = (empty($cssClass) ? "" : "class='$cssClass'");
             return "<div $class>$variable</div>";
        }
        return "";
    }
    public static function injectDiv($display, $class = 'injectArray')
    {
        $injectDisplayArray = "<div class = '".$class."' >$display</div>";
        return "<i class='btn-array fa fa-arrow-circle-down' aria-hidden='true'></i>$injectDisplayArray";
    }
    public static function injectPhpDoc($display)
    {
        $injectPhpDoc = "<div class='phpdoc'>$display</div>";
        return "<i class='btn-phpdoc fa fa-plus-circle' aria-hidden='true'></i>$injectPhpDoc";
    }
    public static function objectDiv($dataObject)
    {
        if (!is_array($dataObject)) {
            return "UNKNOW";
        }
        try {
            $html = "<i class='btn-object fa fa-arrow-circle-down' aria-hidden='true'></i><div class='injectObject'>";
            $html = self::addPartClass($html, $dataObject['parent'], "Parent");
            $html = self::addPartClass($html, $dataObject['interfaces'], "Interfaces");
            $html = self::addPartClass($html, $dataObject['constants'], "Constantes");
            $html = self::addPartClass($html, $dataObject['properties'], "Attributs");
            $html = self::addPartClass($html, $dataObject['methods'], "Méthodes");
            $html = self::addPartClass($html, $dataObject['traits'], "Traits");
            $html.="</div>";
            return $html;
        } catch (\Exception $e) {
            return "UNKNOW";
        }
    }
    public static function interfaceDiv($methods)
    {
        if (!empty($methods)) {
            $html = "";
            $html = self::addPartClass($html, $methods, "Méthodes");
            return self::injectDiv($html);
        }
    }
    private function addPartClass($html, $part, $title)
    {
        if (!empty($part)) {
            $title = self::classSpan($title, "valueDebug");
            $html.=self::classDiv($title);
            if (is_array($part)) {
                foreach ($part as $method) {
                    $html.=self::classDiv($method);
                }
            }
            if (is_string($part)) {
                $html.=self::classDiv($part, 'dataDebug');
            }
        }
        return $html;
    }
}
