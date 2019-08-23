<?php

function render_template($fn, $vars=array()) {
	foreach ($vars as $key=>$value)
		$$key=$value;

	require $fn;
}

function rand_chars($num) {
	$s="";

	for ($i=0; $i<$num; $i++)
		$s.=chr(rand(ord('A'),ord('Z')));

	return $s;
}

function render_select_options($options, $current) {
	foreach ($options as $key=>$label) {
		$keyHtml=htmlspecialchars($key);
		$labelHtml=htmlspecialchars($label);
		$selectedHtml=(($current==$key)?"selected":"");
		echo "<option value='$keyHtml' $selectedHtml>$labelHtml</option>";
	}
}