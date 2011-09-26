<?php

/**
 * Test cases for EditWarning class functions.
 * 
 * This file is part of the MediaWiki extension EditWarning. It contains
 * test cases for the EditWarning class functions.
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
 * @version     0.4-beta
 * @category    Extensions
 * @package     EditWarning
 */

if ( !defined( 'EDITWARNING_UNITTEST' ) ) {
	define( 'EDITWARNING_UNITTEST', true );
}

require_once( "simpletest/autorun.php" );

class EditWarningWebTest extends WebTestCase {
	private $url = "http://localhost:8080/mediawiki";
	
	public function __construct() {
		$this->WebTestCase( "EditWarning Web Test" );
	}
	
	private function login( &$browser, $user, $passwd ) {
		$browser->get( $this->url );
		$browser->clickLink( "Anmelden" );
		$browser->setField( "wpName", $user );
		$browser->setField( "wpPassword", $passwd );
		$browser->clickSubmit( "Anmelden" );
	}
	
	public function testPageAvailability() {
		$this->get( $this->url );
		$this->assertText( "Hauptseite" );
		$this->assertLink( "Bearbeiten" );
	}
	
	public function testExtensionInstalled() {
		$this->get( $this->url . "/index.php/Spezial:Version" );
		$this->assertLink( "EditWarning" );
	}
	
	public function testArticleEditing() {
		$alice = &new SimpleBrowser();
		$this->login( $alice, "Alice", "alicepw" );
		
		// Open page for editing
		$alice->get( $this->url );
		$alice->clickLink( "Bearbeiten" );
		$this->assertTrue( preg_match( "/erhalten andere Benutzer die Warnung, dass Sie die Seite bearbeiten/", $alice->getContent() ) );
		// Reload edit page
		$alice->get( $this->url );
		$alice->clickLink( "Bearbeiten" );
		// Abort
		$alice->clickSubmit( "Abbrechen" );
		$this->assertTrue( preg_match( "/Bearbeitung abgebrochen/", $alice->getContent() ) );
	}
	
	public function testArticleEditingConflict() {
		$alice = &new SimpleBrowser();
		$bob   = &new SimpleBrowser();
		$this->login( $alice, "Alice", "alicepw" );
		$this->login( $bob, "Bob", "bobpw" );
		
		// Open page for editing
		$alice->get( $this->url );
		$alice->clickLink( "Bearbeiten" );
		$bob->get( $this->url );
		$bob->clickLink( "Bearbeiten" );
		$this->assertTrue( preg_match( "/Der Benutzer <strong>Alice</strong> &ouml;ffnete diesen Artikel/", $bob->getContent() ) );
		
	}
}

?>
