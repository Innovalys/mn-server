<?php 
	require_once 'Unirest.php';
class SearchManga {
		public $query;
		public $data;
		public $list_source_id;
		function __construct($str) {
			$this->query = $str;
			$this->$list_source_id = array("mangafox.me","mangareader.net","mangastream.com");
		}
		function SetData($_dataFromAPI){
			$this->$data = $_dataFromAPI;
		}
		function GetResults(){
			return $data;
		}

}
if($_GET){
	echo("enter");
	if($_GET["query"] != null){
		$response = Unirest\Request::get("https://doodle-manga-scraper.p.mashape.com/mangareader.net/search?l=30&q=".$_GET["query"],
		  array(
		    "X-Mashape-Key" => "HhfIVRaNifmshprSUa6pmLwUo0fvp14H42rjsn8WSeSTUrRW9W",
		    "Accept" => "text/plain"
		  )
		);
		print_r($response);
	}
}else{
?>
<form method="GET" action="search.php">
	<input name="query" type="text" placeholder="Search manga ..." />
	<input type="submit" value="Chercher" />
</form>

<?php
}
?>