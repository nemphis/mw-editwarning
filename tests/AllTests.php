<?php

/**
 * SimpleTest testgroup: All tests.
 * 
 * This file is part of the MediaWiki extension EditWarning. It contains
 * all SimpleTest unit tests.
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
 * @author		Thomas David <ThomasDavid@gmx.de>
 * @copyright	2007-2009 Thomas David <ThomasDavid@gmx.de>
 * @license		http://www.gnu.org/licenses/gpl-howto.html GNU AGPL 3.0 or later
 * @version		0.4-prealpha
 * @category	Extensions
 * @package		EditWarning
 */
 
require_once( "simpletest/autorun.php" );

$suite = &new TestSuite( "All EditWarning Tests" );
$suite->addTestFile( "EditWarningClassTest.php" );
$suite->addTestFile( "EditWarningHookTest.php" );
$suite->run(new HtmlReporter());

?>
