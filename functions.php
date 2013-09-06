<?php
function debug($var = '', $exit = false, $called = 0, $showFrom = true, $showHtml = false)
{
	if ($showFrom)
	{
		$calledFrom = debug_backtrace();
		echo '<strong>' . $calledFrom[$called]['file'] . '</strong>';
		echo ' (line <strong>' . $calledFrom[$called]['line'] . '</strong>)';
	}
	echo "\n<pre>\n";
	
	$var = print_r($var, true);
	if ($showHtml)
	{
		$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
	}
	echo $var . "\n</pre>\n";
	
	if ($exit)
	{
		exit();
	}
}

function x($var = '')
{
	debug($var, true, 1);
}