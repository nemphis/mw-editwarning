<?php

require_once("simpletest/autorun.php");
require_once("../EditWarning.class.php");
require_once("Mock_DatabaseConnection.php");

Mock::generate('DatabaseConnection');

class EditWarning_FunctionalTests extends UnitTestCase {

    private $p;
    private $_connection;
    private $_request;
    private $_argument;
    private $_values;

    private function setDBvalues() {
    	$this->_connection->setReturnValue( 'select', $this->_request, $this->_argument );
        for( $i=0; $i<count($this->_values); $i++) {
            $this->_connection->setReturnReferenceAt( $i, 'fetchRow', $this->_values[$i], $this->_request );
        }
    }

    public function __construct() {}

    public function setUp() {
        $this->p           = new EditWarning( 3, 1 );
        $this->_connection = &new MockDatabaseConnection();
        $this->_request    = "ARTICLE_LOCKS";
        $this->_argument   = array( "editwarning_locks", "*", "`article_id` = '1'" );
    }
    public function tearDown() {
        unset($this->p);
    }

    /**
     * Case:
     * The user opens an article for editing which nobody else is
     * currently working on.
     *
     * Assumptions:
     * - There are no locks.
     */
    public function testArticleEditing_NobodyCase() {
    	$this->_values = null;
    	$this->setDBvalues();

        $this->p->load( $this->_connection );
        $this->assertFalse( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case:
     * The user opens an article for editing which is already locked
     * by himself.
     *
     * Assumptions:
     * - There's only an article lock by the user.
     */
    public function testArticleEditing_HimselfCase() {
        $this->_values = array(
          array(
            'id'         => 1,
            'user_id'    => 3,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
            'section'    => 0
          )
        );
        $this->setDBvalues();

        $this->p->load( $this->_connection );
        $this->assertTrue( $this->p->anyLock() );
        $this->assertTrue( $this->p->articleLock() );
        $this->assertIsA( $this->p->articleUserLock(), "EditWarning_Lock" );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on it.
     *
     * Assumptions:
     * - There's only an article lock by someone else.
     */
    public function testArticleEditing_ArticleConflictCase() {
        $this->_values = array(
          array(
            'id'         => 1,
            'user_id'    => 4,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
            'section'    => 0
          )
        );
        $this->setDBvalues();

        $this->p->load( $this->_connection );
        $this->assertTrue( $this->p->anyLock() );
        $this->assertTrue( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on a section of the article.
     *
     * Assumptions:
     * - There's only an section lock by someone else.
     */
    public function testArticleEditing_SectionConflictCase() {
        $this->_values = array(
          array(
            'id'         => 1,
            'user_id'    => 4,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
            'section'    => 1
          )
        );
        $this->setDBvalues();
        
        $this->p->setSection( 1 );
        $this->p->load( $this->_connection );
        $this->assertTrue( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertTrue( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case:
     * The user opens a section of an article for editing which is
     * already locked by himself.
     *
     * Assumptions:
     * - There's only a section lock by the user.
     */
    public function testSectionEditing_HimselfCase() {
        $this->_values = array(
          array(
            'id'         => 1,
            'user_id'    => 3,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
            'section'    => 1
          )
        );
        $this->setDBvalues();

        $this->p->load( $this->_connection );
        $this->assertTrue( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertTrue( $this->p->sectionLock() );
        $this->assertTrue( $this->p->sectionUserLock() );
    }

    /**
     * Case:
     * The user opens a section of an article for editing while someone
     * else is already working on the same section.
     *
     * Assumptions:
     * - There's only a section lock by someone else.
     */
    public function testSectionEditing_SectionConflictCase() {
        $this->_values = array(
          array(
            'id'         => 1,
            'user_id'    => 4,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
            'section'    => 1
          )
        );
        $this->setDBvalues();

        $this->p->setSection( 1 );
        $this->p->load( $this->_connection );
        $this->assertTrue( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertTrue( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }
}

?>
