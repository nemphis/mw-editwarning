<?php

/**
 * Implementation of EditWarning class.
 * 
 * This file is part of the MediaWiki extension EditWarning. It contains
 * the implementation of EditWarning class with functions to add, edit,
 * delete and check for article locks.
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

require_once( "EditWarningLock.class.php" );

/**
 * getTimestamp: Return a timestamp with x minutes in the future.
 */
define("TIMESTAMP_NEW", 1);
/**
 * getTimestamp: Return a timestamp with x minutes in the past.
 */
define("TIMESTAMP_EXPIRED", 2);

class InvalidTypeArgumentException extends Exception {}

class EditWarning {
	/**
	 * @access private
	 * @var int Contains the ID of the current user. 
	 */
    private $_user_id;
    
    /**
     * @access private
     * @var int Contains the ID of the current article.
     */
    private $_article_id;
    
    /**
     * @access private
     * @var int Contains the ID of the current section (optional).
     */
    private $_section;
    
    /**
     * @access private
     * @var mixed Array containing lock objects.
     */
    private $_article_locks = array();

    /**
     * @access public
     * @param int $user_id ID of the current user.
     * @param int $article_id ID of the current article (optional).
     * @param int $section ID of the current section (optional).
     */
    public function __construct( $user_id, $article_id = null, $section = null ) {
    	$this->setUserID( $user_id );
    	$this->setArticleID( $article_id );
    	$this->setSection( $section );
    }

    /**
     * Recieves data from database sets object values and creates
     * lock objects.
     * 
     * @access public
     * @param object $dbr Database connection object.
     */
    public function load( $dbr ) {
    	// Build conditions for select operation.
    	$conditions  = sprintf( "`article_id` = '%s'", $this->getArticleID() );
    	$conditions .= sprintf( " AND `timestamp` >= '%s'", $this->getTimestamp( TIMESTAMP_EXPIRED ) );
    	if ( $this->getSection() != null ) {
    		$conditions .= sprintf( " AND `section` = '%s'", $this->getSection() );
    	}
        $result = $dbr->select( "editwarning_locks", "*", $conditions );

        // Create lock objects for every valid lock.
        while ( $row = $dbr->fetchRow( $result ) ) {
            $this->addLock($this, $row);
        }
    }

    /**
     * Creates timestamp with x minutes (depends on settings) in the future/past.
     * Future timestamps are used for new and updated article locks, past timestamps
     * are used to get all locks with valid timestamp.
     * 
     * @access public
     * @param  int $type Which type of timestamp should be created. Use TIMESTAMP_NEW
     *                   or TIMESTAMP_EXPIRED constant.
     * @return int Unix timestamp.
     */
    public function getTimestamp( $type ) {
    	global $EditWarning_Timeout;

        switch ( $type ) {
            case TIMESTAMP_NEW:
              return mktime( date("H"), date("i") + $EditWarning_Timeout, date("s"), date("m"), date("d"), date("Y") );
              break;
            case TIMESTAMP_EXPIRED:
              return mktime( date("H"), date("i") - $EditWarning_Timeout, date("s"), date("m"), date("d"), date("Y") );
              break;
            default:
              throw new InvalidTypeArgumentException( "Invalid argument for type. Use TIMESTAMP_NEW or TIMESTAMP_EXPIRED constant.");
        }
    }

    /**
     * @access public
     * @return int Id of the current user.
     */
    public function getUserID() {
        return $this->_user_id;
    }

    /**
     * @access public
     * @param int $user_id Id of the current user.
     */
    public function setUserID( $user_id ) {
        $this->_user_id = $user_id;
    }

    /**
     * @access public
     * @return int Id of the current article.
     */
    public function getArticleID() {
        return $this->_article_id;
    }

    /**
     * @access public
     * @param int $article_id Id of the current article.
     */
    public function setArticleID( $article_id ) {
        $this->_article_id = $article_id;
    }

    /**
     * @access public
     * @return int Id of the current section.
     */
    public function getSection() {
        return $this->_section;
    }
    
    /**
     * @access public
     * @param int $section Id of the current section.
     */
    public function setSection( $section ) {
        $this->_section = $section;
    }

