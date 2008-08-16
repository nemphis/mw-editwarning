<?php

if (!defined('MEDIAWIKI')) {
    echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once "\$IP/extensions/EditWarning/EditWarning.php";

See http://www.mediawiki.org/wiki/Extension:EditWarning for more information.
EOT;
    exit(1);
}

$extension_dir = dirname(__FILE__) . "/";

$wgExtensionCredits['EditWarning'][] = array(
    'name'        => "EditWarning",
    'author'      => "Thomas David",
    'url'         => "http://www.mediawiki.org/wiki/Extension:EditWarning",
    'description' => "Warns user editing a page that\'s currently being edited. (Version 0.3.1)"
);

$wgAutoloadClasses['EditWarning']        = $extension_dir . 'EditWarning.class.php';
$wgExtensionMessagesFiles['EditWarning'] = $extension_dir . 'EditWarning.i18n.php';
$wgExtensionFunctions[]                  = 'fnEditWarning_init';

// Assign hooks to functions
$wgHooks['AlternateEdit'][]     = 'fnEditWarning_edit';   // On article edit.
$wgHooks['ArticleSave'][]       = 'fnEditWarning_remove'; // On article save.
$wgHooks['UserLogout'][]        = 'fnEditWarning_logout'; // On user logout.
$wgHooks['ArticleViewHeader'][] = 'fnEditWarning_remove'; // On editing abort.

/**
 * Setup EditWarning extension
 *
 * @return boolean Returns always true.
 */
function fnEditWarning_init() {
    global $wgRequest, $wgOut;

    // Add CSS styles to header
    if ($wgRequest->getVal('action') == 'edit' || $wgRequest->getVal('action') == 'submit') {
      $wgOut->addHeadItem('edit_css', '  <link href="extensions/EditWarning/article_edit.css" rel="stylesheet" type="text/css" />');
    }
    $wgOut->addHeadItem('EditWarning', '  <link href="extensions/EditWarning/style.css" rel="stylesheet" type="text/css" />');

    // Load messages
    wfLoadExtensionMessages('EditWarning');

    return true;
}

/**
 * Gets config vars
 *
 * @param string Config subject
 * @return mixed Config value
 */
function fnEditWarning_getConfig($config_subject) {
    global $wgEditWarningTimeout;

    switch($config_subject) {
        case "timeout":
            if ($wgEditWarningTimeout < 1) {
                // $wgEditWarningTimeout has to be at least 1. If not, set to default value of 10.
                $wgEditWarningTimeout = 10;
            }

            return $wgEditWarningTimeout;
            break;
    }
}

/**
 * Loads HTML template and includes messsages
 *
 * @param string Template name.
 * @param array Messages.
 * @param array Template variables. (optional)
 * @return string HTML code.
 */
function fnEditWarning_getHTML($template, $msg, $vars = array()) {
    global $extension_dir;

    // Load template file
    $file_name = $extension_dir . "tpl_" . $template;
    if (!$file = fopen($file_name, "r")) {
        throw new Exception(sprintf("fnEditWarning_getHTML: Could't open template file %s.", $file_name));
    }
    if (!$tpl_content = fread($file, filesize($file_name))) {
        throw new Exception(sprintf("fnEditWarning_getHTML: Could't read from template file %s.", $file_name));
    }
    if (!fclose($file_name)) {
        throw new Exception(sprintf("fnEditWarning_getHTML: Could't close template file %s.", $file_name));
    }

    // Include template variables
    foreach ($vars as $var => $value) {
        $tpl_content = preg_replace('/{{{' . $var . '}}}/', $value);
    }

    // Include message
    $tpl_content = preg_replace('/{{{MSG}}}/', $msg);

    return $tpl_content;
}

/**
 * Action on article editing
 *
 * @hook AlternateEdit
 * @param editpage Editpage object.
 * @return boolean Returns true, if no error occurs.
 */
