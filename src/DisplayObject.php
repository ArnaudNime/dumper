<?php

namespace DebugHandler;

use DebugHandler\utility\Metadata;
use DebugHandler\utility\AddHTML;

class DisplayObject
{
    private $className;
    private $parentClassName;
    private $object;
    private $reflection;
    protected $phpDoc;
    protected $constants = [];
    protected $traits = [];
    protected $properties = [];
    protected $signatures = [];
    protected $interfaces = [];
    private static $classDisplayed = [];

    public function __construct($object, $objectInArray = false, $reflection = false)
    {
        $this->object = $object;
        $this->className = get_class($object);
        self::$classDisplayed[] =  $this->className;
        $this->parentClassName = get_parent_class($object);
        $this->reflection = $reflection;
        $reflectionClass = new \Reflectionclass($this->className);
        $this->phpDoc = Metadata::PHPDocs($reflectionClass->getdoccomment());
        $methods = $reflectionClass->getMethods();
        asort($methods);

        $this->constants = $this->setConstants($reflectionClass);
        $this->properties = $this->setProperties($reflectionClass, $this->className, $this->object, $objectInArray);
        $this->signatures = $this->setSignatures($this->className, $methods);
        $this->interfaces = $this->setInterfaces($reflectionClass);
        $this->traits = $this->setTraits($reflectionClass);
    }
    public function render()
    {
        $injectParentClass ="";
        $phpdocParentClass = "";
        if ($this->parentClassName) {
            $parentClass = new DisplayClass($this->parentClassName);
            $injectParentClass = AddHTML::objectDiv($parentClass->render());
            $phpdocParentClass = $parentClass->getPhpDoc();
        }
        $htmlParentClass = AddHTML::classSpan($this->parentClassName, "dataDebug").$injectParentClass;
        $htmlClass = AddHTML::classSpan($this->className, "dataDebug");
        return [
             "type" => 'Object',
             "class" => $htmlClass . $this->phpDoc,
             "parent" => $htmlParentClass . $phpdocParentClass,
             "interfaces" => $this->interfaces,
             "constants" => $this->constants,
             "properties" => $this->properties,
             "methods" => $this->signatures,
             "traits" => $this->traits,
         ];
    }
    protected function setConstants($reflectionClass)
    {
        $constants = [];
        foreach ($reflectionClass->getConstants() as $constantName => $constantValue) {
            $htmlDisplay = AddHTML::classSpan($constantName, "dataDebug")." = ".Metadata::get($constantValue);
            $constants[]=$htmlDisplay;
        }
        return $constants;
    }
    protected function setProperties($reflectionClass, $class, $object, $objectInArray = false)
    {
        $properties= [];
        $defaultProperties = $reflectionClass->getDefaultProperties();
        foreach ($reflectionClass->getProperties() as $property) {
            $reflectionProperty = $property;
            if (!$this->reflection) {
                $reflectionProperty = new \ReflectionProperty($class, $property->name);
            }
            $defaultValue = Metadata::get($defaultProperties[$property->name]);
            $propertyVisibility = $this->getVisibilityProperty($reflectionProperty);
            $isStatic = ($reflectionProperty->isStatic()) ? "static" : null;
            $property = "$propertyVisibility $isStatic $".$reflectionProperty->name;
            if ($propertyVisibility != 'public') {
                $reflectionProperty->setAccessible(true);
            }
            $proprietyValue = $reflectionProperty->getValue($object);
            $phpdoc = Metadata::PHPDocs($reflectionProperty->getDocComment());
            $proprietyValueFormated = Metadata::get($proprietyValue);
            $arrowDisplay = "";
            if (is_object($proprietyValue)) {
                $arrowDisplay = $this->verifyRecursion($proprietyValue, $objectInArray);
            }
            if (is_array($proprietyValue) && !empty($proprietyValue)) {
                $displayArray = new DisplayArray($proprietyValue, $objectInArray);
                $test = $displayArray->render();
                $arrowDisplay = AddHTML::injectDiv($test);
            }
            $properties[]= AddHTML::classSpan($property, "dataDebug")
                               . " = $proprietyValueFormated"
                               . $arrowDisplay
                               . " [$defaultValue] "
                               . $phpdoc;
        }
        return $properties;
    }
    private function verifyRecursion($proprietyValue, $objectInArray = false)
    {
        foreach (self::$classDisplayed as $className) {
            if ($className === get_class($proprietyValue)) {
                return " ! **RECURSION** ! ";
            }
        }
        $displayObject = new DisplayObject($proprietyValue, $objectInArray, true);
        return AddHTML::objectDiv($displayObject->render());
    }
    protected function getVisibilityProperty(\ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->isPrivate()) {
            return "private";
        }
        if ($reflectionProperty->isPublic()) {
            return "public";
        }
        if ($reflectionProperty->isProtected()) {
            return "protected";
        }
    }
    protected function setSignatures($reflectionClass, $methods)
    {
        $signatures = [];
        foreach ($methods as $method) {
            $phpDoc = Metadata::PHPDocs($method->getdocComment());
            $visibility = $this->getMethodVisibility($method);
            $isStatic = ($method->isStatic()) ? "static" : null;
            $parameters = $method->getParameters();
            $numberParameters = $method->getNumberOfParameters();
            $arrayParameters = $this->getParametersMethod($parameters);
            $listParameters = implode($arrayParameters, ", ");
            $signatures[] = AddHTML::classSpan("$visibility $isStatic $method->name ($listParameters)", "dataDebug")
                                . " ($numberParameters parameters)"
                                . $phpDoc;
        }

        return $signatures;
    }
    private function getMethodVisibility($classMethod)
    {
        if ($classMethod->isPublic()) {
            return " public ";
        }
        if ($classMethod->isPrivate()) {
            return " private ";
        }
        if ($classMethod->isProtected()) {
            return " protected ";
        }
    }
    protected function setInterfaces($reflectionClass)
    {
        $interfaces = [];
        foreach ($reflectionClass->getinterfacenames() as $interfaceName) {
            $interface = new DisplayInterface($interfaceName);
            $injectInterface = AddHTML::interfaceDiv($interface->render());
            $interfacePhpDoc = $interface->getPhpDoc();
            $interfaces[]= AddHTML::classSpan($interfaceName, "dataDebug") . $injectInterface . $interfacePhpDoc;
        }

        return $interfaces;
    }
    protected function setTraits($reflectionClass)
    {
        $traits = [];
        foreach ($reflectionClass->getTraits() as $trait) {
            $traits[]= AddHTML::classSpan($trait->name, "dataDebug");
        }

        return $traits;
    }
    private function getParametersMethod($parameters)
    {
        $arrayParameters = [];
        foreach ($parameters as $parameter) {
            $arrayParameters[]= "$".$parameter->name;
        }
        return $arrayParameters;
    }
}
