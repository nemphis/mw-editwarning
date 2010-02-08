<?php

/**
 * Implementation of EditWarningMessage class.
 *
 * This file is part of the MediaWiki extension EditWarning. It contains
 * the implementation of EditWarningMessage class responsible for loading
 * the templates, inserting values and outputting the HTML code.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Thomas David <nemphis@code-geek.de>
 * @copyright   2007-2010 Thomas David <nemphis@code-geek.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later
 * @version     0.4-beta
 * @category    Extensions
 * @package     EditWarning
 */

/**
 * Error classes for EditWarningMessage
 */
class LoadTemplateException extends Exception {

}
class NoTemplateContentFoundException extends Exception {

}

abstract class EditWarningMessage {
    private $_content;
    private $_labels = array();

    public function setContent( $content ) {
        $this->_content = $content;
    }

    public function getContent() {
        return $this->_content;
    }

    public function addLabel( $label, $value ) {
        $this->_labels[$label] = $value;
    }

    public function setMsg( $msg, $params ) {
        $this->_labels['MSG'] = wfMsgReal( $msg, $params, true );
    }

    public function getLabels() {
        return $this->_labels;
    }

    public function loadTemplate( $file_name ) {
        try {
            $file = fopen( $file_name, "r" );
            $this->setContent( fread( $file, filesize( $file_name ) ) );
        } catch( Exception $e ) {
            throw new LoadTemplateException( $e );
        }
        fclose( $file );
    }

    /**
     * Replaces labels in template content with associated values.
     *
     * @access public
     */
    public function processTemplate() {
        $content = $this->getContent();

        if ( $content == null ) {
            throw new NoTemplateContentFoundException(
                "No template content found. You should load a template first."
            );
        }

        foreach( $this->getLabels() as $label => $value ) {
            $content = preg_replace(
                    "/{{{" . $label . "}}}/",
                    $value,
                    $content
            );
        }

        return $content;
    }

    /**
     * Output the HTML code.
     *
     * @access public
     */
    public function show() {
        global $wgOut;

        $content = $this->processTemplate();
        $wgOut->addHtml( $content );
    }
}










