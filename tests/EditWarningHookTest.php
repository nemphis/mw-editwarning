<?php

/**
 * Test cases for EditWarning hook.
 *
 * This file is part of the MediaWiki extension EditWarning. It contains
 * test cases for the EditWarning_edit hook.
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

/**
 * Constants for return values of hook functions.
 */
define( 'EDIT_ARTICLE_NEW',     1);
define( 'EDIT_ARTICLE_USER',    2);
define( 'EDIT_ARTICLE_OTHER',   3);
define( 'EDIT_ARTICLE_SECTION', 4);
define( 'EDIT_SECTION_NEW',     5);
define( 'EDIT_SECTION_USER',    6);
define( 'EDIT_SECTION_OTHER',   7);
define( 'EDIT_SECTION_ARTICLE', 8);

/**
 * Return value of fnEditWarning_getSection.
 */
$unitGetSection = 0;

require_once( "simpletest/autorun.php" );
require_once( "../EditWarning.php" );
require_once( "../EditWarning.class.php" );
require_once( "../EditWarningLock.class.php" );
require_once( "../../mw/includes/Defines.php" );
require_once( "../../mw/includes/PoolCounter.php" );
require_once( "../../mw/includes/Exception.php" );
require_once( "../../mw/includes/EditPage.php" );
require_once( "../../mw/includes/Article.php" );
require_once( "../../mw/includes/Title.php" );
require_once( "../../mw/includes/User.php" );

Mock::generate( "EditWarning" );
Mock::generate( "EditPage" );
Mock::generate( "EditWarningLock" );
Mock::generate( "Article" );
Mock::generate( "Title" );
Mock::generate( "User" );

class EditWarningHookTests extends UnitTestCase {
    /**
     * @access private
     * @var object EditWarning mock object.
     */
    private $_ew;

    /**
     * @access private
     * @var object MediaWiki EditPage mock object.
     */
    private $_ep;

    public function __construct() {
        $this->UnitTestCase( "EditWarning Hook Test" );
    }

    public function setUp() {
        $GLOBALS['wgUser'] = &new MockUser();
        $this->_ew = &new MockEditWarning();
        $this->_ep = &new MockEditPage();
        $this->_ep->mArticle = &new MockArticle();
        $this->_ep->mArticle->setReturnValue( "getID", 1 );
        $this->_ep->mArticle->mTitle = &new MockTitle();
        $this->_ep->mArticle->mTitle->setReturnValue( "getNamespace", 'NS_MAIN' );
    }

    public function tearDown() {
        unset( $this->_ew );
        unset( $this->_ep );
    }

    /**
     * Case:
     * The user opens an article for editing which nobody else is
     * currently working on.
     *
     * Assumptions:
     * - There are no locks.
     */
    public function testEditArticle_NobodyCase() {
        $GLOBALS['unitGetSection'] = 0;
        $this->_ew->setReturnValue("anyLock", false);
        $this->_ew->setReturnValue("isArticleLocked", false);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", false);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_ARTICLE_NEW);
    }

