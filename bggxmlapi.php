<?php
function getXMLfromBGG($url,$retryCount){
  global $apiToken;
  // initialize curl instance
  $curlHandle = curl_init();
  //echo "<p>getXMLfromBGG: curl_init</p>";
  // setup curl 
  $headers = ['Authorization: Bearer '.$apiToken];

  //echo "<p>getXMLfromBGG: header variable</p>";

  curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curlHandle, CURLOPT_URL, $url);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curlHandle, CURLOPT_TIMEOUT, 200);

  // execute curl
  //echo "<p>getXMLfromBGG curl_exec</p>";
  try {
    $curlResponse = curl_exec($curlHandle);
  }
  catch (Exception $e) {
    echo '<p>Caught exception: ',  $e->getMessage(), "</p>";
  }

  // handle curl response
  //echo "<p>getXMLfromBGG process curl_exec response</p>";
  if ($curlResponse === false) {
    //echo "<p>getXMLfromBGG curlResponse = false</p>";
    //fwrite($fp, curl_error($ch));
    $curlHTTPcode = curl_getinfo($curlHandle);
    echo "<p>I've waited very long for BGG. BGG might be really busy, it's a very large collection or something else went wrong. [HTTP: ".$curlHTTPcode['http_code']."]</p>";
    //echo "<p>[URL: ".$curlHTTPcode['url']."]</p>";
    // depending on the response retry???
    //$retryCount++;
    // getXMLfromBGG($url, $retryCount);
  } else {
    //echo "<p>getXMLfromBGG curlResponse = XML</p>";
    // convert response to XML object for processing
    $curlInfo = curl_getinfo($curlHandle);
    $curlHTTPcode = $curlInfo['http_code'];

    switch($curlHTTPcode){
      case 202: 
        echo "<p>Waiting 10 seconds...</p>";
        sleep(10);
        getXMLfromBGG($url, $retryCount);
        //echo "[HTTP: ".$curlInfo['http_code']."]";
        //echo "[URL: ".$curlInfo['url']."]";
        break;
      case 401: 
        echo "<p>Unauthorized request (HTTP ".$curlHTTPcode."), please get in contact with the developer.</p>";  
        break;
      case 429:
        echo "<p>Too many requests (HTTP ".$curlHTTPcode."), try to select less options.</p>";  
        break;
      case 200: 
        //echo "<p>getXMLfromBGG:<br><textarea>".$curlResponse."</textarea>";
        $xml = simplexml_load_string($curlResponse);
        if ($xml === false) {
          echo "<p>BGG responded, but I cannot understand their response.</p>";
        } else {
          sleep(3); // sleep a few second as to not trigger a HTTP 429
          return ($xml);
        }
        break;
      default: 
        echo "<p>Unable to handle a HTTP ".$curlHTTPcode." response.</p>"; 
        break;
    }      
  }
}

?>