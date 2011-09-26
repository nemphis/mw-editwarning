<?php

/**
 * Test cases for EditWarning class functions.
 *
 * This file is part of the MediaWiki extension EditWarning. It contains
 * test cases for the EditWarning class functions.
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
 * @author      Thomas David <nemphis@code-geek.de>
 * @copyright   2007-2011 Thomas David <nemphis@code-geek.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later
 * @version     0.4-rc1
 * @category    Extensions
 * @package     EditWarning
 */

if ( !defined( 'EDITWARNING_UNITTEST' ) ) {
    define( 'EDITWARNING_UNITTEST', true );
}

require_once( "simpletest/autorun.php" );
require_once( "../EditWarning.class.php" );
require_once( "Mock_DatabaseConnection.php" );

Mock::generate( "DatabaseConnection" );

class EditWarning_ClassTests extends UnitTestCase {

    private $_p;
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

    public function __construct() {
        $this->UnitTestCase( "EditWarning Class Test" );
    }

    public function setUp() {
        $this->_p           = new EditWarning( 3, 1 );
        $this->_connection = &new MockDatabaseConnection();
        $this->_request    = "ARTICLE_LOCKS";
        $this->_argument   = array( "editwarning_locks", "*", "`article_id` = '1'" );
    }
    public function tearDown() {
        unset($this->_p);
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

        $this->_p->load($this->_connection);
        $this->assertFalse($this->_p->anyLock());
        $this->assertFalse($this->_p->isArticleLocked());
        $this->assertFalse($this->_p->isArticleLockedByUser());
        $this->assertFalse($this->_p->anySectionLocks());
        $this->assertFalse($this->_p->anySectionLocksByUser());
        $this->assertFalse($this->_p->anySectionLocksByOthers());
    }

    /**
     * Case:
     * The user opens an article for editing which is already locked
     * by himself.
     *
     * Assumptions:
     * - There's an article lock by the user.
     */
    public function testArticleEditing_HimselfCase() {
        $this->_values = array(
            array(
                'id'         => 1,
                'user_id'    => 3,
                'user_name'  => "Unittest",
                'article_id' => 1,
                'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
                'section'    => 0
            )
        );
        $this->setDBvalues();

        $this->_p->load($this->_connection);
        $this->assertTrue($this->_p->anyLock());
        $this->assertTrue($this->_p->isArticleLocked());
        $this->assertTrue($this->_p->isArticleLockedByUser());
        $this->assertIsA($this->_p->getArticleLock(), "EditWarningLock");
        $this->assertFalse($this->_p->anySectionLocks());
        $this->assertFalse($this->_p->anySectionLocksByUser());
        $this->assertFalse($this->_p->anySectionLocksByOthers());
    }

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on it.
     *
     * Assumptions:
     * - There's an article lock by someone else.
     */
    public function testArticleEditing_ArticleConflictCase() {
        $this->_values = array(
            array(
                'id'         => 1,
                'user_id'    => 4,
                'user_name'  => "Unittest",
                'article_id' => 1,
                'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
                'section'    => 0
            )
        );
        $this->setDBvalues();

        $this->_p->load($this->_connection);
        $this->assertTrue($this->_p->anyLock());
        $this->assertTrue($this->_p->isArticleLocked());
        $this->assertFalse($this->_p->isArticleLockedByUser());
        $this->assertFalse($this->_p->anySectionLocks());
        $this->assertFalse($this->_p->anySectionLocksByUser());
        $this->assertFalse($this->_p->anySectionLocksByOthers());
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
                'user_name'  => "Unittest",
                'article_id' => 1,
                'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
                'section'    => 1
            )
        );
        $this->setDBvalues();

        $this->_p->setSection(1);
        $this->_p->load($this->_connection);
        $this->assertTrue($this->_p->anyLock());
        $this->assertFalse($this->_p->isArticleLocked());
        $this->assertFalse($this->_p->isArticleLockedByUser());
        $this->assertTrue($this->_p->anySectionLocks());
        $this->assertFalse($this->_p->anySectionLocksByUser());
        $this->assertTrue($this->_p->anySectionLocksByOthers());
    }

    /**
     * Case:
     * The user opens a section of an article for editing which is
     * already locked by himself.
     *
     * Assumptions:
     * - There's a section lock by the user.
     */
    public function testSectionEditing_HimselfCase() {
        $this->_values = array(
           array(
               'id'         => 1,
               'user_id'    => 3,
               'user_name'  => "Unittest",
               'article_id' => 1,
               'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
               'section'    => 1
           )
        );
        $this->setDBvalues();

        $this->_p->load($this->_connection );
        $this->assertTrue($this->_p->anyLock());
        $this->assertFalse($this->_p->isArticleLocked());
        $this->assertFalse($this->_p->isArticleLockedByUser());
        $this->assertTrue($this->_p->anySectionLocks());
        $this->assertTrue($this->_p->anySectionLocksByUser());
        $this->assertFalse($this->_p->anySectionLocksByOthers());
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
                'user_name'  => "Unittest",
                'article_id' => 1,
                'timestamp'  => mktime( 0,0,0, date("m"), date("d")+1, date("Y") ),
                'section'    => 1
            )
        );
        $this->setDBvalues();

        $this->_p->setSection(1);
        $this->_p->load($this->_connection);
        $this->assertTrue($this->_p->anyLock());
        $this->assertFalse($this->_p->isArticleLocked());
        $this->assertFalse($this->_p->isArticleLockedByUser());
        $this->assertTrue($this->_p->anySectionLocks());
        $this->assertFalse($this->_p->anySectionLocksByUser());
        $this->assertTrue($this->_p->anySectionLocksByOthers());
    }
}

?>
