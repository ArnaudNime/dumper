<?php

namespace DebugHandler;

use DebugHandler\utility\AddHTML;
use DebugHandler\utility\Metadata;

class DisplayClass extends DisplayObject
{
    private $className;
    private $parentClassName;

    public function __construct($className)
    {
        $this->className = $className;
        $reflectionClass = new \Reflectionclass($className);

        $this->parentClassName = $reflectionClass->getParentClass();

        $this->phpDoc = Metadata::PHPDocs($reflectionClass->getdoccomment());
        $methods = $reflectionClass->getMethods();
        asort($methods);

        $this->constants = parent::setConstants($reflectionClass);
        $this->properties = $this->setPropertiesClass($reflectionClass, $className);
        $this->signatures = parent::setSignatures($className, $methods);
        $this->interfaces = parent::setInterfaces($reflectionClass);
        $this->traits = parent::setTraits($reflectionClass);
    }

    protected function setPropertiesClass($reflectionClass, $class)
    {
        $properties= [];
        $defaultProperties = $reflectionClass->getDefaultProperties();
        foreach ($reflectionClass->getProperties() as $property) {
            $reflectionProperty = $property;

            $defaultValue = Metadata::get($defaultProperties[$property->name]);

            $propertyVisibility = parent::getVisibilityProperty($reflectionProperty);
            $isStatic = ($reflectionProperty->isStatic()) ? "static" : null;
            $property = "$propertyVisibility $isStatic $".$reflectionProperty->name;

            $phpdoc = Metadata::PHPDocs($reflectionProperty->getDocComment());

            $properties[]= AddHTML::classSpan($property, "dataDebug")
                . " [$defaultValue] "
                . $phpdoc;
        }

        return $properties;
    }

    public function render()
    {
        if ($this->parentClassName) {
            $parentClassName = $this->parentClassName->name;
            $htmlParentClassName = AddHTML::classSpan($parentClassName, "dataDebug");
            $parentClass = new DisplayClass($parentClassName);
            $injectParentClass = AddHTML::objectDiv($parentClass->render());
            $phpdocParentClass = $parentClass->getPhpDoc();
        }

        return [
            "type" => 'Class',
            "class" => AddHTML::classSpan($this->className, 'dataDebug'),
            "parent" => ($htmlParentClassName ?? "" ) . ($injectParentClass ?? "") . ($phpdocParentClass ?? ""),
            "interfaces" => $this->interfaces,
            "constants" => $this->constants,
            "properties" => $this->properties,
            "methods" => $this->signatures,
            "traits" => $this->traits,
        ];
    }

    public function getPhpDoc()
    {
        return $this->phpDoc;
    }
}
