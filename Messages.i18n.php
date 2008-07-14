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
  'notice'        => "",
  'warning'       => "",
  'cancel'        => "",
  'canceled'      => "",
  'minute'        => "",
  'minutes'       => "",
  'seconds'       => "",
  'button_cancel' => ""
);
*/

// English
$messages['en'] = array(
  'notice'        => "Until <strong>$1 $2</strong> other users get the warning that you're editing this page. You can extend this time period by clicking <em>Show preview</em>.",
  'warning'       => "User <strong>{{{USERNAME}}}</strong> opened this page for editing on <strong>{{{DATE}}} {{{TIME}}}</strong>. You shouldn't make any changes for the next <strong>{{{TIMEOUT}}} {{{MINSEC}}}</strong> to avoid edit conflicts.",
  'cancel'        => "You can leave this screen by clicking <em>Cancel</em>.",
  'canceled'      => "Canceled editing.",
  'minute'        => "minute",
  'minutes'       => "minutes",
  'seconds'       => "seconds",
  'button_cancel' => "Cancel"
);

// German
$messages['de'] = array(
  'notice'        => "Bis '''$1 $2''' erhalten andere Benutzer die Warnung, dass Sie die Seite bearbeiten. Mit <em>Vorschau zeigen</em>, können Sie den Zeitraum verlängern.",
  'warning'       => "Der Benutzer <strong>{{{USERNAME}}}</strong> &ouml;ffnete diese Seite am <strong>{{{DATE}}} um {{{TIME}}}</strong> zum Bearbeiten. Sie sollten f&uuml;r die n&auml;chsten <strong>{{{TIMEOUT}}} {{{MINSEC}}}</strong> keine &Auml;nderungen vornehmen, um Bearbeitungskonflikte zu vermeiden.",
  'cancel'        => "Sie können diese Seite über den Button <em>Abbrechen</em> verlassen.",
  'canceled'      => "Bearbeitung abgebrochen.",
  'minute'        => "Minute",
  'minutes'       => "Minuten",
  'seconds'       => "Sekunden",
  'button_cancel' => "Abbrechen"
);