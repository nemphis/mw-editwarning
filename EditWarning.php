<?php

/**
 * MediaWiki extension: EditWarning
 *
 * Warns user editing a page that's currently being edited.
 *
 * @author Thomas David <ThomasDavid@gmx.de>
 * @addtogroup Extensions
 * @version 0.3.1
 * @copyright 2008 by Thomas David
 * @license GNU AGPL 3.0 or later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if(defined('MEDIAWIKI')) {
  global $IP;
  
  define('EWDEBUG', false);
  require_once $IP . "/extensions/EditWarning/EditWarning.class.php";
  
  $wgExtensionFunctions[] = 'fnEditwarning_init';
  $wgExtensionCredits['other'][] = array(
    'name'        => 'EditWarning',
    'author'      => 'Thomas David',
    'url'         => 'http://www.mediawiki.org/wiki/Extension:EditWarning',
    'description' => 'Warns user editing a page that\'s currently being edited. (Version 0.3.1)'
  );

  // Assign hooks to functions  
  $wgHooks['AlternateEdit'][]        = 'fnEditWarning_edit';
  $wgHooks['ArticleSave'][]          = 'fnEditWarning_save';
  $wgHooks['UserLogout'][]           = 'fnEditWarning_logout';
  $wgHooks['ArticleViewHeader'][]    = 'fnEditWarning_cancel';
  
  /**
   * Inititalize EditWarning class
   *
   * @return: true
   */
  function fnEditWarning_init() {
    if (EWDEBUG) error_log('----- fnEditWarning_init()');
    global $wgRequest, $wgOut;
    
    // Add CSS styles to header
    if ($wgRequest->getVal('action') == 'edit' || $wgRequest->getVal('action') == 'submit') {
      $wgOut->addHeadItem('edit_css', '  <link href="extensions/EditWarning/article_edit.css" rel="stylesheet" type="text/css" />');
    }
    $wgOut->addHeadItem('EditWarning', '  <link href="extensions/EditWarning/EditWarning.css" rel="stylesheet" type="text/css" />');
    
    return true;
  }
  
  /**
   * Action on editing page
   *
   * @hook: AlternateEdit
   * @param: &$editpage
   *   Editpage object
   * @return true
   */
  function fnEditWarning_edit(&$editpage) {
    if (EWDEBUG) error_log('----- fnEditWarning_edit()');
    if ($editpage->mArticle->mTitle->getNamespace() == 'NS_MAIN') {
        $article_title = $editpage->mArticle->mTitle->getPartialURL();
    } else {
        $article_title = $editpage->mArticle->mTitle->getNsText() . ":" . $editpage->mArticle->mTitle->getPartialURL();
    }
    $p = EditWarning::getInstance($editpage->mArticle->getID());
    $p->getEditor();
    $p->setReplacementVar('PAGENAME', $article_title);
    
    if ($p->userActive() || $p->onlyUser() || !$p->activeEditing()) {
      //
      // The current user is editing the page - show notice
      //
      $timestamp = mktime(date("H"), date("i") + $p->getTimeout(), date("s"), date("m"), date("d"), date("Y"));
      $p->setReplacementVar('DATE', date("Y-m-d", $timestamp));
      $p->setReplacementVar('TIME', date("H:i", $timestamp));
      $p->editorUpdate();
      
      $html = $p->processTemplate('notice');      
    } else {
      //
      // The user opened the page after the active user - show warning
      //
      $active_user = $p->getActiveUser();
      $time_user   = $active_user['timestamp'];
      $timestamp   = mktime(date("H", $time_user), date("i", $time_user) - $p->getTimeout(), date("s", $time_user), date("m", $time_user), date("d", $time_user), date("Y", $time_user));
      
      // Calculate time to wait.
      $difference   = floatval(abs(time() - $time_user));
      $time_to_wait = bcdiv($difference, 60, 0);
      
      // Debug
      if (EWDEBUG) {
        error_log(sprintf('  difference:   %s seconds', $difference));
        error_log(sprintf('  time_to_wait: %s minutes', $time_to_wait));
      }
      
      $p->setReplacementVar('DATE',     date('Y-m-d', $timestamp));
      $p->setReplacementVar('TIME',     date('H:i', $timestamp));
      $p->setReplacementVar('USERNAME', $active_user['name']);
      
      if ($time_to_wait > 1 || $difference > 60) {
        $p->setReplacementVar('MINSEC', $p->getReplacementMsg('MINUTES'));
      } else {
        $p->setReplacementVar('MINSEC', $p->getReplacementMsg('SECONDS'));
        $time_to_wait = $difference;
      }
      
      $p->setReplacementVar('TIMEOUT', $time_to_wait);      
      $html = $p->processTemplate('warning'); 
    }
    
    $editpage->editFormPageTop .= $html;
    
    return true;
  }
  
  /**
   * Action on article save
   *
   * @hook: ArticleSave
   * @param: &$article
   *   Article object
   * @return: boolean
   *   Returns true if the page lock could be removed from DB.
   *   Default return: false
   */
  function fnEditWarning_save(&$article, &$user, &$text, &$summary, &$minoredit, &$watchthis, &$sectionanchor, &$flags, $revision) {
    if (EWDEBUG) error_log('----- fnEditWarning_save()');
    $p = EditWarning::getInstance();
    $p->setArticleID($article->getID());
    
    if ($p->deleteUser($p->getUserID())) { return true; }
    
    return false;
  }
  
  /**
   * Action if user cancels editing
   *
   * @hook: ArticleViewHeader
   * @param: &$article
   *   Article object
   * @return: boolean
   *   Returns false if the page lock couldn't be removed from DB.
   *   Default return: true
   */
  function fnEditWarning_cancel(&$article) {
    if (EWDEBUG) error_log('----- fnEditWarning_cancel()');
    global $wgRequest, $wgOut;
    
    if ($wgRequest->getVal('cancel') == 'true') {
      $p = EditWarning::getInstance();
      $p->setArticleID($article->getID());
      
      if(!$p->deleteUser($p->getUserID())) { return false; }
      
      $wgOut->addHtml($p->processTemplate('canceled'));
    }
    
    return true;
  }
  
  /**
   * Remove all page locks on logout
   *
   * @hook: UserLogout
   * @param: &$user
   *   User object
   * @return: boolean
   *   Returns true if all page locks could be removed from DB.
   *   Default return: false
   */
  function fnEditWarning_logout(&$user) {
    if (EWDEBUG) error_log('----- fnEditWarning_logout()');
    $p = EditWarning::getInstance();
    
    if ($p->deleteUserAll($p->getUserID())) { return true; }
    
    return false;
  }  
}
