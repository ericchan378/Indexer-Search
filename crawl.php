<?php

    $time_start = microtime(true);

    include("classes/DomDocumentParser.php");
    include("configuration.php");
    include("indexer.php");

    $startUrl = "https://www.pizzahut.com/"; 
    followLinks($startUrl);
    
    //-----------------------------------------------------------------------------------
    
    function linkExists($url){ // check for duplicates
        
        global $connection; // connection to the server
        
        $query = $connection->prepare("SELECT * FROM page WHERE url = :url"); // take the link
                                
        $query->bindParam(":url", $url);
        $query->execute();
        
        return $query->rowCount() != 0; // if already exists -> return true
        
    }
    
    //fills page table
    function insertLink($url, $title, $description){ 
        
        global $connection;

        $newPage = $connection->prepare("INSERT INTO page(url, title, descr)
                                VALUES(:url, :title, :description)");

        $newPage->bindParam(":url", $url);
        $newPage->bindParam(":title", $title);
        $newPage->bindParam(":description", $description);

        $newPage->execute();
        
    }

    //cleans up href
    function createLink($src, $url){
        
        $scheme = parse_url($url)["scheme"]; // http
        $host = parse_url($url)["host"]; //www.smth.com
        
        if(substr($src, 0, 2) == "//"){
             $src = $scheme . ":" . $src;
        }
        else if(substr($src, 0, 1) == "/"){
             $src = $scheme . "://" . $host. $src;
        }
        
        else if(substr($src, 0, 2) == "./"){
            $src = $scheme . "://" . $host . dirname(parse_url($url)["path"]). substr($src, 1);
        }
        else if(substr($src, 0, 3) == "../"){
            $src = $scheme . "://" . $host . "/" . $src;
        }
        else if(substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http"){
            $src = $scheme . "://" . $host . "/" . $src;
        }
        return $src;
        
    }

    function getDetails($url, $html, $parser){

        //$parser = new DomDocumentParser($url, $html);
        
        $titleArray = $parser->getTitleTags();
        
        if(sizeof($titleArray) == 0 || $titleArray->item(0) == NULL){
            $title = "No Title Available";
        }
        else{
        $title = $titleArray->item(0)->nodeValue;
        
        $title = str_replace("\n", "", $title);
        }
        
        $description = "No Description Available";
        
        $metasArray = $parser->getMetaTags();
        
        foreach($metasArray as $meta){
            
            if($meta->getAttribute("name") == "description"){
                $description = $meta->getAttribute("content");
            }
        }
        
        $description = str_replace("\n", "", $description);

        //insert to page table
        insertLink($url, $title, $description);

        //insert into page_word and word tables
        index($url, $html);
        
    }

    function followLinks($url, $depth = 3) {

        if(linkExists($url)){
            return;
        }

        if ($depth === 0){
            return;
        }

        //skip unreachable pages.
        //array of headers
        $headers = get_headers($url);
        $httpCode = $headers[0];
        if(!str_contains($httpCode, "200")){
            return;
        }

        //skip pages with no accessible source (tiktok)
        $html = file_get_contents($url);
        if(strlen($html) == 0){
            return;
        }

        // echo $depth . "<br>"; //echoechoechoechoechoechoechoechoechoechoechoechoechoechoechoechoechoechoecho
        //echo $url . "<br>"; //echoechoechoechoechoechoechoechoechoechoechoechoechoechoechoechoechoechoecho

        $parser = new DomDocumentParser($url, $html); 

        // insert url into page table and index the page.
        getDetails($url, $html, $parser); // fills page, page_word, and word

        //array of hrefs
        $linkList = $parser->getLinks();

        foreach($linkList as $link){
            
            $href = $link->getAttribute("href");

            //skip if "#" is found in href(if href not a bookmark)
            if(strpos($href, "#") !== false){
                continue;
            }
            // skip if href is javascript
            else if(substr($href, 0, 11) == "javascript:"){
                continue;
            }
            //skip if href is to send an email
            else if(substr($href, 0, 7) == "mailto:"){
                continue;
            }
            
            $href = createLink($href, $url); //add missing parts to href

            followLinks($href, $depth - 1);    
        }

    }

    $time_end = microtime(true);
    $time = $time_end - $time_start;

    echo "$time seconds\n";

?>
