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

/*if (!defined('MEDIAWIKI')) {
    echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once "\$IP/extensions/EditWarning/EditWarning.php";

See http://www.mediawiki.org/wiki/Extension:EditWarning for more information.
EOT;
    exit(1);
}*/

$extension_dir = dirname(__FILE__) . "/";

$wgExtensionCredits['EditWarning'][] = array(
    'name'        => "EditWarning",
    'author'      => "Thomas David",
    'url'         => "http://www.mediawiki.org/wiki/Extension:EditWarning",
    'description' => "Warns user editing a page that\'s currently being edited. (Version 0.4-prealpha)"
);

$wgAutoloadClasses['EditWarning']        = $extension_dir . 'EditWarning.class.php';
$wgExtensionMessagesFiles['EditWarning'] = $extension_dir . 'Messages.i18n.php';
$wgExtensionFunctions[]                  = 'fnEditWarning_init';

// Assign hooks to functions
$wgHooks['AlternateEdit'][]     = 'fnEditWarning_edit';   // On article edit.
$wgHooks['ArticleSave'][]       = 'fnEditWarning_remove'; // On article save.
$wgHooks['UserLogout'][]        = 'fnEditWarning_logout'; // On user logout.
$wgHooks['ArticleViewHeader'][] = 'fnEditWarning_remove'; // On editing abort.

/**
 * Setup EditWarning extension
 *
 * @return boolean Returns always true.
 */
function fnEditWarning_init() {
//    global $wgRequest, $wgOut;
//
//    // Add CSS styles to header
//    if ($wgRequest->getVal('action') == 'edit' || $wgRequest->getVal('action') == 'submit') {
//      $wgOut->addHeadItem('edit_css', '  <link href="extensions/EditWarning/article_edit.css" rel="stylesheet" type="text/css" />');
//    }
//    $wgOut->addHeadItem('EditWarning', '  <link href="extensions/EditWarning/style.css" rel="stylesheet" type="text/css" />');
//
//    // Load messages
//    wfLoadExtensionMessages('EditWarning');
//
//    return true;
}

/**
 * Loads HTML template and includes messsages
 *
 * @param string Template name.
 * @param array Messages.
 * @param array Template variables. (optional)
 * @return string HTML code.
 */
function fnEditWarning_getHTML($template, $msg, $vars = array()) {
//    global $extension_dir;
//
//    // Load template file
//    $file_name = $extension_dir . "tpl_" . $template;
//    if (!$file = fopen($file_name, "r")) {
//        throw new Exception(sprintf("fnEditWarning_getHTML: Could't open template file %s.", $file_name));
//    }
//    if (!$tpl_content = fread($file, filesize($file_name))) {
//        throw new Exception(sprintf("fnEditWarning_getHTML: Could't read from template file %s.", $file_name));
//    }
//    if (!fclose($file_name)) {
//        throw new Exception(sprintf("fnEditWarning_getHTML: Could't close template file %s.", $file_name));
//    }
//
//    // Include template variables
//    foreach ($vars as $var => $value) {
//        $tpl_content = preg_replace('/{{{' . $var . '}}}/', $value);
//    }
//
//    // Include message
//    $tpl_content = preg_replace('/{{{MSG}}}/', $msg);
//
//    return $tpl_content;
}

/**
 * Action on article editing
 *
 * @hook AlternateEdit
 * @param editpage Editpage object.
 * @return !!!
 * @todo Write function and update comment.
 */
function fnEditWarning_edit(&$editpage) {}

/**
 * Action if article is saved or editing is aborted.
 *
 * @hook ArticleSave
 * @param
 * @return !!!
 * @todo Write function and update comment.
 */
function fnEditWarning_remove(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {}

/**
 * Action on user logout.
 *
 * @hook UserLogout
 * @param user User object.
 * @return !!!
 * @todo Write function and update comment.
 */
function fnEditWarning_logout(&$user) {}
