<?php

class EditWarningTest extends UnitTestCase {

    private $p;

    public function __construct() {}

    public function setUp() {
        $this->p = new EditWarning();
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
    public function testArticleEditing_NobodyCase1() {}

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by the user.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_NobodyCase2() {}

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by someone else.
     * - There are locks of other articles and sections by other users.
     */
    public function testArticleEditing_NobodyCase3() {}

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by the user.
     * - There are locks of other articles and sections by the user.
     */
    public function testArticleEditing_NobodyCase4() {}

    /**
     * Case: Same as testArticleEditing_NobodyCase1.
     *
     * Assumptions:
     * - There's an expired article lock by someone else.
     * - There are locks of other articles and sections by the user.
     */
    public function testArticleEditing_NobodyCase5() {}

    /**
     * Case:
     * The user opens an article for editing which is already locked
     * by himself.
     *
     * Assumptions:
     * - There's only an article lock by the user.
     */
    public function testArticleEditing_HimselfCase1() {}

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
