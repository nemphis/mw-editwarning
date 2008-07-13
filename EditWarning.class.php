<?php

/**
 * EditWarning base class
 *
 * This file is part of the MediaWiki extension EditWarning
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

class EditWarning {
  private static $_instance = null;
  private $_article_id;
  private $_user_id;
  private $_user_name;
  private $_editors;
  private $_timeout;
  private $_tpl_replacements = array("var" => array(), "msg" => array());

  private function __clone() {}
  
  public static function getInstance($article_id = null) {
    if (EWDEBUG) error_log(sprintf('getInstance(%s)', $article_id));
    if (self::$_instance == null) {
      if (EWDEBUG) error_log('Create new EditWarning instance.');
      self::$_instance = new EditWarning($article_id);
    } else {
      self::$_instance->setArticleID($article_id);
    }
    
    return self::$_instance;
  }
  
  protected function __construct(&$article_id = null) {
    if (EWDEBUG) error_log(sprintf('Construct EditWarning. article_id: %s', $article_id)); 
    global $IP, $wgUser, $wgLanguageCode, $wgRequest, $EditWarning_Timeout;
    
    if ($article_id != null) {
      $this->_article_id = $article_id;
    }
    
    $this->_user_id   = $wgUser->getID();
    $this->_user_name = $wgUser->getName();
    $timeout          = intval($EditWarning_Timeout);
    
    if ($timeout == '' || $timeout == null || $timeout <= 0) {
      // Set default timeout to 10 minutes.
      $this->_timeout = 10;
    } else {
      $this->_timeout = $timeout;
    }
    
    // Load extension message strings
    require_once $IP . "/extensions/EditWarning/Messages.i18n.php";
    isset($messages[strtolower($wgLanguageCode)]) ? $lang = strtolower($wgLanguageCode) : $lang = "en";
    
    foreach($messages[$lang] as $key => $msg) {
      $this->_tpl_replacements['msg'][strtoupper($key)] = $msg;
    }
    
    // Debug
    if (EWDEBUG) {
      error_log('EditWarning vars:');
      error_log(sprintf('  _article_id:       %s', $this->_article_id));
      error_log(sprintf('  _user_id:          %s', $this->_user_id));
      error_log(sprintf('  _user_name:        %s', $this->_user_name));
      error_log(sprintf('  _editors:          %s', $this->_editors));
      error_log(sprintf('  _timeout:          %s', $this->_timeout));
    }
    
    return true;
  }
  
  /**
   * Returns article id.
   *
   * @access public
   * @return int
   */
  public function getArticleID() {
    return $this->_article_id;
  }
  
  /**
   * Returns user id.
   *
   * @access public
   * @return int
   */
  public function getUserID() {
    return $this->_user_id;
  }
  
  /**
   * Returns user name.
   *
   * @access public
   * @return string
   */
  public function getUserName() {
    return $this->_user_name;
  }
  
  /**
   * Returns timeout.
   *
   * @access public
   * @return int
   */
  public function getTimeout() {
    return $this->_timeout;
  }
  
  /**
   * Returns active user
   *
   * @access public
   * @return array
   */
  public function getActiveUser() {
    return $this->_editors['active'];
  }
  
  public function getEditor() {
    // Load article editors
    if($this->_article_id != null) {
      if($this->_getArticleEditor($this->_article_id)) { return true; }
    }
    
    return false;
  }
  
  /**
   * Returns a i18n message
   *
   * @access public
   * @return string
   */
  public function getReplacementMsg($name) {
    return $this->_tpl_replacements['msg'][$name];
  }
  
  /**
   * Returns article editors
   *
   * @access private
   * @return array
   */
  private function _getEditors() {
    return $this->_editors;
  }
  
  /**
   * Returns template contents
   *
   * @access private
   * @return string
   */
  private function _getTemplate($tpl_name) {
    global $IP;
    
    $filename    = $IP . "/extensions/EditWarning/tpl_" . $tpl_name . ".html";
    $file        = @fopen($filename, "r");
    $tpl_content = @fread($file, filesize($filename));
    @fclose($file);
    
    // Debug
    if (EWDEBUG) {
      error_log(sprintf('_getTemplate(%s): %s', $tpl_name, $filename));
      if (!file) error_log('ERROR! Couldn\'t open template file!');
      if (!tpl_content) error_log('ERROR! Couldn\'t read template file!');
    }
    
    return $tpl_content;
  }
  
  /**
   * Sets article id
   *
   * @access public
   * @param int article_id
   * @return true
   */
  public function setArticleID($article_id) {
    if (EWDEBUG) error_log(sprintf('Set _article_id to %s', $article_id));
    $this->_article_id = $article_id;
    
    return true;
  }
  
  /**
   * Sets user values
   *
   * @access public
   * @param array user
   * @return true
   */
  public function setUser($user) {
    $this->_user_id = $user['id'];
    $this->_user_name = $user['name'];
  }
  
  /**
   * Set variable for replacement
   *
   * @access public
   * @return true
   */
  public function setReplacementVar($name, $content) {
    if (EWDEBUG) error_log(sprintf('Set _tpl_replacements[\'var\'][%s] to %s', $name, $content)); 
    $this->_tpl_replacements['var'][$name] = $content;
    return true;
  }
  
  /**
   * Loads template, replaces variables with i18n strings
   *
   * @access public
   * @return string
   */
  public function processTemplate($tpl_name) {
    if (EWDEBUG) error_log(sprintf('processTempate(%s)', $tpl_name));
    $tpl_data = $this->_getTemplate($tpl_name);
    
    // Messages
    foreach ($this->_tpl_replacements['msg'] as $msg => $replacement) {
      $tpl_data = preg_replace("/{{{" . $msg . "}}}/", $replacement, $tpl_data);
    }
    // Variables
    foreach ($this->_tpl_replacements['var'] as $var => $replacement) {
      $tpl_data = preg_replace("/{{{" . $var . "}}}/", $replacement, $tpl_data);
    }
    
    return $tpl_data;
  }
  
  /**
   * Gets article editor from DB and sets array _editors
   *
   * @access private
   * @return true
   */
  private function _getArticleEditor($article_id) {
    if (EWDEBUG) error_log(sprintf('_getArticleEditor(%s)', $article_id));
    global $wgDBprefix;
    
    $editors   = array();
    $dbr       =& wfGetDB(DB_SLAVE);
    $page_id   = $this->getArticleID();
    $timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
    
    // Debug
    if (EWDEBUG) {
      error_log(sprintf('  page_id:   %s', $page_id));
      error_log(sprintf('  timestamp: %s (%s)', $timestamp, date('Y-m-d H:i:s', $timestamp)));
    }
    
    $result    = $dbr->query(sprintf(
      "SELECT * FROM `". $wgDBprefix . "current_edits`
      WHERE `page_id` = '%s' AND `timestamp` >= '%s'
      ORDER BY `timestamp` DESC",
      $page_id, $timestamp
    ));
    
    if (EWDEBUG && !$result) error_log('ERROR! Couldn\'t fetch editor from DB.');
    
    while ($row = $dbr->fetchObject($result)) {
      if (EWDEBUG) error_log(sprintf('   %s [id: %s, timestamp: %s]', $row->user_name, $row->user_id, $row->timestamp));
      $editors[$row->user_name] = array($row->user_id, $row->timestamp);
      
      // Save active user
      $editors['active'] = array(
        "name"      => $row->user_name,
        "id"        => $row->user_id,
        "timestamp" => $row->timestamp
      );
    }
    
    $dbr->freeResult($result);
    $this->_editors = $editors;
    
    return true;
  }
  
  /**
   * Checks if the user is the only editor
   *
   * @access private
   * @return boolean
   *   Returns true if the user is the only editor.
   *   Default return: false
   */
  public function onlyUser() {
    $editors   = $this->_getEditors();
    $user_id   = $this->getUserID();
    $user_name = $this->getUserName();
    
    // Debug
    if (EWDEBUG) {
      error_log('onlyUser()');
      error_log(sprintf('  editors count: %s', count($editors)));
      error_log(sprintf('  first editor:  %s', $editors[$user_name][0]));
      error_log(sprintf('  user_id:       %s', $user_id));
      error_log(sprintf('  user_name:     %s', $user_name));
    }
    
    if ((count($editors) == 1 && $editors[$user_name][0] == $user_id) || count($editors) == 0) {
      if (EWDEBUG) error_log('RETURN true.');
      return true;
    }
    
    if (EWDEBUG) error_log('RETURN false');
    return false;
  }
  
  /**
   * Checks if the user is the active user
   *
   * @access public
   * @return boolean
   *   Returns true if the user is editing the page.
   *   Default return: false
   */
  public function userActive() {
    $editors = $this->_getEditors();
    $user_id = $this->getUserID();
    
    // Debug
    if (EWDEBUG) {
      error_log('userActive()');
      error_log(sprintf('  active id: %s', $editors['active']['id']));
      error_log(sprintf('  user_id:   %s', $user_id));
    }
    
    if ($editors['active']['id'] == $user_id) {
      if (EWDEBUG) error_log('RETURN true');
      return true;
    }
    
    if (EWDEBUG) error_log('RETURN false');
    return false;
  }

  /**
   * Checks if someone is editing the page
   *
   * @access public
   * @return boolean
   *   Returns true if someone is editing the page.
   *   Default return: false
   */
  public function activeEditing() {
    if (DEBUG) error_log('activeEditing()');
    $editors = $this->_getEditors();
    if (count($editors) == 0) {
      if (EWDEBUG) error_log('RETURN false');
      return false;
    }
    
    if (EWDEBUG) error_log('RETURN true');
    return true;
  }

  /**
   * Checks if the user is editing the page.
   *
   * @access public
   * @return boolean
   *   Returns true if user has an entry.
   *   Default return: false
   */
  public function userExists($editors) {
    $editors   = $this->_getEditors();
    $user_id   = $this->getUserID();
    $user_name = $this->getUserName();
    
    if ($editors[$user_name][0] == $user_id) { return true; }
    
    return false;
  }
  
  /**
   * Update the editors for the page
   *
   * @access public
   * @return boolean
   *   Returns false if the user is anonymous
   *   Default return: true
   */
  public function editorUpdate() {
    if (EWDEBUG) error_log('editorUpdate()');
    global $wgDBprefix;
    
    $dbr       =& wfGetDB( DB_SLAVE );
    $timestamp = mktime(date("H"), date("i") + $this->getTimeout(), date("s"), date("m"), date("d"), date("Y"));
    $page_id   = $this->getArticleID();
    $user_id   = $this->getUserID();
    $user_name = $this->getUserName();
    
    // Debug
    if (EWDEBUG) {
      error_log(sprintf('  timestamp: %s (%s)', $timestamp, date('Y-m-d H:i:s', $timestamp)));
      error_log(sprintf('  page_id:   %s', $page_id));
      error_log(sprintf('  user_id:   %s', $user_id));
      error_log(sprintf('  user_name: %s', $user_name));
    }
    
    // Don't save anonymous users or pages without id
    if ($page_id == 0 || $user_id == 0) {
      if (EWDEBUG) error_log('Anonymous user. Break.');
      return false;
    }
    
    if ($this->userExists($this->_getEditors())) {
      $result = $dbr->query(sprintf(
        "UPDATE `" . $wgDBprefix . "current_edits`
        SET `timestamp` = '%s'
        WHERE `page_id` = '%s' AND `user_id` = '%s'",
        $timestamp, $page_id, $user_id
      ));
    } else {
      $result = $dbr->query(sprintf(
        "INSERT `" . $wgDBprefix . "current_edits`
        (`page_id`, `user_id`, `user_name`, `timestamp`) VALUES
        ('%s', '%s', '%s', '%s')",
        $page_id, $user_id, $user_name, $timestamp
      ));
    }
    
    if (EWDEBUG && !$result) error_log('ERROR! Couldn\'t update editors!');
    
    return true;
  }
  
  /**
   * Remove the page lock of a user
   *
   * @param int user_id
   * @return boolean
   *   Return true if the removal was successfull.
   *   Default return: false
   */
  public function deleteUser($user_id) {
    if (EWDEBUG) error_log(sprintf('deleteUser(%s)', $user_id));
    global $wgDBprefix;
    
    $dbr     =& wfGetDB(DB_SLAVE);
    $page_id = $this->getArticleID();
    $result  = $dbr->query(sprintf(
      "DELETE FROM `" . $wgDBprefix . "current_edits`
      WHERE `user_id` = '%s' AND `page_id` = '%s'",
      $user_id, $page_id
    ));
    
    if (EWDEBUG) error_log(sprintf('  page_id: %s', $page_id));
    if ($result) {
      if (EWDEBUG) error_log('RETURN true');
      return true;
    }
    
    if (EWDEBUG) error_log('ERROR! Couldn\'t delete page lock.');
    return false;
  }
  
  /**
   * Remove all page locks of a user
   *
   * @param int user_id
   * @return boolean
   *   Return true if the removal was successfull.
   *   Default return: false
   */
  public function deleteUserAll($user_id) {
    if (EWDEBUG) error_log(sprintf('deleteUserAll(%s)', $user_id));
    global $wgDBprefix;
    
    $dbr    =& wfGetDB(DB_SLAVE);
    $result = $dbr->query(sprintf(
      "DELETE FROM `" . $wgDBprefix . "current_edits`
      WHERE `user_id` = '%s'",
      $user_id
    ));
    
    if($result) {
      if (EWDEBUG) error_log('RETURN true');
      return true;
    }
    
    if (EWDEBUG) error_log('ERROR! Couldn\'t remove all page locks.');
    return false;
  }
}