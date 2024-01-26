<?php // outputs the results 
	class SiteResultsProvider{
		
		private $connection;
		
		public function __construct($connection){
			
			$this->connection = $connection;
			
		}


		//get number of results from searching term
		public function getNumResults($term){

			//get wordId from the word table					
			$wordIdQuery = $this->connection->prepare("SELECT wordId FROM word WHERE wordName = :word");
			$wordIdQuery->bindParam(":word", $term);	
			$wordIdQuery->execute();
			$wordId = $wordIdQuery->fetchColumn();

			//get number of rows that contain a page containing the search term
			//counter becomes key in PDO
			$pagesWithTermQuery = $this->connection->prepare("SELECT COUNT(*) as counter 
															FROM page_word WHERE wordId = :wId");
			$pagesWithTermQuery->bindParam(":wId", $wordId);
			$pagesWithTermQuery->execute();

			//row becomes associative array
			$row = $pagesWithTermQuery->fetch(PDO::FETCH_ASSOC);

			//returns value of counter which is number of results
			return $row["counter"];
													
			/*$searchTerm = "%" . $term . "%";
			$query->bindParam(":term", $searchTerm);
			$query->execute();
			
			$row = $query->fetch(PDO::FETCH_ASSOC); // get the result to display it
			return $row["counter"]; // # of rows that contain a string like term*/
		}
		
		
		
		
		
		public function getResultsHtml($page, $pageSize, $term){
			// by most popular results
			
			//fromLimit is row it starts from. pageSize is number of results
			$fromLimit = ($page - 1) * $pageSize;

			//get wordId from the word table					
			$wordIdQuery = $this->connection->prepare("SELECT wordId FROM word WHERE wordName = :word");
			$wordIdQuery->bindParam(":word", $term);	
			$wordIdQuery->execute();
			$wordId = $wordIdQuery->fetchColumn();

			//get list of pageId from page_word table order by freq
			$pageWordQuery = $this->connection->prepare("SELECT pageId FROM page_word WHERE wordId = :wId ORDER BY freq DESC LIMIT :fromLimit, :pageSize");
			$pageWordQuery->bindParam(":wId", $wordId);
			$pageWordQuery->bindParam(":fromLimit", $fromLimit, PDO::PARAM_INT);
			$pageWordQuery->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
			$pageWordQuery->execute();

			//place results in the container
			$resultsHtml = "<div class = 'searchResults'>";

			while($row = $pageWordQuery->fetch(PDO::FETCH_ASSOC)){
				$pageId = $row["pageId"];  

				$pageQuery = $this->connection->prepare("SELECT * FROM page WHERE pageId = :pId");
				$pageQuery->bindParam(":pId", $pageId);
				$pageQuery->execute();

				$result = $pageQuery->fetch(PDO::FETCH_ASSOC);

				$url = $result["url"];
				$title = $result["title"];
				$description = $result["descr"];

				$title = $this->trimField($title, 70);
				$description = $this->trimField($description, 200);
				$resultsHtml .= "<div class='resultContainer'>
									<h3 class = 'title'>
										<a class='result' href='$url' data-linkId = '$pageId'>$title</a>
									</h3>
									<span class='description'>$description</span>
								</div>";
			}

			$resultsHtml .= "</div>";

			return $resultsHtml;
			
		}
		
		
		private function trimField($string, $characterLimit){
			
			//if string length greater than character limit then concatenate dots, else concatenate nothing.
			$dots = strlen($string) > $characterLimit ? "..." : "";
			return substr($string, 0, $characterLimit) . $dots;
		}
	}
?>
