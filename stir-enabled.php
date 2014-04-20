<?php
/*
	Copyright 2012-2014 Patrick Smith
	
	This content is released under the MIT License: http://opensource.org/licenses/MIT
*/


// If using WordPress, then save MySQL queries.
if (!defined('SAVEQUERIES')):
	define('SAVEQUERIES', true);
endif;


if ( !function_exists( 'microtime_float' )):

function microtime_float()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

endif;



function stir($actionName = null, $notchID = null)
{
	burntActionRecordTime($actionName, $notchID);
}

function stirring($actionName = null, $notchID = null)
{
	burntActionRecordTime($actionName, $notchID);
}

function stirred($actionName = null)
{
	burntActionRecordTime($actionName);
}


function burntActionRecordTime($actionName = null, $notchID = null)
{
	global $burntTimeRecordings;
	
	$now = microtime_float();
	
	if (empty($burntTimeRecordings)):
		$burntTimeRecordings = array(
			'actions' => array(),
			'baseTime' => $now,
			'actionOrder' => array()
		);
	endif;
	
	if (empty($actionName)):
		// Do nothing extra.
	elseif (empty($burntTimeRecordings['actions'][$actionName])):
		$burntTimeRecordings['actions'][$actionName] = array(
			'startTime' => $now,
			'absoluteStart' => $now - $burntTimeRecordings['baseTime'],
			'duration' => 0.0,
			'count' => 0
		);
		$burntTimeRecordings['actionOrder'][] = $actionName;
	else:
		$action = &$burntTimeRecordings['actions'][$actionName];
		
		if (!empty($notchID)):
			if (empty($action['notches'])):
				$action['notches'] = array(
					'IDs' => array(),
					'info' => array()
				);
			endif;
			
			if (empty($action['notches']['lastNotch']))
				$action['notches']['lastNotch'] = $action['startTime'];
			
			$duration = $now - $action['notches']['lastNotch'];
			
			$notch = &$action['notches']['info'][$notchID];
			if (empty($notch)):
				$action['notches']['IDs'][] = $notchID;
				$action['notches']['info'][$notchID] = array(
					'duration' => 0.0,
					'count' => 0
				);
				$notch = &$action['notches']['info'][$notchID];
			endif;
			
			$notch['duration'] += $duration;
			$notch['count'] += 1;
			
			$action['notches']['lastNotch'] = $now;
		elseif (empty($action['ended'])):
			$duration = $now - $action['startTime'];
			
			$action['endTime'] = $now;
			$action['duration'] += $duration;
			$action['count']++;
			$action['ended'] = true;
			if (!empty($action['notches'])):
				unset($action['notches']['lastNotch']);
			endif;
		else:
			$action['startTime'] = $now;
			$action['ended'] = false;
		endif;
	endif;
}

function stirGarnishDuration($durationInSeconds)
{
	if (false):
		return number_format($durationInSeconds, 4).'s';
	else:
		return number_format($durationInSeconds * 1000, 1).'ms';
	endif;
}

