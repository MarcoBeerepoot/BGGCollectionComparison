<html>
<head>
<style>
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

tr:nth-child(even) {
    background-color: #e6e6e6;
}

tr:hover {
          background-color: #ffff99;
        }
</style>
</head>
<body>
<?php 

class Game {
	private $id;
	private $name;
	private $wishlistPriority = "-";
	private $comment;
	private $status;
	
	public function __construct($id, $name, $status) {
		$this->id = $id;
		$this->name = $name;
		$this->status = $status;
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


if(isset($_GET["username1"])){
	$username1 = $_GET["username1"];
	$username2 = $_GET["username2"]; 
	$firstDD = $_GET["firstDD"];
	$secondDD = $_GET["secondDD"];	
} else {	
	$username1 = $_POST["username1"]; 
	$username2 = $_POST["username2"]; 
	$firstDD = $_POST["firstDD"];
	$secondDD = $_POST["secondDD"];	
}
$notMode = "";
if (isset($_POST['notMode'])) {
	$notMode = $_POST['notMode'];
}
$excludeExpansions = false;
if (isset($_POST['excludeExpansions'])) {
	$excludeExpansions = true;
}

//Increase time limit for this script to 240 seconds. Otherwise it will fail with an error after 30 seconds.
set_time_limit(240);

$listFirstPlayer = array();
$listSecondPlayer = array();
foreach($firstDD as $selectedOption){
	$listFirstPlayer = array_merge($listFirstPlayer, processChoices($username1, $selectedOption, $excludeExpansions));
}
foreach($secondDD as $selectedOption){
	$listSecondPlayer = array_merge($listSecondPlayer, processChoices($username2, $selectedOption, $excludeExpansions));
}

if(count($listFirstPlayer) == 0){
	echo "<p>List of ".$username1." is empty.</p>";
} else {

  if(count($listSecondPlayer) == 0){
	echo "<p>List of ".$username2." is empty.</p>";
  } else {
	  if($notMode == ""){
echo "<p>The games that are on both selected lists:</p>";
	  } else {
echo "<p>The games that are <b>not</b> on both selected lists:</p>";
	  }

$similarities = 0;
$found;
foreach($listFirstPlayer as $game){
	$found = false;
	foreach($listSecondPlayer as $game2){
		if($game->getID() == $game2->getID()){
			$found = true;
			if($notMode == ""){
				$similarities = printGameInTable($similarities, $firstDD, $secondDD, $username1, $username2, $game, $game2);
			}
		}
	}
	if(!$found && $notMode == "not"){
		$game2 = new Game("", "", "");
		$game2->setWishlistPriority("");
		$game2->setStatus("");
		$game2->setComment("");
		$similarities = printGameInTable($similarities, $firstDD, $secondDD, $username1, $username2, $game, $game2);
	}
}

  if($notMode == "not"){
	foreach($listSecondPlayer as $game){
	$found = false;
	foreach($listFirstPlayer as $game2){
		if($game->getID() == $game2->getID()){
			$found = true;
		}
	}
	if(!$found){
		$game2 = new Game($game->getID(), $game->getName(), "");
		$game2->setWishlistPriority("");
		$game2->setStatus("");
		$game2->setComment("");
		$similarities = printGameInTable($similarities,  $secondDD,$firstDD,  $username2, $username1, $game2, $game);
	}
}
  }

if($similarities == 0){
	echo "<b>None</b>";
} else {
	echo "</table>";
}
	}
	}
	
	function printGameIntable($similarities, $firstDD, $secondDD, $username1, $username2, $game, $game2){
		$similarities++;
			if($similarities == 1){
				printTable($firstDD, $secondDD, $username1, $username2);
			}
			echo "<tr><td>".$game->getName()."</td>";
			if(isChoiceWishlist($firstDD)){
				echo "<td>".$game->getWishlistPriority()."</td>";
			}
				echo "<td>".$game->getStatus()."</td>";
				echo "<td>".$game->getComment()."</td>";
			if(isChoiceWishlist($secondDD)){
				echo "<td>".$game2->getWishlistPriority()."</td>";
			}
				echo "<td>".$game2->getStatus()."</td>";
				echo "<td>".$game2->getComment()."</td>";
			echo "</tr>";
			return $similarities;
	}

	function getXML($url, $retryCount){
		$sxml = simplexml_load_file($url);
		if($retryCount == 30){
			echo "I've waited very long for BGG. BGG might be really busy, it's a very large collection or something else went wrong.";
			return $sxml;
		}
		//Not the correct way to check this. Should actually look for a 202...
		if($sxml->getName() == "message"){
			sleep(2);
			$retryCount++;
			return getXML($url, $retryCount);
		}
		return $sxml;
	}
	
	function processXML($xml, $dropdownChoice){
		$list = array();
		$i = 0;
		foreach($xml->children() as $child){
			$game = new Game((string) $child['objectid'], (string) $child -> name, $dropdownChoice);
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
		$colspan1 = 2;
		$colspan2 = 2;
		if(isChoiceWishlist($firstDD)){
			$colspan1++;
		}
		
		if(isChoiceWishlist($secondDD)){
			$colspan2++;
		}
		echo "<table><col><colgroup span='".$colspan1."'></colgroup><colgroup span='".$colspan2."'></colgroup><tr><td></td><th colspan='".$colspan1."' scope='colgroup'>".$username1."</th><th colspan='".$colspan2."' scope='colgroup'>".$username2."</th></tr><tr><th>Name</th>";
		if(isChoiceWishlist($firstDD)){
		echo "<th>Wishlist priority</th>";
		}
		echo "<th>Status</th>";
		echo "<th>Comment</th>";
		if(isChoiceWishlist($secondDD)){
		echo "<th>Wishlist priority</th>";
		}
		echo "<th>Status</th>";
		echo "<th>Comment</th>";
		echo "</tr>";
	}
	
	function processChoices($username, $ddChoice, $excludeExpansions){
		$url = "http://www.boardgamegeek.com/xmlapi/collection/".$username."?".$ddChoice."=1";
		if($excludeExpansions){
			$url = $url."&excludesubtype=boardgameexpansion";
		}
		// read feed into SimpleXML object
		$sxml = getXML($url, 0);

		return processXML($sxml, $ddChoice);
	}
	
	?>

</body>
</html> 