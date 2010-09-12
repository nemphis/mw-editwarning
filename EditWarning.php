<?php

/**
 * Implementation of EditWarning and EditWarning_Lock class.
 *
 * This file is part of the MediaWiki extension EditWarning. It contains
 * the implementation of EditWarning and EditWarning_Lock class with
 * functions to add, edit, delete and check for article locks.
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
 * @version     0.4-rc
 * @category    Extensions
 * @package     EditWarning
 */

if ( !defined( 'MEDIAWIKI' ) && !defined( 'EDITWARNING_UNITTEST' ) ) {
    echo <<<EOT
<p>To install this extension, put the following line in LocalSettings.php:\n
<pre>require_once "\$IP/extensions/EditWarning/EditWarning.php";</pre></p>\n\n

<p>See <a href="http://www.mediawiki.org/wiki/Extension:EditWarning/0.4">http://www.mediawiki.org/wiki/Extension:EditWarning/0.4</a> for more information.</p>
EOT;
    exit(1);
}

$extension_dir = dirname(__FILE__) . "/";

$wgExtensionCredits['other'][] = array(
    'name'           => "EditWarning",
    'author'         => "Thomas David",
    'url'            => "http://www.mediawiki.org/wiki/Extension:EditWarning/0.4",
    'version'        => "0.4-rc",
    'descriptionmsg' => "editwarning-desc"
);

$wgAutoloadClasses['EditWarning']        = $extension_dir . 'EditWarning.class.php';
$wgAutoloadClasses['EditWarningMsg']     = $extension_dir . 'EditWarningMsg.class.php';
$wgExtensionMessagesFiles['EditWarning'] = $extension_dir . 'Messages.i18n.php';
$wgExtensionFunctions[]                  = 'fnEditWarning_init';

if ( !defined( 'EDITWARNING_UNITTEST' ) ) {
    $editwarning = new EditWarning();
}

// Assign hooks to functions
$wgHooks['AlternateEdit'][]             = array( 'fnEditWarning_edit', $editwarning );   // On article edit.
$wgHooks['ArticleSave'][]               = array( 'fnEditWarning_remove', $editwarning ); // On article save.
$wgHooks['UserLogout'][]                = array( 'fnEditWarning_logout', $editwarning ); // On user logout.
$wgHooks['ArticleViewHeader'][]         = array( 'fnEditWarning_abort', $editwarning ); // On editing abort.

/**
 * Gets the section id from GET or POST
 *
 * @return int Section id.
 */
function fnEditWarning_getSection() {
    if ( defined( 'EDITWARNING_UNITTEST' ) ) {
        return $GLOBALS['unitGetSection'];
    }

    if ( isset( $_GET['section'] ) && !isset( $_POST['wpSection'] ) ) {
        return intval( $_GET['section'] );
    } else {
        return intval( $_POST['wpSection'] );
    }
}

/**
 * Setup EditWarning extension
 *
 * @return boolean Returns always true.
 */
function fnEditWarning_init() {
    global $wgRequest, $wgOut, $wgScriptPath, $wgUser, $EditWarning_OnlyEditor;

    // Add CSS styles to header
    if ( ( $wgRequest->getVal('action') == 'edit' || $wgRequest->getVal('action') == 'submit' )
            && $EditWarning_OnlyEditor != "false"
            && $wgUser->getID() >= 1 ) {
        $wgOut->addHeadItem('edit_css', '  <link href="' . $wgScriptPath . '/extensions/EditWarning/article_edit.css" rel="stylesheet" type="text/css" />');
    }
    $wgOut->addHeadItem('EditWarning', '  <link href="' . $wgScriptPath . '/extensions/EditWarning/style.css" rel="stylesheet" type="text/css" />');

    // Load messages
    wfLoadExtensionMessages('EditWarning');

    return true;
}

/*
 * Functions and definitions for fnEditWarning_edit
 */
// Used for messages to indicate edit type.
define('TYPE_ARTICLE', 1);
define('TYPE_ARTICLE_SECTIONCONFLICT', 2);
define('TYPE_SECTION', 3);

/**
 * Function to show info message about created or updated locks for sections
 * or articles.
 *
 * @param int $msgtype Type of edit (article or section).
 */