    /**
     * Case:
     * The user opens an article for editing which is already locked
     * by himself.
     *
     * Assumptions:
     * - There's an article lock by the user.
     */
    public function testEditArticle_HimselfCase() {
        $GLOBALS['unitGetSection'] = 0;
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", true);
        $this->_ew->setReturnValue("isArticleLockedByUser", true);
        $this->_ew->setReturnValue("anySectionLocks", false);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_ARTICLE_USER);
    }

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on it.
     *
     * Assumptions:
     * - There's an article lock by someone else.
     */
    public function testEditArticle_ArticleConflictCase() {
        $GLOBALS['unitGetSection'] = 0;
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", true);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", false);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_ARTICLE_OTHER);
    }

    /**
     * Case:
     * The user opens an article for editing while a section is
     * already locked by himself.
     *
     * Assumptions:
     * - There's a section lock by the user.
     */
    public function testEditArticle_OwnSectionConflictCase() {
        $GLOBALS['unitGetSection'] = 0;
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", false);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", true);
        $this->_ew->setReturnValue("anySectionLocksByUser", true);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_ARTICLE_NEW);
    }

    /**
     * Case:
     * The user opens an article for editing while someone else is
     * already working on a section of the article.
     *
     * Assumptions:
     * - There's only an section lock by someone else.
     */
    public function testEditArticle_SectionConflictCase() {
        $GLOBALS['unitGetSection'] = 0;
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", false);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", true);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", true);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_SECTION_OTHER);
    }

    /**
     * Case:
     * The user opens a section for editing while nobody else
     * is working on the article.
     *
     * Assumptions:
     * - No other locks
     */
    public function testEditSection_NobodyCase() {
        $GLOBALS['unitGetSection'] = 1;
        $this->_ew->setReturnValue("anyLock", false);
        $this->_ew->setReturnValue("isArticleLocked", false);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", false);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);
        $this->_ew->setReturnValue("isSectionLocked", false);
        $this->_ew->setReturnValue("isSectionLockedByUser", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_SECTION_NEW);
    }

    /**
     * Case:
     * The user opens a section of an article for editing while
     * the article is already locked by himself.
     *
     * Assumptions:
     * - Article lock by user
     */
    public function testEditSection_ArticleHimselfCase() {
        $GLOBALS['unitGetSection'] = 1;
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", true);
        $this->_ew->setReturnValue("isArticleLockedByUser", true);
        $this->_ew->setReturnValue("anySectionLocks", false);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);
        $this->_ew->setReturnValue("isSectionLocked", false);
        $this->_ew->setReturnValue("isSectionLockedByUser", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_SECTION_NEW);
    }

    /**
     * Case:
     * The user opens a section of an article for editing while
     * the article is already locked by someone else.
     *
     * Assumptions:
     * - Article lock by someone else
     */
    public function testEditSection_ArticleConflictCase() {
        $GLOBALS['unitGetSection'] = 1;
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", true);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", false);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);
        $this->_ew->setReturnValue("isSectionLocked", false);
        $this->_ew->setReturnValue("isSectionLockedByUser", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_ARTICLE_OTHER);
    }

    /**
     * Case:
     * The user opens a section of an article for editing which is
     * already locked by himself.
     *
     * Assumptions:
     * - There's a section lock by the user.
     */
    public function testEditSection_HimselfCase() {
        $GLOBALS['unitGetSection'] = 1;
        //$lock = &new MockEditWarningLock();
        //$lock->setReturnValue( "getSection", 1 );
        //$this->_ew->setReturnValue( "sectionLock", $lock );
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", false);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", true);
        $this->_ew->setReturnValue("anySectionLocksByUser", true);
        $this->_ew->setReturnValue("anySectionLocksByOthers", false);
        $this->_ew->setReturnValue("isSectionLocked", true);
        $this->_ew->setReturnValue("isSectionLockedByUser", true);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_SECTION_USER);
    }

    /**
     * Case:
     * The user opens a section of an article for editing while someone
     * else is already working on the same section.
     *
     * Assumptions:
     * - There's only a section lock by someone else.
     */
    public function testEditSection_SectionConflictCase() {
        $GLOBALS['unitGetSection'] = 1;
        //$lock = &new MockEditWarningLock();
        //$lock->setReturnValue( "getSection", 1 );
        //$this->_ew->setReturnValue( "sectionLock", $lock );
        $this->_ew->setReturnValue("anyLock", true);
        $this->_ew->setReturnValue("isArticleLocked", false);
        $this->_ew->setReturnValue("isArticleLockedByUser", false);
        $this->_ew->setReturnValue("anySectionLocks", true);
        $this->_ew->setReturnValue("anySectionLocksByUser", false);
        $this->_ew->setReturnValue("anySectionLocksByOthers", true);
        $this->_ew->setReturnValue("isSectionLocked", true);
        $this->_ew->setReturnValue("isSectionLockedByUser", false);

        $this->assertEqual(fnEditWarning_edit($this->_ew, $this->_ep), EDIT_SECTION_OTHER);
    }
}

?>
