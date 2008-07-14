<?php

/**
 * MediaWiki extension: EditWarning
 *
 * Warns user editing a page that's currently being edited.
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

if (defined('MEDIAWIKI')) {
    global $IP;

    define('EWDEBUG', false);
    require_once $IP . "/extensions/EditWarning/EditWarning.class.php";

    $wgExtensionFunctions[] = 'fnEditWarning_init';
    $wgExtensionCredits['other'][] = array(
        'name'        => 'EditWarning',
        'author'      => 'Thomas David',
        'url'         => 'http://www.mediawiki.org/wiki/Extension:EditWarning',
        'description' => 'Warns user editing a page that\'s currently being edited. (Version 0.3.1)'
    );

    // Assign hooks to functions
    $wgHooks['AlternateEdit'][]   = 'fnEditWarning_edit';
    $wgHooks['ArticleSave'][]     = 'fnEditWarning_remove';
    $wgHooks['UserLogout'][]      = 'fnEditWarning_logout';
    $wgHooks['ArticleViewHeader'][] = 'fnEditWarning_remove';

    /**
     * Initialize EditWarning extension
     *
     * @return boolean
     *     Returns always true.
     */
    function fnEditWarning_init() {
        global $wgMessageCache;
        $messages = null;

        // Load i18n messages
        require_once dirname(__FILE__) . '/Messages.i18n.php';
        foreach ($messages as $lang => $message)
            $wgMessageCache->addMessages($message, $lang);

        return true;
    }

    /**
     * Action if user opens article for editing
     *
     * @hook AlternateEdit
     * @param &$editpage editpage object
     * @return boolean
     *     Returns always true
     */
    function fnEditWarning_edit(&$editpage) {
        global $EditWarning_Timeout, $wgOut;

        if ($editpage->mArticle->mTitle->getNamespace() == 'NS_MAIN') {
            $article_title = $editpage->mArticle->mTitle->getPartialURL();
        } else {
            $article_title = $editpage->mArticle->mTitle->getNsText() . ":" . $editpage->mArticle->mTitle->getPartialURL();
        }

        $p = new EditWarning();
        $p->load($editpage->mArticle->getID());

        if ($p->isActive() || !$p->activeEditing()) {
            //
            // The current user is editing the article - show notice.
            //
            $p->save();
            $timestamp = mktime(date("H"), date("i") + $EditWarning_Timeout, date("s"), date("m"), date("d"), date("Y"));
            $tpl_param = array(
                'NOTICE'       => wfMsg('notice'),
                'CANCEL'        => wfMsg('cancel'),
                'PAGENAME'      => $article_title,
                'BUTTON_CANCEL' => wfMsg('button_cancel')
            );
            $msg_param = array(date("Y-m-d", $timestamp), date("H:i", $timestamp));
            $html      = fnEditWarning_Template('notice', $tpl_param, $msg_param);
        } else {
            //
            // Someone else is already editing the article - show warning.
            //
            $active_user = $p->getActiveUser();
            $timestamp   = mktime(
                date("H", $time_user),
                date("i", $time_user) - $p->getTimeout(),
                date("s", $time_user),
                date("m", $time_user),
                date("d", $time_user),
                date("Y", $time_user)
            );

            // Calculate time to wait
            $seconds_to_wait = floatval(abs(time() - $time_user));
            $minutes_to_wait = bcdiv($seconds_to_wait, 60, 0);

            $tpl_param = array(date('Y-m-d', $timestamp), date('H:i', $timestamp), $active_user['name']);
            ($minutes_to_wait > 1 || $seconds_to_wait > 60) ? $tpl_param[] = wfMsg('minutes') : $tpl_param = wfMsg('seconds');
            $html = fnEditWarning_Template('warning', $tpl_param);
        }

        $wgOut->addHtml($html);

        return true;
    }

    /**
     * Action on article save / editing abort
     *
     * @hook ArticleSave, ArticleViewHeader
     * @param &$article Article object
     * @return boolean
     */
    function fnEditWarning_remove(&$article, &$user = null, &$text = null, &$summary = null, &$minoredit = null, &$watchthis = null, &$sectionanchor = null, &$flags = null, $revision = null) {
        global $wgRequest, $wgOut;

        if ($user != null || $wgRequest->getVal('cancel') == true) {
            $p = new EditWarning();
            $p->load($article->getID());
            try {
                $p->remove();
            } catch(Exception $e) {
                if (EWDEBUG) error_log(sprintf('Error in fnEditWarning_save(): %s', $e));
                return false;
            }
        }

        // Show notice on edit abort.
        if ($wgRequest->getVal('cancel') == true) {
            $wgOut->addHtml(fnEditWarning_Template('canceled'));
        }

        return true;
    }

    /**
     * Action on user logout
     *
     * @hook UserLogout
     * @param $user User object
     * @return boolean
     */
    function fnEditWarning_logout(&$user) {
        $p = new EditWarning();
        try {
            $p->removeAll();
        } catch(Exception $e) {
            if (EWDEBUG) error_log(sprintf('Error in fnEditWarning_logout(): %s', $e));
            return false;
        }

        return true;
    }

    //
    // Helper functions
    //

    /**
     * Returns template code
     *
     * @param $tpl_name Template name
     * @param $tpl_param Template parameters
     * @param $msg_param wfMsg parameters (optional)
     * @return string Template code
     */
    function fnEditWarning_Template($tpl_name, $tpl_param, $msg_param = null) {
        global $IP;

        // Load template file
        $file_name = $IP . "/extensions/EditWarning/tpl_" . $tpl_name . ".html";
        if (!$file = fopen($file_name, "r")) {
            throw new Exception(sprintf('Could\'t open the file %s.', $file_name));
        }
        if (!$tpl_content = fread($file, filesize($file_name))) {
            throw new Exception(sprintf('Could\'t read from file %s.', $file_name));
        }
        if (!fclose($file)) {
            throw new Exception(sprintf('Could\'t close the file %s.', $file_name));
        }

        // Include messages
        foreach ($tpl_param as $param => $content) {
            $tpl_content = preg_replace('/{{{' . $param . '}}}/', wfMsg($content, implode(',', $msg_param)), $tpl_content);
        }

        return $tpl_content;
    }
}
