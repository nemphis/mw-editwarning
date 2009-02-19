<?php

define("TIMESTAMP_NEW", 1);
define("TIMESTAMP_EXPIRED", 2);

class EditWarning {
    private $_user_id;
    private $_article_id;
    private $_section;
    private $_article_locks = array();

    public function __construct( $user_id, $article_id = null, $section = null ) {
        $this->setUserID( $user_id );
        $this->setArticleID( $article_id );
        $this->setSection( $section );
    }

    public function load( $dbr ) {
    	$conditions  = sprintf( "`article_id` = '%s'", $this->getArticleID() );
    	$conditions .= sprintf( " AND `timestamp` >= '%s'", $this->getTimestamp( TIMESTAMP_EXPIRED ) );
    	if ( $this->getSection() != null ) {
    		$conditions .= sprintf( " AND `section` = '%s'", $this->getSection() );
    	}
        $result = $dbr->select( "editwarning_locks", "*", $conditions );

        while ( $row = $dbr->fetchRow( $result ) ) {
            $this->addLock($this, $row);
        }
    }

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
              // TODO: Exception!
        }
    }

    public function getUserID() {
        return $this->_user_id;
    }

    public function setUserID( $user_id ) {
        $this->_user_id = $user_id;
    }

    public function getArticleID() {
        return $this->_article_id;
    }

    public function setArticleID( $article_id ) {
        $this->_article_id = $article_id;
    }

    public function getSection() {
        return $this->_section;
    }
    public function setSection( $section ) {
        $this->_section = $section;
    }

    public function anyLock() {
        if ( count( $this->getLocks() ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    public function articleLock() {
        $lock_objects = $this->getLocks();
        foreach ( $lock_objects as $lock ) {
            if ( $lock->getSection() == 0 ) {
                return $lock;
            }
        }
        return false;
    }

    public function articleUserLock() {
        $lock_objects = $this->getLocks();
        foreach( $lock_objects as $lock ) {
            if( $lock->getSection() == 0 && $lock->getUserID == $this->getUserID() ) {
                return $lock;
            }
        }
        return false;
    }

    public function sectionLock() {
        $lock_objects = $this->getLocks();
        foreach ( $lock_objects as $lock ) {
            if ( $lock->getSection() > 0 ) {
                return $lock;
            }
        }
        return false;
    }

    public function sectionUserLock() {
        $lock_objects = $this->getLocks();
        foreach ( $lock_objects as $lock ) {
            if ( $lock->getSection() > 0 && $lock->getUserID() == $this->getUserID() ) {
                return $lock;
            }
        }
        return false;
    }

    public function getLocks() {
        return $this->_article_locks;
    }

    public function addLock( $parent, $db_row ) {
        $this->_article_locks[] = new EditWarning_Lock( $parent, $db_row );
    }

    public function addLock( $dbw, $user_id, $user_name, $section ) {
    	$values = array(
    	  'user_id'    => $user_id,
    	  'user_name'  => $user_name,
    	  'article_id' => $this->getArticleID(),
    	  'timestamp'  => $this->getTimestamp( TIMESTAMP_NEW ),
    	  'section'    => $section
    	);
    	$dbw->insert( "editwarning_locks", $values );
    }

    public function updateLock( $dbw, $user_id, $user_name, $section ) {
        $value      = array( "timestamp" => $this->getTimestamp( TIMESTAMP_NEW ) );
        $conditions = array(
          'user_id'    => $user_id,
          'article_id' => $this->getArticleID(),
          'section'    => $section
        );
        $dbw->update( "editwarning_locks", $value, $conditions );
    }

    public function removeLock( $dbw, $user_id, $user_name, $section ) {
        $conditions = array(
          'user_id'    => $user_id,
          'article_id' => $this->getArticleID(),
          'section'    => $section
        );
        $dbw->delete( "editwarning_locks", $conditions );
    }

    public function removeUserLocks() {
        $condition = array( 'user_id' => $this->getUserID() );
        $dbw->delete( "editwarning_locks", $condition );
    }
}

class EditWarning_Lock {
	private $parent;
    private $user_id;
    private $user_name;
    private $section;
    private $timestamp;

    public function __construct($parent, $db_row) {
        $this->setParent( $parent );
        $this->setUserID( $db_row['user_id'] );
        $this->setUserName( $db_row['user_name'] );
        $this->setSection( $db_row['section'] );
        $this->setTimestamp( $db_row['timestamp'] );
    }

    public function getParent() {
        return $this->_parent;
    }

    public function setParent( $parent ) {
        $this->_parent = $parent;
    }

    public function getUserID() {
        return $this->_user_id;
    }

    public function setUserID( $user_id ) {
        $this->_user_id = $user_id;
    }

    public function getUserName() {
        return $this->_user_name;
    }

    public function setUserName( $user_name ) {
        $this->_user_name = $user_name;
    }

    public function getSection() {
        return $this->_section;
    }

    public function setSection( $section ) {
        $this->_section = $section;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setTimestamp( $timestamp ) {
        $this->_timestamp = $timestamp;
    }
}