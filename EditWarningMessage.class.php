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
 * @author		Thomas David <ThomasDavid@gmx.de>
 * @copyright	2007-2009 Thomas David <ThomasDavid@gmx.de>
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later
 * @version		0.4-prealpha
 * @category	Extensions
 * @package		EditWarning
 */

/**
 * Error classes for EditWarningMessage
 */
class LoadTemplateException extends Exception {}
class NoTemplateContentFoundException extends Exception {}

abstract class EWMessage {
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

class EWInfoMessage extends EWMessage {
	public function __construct( $path, $url ) {
		$this->loadTemplate( $path . "/notice.html" );
		$this->addLabel( 'URL', $url );
		$this->addLabel( 'BUTTON_CANCEL', wfMsg( 'ew-button-cancel' ) );
	}
}

class EWCancelMessage extends EWMessage {
	public function __construct( $path ) {
		$this->loadTemplate( $path . "/cancel.html" );
	}
}

class EWWarningMessage extends EWMessage {
	public function __construct( $path, $url ) {
		$this->loadTemplate( $path . "/warning.html" );
		$this->addLabel( 'URL', $url );
		$this->addLabel( 'BUTTON_CANCEL', wfMsg( 'ew-button-cancel' ) );
	}
}

interface EWMessageFactory {
	public static function getInstance( $type, $url, $params );
}

class EWMsg implements EWMessageFactory {
	private static $instance = array();
	
	private function __construct() {}
	
	public static function getInstance( $type, $url, $params ) {
		global $IP;
		
		$path = $IP . "/extensions/EditWarning/templates";
		
		if ( $self->instance[$type] === null ) {
			switch ( $type ) {
				case "ArticleNotice":
				  $params[] = wfMsg( 'ew-leave' );
				  self::$instance[$type] = new EWInfoMessage( $path, $url );
				  self::$instance[$type]->setMsg( 'ew-notice-article', $params );
				  break;
				case "ArticleWarning":
				  $params[] = wfMsg( 'ew-leave' );
				  self::$instance[$type] = new EWWarningMessage( $path, $url );
				  self::$instance[$type]->setMsg( 'ew-warning-article', $params );
				  break;
				case "ArticleSectionWarning":
				  $params[] = wfMsg( 'ew-leave' );
				  self::$instance[$type] = new EWWarningMessage( $path, $url );
				  self::$instance[$type]->setMsg( 'ew-warning-sectionedit', $params );
				  break;
				case "SectionNotice":
				  $params[] = wfMsg( 'ew-leave' );
				  self::$instance[$type] = new EWInfoMessage( $path, $url );
				  self::$instance[$type]->setMsg( 'ew-notice-section', $params );
				  break;
				case "SectionWarning":
				  $params[] = wfMsg( 'ew-leave' );
				  self::$instance[$type] = new EWWarningMessage( $path, $url );
				  self::$instance[$type]->setMsg( 'ew-warning-section', $params );
				  break;
				case "Cancel":
				  self::$instance[$type] = new EWCancelMessage( $path );
				  self::$instance[$type]->setMsg( 'ew-canceled' );
				  break;
				default:
				  throw new IllegalArgumentException( "Unknown message type." );
			}
		}
		
		return self::$instance[$type];
	}
}

//class EditWarningMessage {
//	/**
//	 * @access private
//	 * @var string Holds the type of the message (eg. notice). It is used to
//	 *             determine the template file to load.
//	 */
//	private $_msg_type;
//	
//	/**
//	 * @access private
//	 * @var array Array of labels and associated values.
//	 */
//	private $_labels = array();
//	
//	/**
//	 * @access private
//	 * @var array Array of values used in message strings.
//	 */
//    private $_values = array();
//    
//    /**
//     * @access private
//     * @var string Template content read from template file.
//     */
//	private $_tpl_content;
//
//    /**
//     * @access public
//     * @param string $msg_type Type of message (eg. notice).
//     */
//    public function __construct( $msg_type ) {
//    	$this->setMsgType( strtolower( $msg_type ) );
//    }
//    
//    /**
//     * @access public
//     * @param string $msg_type Type of message (eg. notice).
//     */
//    public function setMsgType( $msg_type ) {
//    	$this->_msg_type = $msg_type;
//    }
//    
//    /**
//     * @access public
//     * @return string Type of message (eg. notice).
//     */
//    public function getMsgType() {
//    	return $this->_msg_type;
//    }
//    
//    /**
//     * @access public
//     * @param string $label Label that should be replaced in template.
//     * @param mixed $value Associated value.
//     */
//    public function setLabel( $label, $value ) {
//    	$this->_labels[$label] = $value;
//    }
//    
//    /**
//     * @access public
//     * @return array Array of labels and associated values.
//     */
//    public function getLabels() {
//    	return $this->_labels;
//    }
//    
//    /**
//     * @access public
//     * @param mixed $value Value that should be inserted in message string.
//     */
//    public function addValue( $value ) {
//    	$this->_values[] = $value;
//    }
//    
//    /**
//     * @access public
//     * @return array Array of values used in message strings.
//     */
//    public function getValues() {
//    	return $this->values;
//    }
//    
//    /**
//     * @access public
//     * @param string $content Template content.
//     */
//    public function setContent( $content ) {
//    	$this->_tpl_content = $content;
//    }
//    
//    /**
//     * @access public
//     * @param string Template content.
//     */
//    public function getContent() {
//    	return $this->_tpl_content;
//    }
//    
//    /**
//     * Loads template content from file.
//     * 
//     * @access public
//     * @param string $file_name Name of the template file.
//     */
//    public function loadTemplate( $file_name ) {
//    	try {
//    		$file = fopen( $file_name, "r" );
//    		$this->setContent( fread( $file, filesize( $file_name ) ) );
//    	} catch( Exception $e ) {
//    		throw new LoadTemplateException( $e );
//    	}
//    	fclose( $file );
//    }
//    
//    /**
//     * Replaces labels in template content with associated values.
//     * 
//     * @access public
//     */
//    public function processTemplate() {
//    	if ( $this->getContent() == null ) {
//    		throw new NoTemplateContentFoundException(
//    		    "No template content found. You should load a template first."
//    		);
//    	}
//
//    	foreach( $this->getLabels() as $label => $value ) {
//    		$this->setContent( preg_replace(
//                "/{{{" . $label . "}}}/",
//                wfMsgReal( $value, $this->getValues(), true ),
//                $this->getContent()
//            ) );
//    	}
//    }
//    
//    /**
//     * Output the HTML code.
//     * 
//     * @access public
//     */
//    public function show() {
//    	global $IP, $wgOut;
//    	
//    	$this->loadTemplate( $IP . "/extensions/EditWarning/templates/" . $this->getMsgType() . ".html" );
//    	$this->processTemplate();
//    	$wgOut->addHtml( $this->getContent() );
//    }
//}
