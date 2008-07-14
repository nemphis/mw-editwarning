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
    /**
     * ID of the database entry
     *
     * @type int
     * @access private
     */
    //private $_id;

    /**
     * ID of the article which is edited
     *
     * @type int
     * @access private
     */
    private $_article_id;

    /**
     * Array with users working on the article
     *
     * @type array
     * @access private
     */
    private $_editors;

    /**
     * A lock/warning is only active until that timestamp
     *
     * @type int
     * @access private
     */
    private $_timestamp;

    /**
     * The user defined number of minutes of the timeout.
     *
     * @type int
     * @access private
     */
    private $_timeout;

    /**
     * ID of the current user
     *
     * @type int
     * @access private
     */
    private $_current_user_id;

    /**
     * Name of the current user
     *
     * @type string
     * @access private
     */
    private $_current_user_name;

    public function __construct() {
        global $wgUser, $EditWarning_Timeout;

        $this->_id                = null;
        $this->_article_id        = null;
        $this->_editors           = null;
        $this->_timestamp         = null;
        $this->_current_user_id   = $wgUser->getID();
        $this->_current_user_name = $wgUser->getName();
        $timeout                  = $EditWarning_Timeout;

        // Set timeout to default value of 10 minutes if unset or invalid
        if ($this->_timeout == '' || $this->_timeout == null || $this->_timeout < 1) {
            $this->_timeout = 10;
        } else {
            $this->_timeout = $timeout;
        }

        $this->_timestamp = mktime(date("H"), date("i") + $this->_timeout, date("s"), date("m"), date("d"), date("Y"));
    }

    /**
     * Gets the editor from the DB and sets all member variables
     *
     * @param article_id int
     * @access public
     * @return true
     */
    public function load($article_id) {
        global $wgDBprefix;

        $dbr       =& wfGetDB(DB_SLAVE);
        $timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        // Get only page edits with timeout in the future.
        if (!$result    = $dbr->query(sprintf(
                "SELECT * FROM `". $wgDBprefix . "current_edits`
                WHERE `page_id` = '%s' AND `timestamp` >= '%s'
                ORDER BY `timestamp` DESC",
                intval($article_id), $timestamp
            ))) {
            throw new Exception('Error while database query.');
        }

/*        if (!$num_result = mysql_num_rows($result)) {
            throw new Exception('The query result is invalid!');
        } elseif ($num_result == 0) {
            $this->_active_editing = false;
        } else {
            $this->_active_editing = true;
        }

        $this->_id         = mysql_result($result, 0, 'id');
        $this->_article_id = mysql_result($result, 0, 'article_id');
        $this->_user_id    = mysql_result($result, 0, 'user_id');
        $this->_user_name  = mysql_result($result, 0, 'user_name');
        $this->_timeout    = mysql_result($result, 0, 'timeout');*/

        $data = $dbr->fetchObject($result);

        return true;
    }

    /**
     * Returns the ID of the current article
     *
     * @access public
     * @return int
     */
    public function getArticleID() {
        if ($this->_article_id == null) {
            throw new Exception('getArticleID() returns null. You should execute load() first.');
        } else {
            return $this->_article_id;
        }
    }

    /**
     * Sets the ID of the current article
     *
     * @access public
     * @param id int
     * @return boolean Returns always true.
     */
    public function setArticleID($id) {
    	$this->_article_id = $id;
    	return true;
    }

    /**
     * Returns an array with the ID and name of the editing user
     *
     * @access public
     * @return array or false if there is nobody editing the article
     */
    public function getEditor() {
        if (count($this->_editors) > 1) {
        	throw new Exception('There are working different users on the article. Use getSectionEditors() instead.');
        } elseif (count($this->_editors) == 0) {
        	return false;
        } else {
            return array('id' => $this->editors[0]['id'], 'name' => $this->editors[0]['id']);
        }
    }

    /**
     * Returns an array with the ID and name of the user working on a section
     *
     * @access public
     * @param section int Section ID
     * @return array or false if there is nobody editing the section
     */
    public function getSectionEditor($section) {
    	if (count($this->_editors) == 0) {
    	    return false;
    	} else {
    	    foreach ($this->_editors as $editor) {
    	        if ($editor['section'] == $section) {
    	            return array('id' => $editor['id'], 'name' => $editor['name']);
    	        }
    	    }
    	}

    	return false;
    }

    /**
     * Returns the full editors list
     *
     * @access public
     * @return array Array of editors.
     */
    public function getEditorList() {
        return $this->_editors;
    }

    /**
     * Add user to editors list
     *
     * @access public
     * @param id int ID of the user.
     * @param name string Name of the user.
     * @param [section] int ID of the section.
     * @return boolean Returns always true.
     */
    public function addEditor($id, $name, $section = 0) {
    	global $wgDBprefix;

        // Check inputs
        foreach ($this->_editors as $editor) {
        	// Check if the user is already in the editors array.
            if ($editor['id'] == $id && $editor['name'] == $name) {
                throw new Exception(sprintf('Can\'t add user %s to editors: The user is already added to the editors list.', $name));
            }
            // Check if someone is already editing the whole article.
            if ($editor['section'] == 0) {
                throw new Exception(sprintf('Can\'t add user %s to editors: Another user is already editing the whole article.', $name));
            }
            // Check if someone is already editing the section.
            if ($editor['section'] == $section) {
                throw new Exception(sprintf('Can\'t add user %s to editors: Another user is already editing the section.', $name));
            }
        }

        // Add user to editors array.
        $this->_editors[] = array(
            'id'      => $id,
            'name'    => $name,
            'section' => $section
        );

        $dbr       =& wfGetDB(DB_SLAVE);
        $query     = sprintf(
            "INSERT `%scurrent_edits`" .
            "(`page_id`, `section`, `user_id`, `user_name`, `timestamp`) VALUES" .
            "('%s', '%s', '%s', '%s', '%s')",
            $wgDBprefix,
            $this->getArticleID(),
            $section,
            $id,
            $name,
            $this->getTimestamp()
        );

        if (!$dbr->query($query)) {
            throw new Exception('Error while saving editor to database.');
        }

        return true;
    }

    /**
     * Update the timestamp of the given user
     *
     * @access public
     * @param id int User ID.
     * @return boolean Returns always true.
     */
    public function updateEditor($id) {
        global $wgDBprefix;

        $dbr       =& wfGetDB(DB_SLAVE);
        $query     = sprintf(
            "UPDATE `%scurrent_edits`" .
            "SET `timestamp` = '%s'" .
            "WHERE `user_id` = '%s' AND `page_id` = '%s'",
            $wgDBprefix,
            $this->getTimestamp(),
            $id,
            $this->getArticleID()
        );

        if (!$dbr->query($query)) {
            throw new Exception('Error while updating editor.');
        }

        return true;
    }

    /**
     * Remove user from editors list
     *
     * @access public
     * @param id int User ID.
     * @return boolean Returns always true.
     */
    public function removeEditor($id) {
        global $wgDBprefix;

        if(array_search($id, $this->getEditorList()) === FALSE) {
            throw new Exception(sprintf('Could not remove editor: The user with the id %s was not found.', $id));
        }

        $dbr       =& wfGetDB(DB_SLAVE);
        $query     = sprintf(
            "DELETE FROM `%scurrent_edits`" .
            "WHERE `user_id` = '%s' AND `page_id` = '%s'",
            $wgDBprefix,
            $id,
            $this->getArticleID()
        );

        if (!$dbr->query($query)) {
            throw new Exception('Error while removing editor.');
        }

        return true;
    }

    /**
     * Returns the name of the editing user
     *
     * @access public
     * @return string
     */
    /*public function getUserName() {
        if ($this->_user_name == null) {
            throw new Exception('getUserName() returns null. You should execute load() first.');
        } else {
            return $this->_user_name;
        }
    }*/

    /**
     * Returns an array with the values of the editing user
     *
     * @access public
     * @return array
     *     (_user_name, _timestamp)
     */
    public function getActiveUser() {
        if ($this->_user_name == null || $this->_timestamp == null) {
            throw new Exception('The object isn\'t initialized. You should execute load() first.');
        } else {
            return array(
                'name'      => $this->_user_name,
                'timestamp' => $this->_timestamp
            );
        }
    }

    /**
     * Returns the timestamp of the editing user
     *
     * @access public
     * @return int
     */
    public function getTimestamp() {
        if ($this->_timestamp == null) {
            throw new Exception('getTimestamp() returns null. You should execute load() first.');
        } else {
            return $this->_timestamp;
        }
    }

    /**
     * Checks if the current user is the active user
     *
     * @param user_id int
     * @access public
     * @return boolean
     *     Returns true if the user is the active user or nobody is editing.
     */
    public function isActive() {
        if ($this->_user_id == $this->_current_user_id || !activeEditing()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if someone is editing the article
     *
     * @access public
     * @return boolean
     *     Returns the value of _active_editing.
     */
    public function activeEditing() {
        if ($this->_active_editing == null) {
            throw new Exception('activeEditing() returns null. You should execute load() first.');
        } else {
            return $this->_active_editing;
        }
    }

    /**
     * Set the timestamp of the editing user
     *
     * @param timestamp int
     * @access public
     * @return boolean
     *     The function always returns true.
     */
    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
        return true;
    }

    /**
     * Saves the EditWarning object.
     *
     * @access public
     * @return boolean
     */
    public function save() {
        global $wgDBprefix;

        $dbr &= wfGetDB(DB_SLAVE);
        $timestamp = mktime(date("H"), date("i") + $this->_timeout, date("s"), date("m"), date("d"), date("Y"));

        // Don't save anonymous users or articles without id
        if ($this->_article_id == 0 || $this->_current_user_id == 0)
            return false;

        if ($this->isActive()) {
            if ($this->activeEditing()) {
                $result = $dbr->query(sprintf(
                    "UPDATE `%scurrent_edits`
                    SET `timestamp` = '%s'
                    WHERE `page_id` = '%s' AND `user_id` = '%s'",
                    $wgDBprefix, $timestamp, $this->_article_id, $this->_user_id
                ));
            } else {
                $result = $dbr->query(sprintf(
                    "INSERT `%scurrent_edits`
                    (`page_id`, `user_id`, `user_name`, `timestamp`) VALUES
                    ('%s', '%s', '%s', '%s')",
                    $this->_article_id, $this->_current_user_id, $this->_current_user_name, $timestamp
                ));
            }
        }

        if (!$result) {
            throw new Exception('Error while saving EditWarning object to database!');
        }

        return true;
    }

    /**
     * Removes the entry from the DB
     *
     * @access public
     * @return boolean
     */
    public function remove() {
        global $wgDBprefix;

        $dbr &= wfGetDB(DB_SLAVE);

        if ($this->isActive()) {
            $result = $dbr->query(sprintf(
                "DELETE FROM `%scurrent_edits`
                WHERE `page_id` = '%s' AND `user_id` = '%s'",
                $wgDBprefix, $this->_article_id, $this->_user_id
            ));
        }

        if (!result) {
            throw new Exception('Could\'t remove EditWarning entry.');
        }

        return true;
    }

    /**
     * Removes all entries of the current user
     *
     * @access public
     * @return boolean
     */
    public function removeAll() {
        global $wgDBprefix;

        $dbr &= wfGetDB(DB_SLAVE);
        $result = $dbr->query(sprintf(
            "DELETE FROM `%scurrent_edits`
            WHERE `user_id` = '%s'",
            $wgDBprefix, $this->_current_user_id
        ));

        if (!result) {
            throw new Exception('Could\'t remove user entries.');
        }

        return true;
    }
}