    /**
     * Checks if there is any valid article lock.
     * 
     * @access public
     * @return bool Returns true if there is at least one lock, else false.
     */
    public function anyLock() {
        if ( count( $this->getLocks() ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if there is a lock for the whole article.
     * 
     * @access public
     * @return object|bool Returns the EditWarningLock object or false.
     */
    public function articleLock() {
        $lock_objects = $this->getLocks();
        foreach ( $lock_objects as $lock ) {
            if ( $lock->getSection() == 0 ) {
                return $lock;
            }
        }
        return false;
    }

    /**
     * Checks if there is a lock for the whole article by the current user.
     * 
     * @access public
     * @return object|bool Returns the EditWarningLock object or false.
     */
    public function articleUserLock() {
        $lock_objects = $this->getLocks();
        foreach( $lock_objects as $lock ) {
            if( $lock->getSection() == 0 && $lock->getUserID() == $this->getUserID() ) {
                return $lock;
            }
        }
        return false;
    }

    /**
     * Checks if there is a section lock for the article.
     * 
     * @access public
     * @return object|bool Returns the EditWarningLock object or false.
     */
    public function sectionLock() {
        $lock_objects = $this->getLocks();
        foreach ( $lock_objects as $lock ) {
            if ( $lock->getSection() > 0 ) {
                return $lock;
            }
        }
        return false;
    }

    /**
     * Checks if there is a section lock for the article by the
     * current user.
     * 
     * @access public
     * @return object|mixed Returns the EditWarningLock object or false.
     */
    public function sectionUserLock() {
        $lock_objects = $this->getLocks();
        foreach ( $lock_objects as $lock ) {
            if ( $lock->getSection() > 0 && $lock->getUserID() == $this->getUserID() ) {
                return $lock;
            }
        }
        return false;
    }

    /**
     * @access public
     * @return mixed Array with EditWarningLock objects.
     */
    public function getLocks() {
        return $this->_article_locks;
    }

    /**
     * Create EditWarningLock object and add it to _article_locks array.
     * 
     * @access public
     * @param mixed $parent Reference to EditWarning class.
     * @param array $db_row Values of one database result row.
     */
    public function addLock( $parent, $db_row ) {
        $this->_article_locks[] = new EditWarningLock( $parent, $db_row );
    }

    /**
     * Saves a new lock into the database.
     * 
     * @access public
     * @param object $dbw MediaWiki write connection object.
     * @param int $user_id Id of the current user.
     * @param string $user_name Name of the current user.
     * @param int $section Id of the current section (0 for no section).
     */
    public function saveLock( $dbw, $user_id, $user_name, $section = 0 ) {
    	$values = array(
    	  'user_id'    => $user_id,
    	  'user_name'  => $user_name,
    	  'article_id' => $this->getArticleID(),
    	  'timestamp'  => $this->getTimestamp( TIMESTAMP_NEW ),
    	  'section'    => $section
    	);
    	$dbw->insert( "editwarning_locks", $values );
    }

    /**
     * Update the timestamp of a lock.
     * 
     * @access public
     * @see getTimestamp()
     * @param object $dbw MediaWiki write connection object.
     * @param int $user_id Id of the current user.
     * @param string $user_name Name of the current user.
     * @param int $section Id of the current section (0 for no section).
     */
    public function updateLock( $dbw, $user_id, $user_name, $section = 0 ) {
        $value      = array( "timestamp" => $this->getTimestamp( TIMESTAMP_NEW ) );
        $conditions = array(
          'user_id'    => $user_id,
          'article_id' => $this->getArticleID(),
          'section'    => $section
        );
        $dbw->update( "editwarning_locks", $value, $conditions );
    }

    /**
     * Removes a lock from the databse.
     * 
     * @access public
     * @param object $dbw MediaWiki write connection object.
     * @param int $user_id Id of the current user.
     * @param string $user_name Name of the current user.
     * @param int $section Id of the current section (0 for no section).
     */
    public function removeLock( $dbw, $user_id, $user_name ) {
        $conditions = array(
          'user_id'    => $user_id,
          'article_id' => $this->getArticleID()
        );
        $dbw->delete( "editwarning_locks", $conditions );
    }

    /**
     * Remove all locks of an user from the database.
     * 
     * @access public
     */
    public function removeUserLocks() {
        $condition = array( 'user_id' => $this->getUserID() );
        $dbw->delete( "editwarning_locks", $condition );
    }
}
