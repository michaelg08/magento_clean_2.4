<?php
//    phpinfo();
/**
    $a = ['a' => 1, 'b' => 2, 'c' => 3];
    $firstKey = array_key_first($a);

    //var_dump($firstKey);
    var_dump(reset($a));
**/
http://api.worldweatheronline.com/premium/v1/weather.ashx?key=eb1d9b2028e4431385b134447210111&q=London&format=json&includelocation=yes

https://api.weather.com/v1/geocode/49.842957/24.031111/observations.json?units=m&language=us


$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://weatherapi-com.p.rapidapi.com/current.json?q=%3CREQUIRED%3E",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"x-rapidapi-host: weatherapi-com.p.rapidapi.com",
		"x-rapidapi-key: 7c3f0e61aemsh12c850518a75c42p12e705jsnd00f28e91091"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	echo $response;
}    
?>