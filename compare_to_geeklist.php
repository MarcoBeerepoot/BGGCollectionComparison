<!DOCTYPE html>
<html lang="en">
<head>
  <title>BGG Collection Comparison</title>
  <link rel="stylesheet" href="style.css" media="all">
  <script type="application/javascript" src="scripts.js"></script>
  <link rel="shortcut icon" href="https://cf.geekdo-static.com/icons/favicon2.ico" type="image/ico">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <header>
    <h1>Comparison of user's BoardGameGeek collection to geeklist</h1>
  </header>
  <main> 
    <div class="content"?>
<?php 
include 'connection.php';
include 'bggxmlapi.php';

class GeeklistItem {
	private $id;
	private $objectId;
	private $objectName;
	
	public function __construct($id, $name, $objectId) {
		$this->id = $id;
		$this->name = $name;
		$this->objectId = $objectId;
	}	
	
	public function getID(){
		return $this->id;
	}
		
	public function setID($newID){
		$this->id = $newID;
	}
	
	public function getObjectName(){
		return $this->objectName;
	}
	
	public function setObjectName($newName){
		$this->objectName = $newName;
	}
	
	public function getObjectID(){
		return $this->objectId;
	}
		
	public function setObjectID($newID){
		$this->objectId = $newID;
	}
}
      
set_time_limit(240);

$username = $_POST["username"]; 
$geeklist = $_POST["geeklist"]; 
$dropdownValue = $_POST["dropdownValue"];

$url = "https://boardgamegeek.com/xmlapi/collection/".$username."?".$dropdownValue."=1";
$url2 = "https://boardgamegeek.com/xmlapi/geeklist/".$geeklist;
// read feed into SimpleXML object
  
$sxml = getXMLfromBGG($url, 0);
if($sxml === false){
  echo "<p>Waiting 10 seconds for BGG to process request...</p>";
  sleep(10);
  $sxml = getXMLfromBGG($url, 0);
  if($sxml === false){
    echo "<p>Waiting 20 seconds for BGG to process request...</p>";
    sleep(20);
    $sxml = getXMLfromBGG($url, 0);
    if($sxml === false){
      echo "<p>BGG is still processing, pleasee try again in 60 seconds.</p>"; 
    } else {
      echo "<p>User collection loaded.</p>";  
    }
  } else {
    echo "<p>User collection loaded.</p>";
  }
} else {
  echo "<p>User collection loaded.</p>";
}
      
$sxml2 = getXMLfromBGG($url, 0);
if($sxml2 === false){
  echo "<p>Waiting 10 seconds for BGG to process request...</p>";
  sleep(10);
  $sxml2 = getXMLfromBGG($url, 0);
  if($sxml2 === false){
    echo "<p>Waiting 20 seconds for BGG to process request...</p>";
    sleep(20);
    $sxml2 = getXMLfromBGG($url, 0);
    if($sxml === false){
      echo "<p>BGG is still processing, pleasee try again in 60 seconds.</p>"; 
    } else {
      echo "<p>Geeklist loaded.</p>"; 
    }
  } else {
    echo "<p>Geeklist loaded.</p>";
  }
} else {
  echo "<p>Geeklist loaded.</p>";
}

$listFirstPlayer = array();
$listGeeklist = array();
$idListFirstPlayer = array();
$i = 0;
foreach($sxml->children() as $child){
   $id = (string) $child['objectid'];
   $idListFirstPlayer[$i] = $id;
   $listFirstPlayer[$id] = (string) $child -> name;
   $i++;
}
if(count($listFirstPlayer) == 0){
	echo "<h2>List is empty or your request is in the queue. Please go back and try again.</h2>";
} else {
  $i = 0;
  foreach($sxml2->children() as $child)
    {
     $id = (string) $child['objectid'];
     if(!empty($id)){ 
    $listGeeklist[$i] = new GeeklistItem((string) $child['id'], (string) $child['objectname'], $id);
    $i++;
     }
    }
    if(count($listGeeklist) == 0){
      echo "<h2>List is empty or your request is in the queue. Please go back and try again.</h2>";
    } else {
      echo "<h2>The games that are on both selected lists:</h2>";
      $i = 0;
      foreach($listGeeklist as $game){
        if(in_array($game->getObjectID(), $idListFirstPlayer)){
          if($i == 0){
            echo "<ul>";
          }
          $i++;
          echo "<li><a href='https://boardgamegeek.com/geeklist/".$geeklist."/item/".$game->getID()."#item".$game->getID()."'>".$listFirstPlayer[$game->getObjectID()]."</a></li>";
        }
      }
      if($i == 0){
         echo "<p>None</p>";
      } else {
         echo "</ul>";
      }
    } 
}
?>
    </div>
    <div class="content">
      <h2>Compare something else:</h2>
      <ul>
       <li><a href="./">Compare two user's collections</a></li>
        <li><a href="compare_to_geeklist.html">Compare a user's collection to a geeklist</a></li>
      </ul>

      <h2>Feature requests or bugs?</h2>
      <p>Take a look at the <a href="https://boardgamegeek.com/thread/1792487">BGG thread</a>.</p>
    </div>
  </main>
  <footer>
    <img src="powered_by_logo_01_SM.jpg" alt="Powered by BGG"/>
  </footer>
</body>
</html> 