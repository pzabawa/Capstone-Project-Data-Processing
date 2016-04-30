<?php

/*
 * Initialize data.
 *
 * "input.csv" should look like this:
 *  [0] => address/neighborhood
 *  [1] => reposting_id
 *  [2] => title
 *  [3] => price
 *  [4] => bedrooms
 *  [5] => size (ft2)
 *  [6] => link
 *  [7] => id
 *
 * "output.csv" will look like this:
 *  [0] => address/neighborhood
 *  [1] => reposting_id
 *  [2] => title
 *  [3] => price
 *  [4] => bedrooms
 *  [5] => size (ft2)
 *  [6] => link
 *  [7] => id
 *  [8] => neighborhood
*   [9] => city
 */
$readHandle = fopen("input-csv-goes-here/input.csv", "r");
$writeHandle = fopen("output-csv-found-here/output.csv", "w");

// TODO Remove duplicates from input.csv...

// Process each line of "input.csv"...
while( $csvLineArray = fgetcsv($readHandle) ){
	// If no neighborhood, skip.
	if( empty($csvLineArray[0]) )
		continue;
	// If no price, skip.
	if( empty($csvLineArray[3]) )
		continue;
	// If no bedrooms, skip.
	if( empty($csvLineArray[4]) )
		continue;
	// Get the neighborhood from the Google Geocode API.
	$neighborhoodCityArray = getNeighborhoodFromGoogle($csvLineArray[0]);
	// If no neighborhood, skip.
	if( empty($neighborhoodCityArray) )
		continue;
	/// Write the processed line to "output.csv".
	fputcsv($writeHandle, array_merge($csvLineArray, $neighborhoodCityArray));
}

// Close file handles.
fclose($readHandle);
fclose($writeHandle);
echo "Script completed without errors.\n";



function getNeighborhoodFromGoogle($address)
{
	$url = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyDepye_kPyix97bgRtiI8weT_D7mcXMNt0&address=" . urlencode(trim($address) . "Philadelphia PA");
	$json = file_get_contents($url);
	$obj = json_decode($json);

	if( $obj->results
	 && $obj->results[0]
	 && $obj->results[0]->address_components ){
		foreach( $obj->results[0]->address_components AS $component ){
			if( in_array("neighborhood", $component->types) ){
				$neighborhood = $component->long_name;
			}
			if( in_array("locality", $component->types) ){
				$city = $component->long_name;
			}
		}
	}

	return ($neighborhood && $city) ? array($neighborhood, $city) : array();
}
