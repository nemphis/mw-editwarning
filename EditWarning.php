<?php

/**
 * Implementation of EditWarning and EditWarning_Lock class.
 * 
 * This file is part of the MediaWiki extension EditWarning. It contains
 * the implementation of EditWarning and EditWarning_Lock class with
 * functions to add, edit, delete and check for article locks.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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
 * @license		http://www.gnu.org/licenses/gpl-howto.html GNU AGPL 3.0 or later
 * @version		0.4-prealpha
 * @category	Extensions
 * @package		EditWarning
 */

if ( !defined( 'MEDIAWIKI' ) && !defined( 'EDITWARNING_UNITTEST' ) ) {
    echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once "\$IP/extensions/EditWarning/EditWarning.php";

See http://www.mediawiki.org/wiki/Extension:EditWarning for more information.
EOT;
    exit(1);
}

$extension_dir = dirname(__FILE__) . "/";

$wgExtensionCredits['EditWarning'][] = array(
    'name'        => "EditWarning",
    'author'      => "Thomas David",
    'url'         => "http://www.mediawiki.org/wiki/Extension:EditWarning",
    'description' => "Warns user editing a page that\'s currently being edited. (Version 0.4-prealpha)"
);

$wgAutoloadClasses['EditWarning']        = $extension_dir . 'EditWarning.class.php';
$wgAutoloadClasses['EditWarningMessage'] = $extension_dir . 'EditWarningMessage.class.php';
$wgExtensionMessagesFiles['EditWarning'] = $extension_dir . 'Messages.i18n.php';
$wgExtensionFunctions[]                  = 'fnEditWarning_init';

if ( !defined( 'EDITWARNING_UNITTEST' ) ) {
	$editwarning = new EditWarning( $wgUser->getID() );
}

// Assign hooks to functions
$wgHooks['AlternateEdit'][]     = array( 'fnEditWarning_edit', $editwarning );   // On article edit.
$wgHooks['ArticleSave'][]       = array( 'fnEditWarning_remove', $editwarning ); // On article save.
$wgHooks['UserLogout'][]        = array( 'fnEditWarning_logout', $editwarning ); // On user logout.
$wgHooks['ArticleViewHeader'][] = array( 'fnEditWarning_remove', $editwarning ); // On editing abort.

/**
 * Setup EditWarning extension
 *
 * @return boolean Returns always true.
 */
function fnEditWarning_init() {
    global $wgRequest, $wgOut;

    // Add CSS styles to header
    if ($wgRequest->getVal('action') == 'edit' || $wgRequest->getVal('action') == 'submit') {
      $wgOut->addHeadItem('edit_css', '  <link href="extensions/EditWarning/article_edit.css" rel="stylesheet" type="text/css" />');
    }
    $wgOut->addHeadItem('EditWarning', '  <link href="extensions/EditWarning/style.css" rel="stylesheet" type="text/css" />');

    // Load messages
    wfLoadExtensionMessages('EditWarning');

    return true;
}

/**
 * Action on article editing
 *
 * @hook AlternateEdit
 * @param object $editpage Editpage object.
 * @param object $ew EditWarning object
 * @return boolean|int It returns a constant int if it runs in unit test
 *                     environment, else true.
 */
