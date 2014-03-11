<?php

$stop_words = file('stopwords.txt', FILE_IGNORE_NEW_LINES);
print_r($stop_words);

$muni_arr = array();
$major_district_arr = array();
$district_arr = array();
$neighborhood_arr = array();
$sub_district_arr = array();

$lines = file('point.csv', FILE_IGNORE_NEW_LINES);
$lines_count = 0;
foreach ($lines as $line) {
	$lines_count++;
	if ($lines_count == 1) continue;
	$pieces = explode(";",$line);
	$words = get_words($pieces[5]);
  foreach ($words as $word => $value) {
    if (!in_array(utf8_decode($word), $stop_words)) {
		  $muni = strip_arr($pieces[6]);
			$muni_arr[$muni][$word] = $muni_arr[$muni][$word] + 1;
		
			$major_district = strip_arr($pieces[7]);
      $major_district_arr[$major_district][$word] = $major_district_arr[$major_district][$word] + 1;

			$district = strip_arr($pieces[8]);
      $district_arr[$district][$word] = $district_arr[$district][$word] + 1;

      $neighborhood = strip_arr($pieces[9]);
      $neighborhood_arr[$neighborhood][$word] = $neighborhood_arr[$neighborhood][$word] + 1;

      $sub_district = strip_arr($pieces[10]);
      $sub_district_arr[$sub_district][$word] = $sub_district_arr[$sub_district][$word] + 1;
		}
  }
	//print_r($neighborhood_arr);
}

foreach ($neighborhood_arr as $neighborhood => $words) {
	$fh = fopen("output/" . $neighborhood  . ".txt", 'w')  or die("can't open file");;
	$fh_cloud = fopen("output/" . $neighborhood  . "_cloud.txt", 'wa')  or die("can't open file");
	asort($words);
	foreach ($words as $word => $value) {
		for ($i = 1; $i <= $value; $i++) {
        	        fwrite($fh_cloud, $word . " " );
        	}
		
		fwrite($fh, $word . "," . $value . "\n");
	}
	fclose($fh);
	fclose($fh_cloud);
}

function strip_arr($arr){
	return substr($arr,4,-4); 
}
function strip_text($text){
	return substr($text,1,-1); 
}

function get_words($text) {
  $r = array();
	$text = strip_text($text);
	if (strlen($text) == 0)	{
		return $r;
	}
	$text =  urlencode($text);

        $url = "http://semsi.kansanmuisti.fi:8080/stem?text=" . $text;
        $ch = curl_init();

        $opts = array(  CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_SSLVERSION => 1);
                curl_setopt_array($ch, $opts);

        $json = curl_exec($ch);
        echo $json . "\n";
        $json = json_decode($json);
        $response = $json->response;
        $i=0;
        foreach ($response as $value) {
          try {
            $r[$value] = $r[$value] + 1;
          }catch (Exception $e){
            echo 'Caught exception: ',  $e->getMessage(), "\n";
          }
        }
        return $r;
}

?>
