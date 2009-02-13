<?php

class EditWarningTest extends UnitTestCase {

    private $p;

    public function __construct() {}

    public function setUp() {
        $this->p = new EditWarning( 3 );
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
    public function testArticleEditing_NobodyCase1() {
    	$connection = &new MockDatabaseConnection();
        $request    = "ARTICLE_LOCKS";
        $argument   = array( "editwarning_locks", "*", "`article_id` = '1'" );
        $values     = null;

        $connection->setReturnValue( 'select', $request, $argument );
        $connection->setReturnReferenceAt( 0, 'fetchRow', $values, $request );

        $this->p->load( $connection, 1 );
        $this->assertFalse( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by the user.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_NobodyCase2() {
        $connection = &new MockDatabaseConnection();
        $request    = "ARTICLE_LOCKS";
        $argument   = array( "editwarning_locks", "*", "`article_id` = '1'" );
        $values     = array(
          array(
            'id'            => 1,
            'user_id'       => 3,
            'article_id'    => 1,
            'timestamp'     => mktime( 0,0,0, 1, 1, 1970 ),
            'section'       => 0
          ),
          array(
            'id'           => 2,
            'user_id'      => 4,
            'article_id'   => 5,
            'timestamp'    => date(),
            'section'      => 0
          ),
          array(
            'id'           => 3,
            'user_id'      => 5,
            'article_id'   => 6,
            'timestamp'    => date(),
            'section'      => 1
          )
        );

        $connection->setReturnValue( 'select', $request, $argument );
        $connection->setReturnReferenceAt( 0, $values[0], $request );
        $connection->setReturnReferenceAt( 1, $values[1], $request );
        $connection->setReturnReferenceAt( 2, $values[2], $request );

        $this->p->load( $connection, 1 );
        $this->assertFalse( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_NobodyCase3() {
        $connection = &new MockDatabaseConnection();
        $request = "ARTICLE_LOCKS";
        $argument = array( "editwarning_locks", "*", "`article_id` = '1'" );
        $values = array(
          array(
            'id'         => 1,
            'user_id'    => 4,
            'article_id' => 1,
            'timestamp'  => date(),
            'section'    => 0
          ),
          array(
            'id'         => 2,
            'user_id'    => 5,
            'article_id' => 5,
            'timestamp'  => date(),
            'section'    => 0
          ),
          array(
            'id'         => 3,
            'user_id'    => 6,
            'article_id' => 6,
            'timestamp'  => date(),
            'section'    => 1
          )
        );

        $connection->setReturnValue( 'select', $request, $argument );
        $connection->setReturnReferenceAt( 0, $values[0], $request );
        $connection->setReturnReferenceAt( 1, $values[1], $request );
        $connection->setReturnReferenceAt( 2, $values[2], $request );

        $this->p->load( $connection, 1 );
        $this->assertFalse( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by the user.
     * - There are locks of other articles and sections by the user.
     */
    public function testArticleEditing_NobodyCase4() {
        $connection = &new MockDatabaseConnection();
        $request = "ARTICLE_LOCKS";
        $argument = array( "editwarning_locks", "*", "`article_id` = '1'" );
        $values = array(
          array(
            'id'         => 1,
            'user_id'    => 3,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, 1, 1, 1970 ),
            'section'    => 0
          ),
          array(
            'id'         => 2,
            'user_id'    => 3,
            'article_id' => 5,
            'timestamp'  => date(),
            'section'    => 0
          ),
          array(
            'id'         => 3,
            'user_id'    => 3,
            'article_id' => 6,
            'timestamp'  => date(),
            'section'    => 1
          )
        );

        $connection->setReturnValue( 'select', $request, $argument );
        $connection->setReturnReferenceAt( 0, $values[0], $request );
        $connection->setReturnReferenceAt( 1, $values[1], $request );
        $connection->setReturnReferenceAt( 2, $values[2], $request );

        $this->p->load( $connection, 1 );
        $this->assertFalse( $this->p->anyLock() );
        $this->assertFalse( $this->p->articleLock() );
        $this->assertFalse( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by someone else.
     * - There are locks of other articles and sections by the user.
     */
    public function testArticleEditing_NobodyCase5() {
        $connection = &new MockDatabaseConnection();
        $request = "ARTICLE_LOCKS";
        $argument = array( "editwarning_locks", "*", "`article_id` = '1'" );
        $values = array(
          array(
            'id'         => 1,
            'user_id'    => 4,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, 1, 1, 1970 ),
            'section'    => 0
          ),
          array(
            'id'         => 2,
            'user_id'    => 3,
            'article_id' => 5,
            'timestamp'  => date(),
            'section'    => 0
          ),
          array(
            'id'         => 3,
            'user_id'    => 3,
            'article_id' => 6,
            'timestamp'  => date(),
            'section'    => 1
          )
        );

        $connection->setReturnValue( 'select', $request, $argument );
        $connection->setReturnReferenceAt( 0, $values[0], $request );
        $connection->setReturnReferenceAt( 1, $values[1], $request );
        $connection->setReturnReferenceAt( 2, $values[2], $request );

        $this->p->load( $connection, 1 );
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
    public function testArticleEditing_HimselfCase1() {
        $connection = &new MockDatabaseConnection();
        $request = "ARTICLE_LOCKS";
        $argument = array( "editwarning_locks", "*", "`article_id` = '1'" );
        $values = array(
          array(
            'id'         => 1,
            'user_id'    => 3,
            'article_id' => 1,
            'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
            'section'    => 0
          )
        );

        $connection->setReturnValue( 'select', $request, $argument );
        $connection->setReturnReferenceAt( 0, $values[0], $request );
        $connection->setReturnReferenceAt( 1, $values[1], $request );
        $connection->setReturnReferenceAt( 2, $values[2], $request );

        $this->p->load( $connection, 1 );
        $this->assertTrue( $this->p->anyLock() );
        $this->assertTrue( $this->p->articleLock() );
        $this->assertTrue( $this->p->articleUserLock() );
        $this->assertFalse( $this->p->sectionLock() );
        $this->assertFalse( $this->p->sectionUserLock() );
    }

    /**
     * Case: Same as testArticleEditing_HimselfCase1
     *
     * Assumptions:
     * - There's an article lock by the user.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_HimselfCase2() {}

    /**
     * Case: Same as testArticleEditing_HimselfCase1
     *
     * Assumptions:
     * - There's an article lock by the user.
     * - There are locks of other articles and sections by the user.
     */
    public function testArticleEditing_HimselfCase3() {}

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on it.
     *
     * Assumptions:
     * - There's only an article lock by someone else.
     */
    public function testArticleEditing_ArticleConflictCase1() {}

    /**
     * Case: Same as testArticleEditing_ArticleConflictCase1.
     *
     * Assumptions:
     * - There's an article lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_ArticleConflictCase2() {}

    /**
     * Case: Same as testArticleEditing_ArticleConflictCase1.
     *
     * Assumptions:
     * - There's an article lock by someone else.
     * - There are locks of other articles and sections by the user.
     */
    public function testArticleEditing_ArticleConflictCase3() {}

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on a section of the article.
     *
     * Assumptions:
     * - There's only an section lock by someone else.
     */
    public function testArticleEditing_SectionConflictCase1() {}

    /**
     * Case: Same as testArticleEditing_SectionConflictCase1
     *
     * Assumptions:
     * - There's an section lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_SectionConflictCase2() {}

    /**
     * Case: Same as testArticleEditing_SectionConflictCase2
     *
     * Assumptions:
     * - There's an section lock by someone else.
     * - There are locks of other articles an sections by the user.
     */
    public function testArticleEditing_SectionConflictCase3() {}

    /**
     * Case:
     * The user opens a section of an article for editing which nobody
     * else is currently working on.
     *
     * Assumptions:
     * - There are no locks.
     */
    public function testSectionEditing_NobodyCase1() {}

    /**
     * Case: Same as testSectionEditing_NobodyCase1
     *
     * Assumptions:
     * - There's an expired section lock by the user.
     * - There are locks of other articles and sections by other users.
     */
    public function testSectionEditing_NobodyCase2() {}

    /**
     * Case: Same as testSectionEditing_NobodyCase1
     *
     * Assumptions:
     * - There's an expired section lock by the user.
     * - There are locks of other articles and sections by the user.
     */
    public function testSectionEditing_NobodyCase3() {}

    /**
     * Case: Same as testSectionEditing_NobodyCase1
     *
     * Assumptions:
     * - There's an expired section lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testSectionEditing_NobodyCase4() {}

    /**
     * Case: Same as testSectionEditing_NobodyCase1
     *
     * Assumptions:
     * - There's an expired section lock by someone else.
     * - There are locks of other articles and sections by the user.
     */
    public function testSectionEditing_NobodyCase5() {}

    /**
     * Case:
     * The user opens a section of an article for editing which is
     * already locked by himself.
     *
     * Assumptions:
     * - There's only a section lock by the user.
     */
    public function testSectionEditing_HimselfCase1() {}

    /**
     * Case: Same as testSectionEditing_HimselfCase1
     *
     * Assumptions:
     * - There's a section lock by the user.
     * - There are locks of other articles and sections by other users.
     */
    public function testSectionEditing_HimselfCase2() {}

    /**
     * Case: Same as testSectionEditing_HimselfCase1
     *
     * Assumptions:
     * - There's a section lock by the user.
     * - There are locks of other articles and sections by the user.
     */
    public function testSectionEditing_HimselfCase3() {}

    /**
     * Case:
     * The user opens a section of an article for editing while someone
     * else is already working on the same section.
     *
     * Assumptions:
     * - There's only a section lock by someone else.
     */
    public function testSectionEditing_SectionConflictCase1() {}

    /**
     * Case: Same as testSectionEditing_SectionConflictCase1
     *
     * Assumptions:
     * - There's a section lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testSectionEditing_SectionConflictCase2() {}

    /**
     * Case: Same as testSectionEditing_SectionConflictCase1
     *
     * Assumptions:
     * - There's a section lock by someone else.
     * - There are locks of other articles ans sections by the user.
     */
    public function testSectionEditing_SectionConflictCase3() {}

    /**
     * Case:
     * The user opens a section of an article for editing while someone
     * else is already working on the whole article.
     *
     * Assumptions:
     * - There's only an article lock by someone else.
     */
    public function testSectionEditing_ArticleConflictCase1() {}

    /**
     * Case: Same as testSectionEditing_ArticleConflictCase1
     *
     * Assumptions:
     * - There's an article lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testSectionEditing_ArticleConflictCase2() {}

    /**
     * Case: Same as testSectionEditing_ArticleConflictCase1
     *
     * Assumptions:
     * - There's an article lock by someone else.
     * - There are locks of other articles and sections by the user.
     */
    public function testSectionEditing_ArticleConflictCase3() {}
}

?>
