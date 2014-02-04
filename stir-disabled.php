<?php
/*
	Copyright 2012-2014 Patrick Smith
	
	This content is released under the MIT License: http://opensource.org/licenses/MIT
*/

function stir($actionName = null)
{
}

function stirring($actionName = null, $notchID = null)
{
}

function stirred($actionName = null)
{
}


function stirDisplayJSONInfo($info, $actionID = null)
{
	header('Content-Type: application/json');
	
	echo json_encode($info);
}

function stirDisplayRecordedTimesForHTML()
{
	
}