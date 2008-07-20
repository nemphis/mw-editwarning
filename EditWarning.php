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

    // Include messages
    foreach ($msg as $var => $message) {
        $tpl_content = preg_replace('/{{{' . $var . '}}}/', $message);
    }

    return $tpl_content;
}

?>
