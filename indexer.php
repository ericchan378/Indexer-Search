<?php
    include("configuration.php");

	function index($url, $html){

		global $connection;

		//-----------------------------------------------------------------------------------
		$html = transliterateString($html); 

		$dom = new DOMDocument();
		@$dom->loadHTML($html);

		//find all script tags and remove them.
		$script = $dom->getElementsByTagName('script');
		$remove = [];
		foreach($script as $item)
		{
 			$remove[] = $item;
		}
		foreach ($remove as $item){
  			$item->parentNode->removeChild($item); 
		}

		//find all style tags and remove them.
		$style = $dom->getElementsByTagName('style');
		$removeStyle = [];
		foreach($style as $item)
		{
 			$removeStyle[] = $item;
		}
		foreach ($removeStyle as $item){
  			$item->parentNode->removeChild($item); 
		}

		$html = $dom->saveHTML();

		$text = preg_replace ('/<[^>]*>/', ' ', $html); //remove html tags
		$text = preg_replace("/'s /", ' ', $text); //removes apostrophe s ('s)
		$text = preg_replace('/[\.\?,!]+/', ' ', $text); //remove punctuation
		$text = preg_replace("/[^A-Za-z' ]/", ' ', $text); //removes non-alphabet and single quotes
		$text = strtolower($text); //to lowercase

		$data = preg_split('/[\s]+/', $text);
		//-----------------------------------------------------------------------------------

		$uniqueWords = [];

		foreach($data as $word){

			if (array_key_exists($word, $uniqueWords)) {

    			$uniqueWords[$word] = $uniqueWords[$word] + 1; 
		
    		}
    		else {

    			$uniqueWords[$word] = 1;

    		}
		}

		$getPageId = $connection->prepare("SELECT pageId FROM page WHERE url = :url");
		$getPageId->bindParam(":url", $url);
		$getPageId->execute();
		$pageId = $getPageId->fetchColumn();

		foreach($uniqueWords as $word => $freq) {

			$wordIdQuery = $connection->prepare("SELECT wordId FROM word WHERE wordName = :word");
			$wordIdQuery->bindParam(":word", $word);
			$wordIdQuery->execute();

			//check if word is in word table and add if not
			if($wordIdQuery->rowCount() == 0) {
				$newWordQuery = $connection->prepare("INSERT INTO word(wordName) VALUES(:word)");
				$newWordQuery->bindParam(":word", $word);
				$newWordQuery->execute();

				$wordIdQuery->execute();
			}

			$wordId = $wordIdQuery->fetchColumn();

			$newPageWord = $connection->prepare("INSERT INTO page_word(pageId, wordId, freq) VALUES(:pageId, :wordId, :freq)");
			$newPageWord->bindParam(":pageId", $pageId, PDO::PARAM_INT); 
			$newPageWord->bindParam(":wordId", $wordId, PDO::PARAM_INT);
			$newPageWord->bindParam(":freq", $freq, PDO::PARAM_INT);
			$newPageWord->execute();

			/*
			$getPageId = $connection->prepare("SELECT pageId FROM page WHERE url = :url");
			$getPageId->bindParam(":url", $url);
			$getPageId->execute();
			$pageId = $getPageId->fetchColumn();

			//check if entry exist in page_word table and add if not
			$pwQuery = $connection->prepare("SELECT pageWordId FROM page_word WHERE (pageId = :pageId AND wordId = :wordId)");
			$pwQuery->bindParam(":pageId", $pageId, PDO::PARAM_INT);
			$pwQuery->bindParam(":wordId", $wordId, PDO::PARAM_INT);
			$pwQuery->execute();

			//if no page_word entry, then make one and start counter(freq) at 0
			if($pwQuery->rowCount() == 0){
				$ii = 0;
				$newPageWord = $connection->prepare("INSERT INTO page_word(pageId, wordId, freq) VALUES(:pageId, :wordId, :ii)");
				$newPageWord->bindParam(":pageId", $pageId, PDO::PARAM_INT); 
				$newPageWord->bindParam(":wordId", $wordId, PDO::PARAM_INT);
				$newPageWord->bindParam(":ii", $ii, PDO::PARAM_INT);
				$newPageWord->execute();
			}

			$incrPageWord = $connection->prepare("UPDATE page_word SET freq = freq+1 WHERE (pageId = :pageId AND wordId = :wordId)");
			$incrPageWord->bindParam(":pageId", $pageId, PDO::PARAM_INT);
			$incrPageWord->bindParam(":wordId", $wordId, PDO::PARAM_INT);
			$incrPageWord->execute();
			*/

		}

	}

	function transliterateString($txt) { //https://stackoverflow.com/questions/6837148/change-foreign-characters-to-their-roman-equivalent
    		$transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');

    		return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);

	}
?>
