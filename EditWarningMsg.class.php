<?php

/**
 * Implementation of EditWarningMsg class.
 *
 * This file is part of the MediaWiki extension EditWarning. It contains
 * the implementation of EditWarningMsg class responsible for creating
 * EditWarningMessage subclasses.
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

require_once( "EditWarningMsgFactory.class.php" );
require_once( "EditWarningInfoMsg.class.php" );
require_once( "EditWarningWarnMsg.class.php" );
require_once( "EditWarningCancelMsg.class.php" );

class IllegalArgumentException extends Exception {}

/**
 * Singleton factory for EditWarningMessage subclasses.
 */
class EditWarningMsg implements EditWarningMsgFactory {
    private static $instance = array();

    private function __construct() {

    }
    private function __clone() {

    }

    public static function getInstance( $type, $url = null, $params = null ) {
        global $IP;

        $path = $IP . "/extensions/EditWarning/templates";

        if ( $self->instance[$type] === null ) {
            switch ( $type ) {
                case "ArticleNotice":
                    $params[] = wfMsg( 'ew-leave' );
                    self::$instance[$type] = new EditWarningInfoMsg( $path, $url );
                    self::$instance[$type]->setMsg( 'ew-notice-article', $params );
                    break;
                case "ArticleWarning":
                    $params[] = wfMsg( 'ew-leave' );
                    self::$instance[$type] = new EditWarningWarnMsg( $path, $url );
                    self::$instance[$type]->setMsg( 'ew-warning-article', $params );
                    break;
                case "ArticleSectionWarning":
                    $params[] = wfMsg( 'ew-leave' );
                    self::$instance[$type] = new EditWarningWarnMsg( $path, $url );
                    self::$instance[$type]->setMsg( 'ew-warning-sectionedit', $params );
                    break;
                case "SectionNotice":
                    $params[] = wfMsg( 'ew-leave' );
                    self::$instance[$type] = new EditWarningInfoMsg( $path, $url );
                    self::$instance[$type]->setMsg( 'ew-notice-section', $params );
                    break;
                case "SectionWarning":
                    $params[] = wfMsg( 'ew-leave' );
                    self::$instance[$type] = new EditWarningWarnMsg( $path, $url );
                    self::$instance[$type]->setMsg( 'ew-warning-section', $params );
                    break;
                case "Cancel":
                    self::$instance[$type] = new EditWarningCancelMsg( $path );
                    break;
                default:
                    throw new IllegalArgumentException( "Unknown message type." );
            }
        }

        return self::$instance[$type];
    }
}