function fnEditWarning_edit(&$ew, &$editpage) {
	global $wgRequest, $wgUser;
	
	// Abort on nonexisting pages or anonymous users.
	if ( $editpage->mArticle->getID() < 1 || $wgUser->getID() < 1 ) {
		return true;
	}
	
	$ew->setArticleID( $editpage->mArticle->getID() );
	$section    = (int) $wgRequest->getVal( 'section' );
	$dbr        =& wfGetDB( DB_SLAVE );
	$dbw        =& wfGetDB( DB_MASTER );
	
	// Get article title for cancel button
    if ($editpage->mArticle->mTitle->getNamespace() == 'NS_MAIN') {
        $article_title = $editpage->mArticle->mTitle->getPartialURL();
    } else {
        $article_title = $editpage->mArticle->mTitle->getNsText() . ":" . $editpage->mArticle->mTitle->getPartialURL();
    }
    
    $url = $PHP_SELF . "?title=" . $article_title;
	
	// Check request values
	if ( $section > 0 ) {
		// Section editing
		$ew->setSection( $section );
		$ew->load( $dbr );
		
		if ( $ew->anyLock() ) {
			if ( $lock = $ew->sectionLock() ) {
				if ( $ew->sectionUserLock() ) {
					// User itself has lock on that section.
					$ew->updateLock( $dbw, $wgUser->getID(), $wgUser->getName(), $section );
					
					// Show info message with updated timestamp.
					$msg = new EditWarningMessage( 'notice' );
					$msg->setLabel( 'MSG', 'ew-notice-section' );
					$msg->setLabel( 'URL', $url );
					$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
					$msg->addValue( date( "Y-m-d", $ew->getTimestamp( TIMESTAMP_NEW ) ) );
					$msg->addValue( date( "H:i", $ew->getTimestamp( TIMESTAMP_NEW ) ) );
					$msg->addValue( wfMsg( 'ew-leave' ) );
					$msg->show();
					unset( $ew );
					unset( $msg );
					
					if ( defined( 'EDITWARNING_UNITTEST' ) ) {
						return EDIT_SECTION_USER;
					} else {
						return true;
					}
				} else {
					// Someone else is already working on this section.
					
					// Calculate time to wait
					$difference   = floatval( abs( time() - $lock->getTimestamp() ) );
					$time_to_wait = bcdiv( $difference, 60, 0 );
					
					// Show warning.
					$msg = new EditWarningMessage( 'warning' );
					$msg->setLabel( 'MSG', 'ew-warning-section' );
					$msg->setLabel( 'URL', $url );
					$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
					$msg->addValue( $lock->getUserName() );
					$msg->addValue( date( "Y-m-d", $lock->getTimestamp() ) );
					$msg->addValue( date( "H:i", $lock->getTimestamp() ) );
					$msg->addValue( $time_to_wait );
					
					// Use minutes or seconds string?
					if ($time_to_wait > 1 || $difference > 60) {
						$msg->addValue( wfMsg( 'minutes' ) );
					} else {
						$msg->addValue( wfMsg( 'seconds' ) );
					}
					
					$msg->addValue( wfMsg( 'ew-leave' ) );
					$msg->show();
					unset( $ew );
					unset( $msg );
					
					if ( defined( 'EDITWARNING_UNITTEST' ) ) {
						return EDIT_SECTION_OTHER;
					} else {
						return true;
					}
				}
			} else {
				// Someone else is working on the whole article.
				$lock = $ew->articleLock();
				
				// Calculate time to wait
				$difference   = floatval( abs( time() - $lock->getTimestamp() ) );
				$time_to_wait = bcdiv( $difference, 60, 0 );
				
				// Show warning.
				$msg = new EditWarningMessage( 'warning' );
				$msg->setLabel( 'MSG', 'ew-warning-article' );
				$msg->setLabel( 'URL', $url );
				$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
				$msg->addValue( $lock->getUserName() );
				$msg->addValue( date( "Y-m-d", $ew->getTimestamp() ) );
				$msg->addValue( date( "H:i", $ew->getTimestamp() ) );
				$msg->addValue( $time_to_wait );
				
				// Use minutes or seconds string?
				if ($time_to_wait > 1 || $difference > 60) {
					$msg->addValue( wfMsg( 'minutes' ) );
				} else {
					$msg->addValue( wfMsg( 'seconds' ) );
				}
				
				$msg->addValue( wfMsg( 'ew-leave' ) );
				$msg->show();
				unset( $ew );
				unset( $msg );
				
				if ( defined( 'EDITWARNING_UNITTEST' ) ) {
					return EDIT_SECTION_ARTICLE;
				} else {
					return true;
				}
			}
		} else {
			$ew->saveLock( $dbw, $wgUser->getID(), $wgUser->getName(), $section );
			
			// Show info message.
			$msg = new EditWarningMessage( 'notice' );
			$msg->setLabel( 'MSG', 'ew-notice-section' );
			$msg->setLabel( 'URL', $url );
			$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
			$msg->addValue( date( "Y-m-d", $ew->getTimestamp( TIMESTAMP_NEW ) ) );
			$msg->addValue( date( "H:i", $ew->getTimestamp( TIMESTAMP_NEW ) ) );
			$msg->addValue( wfMsg( 'ew-leave' ) );
			$msg->show();
			unset( $ew );
			unset( $msg );
			
			if ( defined( 'EDITWARNING_UNITTEST' ) ) {
				return EDIT_SECTION_NEW;
			} else {
				return true;
			}
		}
	} else {
		// Article editing
		$ew->load( $dbr );
		
		if ( $ew->anyLock() ) {
			if ( $lock = $ew->articleLock() ) {
				if ( $ew->articleUserLock() ) {
					// User itself has lock on that article.
					$ew->updateLock( $dbw, $wgUser->getID(), $wgUser->getName() );
					
					// Show info message with updated timestamp.
					$msg = new EditWarningMessage( 'notice' );
					$msg->setLabel( 'MSG', 'ew-notice-article');
					$msg->setLabel( 'URL', $url );
					$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
					$msg->addValue( date( "Y-m-d", $ew->getTimestamp( TIMESTAMP_NEW ) ) );
					$msg->addValue( date( "H:i", $ew->getTimestamp( TIMESTAMP_NEW ) ) );
					$msg->addValue( wfMsg( 'ew-leave' ) );
					$msg->show();
					unset( $ew );
					unset( $msg );
					
					if ( defined( 'EDITWARNING_UNITTEST' ) ) {
						return EDIT_ARTICLE_USER;
					} else {
						return true;
					}
				} else {
					// Someone else is already working on the whole article.
					
					// Calculate time to wait
					$difference   = floatval( abs( time() - $lock->getTimestamp() ) );
					$time_to_wait = bcdiv( $difference, 60, 0 );
					
					// Show warning.
					$msg = new EditWarningMessage( 'warning' );
					$msg->setLabel( 'MSG', 'ew-warning-article' );
					$msg->setLabel( 'URL', $url );
					$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
					$msg->addValue( $lock->getUserName() );
					$msg->addValue( date( "Y-m-d", $lock->getTimestamp() ) );
					$msg->addValue( date( "H:i", $lock->getTimestamp() ) );
					$msg->addValue( $time_to_wait );
					
					// Use minutes or seconds string?
					if ($time_to_wait > 1 || $difference > 60) {
						$msg->addValue( wfMsg( 'minutes' ) );
					} else {
						$msg->addValue( wfMsg( 'seconds' ) );
					}
					
					$msg->addValue( 'ew-leave' );
					$msg->show();
					unset( $ew );
					unset( $msg);
					
					if ( defined( 'EDITWARNING_UNITTEST' ) ) {
						return EDIT_ARTICLE_OTHER;
					} else {
						return true;
					}
				}
			} else {
				// Someone else is already working on a section of the article.
				$lock = $ew->sectionLock();
				
				// Calculate time to wait
				$difference   = floatval( abs( time() - $lock->getTimestamp() ) );
				$time_to_wait = bcdiv( $difference, 60, 0 );
				
				// Show warning.
				$msg = new EditWarningMessage( 'warning' );
				$msg->setLabel( 'MSG', 'ew-warning-sectionedit' );
				$msg->setLabel( 'URL', $url );
				$msg->setLabel( 'BUTTON_CANCEL' );
				$msg->addValue( $time_to_wait );
				
				// Use minutes or seconds string?
				if ($time_to_wait > 1 || $difference > 60) {
					$msg->addValue( wfMsg( 'minutes' ) );
				} else {
					$msg->addValue( wfMsg( 'seconds' ) );
				}
				
				$msg->addValue( 'ew-leave' );
				$msg->show();
				unset( $ew );
				unset( $msg );
				
				if ( defined( 'EDITWARNING_UNITTEST' ) ) {
					return EDIT_ARTICLE_SECTION;
				} else {
					return true;
				}
			}
		} else {
			// There are no locks.
			$ew->saveLock( $dbw, $wgUser->getID(), $wgUser->getName() );
			
			// Show info message.
			$msg = new EditWarningMessage( 'notice' );
			$msg->setLabel( 'MSG', 'ew-notice-article' );
			$msg->setLabel( 'URL', $url );
			$msg->setLabel( 'BUTTON_CANCEL', 'ew-button-cancel' );
			$msg->addValue( date( "Y-m-d", $ew->getTimestamp() ) );
			$msg->addValue( date( "H:i", $ew->getTimestamp() ) );
			$msg->addValue( wfMsg( 'ew-leave' ) );
			$msg->show();
			unset( $ew );
			unset( $msg );
			
			if ( defined( 'EDITWARNING_UNITTEST' ) ) {
				return EDIT_ARTICLE_NEW;
			} else {
				return true;
			}
		}
	}
}

/**
 * Action if article is saved or editing is aborted.
 *
 * @hook ArticleSave
 * @param
 * @return boolean Returns always true.
 */
function fnEditWarning_remove(&$ew, &$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
	// Abort on nonexisting pages or anonymous users.
	if ( $article->getID() < 1 || $user->getID() < 1 ) {
		return true;
	}
	
	$dbw =& wfGetDB( DB_MASTER );
	$ew->setArticleID( $article->getID() );
	$ew->removeLock( $dbw, $user->getID(), $user->getName() );
	return true;
}

/**
 * Action on user logout.
 *
 * @hook UserLogout
 * @param user User object.
 * @return boolean Returns always true.
 * 
 */
function fnEditWarning_logout(&$ew, &$user) {
	$ew->setUserID( $user->getID() );
	$ew->removeUserLocks();
	
	return true;
}
