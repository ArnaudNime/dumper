<?php

namespace DebugHandler;

use DebugHandler\utility\Metadata;

class DisplayInterface extends DisplayClass
{
    public function __construct($interfaceName)
    {
        $reflectionInterface = new \Reflectionclass($interfaceName);
        $this->phpDoc = Metadata::PHPDocs($reflectionInterface->getdoccomment());
        $methods = $reflectionInterface->getMethods();
        asort($methods);
        $this->signatures = parent::setSignatures($interfaceName, $methods);
    }
    public function render()
    {
        return $this->signatures;
    }
    public function getPhpDoc()
    {
        return $this->phpDoc;
    }
}
