<?php

function render_template($fn, $vars=array()) {
	foreach ($vars as $key=>$value)
		$$key=$value;

	require $fn;
}
