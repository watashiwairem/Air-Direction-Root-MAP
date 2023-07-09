
<?php
function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit = 'km') {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "KM") {
        return ($miles * 1.609344);
    } elseif ($unit == "NM") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}
	/* Database connection settings */
	$host = 'localhost';
	$user = 'root';
	$pass = '';
	$db = 'sehirbilgileri';
	$mysqli = new mysqli($host,$user,$pass,$db) or die($mysqli->error);


	if(isset($_GET['sehir-1']) && isset($_GET['sehir-2'])){
		$sehir_bir = $_GET['sehir-1'];

		$queryStartPoint = "SELECT `nameSehir` FROM `sehir-bilgileri` WHERE sehir_id = $sehir_bir";
    	$resultStartPoint = $mysqli->query($queryStartPoint) or die('Data selection for starting point failed: ' . $mysqli->error);
    	$rowStartPoint = mysqli_fetch_array($resultStartPoint);
    	$startPointName = $rowStartPoint['nameSehir'];

		$sehir_iki = $_GET['sehir-2'];
		$queryEndPoint = "SELECT `nameSehir` FROM `sehir-bilgileri` WHERE sehir_id = $sehir_iki";
		$resultEndPoint = $mysqli->query($queryEndPoint) or die('Data selection for starting point failed: ' . $mysqli->error);
		$rowEndPoint = mysqli_fetch_array($resultEndPoint);
    	$startEndName = $rowEndPoint['nameSehir'];
	
		$query1 = "SELECT `latitude`, `longitude` FROM `sehir-bilgileri` WHERE sehir_id = $sehir_bir";
		$result1 = $mysqli->query($query1) or die('Data selection for starting point failed: ' . $mysqli->error);
		$row1 = mysqli_fetch_array($result1);
		$startLatitude = $row1['latitude'];
		$startLongitude = $row1['longitude'];
	
		$query2 = "SELECT `latitude`, `longitude` FROM `sehir-bilgileri` WHERE sehir_id = $sehir_iki";
		$result2 = $mysqli->query($query2) or die('Data selection for destination point failed: ' . $mysqli->error);
		$row2 = mysqli_fetch_array($result2);
		$destLatitude = $row2['latitude'];
		$destLongitude = $row2['longitude'];
	
		$distance = calculateDistance($startLatitude, $startLongitude, $destLatitude, $destLongitude);
		$formattedDistance = number_format($distance, 2);
	}
	
 	$coordinates = array();
 	$latitudes = array();
 	$longitudes = array();

	// Select all the rows in the markers table
	$query = "SELECT  `latitude`, `longitude` FROM `sehir-bilgileri` Where sehir_id= $sehir_bir ";
	$result = $mysqli->query($query) or die('data selection for google map failed: ' . $mysqli->error);

	$query2 = "SELECT  `latitude`, `longitude` FROM `sehir-bilgileri` Where sehir_id= $sehir_iki ";
	$result2 = $mysqli->query($query2) or die('data selection for google map failed: ' . $mysqli->error);

 	while ($row = mysqli_fetch_array($result)) {

		$latitudes[] = $row['latitude'];
		$longitudes[] = $row['longitude'];
		$coordinates[] = 'new google.maps.LatLng(' . $row['latitude'] .','. $row['longitude'] .'),';
	}

	while ($row2 = mysqli_fetch_array($result2)) {

		$latitudes[] = $row2['latitude'];
		$longitudes[] = $row2['longitude'];
		$coordinates[] = 'new google.maps.LatLng(' . $row2['latitude'] .','. $row2['longitude'] .'),';
	}

	//remove the comaa ',' from last coordinate
	$lastcount = count($coordinates)-1;
	$coordinates[$lastcount] = trim($coordinates[$lastcount], ",");
?>

<!DOCTYPE html>
<html>
	<head>
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="style.css">
		<title>Google MAP</title>
	</head>

	<body>
		<div>
    		<h3 style="margin-left:auto; margin-right:auto;width:200px;height:20px; border-top-style:solid;border-bottom-style:solid; border-left-style:double;border-right-style:double;">BİLGİLER</h3>
    		<div style="margin-left:auto; margin-right:auto;width:400px;height:23px; border:4px dashed #000;"><?php echo "Başlangıç Noktası: " . $startPointName; ?></div>
			<div style="margin-left:auto; margin-right:auto;width:400px;height:23px; border:4px dashed #000;"><?php echo "Varış Noktası: " . $startEndName; ?></div>
			<div style="margin-left:auto; margin-right:auto;width:400px;height:23px; border:4px dashed #000;"><?php echo "İki Şehir Arası mesafe: " . $formattedDistance ?></div>
		</div>
<br>
		<div id="map" style="width: 100%; height: 80vh;"></div>

		<script>
			function initMap() {
			  var mapOptions = {
			    zoom: 18,
			    center: {<?php echo'lat:'. $latitudes[0] .', lng:'. $longitudes[0] ;?>}, //{lat: --- , lng: ....}
			    mapTypeId: google.maps.MapTypeId.SATELITE
			  };

			  var map = new google.maps.Map(document.getElementById('map'),mapOptions);

			  var RouteCoordinates = [
			  	<?php
			  		$i = 0;
					while ($i < count($coordinates)) {
						echo $coordinates[$i];
						$i++;
					}
			  	?>
			  ];

			  var RoutePath = new google.maps.Polyline({
			    path: RouteCoordinates,
			    geodesic: true,
			    strokeColor: '#194d06',
			    strokeOpacity: 1.0,
			    strokeWeight: 10
			  });

			  mark = 'img/ilk.png';
			  flag = 'img/son.png';

			  startPoint = {<?php echo'lat:'. $latitudes[0] .', lng:'. $longitudes[0] ;?>};
			  endPoint = {<?php echo'lat:'.$latitudes[$lastcount] .', lng:'. $longitudes[$lastcount] ;?>};

			  var marker = new google.maps.Marker({
			  	position: startPoint,
			  	map: map,
			  	icon: mark,
			  	title:"Start point!",
			  	animation: google.maps.Animation.BOUNCE
			  });

			  var marker = new google.maps.Marker({
			  position: endPoint,
			   map: map,
			   icon: flag,
			   title:"End point!",
			   animation: google.maps.Animation.DROP
			});

			  RoutePath.setMap(map);
			
			}

			google.maps.event.addDomListener(window, 'load', initialize)
    	</script>

    	<!--remenber to put your google map key-->
	    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC-dFHYjTqEVLndbN2gdvXsx09jfJHmNc8&callback=initMap"></script>

	</body>
</html>