<?php

/**
 * GIS functions with Wordpress user objects.
 * 
 * Users require a meta property called 
 * user_latitude and user_longitude
 */

/**
 * Returns an array with the lat long of the current user;
 * @return array
 */
function currentLatLong() {
	$current_user =  wp_get_current_user();	
	return getLatLong($current_user);
}


/**
 * Returns a rought bounding box according to a specified distance 
 * See vinsol.com/blog/2t011/08/30/geoproximity-search-with-mysql/
						    1˚ of latitude ~= 111.04 Kms
						    1˚ of longitude ~= cos(latitude) * 111.04 Kms 
 * @param float Distance in meters
 * @return An array with minLat, minLong, maxLat, maxLong
 */
function roughBoundBox($radiusMeters) {
	list($originLat, $originLong) = currentLatLong();

	if(!is_numeric($originLat))   $originLat  = 0;
	if(!is_numeric($originLong))  $originLong = 0;

	$deltaLat  = $radiusMeters/1000/111.04;
	$deltaLong = $radiusMeters/1000/(cos($originLat)*111.04);
	
	$minLat		= $originLat  - $deltaLat;
	$maxLat		= $originLat  + $deltaLat;
	
	$minLong	= $originLong - $deltaLong;
	$maxLong	= $originLong + $deltaLong;
	
	return array($minLat, $minLong, $maxLat, $maxLong);
}


function distance_from_user($foundUserID) {

	list($currentLat, $currentLong) = currentLatLong();
	
	$foundUser	= new WP_User($foundUserID);
	list($foundLat, $foundLong)		= getLatLong($foundUser);
	
	$distance = haversineGreatCircleDistance($currentLat, $currentLong, $foundLat, $foundLong);

	return $distance;
	
	/*
	$debugData = array(
		'date'		=> date('r'),
		'foundUserID' => $foundUserID,
			'current_firstName' => $firstName,
			'current_long'	=> $currentLong,
			'current_lat'	=> $currentLat,
			'current_ID'	=> $currentID,
			'found_lat'		=> $foundLat,
			'found_long'	=> $foundLong,
			'current_user'	=> isObjectStr($current_user),
			'foundUser'		=> isObjectStr($foundUser),
			'distance'		=> $distance,
	);
	$debugStr = var_export($debugData, TRUE);
	file_put_contents('/tmp/wp_distanceFromuser.txt', $debugStr, FILE_APPEND);
	*/
}


function isObjectStr($obj) {
	return is_object($obj) ? "It is an object" : "It is not an object";
}



function getLatLong($wpUser) {
	 return array(
			$wpUser->get('user_latitude'),
			$wpUser->get('user_longitude'),
	);
}



/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [m]
 * @author Martin Stoeckli martinstoeckli@gmx.ch http://www.martinstoeckli.ch/php/php.html#great_circle_dist
 * @return float Distance between points in [m] (same as earthRadius)
 */
function haversineGreatCircleDistance(
		$latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
	// convert from degrees to radians
	$latFrom = deg2rad($latitudeFrom);
	$lonFrom = deg2rad($longitudeFrom);
	$latTo = deg2rad($latitudeTo);
	$lonTo = deg2rad($longitudeTo);

	$latDelta = $latTo - $latFrom;
	$lonDelta = $lonTo - $lonFrom;

	$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
			cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	return $angle * $earthRadius;
}
