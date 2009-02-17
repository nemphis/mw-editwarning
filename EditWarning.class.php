<?php

class EditWarning {
    private $_user_id;
    private $_article_id;
    private $_section;
    private $_article_locks;

    public function __construct() {}

    public function load( $db_connection, $article_id ) {}
    public function getArticleID() {}
    public function setArticleID() {}
    public function anyLock() {}
    public function articleLock() {}
    public function articleUserLock() {}
    public function sectionLock() {}
    public function sectionUserLock() {}
    public function addLock() {}
    public function updateLock() {}
    public function removeLock() {}
    public function removeUserLocks() {}
}