function fnEditWarning_edit(&$editpage) {
    global $wgUser, $wgRequest, $wgOut;

    $article_id = $editpage->mArticle->getID();
    $p          = new EditWarning($article_id);
    $editors    = $p->getEditors();
    $user_id    = $wgUser->getID();
    $section_id = intval($wgRequest->getVal('section'));
    $timeout    = fnEditWarning_getConfig('timeout');

    if ($editpage->mArticle->mTitle->getNamespace() == 'NS_MAIN') {
    	$article_title = $editpage->mArticle->mTitle->getPartialURL();
    } else {
        $article_title = $editpage->mArticle->mTitle->getNsText() . ":" . $editpage->mArticle->mTitle->getPartialURL();
    }

    $article_url = $_SERVER['PHP_SELF'] . "?title=" . $article_title;

    $tpl_vars = array(
        'URL'           => $article_url,
        'BUTTON_CANCEL' => wfMsg('ew-button-cancel')
    );

    if ($p->activeEditing()) {
    	//
        // Someone is editing the article.
        //

        if (!$section_id) {
        	//
            // The user wants to edit the whole article.
            //

            if ($p->sectionEditing()) {
            	//
                // Someone is editing a section of the article - show warning.
                //

                // Get newest timestamp
                $timestamp = 0;
                foreach ($editors as $editor) {
                    if ($editor['section'] == $section_id && $editor['timestamp'] > $timestamp) {
                        $timestamp = $editor['timestamp'];
                    }
                }

                // Timestamp when the editor was last seen.
                $edit_time = mktime(
                    date("H", $timestamp),
                    date("i", $timestamp) - $timeout,
                    date("s", $timestamp),
                    date("m", $timestamp),
                    date("d", $timestamp),
                    date("Y", $timestamp)
                );

                // Calculate time to wait.
                $seconds_to_wait = floatval(abs(time() - $timestamp));
                $minutes_to_wait = bcdiv($seconds_to_wait, 60, 0);

                if ($minutes_to_wait > 1 || $seconds_to_wait > 60) {
                	$message = wfMsg('ew-warning-sectionedit', $minutes_to_wait, wfMsg('ew-minutes'));
                } else {
                    $message = wfMsg('ew-warning-sectionedit', $seconds_to_wait, wfMsg('ew-seconds'));
                }

                $html = fnEditWarning_getHTML('warning', $message, $tpl_vars);
            } else {
            	//
            	// Someone is editing the whole article.
            	//

            	if ($p->isUserEditingArticle($user_id)) {
            	    //
                    // The user is editing the article - show notice.
                    //

                    $timestamp = mktime(date("H"), date("i") + $timeout, date("s"), date("m"), date("d"), date("Y"));
                    $p->update($article_id, $user_id);
                    $message = wfMsg('ew-notice-article', date("Y-m-d", $timestamp), date("H:i", $timestamp));
                    $html    = fnEditWarning_getHTML('notice', $message, $tpl_vars);
            	} else {
            	    //
            	    // Someone else is already editing the article - show warning.
            	    //

                    $editor = $p->getArticleEditor();
                    $edit_timeout = mktime(
                        date("H", $editor['timestamp']),
                        date("i", $editor['timestamp']) - $timeout,
                        date("s", $editor['timestamp']),
                        date("m", $editor['timestamp']),
                        date("d", $editor['timestamp']),
                        date("Y", $editor['timestamp'])
                    );

            	    // Calculate time to wait
                    $seconds_to_wait = floatval(abs(time() - $edit_timeout));
                    $minutes_to_wait = bcdiv($seconds_to_wait, 60, 0);

                    if ($minutes_to_wait > 1 || $seconds_to_wait > 60) {
                        $message = wfMsg('ew-warning-article', $minutes_to_wait, wfMsg('ew-minutes'));
                    } else {
                        $message = wfMsg('ew-warning-article', $seconds_to_wait, wfMsg('ew-seconds'));
                    }

                    $html = fnEditWarning_getHTML('notice', $message, $tpl_vars);
            	}
            }
        } else {
        	//
            // The user wants to edit a section of the article.
            //

            if ($p->sectionEditing()) {
            	//
                // One or more sections are edited.
                //

                if ($p->isUserEditingSection($user_id, $section_id)) {
                	//
                    // The user is editing the section - show notice.
                    //

                    $timestamp = mktime(date('H'), date('i') + $timeout, date('s'), date('m'), date('d'), date('Y'));
                    $p->update($article_id, $user_id);
                    $message = wfMsg('ew-notice-section', date("Y-m-d", $timestamp), date("H:i", $timestamp));
                    $html    = fnEditWarning_getHTML('notice', $message, $tpl_vars);
                } else {
                	//
                    // Someone else is editing the section - show warning.
                    //

                    $editor       = $p->getSectionEditor($section_id);
                    $edit_timeout = mktime(
                        date('H', $editor['timestamp']),
                        date('i', $editor['timestamp']) - $timeout,
                        date('s', $editor['timestamp']),
                        date('m', $editor['timestamp']),
                        date('d', $editor['timestamp']),
                        date('Y', $editor['timestamp'])
                    );

            	    // Calculate time to wait
                    $seconds_to_wait = floatval(abs(time() - $edit_timeout));
                    $minutes_to_wait = bcdiv($seconds_to_wait, 60, 0);

                    if ($minutes_to_wait > 1 || $seconds_to_wait > 60) {
                        $message = wfMsg('ew-warning-section', $editor['name'], $minutes_to_wait, wfMsg('ew-minutes'));
                    } else {
                        $message = wfMsg('ew-warning-section', $editor['name'], $seconds_to_wait, wfMsg('ew-seconds'));
                    }

                    $html = fnEditWarning_getHTML('warning', $message, $tpl_vars);
                }
            } else {
            	//
                // Someone is editing the whole article.
                //

                $editor = $p->getArticleEditor();
                $edit_timeout = mktime(
                    date('H', $editor['timestamp']),
                    date('i', $editor['timestamp']) - $timeout,
                    date('s', $editor['timestamp']),
                    date('m', $editor['timestamp']),
                    date('d', $editor['timestamp']),
                    date('Y', $editor['timestamp'])
                );

                // Calculate time to wait
                $seconds_to_wait = floatval(abs(time() - $edit_timeout));
                $minutes_to_wait = bcdiv($seconds_to_wait, 60, 0);

                if ($minutes_to_wait > 1 || $seconds_to_wait > 60) {
                	$message = wfMsg('ew-warning-article', $editor['name'], $minutes_to_wait, wfMsg('ew-minutes'));
                } else {
                    $message = wfMsg('ew-warning-article', $editor['name'], $seconds_to_wait, wfMsg('ew-seconds'));
                }

                $html = fnEditWarning_getHTML('warning', $message, $tpl_vars);
            }
        }
    } else {
    	//
        // Nobody is editing the article.
        //

        $user_name = $wgUser->getName();
        $timestamp = mktime(date("H"), date("i") + $timeout, date("s"), date("m"), date("d"), date("Y"));

        if (!$section_id) {
        	//
            // The user wants to edit the whole article.
            //

            $p->add($article_id, $user_id, $user_name, $timeout);
            $message = wfMsg('ew-notice-article', date("Y-m-d", $timestamp), date("H:i", $timestamp));
        } else {
        	//
            // The user wants to edit a certain section of the article.
            //

            $p->add($article_id, $user_id, $user_name, $timeout, $section_id);
            $message = wfMsg('ew-notice-section', date("Y-m-d", $timestamp), date("H:i", $timestamp));
        }

        $html = fnEditWarning_getHTML('notice', $message, $tpl_vars);
    }

    $wgOut->addHtml($html);

    return true;
}

/**
 * Action if article is saved or editing is aborted.
 *
 * @hook ArticleSave
 * @param
 * @return boolean Returns true, if no error occurs.
 */
function fnEditWarning_remove(&$article, &$user, &$text, &$summary, $minor, $watch, $sectionanchor, &$flags) {
	global $wgRequest, $wgUser, $wgOut;

    $user_id    = $wgUser->getID();
	$article_id = $article->mTitle->getID();
	$p = new EditWarning($article_id);
	$p->remove($user_id, $article_id);

	if ($wgRequest->getVal('cancel') == "true") {
	    // The user has aborted editing - show message.
	    $message = wfMsg('ew-canceled');
	    $wgOut->addHtml(fnEditWarning_getHTML('canceled', $message));
	}

	return true;
}

/**
 * Action on user logout.
 *
 * @hook UserLogout
 * @param user User object.
 * @return boolean Returns true, if no error occurs.
 */
function fnEditWarning_logout(&$user) {
    EditWarning::removeAll($user->getID());

    return true;
}
