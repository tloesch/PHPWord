<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2018 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord;

use PhpOffice\Common\Text;
use PhpOffice\PhpWord\Escaper\RegExp;
use PhpOffice\PhpWord\Escaper\Xml;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\Shared\ZipArchive;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\AdvancedTemplateProcessorBlock;


class AdvancedTemplateProcessor extends TemplateProcessor
{


    protected $blocks = [];

    public function getBlock($blockname, $index = -1) {
        if(!isset($this->blocks[$blockname])) {
            $this->blocks[$blockname] = [];
            $regex = '/\${'. $blockname .'}(.*)\${\/'. $blockname .'}/U';
            preg_match_all($regex, $this->tempDocumentMainPart, $matches);

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
//
//    public function getBlock($blockname, $index = -1/*, $source = null*/) {
////        if( $source == null) {
////            $regex = '/(<\?xml.*)(<w:p.*>\${' . $blockname . '}<\/w:.*?p>)(.*)(<w:p.*\${\/' . $blockname . '}<\/w:.*?p>)/U';
////            $source = $this->tempDocumentMainPart;
////        } else {
////            $regex = '/\${'. $blockname .'}(.*)\${\/'. $blockname .'}/U';
////        }
//        $regex = '/\${'. $blockname .'}(.*)\${\/'. $blockname .'}/U';
//       // $source = '${test}hallo${/test}';
//        preg_match_all ($regex, $this->tempDocumentMainPart, $matches);
//
//        $contents = null;
///*
//        if (isset($matches[3])) {
//            $content = $matches[2] . $matches[3] . $matches[4];
//        }
//*/
//        if(count($matches) === 2 /*&& ($source != $this->tempDocumentMainPart)*/) {
//            $contents =  $matches[0];
//        }
//        $blocks = [];
//        if($contents) {
//            foreach($contents as $content) {
//                $blocks[] = new AdvancedTemplateProcessorBlock($content);
//            }
//        }
//        if(count($blocks)  <= 0) {
//            return null;
//        }
//        if($index == -1) {
//            return $blocks;
//        }
//        return $blocks[$index];
//
//    }


    /**
     * Replace a block.
     *
     * @param string $blockname
     * @param string $replacement
     */
    public function replaceBlock($block, $replacement)
    {
        preg_match_all(
            '/\${'. $block->getName() .'}(.*)\${\/'. $block->getName() .'}/U',
            $this->tempDocumentMainPart,
            $matches
        );

        if (isset($matches[1])) {
            $this->tempDocumentMainPart = str_replace(
                $matches[0][0],
                $replacement,
                $this->tempDocumentMainPart
            );
        }
    }

}


