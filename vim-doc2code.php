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
 * Convert docs to code.
 *
 * In order to read the documentation of a function inside vim,
 * the fastest way is via "jump to definition".
 * This feature is avaliable via ctags.
 *
 * docs is located at https://github.com/php/doc-en.git
 *
 * # Usage
 *
 *   $ php vim-doc2code.php <docs>
 *
 * # Examples
 *
 *   $ php vim-doc2code.php ../../php/doc-en
 *
 * # Notes
 *   - Input files:
 *     - reference/<module>/functions/<name>.xml
 **/

/**
 * Get textContent by TagName
 **/

function doc_tag_text($doc, $tag_name){
 	$nodes = $doc->getElementsByTagName($tag_name);
	if ($nodes->length == 0) {
		return false;
	}
	$node = $nodes->item(0);
	return $node->textContent;
}

function dc_write_refpurpose($f, $node){

 	$nodes = $node->getElementsByTagName("refpurpose");
	if ($nodes->length == 0) {
		return false;
	}

	$node = $nodes->item(0);

	fwrite($f, "/**\n");
	$p = $nodes->item(0)->textContent;
	$p = str_replace("\n", " ", $p);
	$p = preg_replace("/ +/", " ", $p);
	$p = wordwrap($p, 72 - 10);
	foreach (explode("\n", $p) as $l) {
		$l = trim($l);
		if (empty($l) == true) {
			continue;
		}
		fwrite($f, " * {$l}.\n");
	}
	fwrite($f, " **/\n\n");
}

function dc_write_description($f, $node){
	$nodes = $node->getElementsByTagName("para");
	for ($i = 0; $i < $nodes->length; $i++) {
		if ($i > 0) {
			// add line break between paragraphs
			fwrite($f, "   *\n");
		}
		$p = $nodes->item($i)->textContent;
		$p = str_replace("\n", " ", $p);
		$p = preg_replace("/ +/", " ", $p);
		$p = wordwrap($p, 72 - 10);
		foreach (explode("\n", $p) as $l) {
			$l = trim($l);
			if (empty($l) == true) {
				continue;
			}
			fwrite($f, "   * {$l}\n");
		}
	}
}

function dc_write_parameters($f, $refsect1){

	$synopsis = $refsect1->childNodes->item(1);
	$parameter_items = array();

	for ($i = 0; $i < $synopsis->childNodes->length; $i++) {
		$node = $synopsis->childNodes->item($i);
		if ($synopsis->childNodes->item($i)->nodeName == "methodname") {
			$name = $synopsis->childNodes->item($i);
		}
		if ($synopsis->childNodes->item($i)->nodeName == "methodparam") {
			$parameter_item = "";
			$param = $synopsis->childNodes->item($i);
			for ($j = 0; $j < $param->childNodes->length; $j++) {
				$param_item = $param->childNodes->item($j);
				// var_dump($param_item->nodeName);
				if ($param_item->nodeName == "parameter") {
					if ($param_item->getAttribute("role") == "reference") {
						$parameter_item .= "&";
					}
					$parameter_item .= "$".$param_item->textContent;
				} elseif ($param_item->nodeName == "type") {
					$text = $param_item->textContent;
					if ($param_item->getAttribute("class") == "union") {
						// do nothing
					} else {
						$parameter_item .= " ".$text." ";
					}
				} elseif ($param_item->nodeName == "initializer") {
					$text = $param_item->textContent;
					$text = str_replace(array(
						  "&null;"
						, "&false;"
						, "&true;"
						), array(
						  "null"
						, "false"
						, "true"
						), $text);
					$parameter_item .= " = ".$text;
				}
			}
			$parameter_items[] = $parameter_item;
		}
	}

	if (count($parameter_items) > 0) {
		$parameter_text = implode(", ", $parameter_items);
		$line_length = strlen($name->textContent) + strlen($parameter_text) + 14;
		if ($line_length > 72) {
			$parameter_text =
				 "\n    "
				.implode("\n  , ", $parameter_items)
				."\n  "
				;
		}
		fwrite($f, $parameter_text);
	}
}

$dir = $_SERVER["argv"][1];

$file = "tags.php";

$f = fopen($file, "w+");
fwrite($f, "<?php\n\n");

foreach (glob("{$dir}/reference/*/functions/*.xml") as $file) {

	// if (basename($file) != "preg-replace.xml") { continue; }

	$xml = file_get_contents($file);

	$doc = new DOMDocument;
	@$doc->loadHTML($xml);

	$refsects = $doc->getElementsByTagName("refsect1");
	for ($i = 0; $i < $refsects->length; $i++) {
		$refsect = $refsects->item($i);
		if ($refsect->getAttribute("role") == "description") {
			$description = $refsect;
		}
	}

	dc_write_refpurpose($f, $doc);
	$methodname = doc_tag_text($doc, "methodname");

	fwrite($f, "function {$methodname}(");
	dc_write_parameters($f, $description);
	fwrite($f, ") {\n\n");

	// > description
	fwrite($f, "  /**\n");
	dc_write_description($f, $description);
	fwrite($f, "   **/\n");

	// > end function
	fwrite($f, "}\n\n");
}

fclose($f);

