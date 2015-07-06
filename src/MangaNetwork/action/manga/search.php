<?php 
	include_once 'MangaNetwork/utils.php';

	function SearchManga($_context){
		$query = $_context->params["query"];
		$source = $_context->params["source"];
		return GetResult($source,$query);
	}
	function GetResult($_source,$_query){
		$curl = curl_init();
		$rep = array();
		if($_source == NULL || $_source == " "){
			throw new MnException("Error : Source ID cannot be null !", 400);
		}else if($_source == "all"){
			// Traitement pour toutes les APIs
				// Traitement MangaEden
			curl_setopt_array($curl, [
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_HTTPHEADER => [
			    	'Accept: text/plain'
			    ],
			    CURLOPT_URL => 'http://www.mangaeden.com/api/list/0/'
			]);
			$rawResponse = json_decode(curl_exec($curl),true);

			foreach($rawResponse["manga"] as $manga){
				if(strripos($manga["t"],$_query) != false){
					array_push($rep, $manga);
				}
			}
			$mangascraper = array("mangafox.me","mangareader.net","mangastream.com");
			foreach($mangascraper as $source){
				curl_setopt_array($curl, [
				    CURLOPT_RETURNTRANSFER => 1,
				    CURLOPT_SSL_VERIFYPEER => false,
				    CURLOPT_HTTPHEADER => [
				    	'X-Mashape-Key: ' . GetMashapeKey(),
				    	'Accept: text/plain'
				    ],
				    CURLOPT_URL => 'https://doodle-manga-scraper.p.mashape.com/'.$source.'/search?q='.$_query
				]);
				$rawResponses = json_decode(curl_exec($curl),true);

				foreach($rawResponses as $rawResponse){
					$rawResponse["source"] = $source;
					array_push($rep,$rawResponse);
				}
			}

		}else if($_source == "mangaeden.com"){
			// Traitement MANGAEDEN
			curl_setopt_array($curl, [
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_HTTPHEADER => [
			    	'Accept: text/plain'
			    ],
			    CURLOPT_URL => 'http://www.mangaeden.com/api/list/0/'
			]);

			$rawResponse = json_decode(curl_exec($curl),true);
			$rep = array(count($rawResponse["manga"]));
			if(isset($rawResponse["manga"]) AND !empty($rawResponse["manga"])) {
				foreach($rawResponse["manga"] as $manga){
					if(strripos($manga["t"],$_query) !== false){
						array_push($rep, $manga);
					}
				}
			}
		}else if($_source == "mangafox.me" || $_source == "mangareader.net" || $_source == "mangastream.com"){
			// Traitement MANGASCRAPER
			curl_setopt_array($curl, [
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_HTTPHEADER => [
			    	'X-Mashape-Key: ' . GetMashapeKey(),
			    	'Accept: text/plain'
			    ],
			    CURLOPT_URL => 'https://doodle-manga-scraper.p.mashape.com/'.$_source.'/search?q='.$_query
			]);
			$rawResponse = json_decode(curl_exec($curl),true);
			array_push($rep,$rawResponse);
		}else{
			throw new MnException("Error : Source ID '".$_source."' doesn't exist !", 400);
		}
		// Test for error
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
			if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 404)
				throw new MnException("Error : search could be retrieved with Query '" . $_query . " on".$_source, 404);
			throw new MnException("Error : error while retrieving mangas for '" . $_query . " on ".$_source, 400);
		}
		curl_close($curl);
		return unificateData($_source,$rep);
		//return $rep;
	}
	function unificateData($_src,$_tabData){
		$allData = array();
		
		$ligne = array(
    		"source_API"	=> "",
    		"source_ID"		=> "",
    		"source_URL"	=> "",
    		"title" 	    => "",
    		"genres" 		=> array(),
    		"cover" 		=> "",
		);
		if($_src == "mangaeden.com"){
			foreach($_tabData as $manga){
				if($manga["i"]){
					$ligne["source_API"] = "mangaeden";
					$ligne["source_URL"] = $_src;
					$ligne["source_ID"]  = $manga["i"];
					$ligne["title"]      = $manga["t"];
					$ligne["genres"] 	 = $manga["c"];
					$ligne["cover"]      = $manga["im"];
					array_push($allData,$ligne);
				}
			}
		}else if($_src == "mangafox.me" || $_src == "mangastream.com" || $_src == "mangareader.net"){
				foreach($_tabData as $manga){
					foreach($manga as $currmanga){
						$ligne["source_API"] = $_src;
						$ligne["source_URL"] = $_src;
						$ligne["source_ID"]  = $currmanga["mangaId"];
						$ligne["title"]  = $currmanga["name"];
						if(array_key_exists("genres",$currmanga)){
							$ligne["genres"] 	= $currmanga["genres"];
						}
						array_push($allData,$ligne);
					}
				}
		}else if($_src == "all"){
			foreach($_tabData as $manga){
				
				if(array_key_exists("genres",$manga)){
					// MangaReader or MangaFox
					$ligne["source_URL"] = $manga["source"];
					$ligne["source_ID"]  = $manga["mangaId"];
					$ligne["title"]      = $manga["name"];
					$ligne["genres"] 	 = $manga["genres"];
					$ligne["cover"] 	 = "";
					array_push($allData,$ligne);
				}else if(array_key_exists("name",$manga)){
					// MangaStream
					$ligne["source_URL"] = $manga["source"];
					$ligne["source_ID"]  = $manga["mangaId"];
					$ligne["title"]      = $manga["name"];
					$ligne["genres"] 	 = array();
					$ligne["cover"] 	 = "";
					array_push($allData,$ligne);
				}else if(array_key_exists("i",$manga)){
					// MangaEden
					$ligne["source_URL"] = "mangaeden.com";
					$ligne["source_ID"]  = $manga["i"];
					$ligne["title"]      = $manga["t"];
					$ligne["genres"]  	 = $manga["c"];
					$ligne["cover"] 	 = $manga["im"];
					array_push($allData,$ligne);
				}
			}
			
		}else{

		}
		return $allData;
	}
?>