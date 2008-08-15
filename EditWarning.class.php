<?php

class EditWarning {

	/**
	 * This array saves all editors of a page with id, name, timestamp
	 * and section.
	 *
	 * @type array
	 * @access private
	 */
	private $_editors = null;

    public function __construct() {

    }

    /**
     * Access-Method for $editors array.
     *
     * @access public
     * @return array editors array.
     */
    public function getEditors() {
    	return $this->_editors;
    }

    /**
     * Gets all editors of a page from database.
     *
     * @access public
     * @param int Article ID.
     * @return boolean True, if no error occured.
     */
    public function load($article_id) {
    	global $wgDBprefix;

    	$dbr       =& wfGetDB(DB_SLAVE);
        $timestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $query = sprintf(
            "SELECT * FROM `%scurrent_edits`" .
            "WHERE `page_id` = '%s' AND `timestamp` >= '%s'" .
            "ORDER BY `timestamp` DESC",
            $wgDBprefix,
            $article_id,
            $timestamp
        );

        if (!$result = $dbr->query($query)) {
            throw new Exception("load(): Error while getting data from database.");
        }

        $this->_editors = array();
        while ($row = $dbr->fetchObject($result)) {
            $this->_editors[] = array(
                'id'        => $row->id,
                'name'      => $row->name,
                'timestamp' => $row->timestamp,
                'section'   => $row->section
            );
        }

        return true;
    }

    // TODO: add() function to add a user to database.

    // TODO: update() function to update the timestamp of a user.

    /**
     * Removes the pagelock of an editor for a certain article.
     *
     * @access public
     * @param int User ID.
     * @param int Article ID.
     * @return boolean True, if no error occured.
     */
    public function remove($user_id, $article_id) {
    	global $wgDBprefix;

    	$dbr   =& wfGetDB(DB_SLAVE);
    	$query = sprintf(
    	    "DELETE FROM `%scurrent_edits`" .
    	    "WHERE `user_id` = '%s' AND `page_id` = '%s'",
    	    $wgDBprefix,
    	    $user_id,
    	    $article_id
    	);

    	if (!$result = $dbr->query($query)) {
    	    throw new Exception("remove(): Error while removing entry from database.");
    	}

    	return true;
    }

    /**
     * Removes all pagelocks of an editor.
     *
     * @access public
     * @param int User ID.
     * @return boolean True, if no error occured.
     */
    public function removeAll($user_id) {
    	global $wgDBprefix;

    	$dbr   =& wfGetDB(DB_SLAVE);
    	$query = sprintf(
    	    "DELETE FROM `%scurrent_edits`" .
    	    "WHERE `user_id` = '%s'",
    	    $wgDBprefix,
    	    $user_id
    	);

    	if (!$result = $dbr->query($query)) {
    	    throw new Exception("removeAll(): Error while removing entries from database.");
    	}

    	return true;
    }

    /**
     * Checks if someone is working on the article.
     *
     * @access public
     * @return boolean True, if someone is working on the article, else false
     */
    public function activeEditing() {
    	if ($this->getEditors() == null) {
    	    throw new Exception("Error! The editors array is unset.");
    	}

    	if (count($this->getEditors()) > 0) {
    	    return true;
    	} else {
    	    return false;
    	}
    }

    /**
     * Checks if someone is working on an article's section.
     *
     * @access public
     * @return boolean True, if someone is working on an article's section, else false.
     */
    public function sectionEditing() {
    	if ($this->getEditors() == null) {
    	    throw new Exception("Error! The editors array is unset.");
    	}

    	foreach ($this->getEditors() as $editor) {
    	    if ($editor['section'] != 0) {
    	        return true;
    	    }
    	}

    	return false;
    }

    /**
     * Checks if a certain user is working on the article.
     *
     * @access public
     * @param int User ID.
     * @return boolean True, if the user is working on the article, else false
     */
    public function isUserEditing($user_id) {
        if ($this->getEditors() == null) {
            throw new Exception("Error! The editors array is unset.");
        }

        foreach ($this->getEditors() as $editor) {
            if ($editor['id'] == $user_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the user working on a certain article.
     *
     * @access public
     * @return array Array with ID, name and timestamp
     */
    public function getArticleEditor() {
    	$editors = $this->getEditors();

    	if (count($editors) > 0) {
    		return $editors[0];
    	} else {
    	    return false;
    	}
    }

    /**
     * Get the user working on a certain article section.
     *
     * @access public
     * @param int Section ID.
     * @return array Array with ID, name and timestamp or false
     */
    public function getSectionEditor($section_id) {
    	foreach ($this->getEditors() as $editor) {
    		if ($editor['section'] == $section_id) {
    		    return $editor;
    		}
    	}

    	return false;
    }
}

?>