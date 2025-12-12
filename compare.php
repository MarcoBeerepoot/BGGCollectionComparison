<!DOCTYPE html>
<html lang="en">
<head>
  <title>BGGCC - User vs User</title>
  <link rel="stylesheet" href="style.css" media="all">
  <link rel="shortcut icon" href="favicon2.ico" type="image/ico">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <header>
    <h1><span>BGG</span> Collection Comparison Tool - User vs User</h1>
  </header>
  <main>
    <div class="content">
<?php 
//Increase time limit for this script to 240 seconds. Otherwise it will fail with an error after 30 seconds.
set_time_limit(240);
      
include 'connection.php';
include 'bggxmlapi.php';
  
class Game {
	private $id;
	private $name;
	private $wishlistPriority = "-";
	private $comment;
	private $status;
	
	public function __construct($id, $name, $status, $rating) {
		$this->id = $id;
		$this->name = $name;
		$this->status = $status;
    $this->rating = $rating;
	}	
	
	public function getID(){
		return $this->id;
	}
		
	public function setID($newID){
		$this->id = $newID;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($newName){
		$this->name = $newName;
	}
	
	public function getStatus(){
		return $this->convertStatusToReadableText($this->status);
	}
	
	public function setStatus($status){
		$this->status = $status;
	}
	
	public function getComment(){
		return $this->comment;
	}
	
	public function setComment($comment){
		$this->comment = $comment;
	}
  
	public function getRating(){
		return $this->rating;
	}
	
	public function setRating($rating){
		$this->rating = $rating;
	}
	
	public function getWishlistPriority(){
		return $this->wishlistPriority;
	}
	
	public function setWishlistPriority($wishlistPriority){
		$this->wishlistPriority = $this->convertWishlistPriorityToText($wishlistPriority);
	}
	
	private function convertWishlistPriorityToText($priority){
		switch ($priority) {
    case 1:
        return "1 - Must have";
        break;
    case 2:
        return "2 - Love to have";
        break;
    case 3:
        return "3 - Like to have";
        break;
    case 4:
        return "4 - Thinking about it";
        break;
    case 5:
        return "5 - Don't buy this";
        break;
		default:
		return $priority;
    }
	}
	
	private function convertStatusToReadableText($status){
		switch ($status) {
    case "own":
        return "owned";
        break;
    case "comment":
        return "commented";
        break;
    case "trade":
        return "for trade";
        break;
    case "want":
        return "wanted";
        break;
    case "wanttoplay":
        return "want to play";
        break;
    case "wanttobuy":
        return "want to buy";
        break;
    case "prevowned":
        return "previously owned";
        break;
    case "hasparts":
        return "has parts";
        break;
    case "wantparts":
        return "want parts";
        break;
		default:
		return $status;
    }
	}
}
    
// Get user input
// added sanitization but filter has been labeled deprecated as per PHP 8.1.0
if(isset($_GET["username1"])){
	$username1 = trim(filter_var($_GET['username1'], FILTER_SANITIZE_STRING)); 
	$username2 = trim(filter_var($_GET['username2'], FILTER_SANITIZE_STRING));
	$firstDD = $_GET['firstDD'];
	$secondDD = $_GET['secondDD'];
} else {	
	$username1 = trim(filter_var($_POST['username1'], FILTER_SANITIZE_STRING)); 
	$username2 = trim(filter_var($_POST['username2'], FILTER_SANITIZE_STRING)); 
	$firstDD = $_POST['firstDD'];
	$secondDD = $_POST['secondDD'];
}
      
// Get selectd Option
$notMode = "";
if (isset($_POST['notMode'])) {
	$notMode = $_POST['notMode'];
}
$excludeExpansions = false;
if (isset($_POST['excludeExpansions'])) {
	$excludeExpansions = true;
}
$showRating = false;
if (isset($_POST['showRating'])) {
	$showRating = true;
}

if(!(validBGGUsernameInput($username1) && validBGGUsernameInput($username2))) {
  echo "<h2>Something is wrong</h2><p>One or both of the BGG users is invalid.</p>";
} else {
  $listFirstPlayer = array();
  $listSecondPlayer = array();
  foreach($firstDD as $selectedOption){
    $listFirstPlayer = array_merge($listFirstPlayer, processChoices($username1, $selectedOption, $excludeExpansions, $BGGApiXMLToken));
  }
  foreach($secondDD as $selectedOption){
    $listSecondPlayer = array_merge($listSecondPlayer, processChoices($username2, $selectedOption, $excludeExpansions, $BGGApiXMLToken));
  }

  if(count($listFirstPlayer) == 0){
    echo "<h2>Something is wrong</h2><h2>Something is wrong</h2><p>List of ".$username1." is empty.</p>";
  } else {
    if(count($listSecondPlayer) == 0){
      echo "<h2>Something is wrong</h2><p>List of ".$username2." is empty.</p>";
    } else {
      if ($notMode == ""){
        //echo "<p>The games that are on both selected lists:</p>";
        $caption = "The games that are on both selected lists:";
      } else {
        //echo "<p>The games that are <b>not</b> on both selected lists:</p>";
        $caption = "The games that are <b>not</b> on both selected lists:";
      }

      $similarities = 0;
      $found;
      foreach($listFirstPlayer as $game){
        $found = false;
        foreach($listSecondPlayer as $game2){
          if($game->getID() == $game2->getID()){
            $found = true;
            if($notMode == ""){
              $similarities = printGameInTable($similarities, $firstDD, $secondDD, $username1, $username2, $game, $game2, $showRating);
            }
          }
        }
        if(!$found && $notMode == "not"){
          $game2 = new Game("", "", "", "");
          $game2->setWishlistPriority("");
          $game2->setStatus("");
          $game2->setComment("");
          $similarities = printGameInTable($similarities, $firstDD, $secondDD, $username1, $username2, $game, $game2, $showRating);
        }
      }

      if($notMode == "not"){
      foreach($listSecondPlayer as $game){
        $found = false;
        foreach($listFirstPlayer as $game2) {
          if($game->getID() == $game2->getID()){
            $found = true;
          }
        }
        if(!$found){
          $game2 = new Game($game->getID(), $game->getName(), "", $game->getRating());
          $game2->setWishlistPriority("");
          $game2->setStatus("");
          $game2->setComment("");
          $similarities = printGameInTable($similarities, $secondDD, $firstDD, $username2, $username1, $game2, $game, $showRating);
        }
      }
    }

    if($similarities == 0){
      echo "<h2>".$caption."</h2>";
      echo "<p><strong>None</strong></p>";
    } else {
      echo "</tbody></table>";
    }
  }
  }
}
   
function printGameIntable($similarities, $firstDD, $secondDD, $username1, $username2, $game, $game2, $showRating){
  $similarities++;
  if($similarities == 1){
    printTable($firstDD, $secondDD, $username1, $username2, $caption);
    echo "<tbody>";
  }
  if($showRating){
    echo "<tr><th scope='row'>".$game->getName()." (".$game->getRating().")</th>";
  } else {
    echo "<tr><th scope='row'>".$game->getName()."</th>";
  }
  echo "<td>".$game->getStatus()."</td>";
  if(isChoiceWishlist($firstDD)){
    echo "<td>".$game->getWishlistPriority()."</td>";
  }
  echo "<td>".$game->getComment()."</td>";
  echo "<td>".$game2->getStatus()."</td>";
  if(isChoiceWishlist($secondDD)){
    echo "<td>".$game2->getWishlistPriority()."</td>";
  }
  echo "<td>".$game2->getComment()."</td>";
  echo "</tr>";
  return $similarities;
}
	
function processXML($xml, $dropdownChoice){
  $list = array();
  $i = 0;
  foreach($xml->children() as $child){
    $rating = (float) $child -> stats -> rating -> average['value'];
    $ratingText = number_format($rating,1,".",",");
    $game = new Game((string) $child['objectid'], (string) $child -> name, $dropdownChoice, $ratingText);
    if(isChoiceWishlist($dropdownChoice)){
      $game->setWishlistPriority($child -> status['wishlistpriority']);
      $game->setComment($child -> wishlistcomment);
    } else {
      $game->setComment($child -> comment);
    }
    $list[$i] = $game;
    $i++;
  }
  return $list;
}

function isChoiceWishlist($dropdownChoice){
  if(is_string($dropdownChoice)){
    return $dropdownChoice == "wishlist";
  }
  foreach($dropdownChoice as $selectedOption){
    if($selectedOption == "wishlist"){
      return true;
    }
  }
  return false;
}

function printTable($firstDD, $secondDD, $username1, $username2){
  global $caption;
  $colspan1 = 2;
  $colspan2 = 2;
  if(isChoiceWishlist($firstDD)){
    $colspan1++;
  }

  if(isChoiceWishlist($secondDD)){
    $colspan2++;
  }
  echo "<table><caption>".$caption."</caption><colgroup class='colName'></colgroup><colgroup span='".$colspan1."' class='colUser'></colgroup><colgroup span='".$colspan2."' class='colUser'><thead><tr><th rowspan='2'>Name</th><th scope='col' colspan='".$colspan1."' class='userName'>".$username1."</th><th scope='col' colspan='".$colspan2."' class='userName'>".$username2."</th></tr><tr>";
  echo "<th scope='col'>Status</th>";
  if(isChoiceWishlist($firstDD)){
    echo "<th scope='col'>Wishlist priority</th>";
  }
  echo "<th scope='col'>Comment</th>";
  echo "<th scope='col'>Status</th>";
  if(isChoiceWishlist($secondDD)){
    echo "<th scope='col'>Wishlist priority</th>";
  }
  echo "<th scope='col'>Comment</th>";
  echo "</tr></thead>";
}

function processChoices($username, $ddChoice, $excludeExpansions, $apiToken){
  $url = "https://boardgamegeek.com/xmlapi/collection/".rawurlencode($username)."?".$ddChoice."=1";
  if($excludeExpansions){
    $url = $url."&excludesubtype=boardgameexpansion";
  }
  // read feed into SimpleXML object
  $sxml = getXMLfromBGG($url, true);
  if($sxml === false){
    echo "<p>Waiting 10 seconds for BGG to process request...</p>";
    sleep(10);
    $sxml = getXMLfromBGG($url, true);
    if($sxml === false){
      echo "<p>Waiting 20 seconds for BGG to process request...</p>";
      sleep(20);
      $sxml = getXMLfromBGG($url, true);
      if($sxml === false){
        echo "<p>BGG is still processing, pleasee try again in 60 seconds.</p>"; 
      } else {
        return processXML($sxml, $ddChoice);  
      }
    } else {
      return processXML($sxml, $ddChoice);
    }
  } else {
    return processXML($sxml, $ddChoice);
  }
  //echo "Processed Choices"; 
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
    <p><img src="powered_by_logo_01_SM.jpg" alt="Powered by BGG"/></p>
    <p>version 2.1.1 by <a href="https://boardgamegeek.com/profile/banaan666">banaan666</a> and <a href="https://boardgamegeek.com/profile/DraedGhawl">DraedGhawl</a></p>
  </footer>
</body>
</html> 