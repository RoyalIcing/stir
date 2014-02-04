<?php
/*
	Copyright 2012-2014 Patrick Smith
	
	This content is released under the MIT License: http://opensource.org/licenses/MIT
*/


define('STIR_BASE_PATH', dirname(__FILE__). '/');


if (defined('STIR_ENABLED') and STIR_ENABLED):
	require_once(STIR_BASE_PATH. 'stir-enabled.php');
else:
	require_once(STIR_BASE_PATH. 'stir-disabled.php');
endif;
