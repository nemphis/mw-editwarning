<?php

/**
 * EditWarning message strings.
 *
 * This file is part of the MediaWiki extension EditWarning.
 * It contains all message strings to support translation to
 * different languages.
 *
 * This file is part of the MediaWiki extension EditWarning. It contains
 * the implementation of EditWarning and EditWarning_Lock class with
 * functions to add, edit, delete and check for article locks.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
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
 * @copyright   2007-2010 Thomas David <nemphis@code-geek.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later
 * @version     0.4-rc
 * @category    Extensions
 * @package     EditWarning
 */

$messages = array();

/* Template. Just copy and translate. :)
$messages['LANGCODE'] = array(
    'editwarning-desc'       => "",
    'ew-notice-article'      => "",
    'ew-notice-section'      => "",
    'ew-warning-article'     => "",
    'ew-warning-section'     => "",
    'ew-warning-sectionedit' => "",
    'ew-leave'               => "",
    'ew-canceled'            => "",
    'ew-minute'              => "",
    'ew-minutes'             => "",
    'ew-seconds'             => "",
    'ew-button-cancel'       => ""
);
*/

// English
$messages['en'] = array(
    'editwarning-desc'       => "Shows a warning message if several users edit the same article at the same time.",
    'ew-notice-article'      => "Until <strong>$1 $2</strong> other users get the warning that you're editing this article. " .
                                "You can extend this time period by clicking <em>Show preview</em>.<br />$3",
    'ew-notice-section'      => "Until <strong>$1 $2</strong> other users get the warning that you're editing this section. " .
                                "You can extend this time period by clicking <em>Show preview</em>.<br />$3",
    'ew-warning-article'     => "User <strong>$1</strong> opened this page for editing on <strong>$2 $3</strong>. " .
                                "You shouldn't make any changes for the next <strong>$4 $5</strong> to avoid edit conflicts.<br />$6",
    'ew-warning-section'     => "User <strong>$1</strong> opened this section for editing on <strong>$2 $3</strong>. " .
                                "You shouldn't make any changes for the next <strong>$4 $5</strong> to avoid edit conflicts.<br />$6",
    'ew-warning-sectionedit' => "One or more sections of this article are edited currently. " .
                                "Please edit a specific section or wait <strong>$1 $2</strong> for bigger changes of this article, to avoid edit conflicts.<br />$3",
    'ew-leave'               => "You can leave this screen by clicking <em>Cancel</em>.",
    'ew-canceled'            => "Canceled editing.",
    'ew-minute'              => "minute",
    'ew-minutes'             => "minutes",
    'ew-seconds'             => "seconds",
    'ew-button-cancel'       => "Cancel"
);

// German
$messages['de'] = array(
    'editwarning-desc'       => "Zeigt eine Warnmeldung an, wenn mehrere Benutzer gleichzeitig an einem Artikel arbeiten.",
    'ew-notice-article'      => "Bis <strong>$1 $2</strong> erhalten andere Benutzer die Warnung, dass du diesen Artikel bearbeitest. " .
                                "Mit <em>Vorschau zeigen</em>, kannst du den Zeitraum verlängern.<br />$3",
    'ew-notice-section'      => "Bis <strong>$1 $2</strong> erhalten andere Benutzer die Warnung, dass du diesen Abschnitt bearbeitest. " .
                                "Mit <em>Vorschau zeigen</em>, kannst du den Zeitraum verlängern.<br />$3",
    'ew-warning-article'     => "Der Benutzer <strong>$1</strong> öffnete diesen Artikel am <strong>$2 um $3</strong> zum Bearbeiten. " .
                                "Du solltest für die nächsten <strong>$4 $5</strong> keine Änderungen vornemen, um Bearbeitungskonflikte zu vermeiden.<br />$6",
    'ew-warning-section'     => "Der Benutzer <strong>$1</strong> öffnete diesen Abschnitt am <strong>$2 um $3</strong> zum Bearbeiten. " .
                                "Du solltest für die nächsten <strong>$4 $5</strong> keine Änderungen vornemen, um Bearbeitungskonflikte zu vermeiden.<br />$6",
    'ew-warning-sectionedit' => "An einem oder mehreren Abschnitten in diesem Artikel wird gerade gearbeitet. " .
                                "Bearbeite bitte einen bestimmten Abschnitt des Artikels oder warte " .
                                "<strong>$1 $2</strong> für größere Änderungen, um Bearbeitungskonflikte zu vermeiden.<br />$3",
    'ew-leave'               => "Du kannst diese Seite über den Button <em>Abbrechen</em> verlassen.",
    'ew-canceled'            => "Bearbeitung abgebrochen.",
    'ew-minute'              => "Minute",
    'ew-minutes'             => "Minuten",
    'ew-seconds'             => "Sekunden",
    'ew-button-cancel'       => "Abbrechen"
);

// German (formal)
$messages['de-formal'] = array(
    'editwarning-desc'       => "Zeigt eine Warnmeldung an, wenn mehrere Benutzer gleichzeitig an einem Artikel arbeiten.",
    'ew-notice-article'      => "Bis <strong>$1 $2</strong> erhalten andere Benutzer die Warnung, dass Sie diesen Artikel bearbeiten. " .
                                "Mit <em>Vorschau zeigen</em>, können Sie den Zeitraum verlängern.<br />$3",
    'ew-notice-section'      => "Bis <strong>$1 $2</strong> erhalten andere Benutzer die Warnung, dass Sie diesen Abschnitt bearbeiten. " .
                                "Mit <em>Vorschau zeigen</em>, können Sie den Zeitraum verlängern.<br />$3",
    'ew-warning-article'     => "Der Benutzer <strong>$1</strong> öffnete diesen Artikel am <strong>$2 um $3</strong> zum Bearbeiten. " .
                                "Sie sollten für die nächsten <strong>$4 $5</strong> keine Änderungen vornemen, um Bearbeitungskonflikte zu vermeiden.<br />$6",
    'ew-warning-section'     => "Der Benutzer <strong>$1</strong> öffnete diesen Abschnitt am <strong>$2 um $3</strong> zum Bearbeiten. " .
                                "Sie sollten für die nächsten <strong>$4 $5</strong> keine Änderungen vornemen, um Bearbeitungskonflikte zu vermeiden.<br />$6",
    'ew-warning-sectionedit' => "An einem oder mehreren Abschnitten in diesem Artikel wird gerade gearbeitet. " .
                                "Bearbeiten Sie bitte einen bestimmten Abschnitt des Artikels oder warten Sie " .
                                "<strong>$1 $2</strong> für größere Änderungen, um Bearbeitungskonflikte zu vermeiden.<br />$3",
    'ew-leave'               => "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
    'ew-canceled'            => "Bearbeitung abgebrochen.",
    'ew-minute'              => "Minute",
    'ew-minutes'             => "Minuten",
    'ew-seconds'             => "Sekunden",
    'ew-button-cancel'       => "Abbrechen"
);