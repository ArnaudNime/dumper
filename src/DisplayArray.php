<?php

namespace DebugHandler;

use DebugHandler\utility\Metadata;
use DebugHandler\utility\AddHTML;

class DisplayArray
{
    private $level = 0;
    private $objectInArray;
    public static $nbObject = 0;
    private $numObject;
    private $array;
    public static $data = [];
    private static $indentation = "";

    public function __construct($array, $objectInArray = false)
    {
         $this->array = $array;
         $this->objectInArray = $objectInArray;
         self::$nbObject++;
         $this->numObject = self::$nbObject;
    }
    public function isInArray($array)
    {
        static $iterator = [];
        $iterator[$this->level] = ['position' => 0, 'size' => count($array)];
        foreach ($array as $key => $value) {
            $this->changeLevel($iterator);
            ++$iterator[$this->level]['position'];
            self::$indentation = str_repeat(".......", $this->level);
            self::$indentation = "<span class='hiddenIndent'>".self::$indentation."</span>";
            if ($this->level!=0) {
                self::$indentation.="<i class='fa fa-chevron-circle-right' aria-hidden='true'></i>";
            }
            $metadata = Metadata::get($value, $this->objectInArray);
            $metadataFormated = Metadata::caseAssociatedArray(
                $key,
                $metadata,
                self::$indentation,
                $this->objectInArray
            );
            if (is_object($value)) {
                $displayObject = new DisplayObject($value, true, true);
                $metadataFormated .= AddHTML::objectDiv($displayObject->render());
            }
            self::$data[$this->numObject][] = $metadataFormated;
            if (is_array($value)) {
                ++$this->level;
                $nextLevel = ['position' => 0,'size' => count($value)];
                $iterator[$this->level]= $nextLevel;
                $this->isInArray($value);
            }
            if ($this->numObject == 200) {
                var_dump($metadataFormated);
                die;
            }
        }
        return self::$data[$this->numObject];
    }
    public function render()
    {
        if (empty($this->array)) {
            return "";
        }
        $dataArray = $this->isInArray($this->array);
        $html = "";
        foreach ($dataArray as $data) {
            $html.= "<div>$data</div>";
        }
        return $html;
    }
    private function changeLevel($iterator)
    {
        if ($this->level == 0) {
            return 0;
        }
        if ($iterator[$this->level]["position"] == $iterator[$this->level]["size"]) {
             $this->level--;
             $this->changeLevel($iterator);
        }
    }
}
