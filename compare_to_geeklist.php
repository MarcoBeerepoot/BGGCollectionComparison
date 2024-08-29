<html>
<body>
<?php 

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
$username = $_POST["username"]; 
$geeklist = $_POST["geeklist"]; 
$dropdownValue = $_POST["dropdownValue"];

$url = "http://www.boardgamegeek.com/xmlapi/collection/".$username."?".$dropdownValue."=1";
$url2 = "http://www.boardgamegeek.com/xmlapi/geeklist/".$geeklist;
// read feed into SimpleXML object
$sxml = simplexml_load_file($url);
$sxml2 = simplexml_load_file($url2);

$listFirstPlayer = array();
$listGeeklist = array();
$idListFirstPlayer = array();
$i = 0;
foreach($sxml->children() as $child)
  {
   $id = (string) $child['objectid'];
   $idListFirstPlayer[$i] = $id;
   $listFirstPlayer[$id] = (string) $child -> name;
   $i++;
  }
if(count($listFirstPlayer) == 0){
	echo "<p>List is empty or your request is in the queue. Please go back and try again.</p>";
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
	echo "<p>List is empty or your request is in the queue. Please go back and try again.</p>";
  } else {
echo "<p>The games that are on both selected lists:</p>";

$i = 0;
foreach($listGeeklist as $game){
	if(in_array($game->getObjectID(), $idListFirstPlayer)){
		$i++;
		echo "<a href='https://boardgamegeek.com/geeklist/".$geeklist."/item/".$game->getID()."#item".$game->getID()."'>".$listFirstPlayer[$game->getObjectID()]."</a><br>";
	
	}
}
if($i == 0){
	echo "None";
}
	}
	}
?>

</body>
</html> 