<?php

/**
 * EditWarning message strings
 *
 * This file is part of the MediaWiki extension EditWarning
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

$messages = array();

/* Template. Just copy and translate. :)
$messages['LANGCODE'] = array(
  'ew-notice-article'      => "",
  'ew-notice-section'      => "",
  'ew-warning-article'     => "",
  'ew-warning-section'     => "",
  'ew-warning-sectionedit' => "",
  'ew-canceled'            => "",
  'ew-minute'              => "",
  'ew-minutes'             => "",
  'ew-seconds'             => "",
  'ew-button-cancel'       => ""
);
*/

// English
$messages['en'] = array(
  'ew-notice-article'      => "Until <strong>$1 $2</strong> other users get the warning that you're editing this article. " .
                              "You can extend this time period by clicking <em>Show preview</em>.<br />" .
                              "You can leave this screen by clicking <em>Cancel</em>.",
  'ew-notice-section'      => "Until <strong>$1 $2</strong> other users get the warning that you're editing this section. " .
                              "You can extend this time period by clicking <em>Show preview</em>.<br />" .
                              "You can leave this screen by clicking <em>Cancel</em>.",
  'ew-warning-article'     => "User <strong>$1</strong> opened this page for editing on <strong>$2 $3</strong>. " .
                              "You shouldn't make any changes for the next <strong>$4 $5</strong> to avoid edit conflicts.<br />" .
                              "You can leave this screen by clicking <em>Cancel</em>.",
  'ew-warning-section'     => "User <strong>$1</strong> opened this section for editing on <strong>$2 $3</strong>. " .
                              "You shouldn't make any changes for the next <strong>$4 $5</strong> to avoid edit conflicts.<br />" .
                              "You can leave this screen by clicking <em>Cancel</em>.",
  'ew-warning-sectionedit' => "Someone is already working on a section of this article. " .
                              "Please edit a specific section or wait $1 $2 for bigger changes of this article, to avoid edit conflicts.<br />" .
                              "You can leave this screen by clicking <em>Cancel</em>.",
  'ew-canceled'            => "Canceled editing.",
  'ew-minute'              => "minute",
  'ew-minutes'             => "minutes",
  'ew-seconds'             => "seconds",
  'ew-button-cancel'       => "Cancel"
);

// German
$messages['de'] = array(
  'ew-notice-article'      => "Bis <strong>$1 $2</strong> erhalten andere Benutzer die Warnung, dass Sie diesen Artikel bearbeiten. " .
                              "Mit <em>Vorschau zeigen</em>, können Sie den Zeitraum verlängern.<br />" .
                              "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
  'ew-notice-section'      => "Bis <strong>$1 $2</strong> erhalten andere Benutzer die Warnung, dass Sie diesen Abschnitt bearbeiten. " .
                              "Mit <em>Vorschau zeigen</em>, können Sie den Zeitraum verlängern.<br />" .
                              "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
  'ew-warning-article'     => "Der Benutzer <strong>$1</strong> öffnete diesen Artikel am <strong>$2 um $3</strong> zum Bearbeiten. " .
                              "Sie sollten für die nächsten <strong>$4 $5</strong> keine Änderungen vornemen, um Bearbeitungskonflikte zu vermeiden.<br />" .
                              "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
  'ew-warning-section'     => "Der Benutzer <strong>$1</strong> öffnete diesen Abschnitt am <strong>$2 um $3</strong> zum Bearbeiten. " .
                              "Sie sollten für die nächsten <strong>$4 $5</strong> keine Änderungen vornemen, um Bearbeitungskonflikte zu vermeiden.<br />" .
                              "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
  'ew-warning-sectionedit' => "An einem oder mehreren Abschnitten in diesem Artikel wird gerade gearbeitet. " .
                              "Bearbeiten Sie bitte einen bestimmten Abschnitt des Artikels oder warten Sie " .
                              "<strong>$1 $2</strong> für größere Änderungen, um Bearbeitungskonflikte zu vermeiden.<br />" .
                              "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
  'ew-canceled'            => "Bearbeitung abgebrochen.",
  'ew-minute'              => "Minute",
  'ew-minutes'             => "Minuten",
  'ew-seconds'             => "Sekunden",
  'ew-button-cancel'       => "Abbrechen"
);