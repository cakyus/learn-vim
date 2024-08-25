<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 **/

/**
 * vim-aleq.php - Align text on equal sign.
 *
 * # Example
 *
 * ## Input
 *
 * Survey = App.Survey
 * Sidebar = App.Sidebar
 * Main = App.Main
 *
 * ## Output
 *
 * Survey  = App.Survey
 * Sidebar = App.Sidebar
 * Main    = App.Main
 *
 * # Install
 *
 * ~/.vimrc
 * command! -range Aleq execute <line1>.",".<line2> . "! php ~/bin/vim-aleq.php"
 *
 **/

$text = stream_get_contents(STDIN);

// split into line
$lines = explode("\n", $text);

// find the farthest equal sign
$pos_max = 0;
foreach ($lines as $i => $line) {

	// > check equal

	$pos = strpos($line, '=');
	if ($pos === false) {
		continue;
	}

	// > re format line

	// >> do not count spaces before equal
	$ltext = substr($line, 0, $pos);
	$ltext = rtrim($ltext, " ");

	// >> do not count spaces after equal
	$rtext = substr($line, $pos + 1);
	$rtext = ltrim($rtext, " ");

	$line = $ltext."=".$rtext;
	$lines[$i] = $line;

	$pos = strpos($line, '=');

	if ($pos <= $pos_max) {
		continue;
	}

	$pos_max = $pos;
}

// align by equal sign
foreach ($lines as $i => $line) {

	$pos = strpos($line, '=');

	if ($pos === false) {
		continue;
	}

	$ltext = substr($line, 0, $pos);
	$rtext = substr($line, $pos + 1);
	$ltext = str_pad($ltext, $pos_max);
	$line  = $ltext." = ".$rtext;
	$lines[$i] = $line;
}

$text = implode("\n", $lines);

fwrite(STDOUT, $text);

