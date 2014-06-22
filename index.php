<?php

/**
 * This sample lists videos that are associated with a particular keyword and are in the radius of
 *   particular geographic coordinates by:
 *
 * 1. Searching videos with "youtube.search.list" method and setting "type", "q", "location" and
 *   "locationRadius" parameters.
 * 2. Retrieving location details for each video with "youtube.videos.list" method and setting
 *   "id" parameter to comma separated list of video IDs in search result.
 *
 * @author Ibrahim Ulukaya
 */

$htmlBody = <<<END
<form method="GET" class="form">
<h3> Search Fields <span class="highlight"></br>(You can replace text below, but all fields must be filled in)</span></h3>
  <div class="form-item">
    Search Term: &nbsp<input type="search" class="searchbox" id="q" name="q" class="typeahead" value="ISIS">
  </div>
  <div class="form-item">
    Coordinates: &nbsp <input type="text" id="location" name="location" placeholder="00.00000,00.00000" value="36.3400, 43.1300">
  </div>
  <div class="form-item">
    Radius: &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<input type="text" id="locationRadius" name="locationRadius" value="1000km">
  </div>
  <div class="form-item">
    Max Results: &nbsp <input type="number" id="maxResults" name="maxResults" min="1" max="50" step="1" value="50">
  </div>
  <input type="submit" value="Search" class="button">
</form>
END;

// This code executes if the user enters a search query in the form
// and submits the form. Otherwise, the page displays the form above.
if ($_GET['q'] && $_GET['maxResults']) {
  // Call set_include_path() as needed to point to your client library.
  require_once 'Google/Client.php';
  require_once 'Google/Service/YouTube.php';

  /*
   * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
  * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
  * Please ensure that you have enabled the YouTube Data API for your project.
  */
  
  $DEVELOPER_KEY = 'AIzaSyD7o4gDHBjZ9ekU5BHWwR3P0jdZdv4EMxU';

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);

  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
        'type' => 'video',
        'q' => $_GET['q'],
        'location' =>  $_GET['location'],
        'locationRadius' =>  $_GET['locationRadius'],
        'maxResults' => $_GET['maxResults'],
    ));

    $videoResults = array();
    
    # Merge video ids
    foreach ($searchResponse['items'] as $searchResult) {
      array_push($videoResults, $searchResult['id']['videoId']);
    }
    
    $videoIds = join(',', $videoResults);
    
   
    # Call the videos.list method to retrieve location details for each video.
    $videosResponse = $youtube->videos->listVideos('snippet, recordingDetails', array(
    'id' => $videoIds,
    ));

    $videos = '';

    // Display the list of matching videos.
    foreach ($videosResponse['items'] as $videoResult) {
		
     $videos .= sprintf('<div> <h4>%s</h4> <p><iframe width="640" height="390" src="http://www.youtube.com/embed/%s"></iframe></p> <p>Video Id Number: <span class="highlight" style="font-size:1.5em;">%s</span></p><p>Geotags (lat, long) :  (%s,%s)</p></div>',
	      $videoResult['snippet']['title'],
	      $videoResult['id'], // This line returns the video id for the embed code
	      $videoResult['id'], 
	      $videoResult['recordingDetails']['location']['latitude'],
          $videoResult['recordingDetails']['location']['longitude']);
              
     /*
$videos .= sprintf('<li>%s (%s,%s) || %d</li>',
          $videoResult['snippet']['title'],
          $videoResult['recordingDetails']['location']['latitude'],
          $videoResult['recordingDetails']['location']['longitude'],
          $videoResult['recordingDetails']['recordingDate']);
*/
    }

    $htmlBody .= <<<END
    <div class="contain-results">
    <ul>$videos</ul>
    </div>
END;
  } catch (Google_ServiceException $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }
}
?>

<!doctype html>
<html>
<head>

<link rel="stylesheet" type="text/css" href="tubes.css">
  <meta charset="UTF-8">
<title>youtube vid search tool</title>
</head>
<body>
<h1>YOUTUBE GEO SEARCH TOOL _V 1.0</h1>
<h2>Find videos about a subject within a specified geographic radius</h2>
<div class= "suggestions">
	<h3> Some possible Search Terms <br/><span class="highlight">(Highlight and copy these for now)</span></h3>
	<a class="link"> isis </a>
	<a class="link"> isil <a>
	<a class="link"> ISIS <a>
	<a class="link"> ISIL <a>
	<a class="link"> داعش </a>
	<a class="link"> الدولة الإسلامية في العراق والشام</a>

	<h3> Some possible Coordinates (in decimal degrees)<br/><span class="highlight">(Highlight and copy these for now)</span></h3>
	
	<a> Mosul: <span class="link">36.3400, 43.1300</span> </a>
	<a> Fallujah: <span class="link">33.3500, 43.7833</span> </a>
	<a> Tikrit: <span class="link">34.6000, 43.6833</span> </a>
	<a> Erbil: <span class="link">36.19111, 44.00917</span> </a>
	<a> Deir ez-Zor Governorate:  <span class="link">35.3360, 40.1450</span> </a>
	<a> Ar-Raqqah:  <span class="link">35.9500, 39.0167</span> </a>
	<a> Al-Bukamal:  <span class="link">34.4536, 40.9367</span> </a>
	<a href="http://dateandtime.info/citycoordinates.php" target="_blank"> <span class="link"> Find new city coordinates</span></a>

</div>
<?=$htmlBody?>
</body>
</html>




