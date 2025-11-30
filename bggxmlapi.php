<?php
function getXMLfromBGG($url,$convertToObject){
  global $apiToken;
  // initialize curl instance
  $curlHandle = curl_init();
  
  // setup curl 
  $headers = ['Authorization: Bearer '.$apiToken];

  curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curlHandle, CURLOPT_URL, $url);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curlHandle, CURLOPT_TIMEOUT, 200);

  // execute curl
  try {
    $curlResponse = curl_exec($curlHandle);
  }
  catch (Exception $e) {
    echo '<p>Caught exception: ',  $e->getMessage(), "</p>";
  }

  // handle curl response
  if ($curlResponse === false) {
    $curlHTTPcode = curl_getinfo($curlHandle);
    echo "<p>I've waited very long for BGG. BGG might be really busy, it's a very large collection or something else went wrong. [HTTP: ".$curlHTTPcode['http_code']."]</p>";
    //echo "<p>[URL: ".$curlHTTPcode['url']."]</p>";
  } else {
    // convert response to XML object for processing
    $curlInfo = curl_getinfo($curlHandle);
    $curlHTTPcode = $curlInfo['http_code'];

    switch($curlHTTPcode){
      case 202: 
        return (false);
        break;
      case 401: 
        echo "<p>Unauthorized request (HTTP ".$curlHTTPcode."), please get in contact with the developer.</p>";
        break;
      case 429:
        echo "<p>Too many requests (HTTP ".$curlHTTPcode."), try to select less options.</p>";  
        break;
      case 200: 
        //echo "<p>getXMLfromBGG:<br><textarea>".$curlResponse."</textarea>";
        if($convertToObject == true){
          $xml = simplexml_load_string($curlResponse);
          if ($xml === false) {
            echo "<p>BGG responded, but I cannot understand their response.</p>";
          } else {
            sleep(3); // sleep a few second as to not trigger a HTTP 429
            return ($xml);
          }
        } else {
          $xml = $curlResponse;
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

// Check if $input confirms to BGG Username requirements:
// min. length 4
// max. length 20
// starts with a letter
// can only contain letters, numbers and _ (underscore)
function validBGGUsernameInput($input){
  $RegexUsernameBGG = "/^[A-Za-z]{1}[A-Za-z0-9_]{3}[A-Za-z0-9_]{0,}$/";
  if(preg_match($RegexUsernameBGG,$input)){
    return true;
  } else {
    return false;
  }
}

// Check if $input confirms to BGG Geeklist requirements:
// Any integer value should be fine
function validBGGGeeklistInput($input){
  $RegexGeeklistBGG = "/^[0-9]+$/";
  if(preg_match($RegexGeeklistBGG,$input)){
    return true;
  } else {
    return false;
  }
}