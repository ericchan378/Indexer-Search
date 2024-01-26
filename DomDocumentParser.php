<?php

    class DomDocumentParser{
        private $doc;
        public function __construct($url, $html){

            $this->doc = new DomDocument();

            @$this->doc->loadHTML($html);
            
        }
        
        public function getLinks(){
            return $this->doc->getElementsByTagName("a");
            
        }
        
        public function getTitleTags(){
            return $this->doc->getElementsByTagName("title");
            
        }
        
        public function getMetaTags(){
            return $this->doc->getElementsByTagName("meta");
            
        }
        
    }
?>
