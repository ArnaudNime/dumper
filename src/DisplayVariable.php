<?php

namespace DebugHandler;

use Prophecy\Exception\Doubler\ClassCreatorException;
use Twig_Environment;
use Zend\Expressive\Template\Twig;

class DisplayVariable
{
    private $twig;
    private $templateData;

    public static function dbg($variable)
    {
        return new Self($variable);
    }
    private function __construct($variable)
    {
        $loader = new \Twig_Loader_Filesystem('src/App/Debug/views');
        $this->twig = new Twig_Environment($loader);

        if (is_integer($variable)) {
            $this->isNumericValue("Integer", $variable);
        }
        if (is_bool($variable)) {
            $this->isBoolValue($variable);
        }
        if (is_float($variable)) {
            $this->isNumericValue("Decimal", $variable);
        }
        if (is_string($variable)) {
            $this->isStringValue("String", $variable);
        }
        if (is_array($variable)) {
            $this->isArray("Array", $variable);
        }
        if (is_object($variable)) {
            $this->isObject($variable);
        }
        echo $this->template->render($this->templateData);
        die;
    }
    private function isNumericValue(string $type, $value)
    {
        $this->template = $this->twig->load('numeric.html.twig');
        $this->templateData = [
          "type" => $type,
          "value" => $value,
        ];
    }
    private function isStringValue(string $type, $value)
    {
        if (class_exists($value)) {
            $this->template = $this->twig->load('object.html.twig');
            $displayClass = new DisplayClass($value);
            $this->templateData = $displayClass->render($displayClass);
        } else {
            $this->template = $this->twig->load('string.html.twig');
            $this->templateData = [
                "type" => $type,
                "size" => strlen($value),
                "value" => $value,
            ];
        }
    }
    private function isArray(string $type, array $array)
    {
        $this->template = $this->twig->load('array.html.twig');
        $displayArray = new DisplayArray($array);
        $data = $displayArray->render($array);
        $this->templateData = [
            "type" => $type,
            "size" => count($array),
            "data" => $data,
        ];
    }
    private function isObject($object)
    {
        $this->template = $this->twig->load('object.html.twig');
        $displayObject = new DisplayObject($object);
        $this->templateData = $displayObject->render();
    }
    private function isBoolValue(bool $variable)
    {
        $this->template = $this->twig->load('boolean.html.twig');
        $value = ($variable) ? "TRUE" : "FALSE";
        $this->templateData = [
          "type" => "Boolean",
          "data" => $value,
        ];
    }
}