// whiskPourOut
// stirGetTimes()
function stirGetRecordedTimes()
{
	global $burntTimeRecordings;
	
	$info = array();
	
	$now = microtime_float();
	$totalDuration = $now - $burntTimeRecordings['baseTime'];
	$totalWeight = 20;
	
	$info['totalDuration'] = stirGarnishDuration($totalDuration);
	$info['totalWeight'] = $totalWeight;
	$info['actions'] = array();

	foreach ($burntTimeRecordings['actionOrder'] as $actionName):
		$actionInfo = array();
		$action = $burntTimeRecordings['actions'][$actionName];
		$actionInfo['actionID'] = $actionName;
		
		$duration = $action['duration'];
		$actionWeight = ($duration / $totalDuration);
		
		$actionInfo['duration'] = stirGarnishDuration($duration);
		$actionInfo['weight'] = $actionWeight;
		
		ob_start();
		//echo str_pad(' ', round((1.0 - $actionWeight) * $totalWeight));
		$startTime = $action['absoluteStart'];
		$startWeight = ($startTime / $totalDuration);
		$actionInfo['precedingDuration'] = $startWeight;
		$actionInfo['precedingWeight'] = $startWeight;
		//$durationSinceBeginningDisplay = str_pad('', round($startWeight * $totalWeight), '.', STR_PAD_LEFT);
		//$durationDisplay = str_pad(' ', round($actionWeight * $totalWeight), '#', STR_PAD_LEFT);
		//echo $durationSinceBeginningDisplay.$durationDisplay."\n";
		
		$durationDisplay = stirGarnishDuration($duration);
		
		//echo "{$actionName}: ";
		
		if ($action['count'] > 1):
			$averageDurationDisplay = stirGarnishDuration($action['duration'] / $action['count']);
			echo "total: $durationDisplay / {$action['count']}, average: $averageDurationDisplay";
		elseif ($action['count'] > 0):
			echo "$durationDisplay";
		endif;
		
		if (!empty($action['notches'])):
			$betweenSpacer = '    ';
			echo "\n";
			echo ' ['.$betweenSpacer;
			foreach ($action['notches']['IDs'] as $notchID):
				$notchInfo = $action['notches']['info'][$notchID];
				$durationDisplay = stirGarnishDuration($notchInfo['duration']);
				echo "$notchID: $durationDisplay ({$notchInfo['count']})".$betweenSpacer;
			endforeach;
			echo ']';
		endif;
		
		$output = ob_get_clean();
		str_replace(' ', '&nbsp;', $output);
		
		$actionInfo['breakdown'] = $output;
		
		$info['actions'][] = $actionInfo;
	endforeach;
	
	return $info;
}

function stirGetRecordedQueries()
{
	global $wpdb;
	
	// Only works with WordPress's database querier, for the moment.
	if (!isset($GLOBALS['wpdb']) || !isset($wpdb->queries)) {
		return null;
	}
	
	$recordedQueries = array();
	foreach ($wpdb->queries as $recordedQuery):
		$recordedQueries[] = array('query' => $recordedQuery[0], 'duration' => stirGarnishDuration($recordedQuery[1]), 'backtrace' => $recordedQuery[2]);
	endforeach;
	
	return $recordedQueries;
}

function stirDisplayRecordedTimes($options = null)
{
	global $stir;
	
	$outputInfo = array();
	
	$recordedTimes = stirGetRecordedTimes();
	
	global $ilCacheLoadTime, $ilMustUsePluginLoadTime, $themeReadyLoadTime;
	$recordedTimes['fromLoadTotalDuration'] = timer_stop(0, 4);
	$recordedTimes['cacheLoadTime'] = !empty($ilCacheLoadTime) ? $ilCacheLoadTime : '?';
	$recordedTimes['mustUsePluginLoad'] = !empty($ilMustUsePluginLoadTime) ? $ilMustUsePluginLoadTime : '?';
	$recordedTimes['themeReadyLoad'] = $themeReadyLoadTime;
	
	
	$outputInfo['recordedTimes'] = $recordedTimes;
	$outputInfo['recordedQueries'] = stirGetRecordedQueries();
	
	
	if (isset($stir['JSONResult'])):
		$outputInfo['result'] = $stir['JSONResult'];
		if (!empty($stir['actionID']))
			$outputInfo['actionID'] = $stir['actionID'];
		
		echo json_encode($outputInfo);
	else:
		echo htmlspecialchars(json_encode($outputInfo));
	endif;
}


function stirDisplayRecordedTimesForHTML()
{
	stirred('whole');
?>
<div id="tidalRecordedTimes">
<?php
		stirDisplayRecordedTimes();
?>
</div>
<?php
}

function stirDisplayJSONInfo($result, $actionID = null)
{
	global $stir;
	
	header('Content-Type: application/json');
	
	if (empty($stir)) {
		$stir = array();
	}
	
	$stir['JSONResult'] = $result;
	$stir['actionID'] = $actionID;
	
	// Defer display of JSON info, to record everything including
	// after this function completes.
	register_shutdown_function('stirJSONInfoShutdown');
}

function stirJSONInfoShutdown()
{
	stirred('whole');
	stirDisplayRecordedTimes();
}


// Begin recording whole time span.
stir('whole');
