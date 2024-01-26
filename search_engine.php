<?php
	include("configuration.php");
	include("classes/SiteResultsProvider.php"); //
    if(isset($_GET["term"])){
        $term = $_GET["term"];
    }
    else{
        exit("No search term provided");
    }
    $page = isset($_GET["page"]) ? $_GET["page"] : 1; //page number default to 1 if not specified
?>

<!DOCTYPE html>
<html>
    <head>
	    
        <title>Search Engine</title>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js"
		integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
		crossorigin="anonymous">
	</script>
	    
    </head>
    
    <body>
        
        
        
        
        <div class = "searchContainer">
        
            <form action="search_engine.php" method = "GET">
                <div class = "searchBarContainer">
                    <input class="searchBox" id="search" type="text" name="term" value="<?php echo $term; ?>">
                    <button class="searchButton"> Search </button>
                </div>
            </form>
        
        </div>
        
        
        
        <div class="mainResultsSection">
		
			<?php
			$resultsProvider = new SiteResultsProvider($connection);
			
			$pageSize = 20;
			$numResults = $resultsProvider->getNumResults($term);
			
			echo "<p class = 'resultsCount'>$numResults results found </p>";
			 // ($page, $pageSize, $term);
			echo $resultsProvider->getResultsHtml($page, $pageSize, $term); // display results
			?> 
			
			
			<div class = "pages">
				<div class = "pageButtons">
				
					<div class = "pageNumber">
					
					</div>
					<?php
					
					
						$pagesToDisplay = 10;
						$numPages = ceil($numResults / $pageSize); // based on the number of results
						$pagesLeft = min($pagesToDisplay, $numPages);
						$currentPage = $page - floor($pagesToDisplay / 2);  
						
						if($currentPage < 1) {
							$currentPage = 1;
						}
						
						if($currentPage + $pagesLeft > $numPages + 1){ // until pages with results exist
							$currentPage = $numPages + 1 - $pagesLeft;
						}
						
						while($pagesLeft != 0 && $currentPage <= $numPages){
							
							if($currentPage == $page){
								
								echo "<div class = 'pageNumber'>
										<span class = 'number'>$currentPage<span>
									</div>";
									
							}
							else {
								
								echo "<div class = 'pageNumber'>
										<a href = 'search_engine.php?term=$term&page=$currentPage'>
										<span class = 'pageNumber'>$currentPage<span>
										</a>
									 </div>";
									 
							}
							$currentPage++;
							$pagesLeft--;
						}
					?>
				</div>
			</div>
		</div>
	    
	    <script type = "text/javascript" src = "script.js"></script>
        
        
    </body>
</html>
