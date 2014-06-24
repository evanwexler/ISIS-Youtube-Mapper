<?php

/**
 * Working from init examply by @author Ibrahim Ulukaya
 */

/* Build initial form  to get some, gets called in the HTML further down */

$htmlBody = <<<END
<form method="GET" class="form" form name="form">
<h3> Search Fields <span class="highlight"></br>(You can replace text below, but all fields must be filled in)</span></h3>
  <div class="form-item">
    Search Term: &nbsp<input type="search" class="searchbox" id="q" name="q" class="typeahead" placeholder="enter search term" >
  </div>
  <div class="form-item">
    Coordinates: &nbsp <input type="text" id="location" name="location" placeholder="00.00000,00.00000" placeholder="36.3400, 43.1300">
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
        'videoType' => 'any',
        'order' => 'date',
        'q' => $_GET['q'],
        'location' =>  $_GET['location'],
        'locationRadius' =>  $_GET['locationRadius'],
        'maxResults' => $_GET['maxResults'],
    ));

    $videoResults = array();
    
    # Merge video ids for a list 
    foreach ($searchResponse['items'] as $searchResult) {
      array_push($videoResults, $searchResult['id']['videoId']);
    }
    
    $videoIds = join(',', $videoResults);
    
   
    # Call the videos.list method to retrieve location details for each video.
    $videosResponse = $youtube->videos->listVideos('snippet, recordingDetails', array(
    'id' => $videoIds,
    ));

    $videos = '';

    // Display the list of matching videos with embedded videos
    foreach ($videosResponse['items'] as $videoResult) {
		$a++;
     $videos .= sprintf('<div class="result"> <h4>%s</h4> <p><iframe class ="video" width="640" height="390" src="http://www.youtube.com/embed/%s"></iframe></p> <p class="id-print">Video Id Number: <span class="highlight" style="font-size:1.5em;">%s</span></p><p class="geotags">Geotags (lat, long) : (%s,%s)</p> <iframe class="map" frameborder="0" src="https://www.google.com/maps/embed/v1/place?key=AIzaSyD7o4gDHBjZ9ekU5BHWwR3P0jdZdv4EMxU&q=%s,%s"></iframe> </div> ',
	      $videoResult['snippet']['title'],
	      $videoResult['id'], // This line returns the video id for the embed code
	      $videoResult['id'], // This line returns the video id to print out underneath the video window, for reference
	      $videoResult['recordingDetails']['location']['latitude'],
          $videoResult['recordingDetails']['location']['longitude'],
          $videoResult['recordingDetails']['location']['latitude'],
          $videoResult['recordingDetails']['location']['longitude']);
              
    }

    $htmlBody .= <<<END
    <div class="contain-results">
    <ul>$videos</ul>
    </div>
END;

/* throw some error when things go awry */

  } catch (Google_ServiceException $e) {
    $htmlBody .= sprintf('<p>IGNORE. THIS IS FOR EVAN: A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>IGNORE. THIS IS FOR EVAN: An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }
}
?>

<!-- Rest of HTML for the page -->
<!doctype html>
<html>
<head>
<link href='http://fonts.googleapis.com/css?family=Raleway:100,200,400' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="tubes.css">
  <meta charset="UTF-8">
<title>youtube vid search tool</title>
</head>
<body>
<h1>YOUTUBE GEO SEARCH TOOL _V 1.1</h1>
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
	<a> Baiji (Oil Refinery): <span class="link">34.9292, 43.4931</span> </a>
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

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<script type="text/javascript"> 


$( ".button" ).click(function() {
  validateForm();
});

function validateForm() {
    var x = document.forms["form"]["q"].value;
    var y = document.forms["form"]["location"].value;
    if (x == null || x == "" || y == null || y == "" ) {
        alert("Sorry. All fields must be filled out to search. :(");
        return false;
    }
}


/* Make me some maps */
/*
function initialize() {
        var mapOptions = {
          center: new google.maps.LatLng(-34.397, 150.644),
          zoom: 8
        };
        var map = new google.maps.Map(document.getElementById("map-canvas"),
            mapOptions);
      }
      google.maps.event.addDomListener(window, 'load', initialize);
*/


</script> 