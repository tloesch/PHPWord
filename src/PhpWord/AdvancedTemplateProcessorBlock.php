<?php

namespace PhpOffice\PhpWord;

class AdvancedTemplateProcessorBlock
{

    protected $content = "";
    protected $lastContent = "";
    protected $parent = null;
    protected $childIndex = 0;
    protected $blocks = [];

    public function __construct($blockContent, $parent = null, $childIndex = 0) {
        $this->lastContent = $this->content = $blockContent;
        $this->parent = $parent;
        $this->childIndex = $childIndex;
    }

    public function getChildIndex() {
        return $this->childIndex;
    }

    public function getContent() {
        return $this->content;
    }

    public function getLastContent() {
        return $this->lastContent;
    }

    public function getName() {
        $content = $this->getContent();
        $start = strpos($content, '${') + 2;
        $length = strpos($content, "}") - $start;
        return substr($content, $start, $length);
    }

    public function getBlock($blockname, $index = -1) {
        if(!isset($this->blocks[$blockname])) {
            $this->blocks[$blockname] = [];
            $regex = '/\${'. $blockname .'}(.*)\${\/'. $blockname .'}/U';
            preg_match_all($regex, $this->getContent(), $matches);

            if(count($matches) === 2) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $this->blocks[$blockname][] = new AdvancedTemplateProcessorBlock($matches[0][$i], $this, $i);
                }
            }
        }
        if($index == -1) {
            return $this->blocks[$blockname];
        }
        return $this->blocks[$blockname][$index];
    }

    public function replaceBlock($blockToReplace, $blockContent, $applyToParent = true) {
        $this->replaceBlockByIndex($blockToReplace->getName(), $blockToReplace->getChildIndex(), $blockContent, $applyToParent);

    }

    public function replaceBlockByIndex($blockToReplaceName, $index, $blockContent, $applyToParent = true) {
        $block = $this->blocks[$blockToReplaceName][$index];

        $lastContentExists = strpos($this->getContent(), $block->getLastContent()) !== FALSE;
        $content = $block->getContent();
        if($lastContentExists) {
            $content = $block->getLastContent();
        }
        $this->setContent(str_replace($content, $block->ensureBlockTagsExists($blockContent), $this->getContent()));
        $block->setContent($blockContent);

        $applyToParent && $this->applyContentToParent();
    }

    public function deleteBlock($block, $applyToParent = true) {
        $this->replaceBlock($block, '', $applyToParent);
        unset($block);
    }

    public function ensureBlockTagsExists($content) {
        $blockname = $this->getName();
        $startTag = '${'.$blockname.'}';
        $endTag = '${/'.$blockname.'}';
        $start = strpos($content, $startTag) !== FALSE;
        $end = strpos($content, $endTag) !== FALSE;
        if(!$start) {
            $content = $startTag . $content;
        }
        if(!$end) {
            $content .= $endTag ;
        }
        return $content;
    }

    protected function setContent($content) {
        $content = $this->ensureBlockTagsExists($content);
        $this->lastContent = $this->content;
        $this->content = $content;
    }



    public function setValue($search, $replace, $applyToParent = true) {
        $text = $this->getBody();
        $newText = str_replace($search, $replace, $text);
        $this->setContent(str_replace($text, $newText, $this->getContent()));
        foreach($this->blocks as $blockCollection) {
            foreach($blockCollection as $block) {
                $block->setValue($search, $replace);
            }
        }
        $applyToParent && $this->applyContentToParent();
    }

    public function applyContentToParent() {
        $this->parent && $this->parent->replaceBlock($this, $this->getContent());
    }

    public function getBody() {
        $blockname = $this->getName();
        $startTag = '${'.$blockname.'}';
        $endTag = '${/'.$blockname.'}';
        $content = $this->getContent();
        $start = strpos($content, $startTag) + strlen($startTag);
        $length = strpos($content, $endTag) - $start;
        return substr($content, $start, $length);
    }

    public function getText() {
        return preg_replace('/\${[^}]*}/', "", $this->getContent());
    }

    public function getParent() {
        return $this->parent;
    }

    public function __destruct() {
        foreach($this->blocks as $block) {
            unset($block);
        }
    }


}