function showInfoMsg($msgtype, $timestamp, $cancel_url) {
    $type = ($msgtype == TYPE_ARTICLE) ? "ArticleNotice" : "SectionNotice";

    // Show info message with updated timestamp.
    $msg_params[] = date("Y-m-d", $timestamp);
    $msg_params[] = date("H:i", $timestamp);
    $msg = EditWarningMsg::getInstance($type, $cancel_url, $msg_params);
    $msg->show();
    unset($msg);
}

/**
 * Function to show warning message about existing locks for sections or
 * articles.
 *
 * @param <type> $lockobj EditWarningLock object.
 */
function showWarningMsg($msgtype, $lockobj, $cancel_url) {
    switch ($msgtype) {
        case TYPE_ARTICLE:
            $type = "ArticleWarning";
            break;
        case TYPE_ARTICLE_SECTIONCONFLICT:
            $type = "ArticleSectionWarning";
            break;
        case TYPE_SECTION:
            $type = "SectionWarning";
            break;
    }
    
    // Calculate time to wait
    $difference = floatval(abs(time() - $lockobj->getTimestamp()));
    $time_to_wait = bcdiv($difference, 60, 0);

    // Parameters for message string
    if ($msgtype == TYPE_ARTICLE || $msgtype == TYPE_SECTION) {
        $msg_params[] = $lockobj->getUserName();
        $msg_params[] = date("Y-m-d", $lockobj->getTimestamp());
        $msg_params[] = date("H:i", $lockobj->getTimestamp());
    }

    $msg_params[] = $time_to_wait;

    // Use minutes or seconds string?
    if ($time_to_wait > 1 || $difference > 60) {
        $msg_params[] = wfMsg('ew-minutes');
    } else {
        $msg_params[] = wfMsg('ew-seconds');
    }

    $msg = EditWarningMsg::getInstance($type, $cancel_url, $msg_params);
    $msg->show();
    unset($msg);
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
    global $wgUser, $wgScriptPath, $wgScriptExtension, $PHP_SELF;

    // Abort on nonexisting pages
    if ( $editpage->mArticle->getID() < 1 ) {
        return true;
    }

    if (! defined( 'EDITWARNING_UNITTEST' ) ) {
        $dbr        =& wfGetDB( DB_SLAVE );
        $dbw        =& wfGetDB( DB_MASTER );
    }

    $ew->setUserID( $wgUser->getID() );
    $ew->setUserName($wgUser->getName());
    $ew->setArticleID( $editpage->mArticle->getID() );
    $section    = fnEditWarning_getSection();
    $msg_params = array();

    // Get article title for cancel button
    if ($editpage->mArticle->mTitle->getNamespace() == 'NS_MAIN') {
        $article_title = $editpage->mArticle->mTitle->getPartialURL();
    } else {
        $article_title = $editpage->mArticle->mTitle->getNsText() . ":" . $editpage->mArticle->mTitle->getPartialURL();
    }

    $url = $PHP_SELF . "?title=" . $article_title . "&cancel=true";

    // Check request values
    if ( $section > 0 ) {
        // Section editing
        $ew->setSection( $section );
        $ew->load( $dbr );

        // Is the whole article locked?
        if ($ew->isArticleLocked()) {
            // Is it by the user?
            if ($ew->isArticleLockedByUser()) {
                // The user has already a lock on the whole article, but
                // edits now a single section. Change article lock to
                // section lock.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_SECTION_NEW;
                }

                $ew->removeLock($dbw);
                $ew->saveLock($dbw, $section);
                showInfoMsg(TYPE_SECTION, $ew->getTimestamp(TIMESTAMP_NEW), $url);
                unset($ew);
                return true;
            } else {
                // Someone else has a lock on the whole article. Show warning.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_ARTICLE_OTHER;
                }

                showWarningMsg(TYPE_ARTICLE, $ew->getArticleLock(), $url);
                unset($ew);
                return true;
            }
        } elseif ($ew->isSectionLocked($section)) {
            $sectionLock = $ew->getSectionLock($section);
            
            // Is the section locked by the user?
            if ($ew->isSectionLockedByUser($sectionLock)) {
                // The section is locked by the user. Update lock.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_SECTION_USER;
                }
                
                $ew->updateLock($dbw, $section);
                showInfoMsg(TYPE_SECTION, $ew->getTimestamp(TIMESTAMP_NEW), $url);
                unset($ew);
                return true;
            } else {
                // The section is locked by someone else. Show warning.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_SECTION_OTHER;
                }
                
                showWarningMsg(TYPE_SECTION, $sectionLock, $url);
                unset($ew);
                return true;
            }
        } else {
            // No locks. Create section lock for user.
            if (defined('EDITWARNING_UNITTEST')) {
                return EDIT_SECTION_NEW;
            }

            // Don't save locks for anonymous users.
            if ($ew->getUserID() < 1) {
                return true;
            }
            
            $ew->saveLock($dbw, $section);
            showInfoMsg(TYPE_SECTION, $ew->getTimestamp(TIMESTAMP_NEW), $url);
            unset($ew);
            return true;
        }
    } else {
        // Article editing
        $ew->load($dbr);
        
        // Is the article locked?
        if ($ew->isArticleLocked()) {
            if ($ew->isArticleLockedByUser()) {
                // The article is locked by the user. Update lock.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_ARTICLE_USER;
                }

                $ew->updateLock($dbw);
                showInfoMsg(TYPE_ARTICLE, $ew->getTimestamp(TIMESTAMP_NEW), $url);
                unset($ew);
                return true;
            } else {
                // The article is locked by someone else. Show warning.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_ARTICLE_OTHER;
                }

                $article_lock = $ew->getArticleLock();
                showWarningMsg(TYPE_ARTICLE, $article_lock, $url);
                unset($ew);
                return true;
            }
        } elseif ($ew->anySectionLocks()) {
            // There is at least one section lock
            if ($ew->anySectionLocksByOthers()) {
                // At least one section lock by another user.
                // So an article lock is not possible. Show warning.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_SECTION_OTHER;
                }

                $sectionLocks = $ew->getSectionLocksByOther();
                // Get the newest lock of a section for the warning message.
                $lock = $sectionLocks[$ew->getSectionLocksByOtherCount() - 1];
                showWarningMsg(TYPE_ARTICLE_SECTIONCONFLICT, $lock, $url);
                unset($ew);
                return true;
            } else {
                // The user has exclusively one or more locks on sections
                // of the article, but now wants to edit the whole article.
                // Change sections locks to article lock.
                if (defined('EDITWARNING_UNITTEST')) {
                    return EDIT_ARTICLE_NEW;
                }

                $ew->removeUserLocks($dbw);
                $ew->saveLock($dbw);
                showInfoMsg(TYPE_ARTICLE, $ew->getTimestamp(TIMESTAMP_NEW), $url);
                unset($ew);
                return true;
            }
        } else {
            // No locks. Create new article lock.
            if (defined('EDITWARNING_UNITTEST')) {
                return EDIT_ARTICLE_NEW;
            }

            // Don't save locks for anonymous users.
            if ($ew->getUserID() < 1) {
                return true;
            }

            $ew->saveLock($dbw);
            showInfoMsg(TYPE_ARTICLE, $ew->getTimestamp(TIMESTAMP_NEW), $url);
            unset($ew);
            return true;
        }
    }
}


/**
 * Action if article is saved.
 *
 * @hook ArticleSave
 * @param
 * @return boolean Returns always true.
 */
function fnEditWarning_remove( &$ew, &$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags ) {
    global $wgUser;

    // Abort on nonexisting pages or anonymous users.
    if ( $article->getID() < 1 || $user->getID() < 1 ) {
        return true;
    }

    $dbw =& wfGetDB(DB_MASTER);
    $ew->setUserID($wgUser->getID());
    $ew->setArticleID($article->getID());
    $ew->removeLock($dbw);

    return true;
}

/**
 * Action if editing is aborted.
 *
 * @hook ArticleViewHeader
 * @param
 * @return boolean Returns always true.
 */
function fnEditWarning_abort( $ew, &$article, &$outputDone, &$pcache ) {
    global $wgRequest, $wgUser;

    if( $wgRequest->getVal('cancel' ) == "true") {
        $dbw =& wfGetDB(DB_MASTER);
        $ew->setUserID($wgUser->getID());
        $ew->setArticleID($article->getID());
        $ew->removeLock($dbw);

        $msg = EditWarningMsg::getInstance( "Cancel" );
        $msg->show();
        unset( $ew );
        unset( $msg );
    }

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
    $dbw =& wfGetDB( DB_MASTER );
    $ew->setUserID( $user->getID() );
    $ew->removeUserLocks( $dbw );

    return true;
}
