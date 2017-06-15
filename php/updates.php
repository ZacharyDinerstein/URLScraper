<?php 
  
 function grabLinks($urll) { 
     $allUrls = array();
     $url = file_get_contents($urll); 
     preg_match_all("/href=\"(.*?)\"/i",$url,$result);  
        for ($i=0; $i<count($result[1]); $i++) 
        { 
            array_push($allUrls, $result[1][$i]);
        }
     
     return $allUrls;
} 
    $url = "https://fl.pluginkaraoke.com";
    $urlHost = parse_url($url, PHP_URL_HOST);
    echo $urlHost;
    $hrefs = grabLinks($url);

    echo "<h2>Original URLS</h2><br />";
    echo '<pre>' . var_export($hrefs, true) . '</pre>';
    

/* XXX
    * For Loop => Remove all extrernal links
    *   If statement => if the parsedUrl has a host property & that host property 
    *       isn't equal to our root Url, don't add to array.
    * XXX
    */
    $list = array();
    $lengthOfArray = count($hrefs);
    for ($i = 0; $i < $lengthOfArray; $i++) {
    	$href = $hrefs[$i];
        $parsedUrl = parse_url($href);
        
    
//        echo '<pre>' . var_export($parsedUrl, true) . '</pre>';
        
        if(isset($parsedUrl["host"]) && ($parsedUrl["host"] !=  $urlHost)) {
            //this is a link to an external site - we dont want it, do nothing 
        }
        else {
            array_push($list, $href);
        }
    }
     
    
//    echo '<pre>' . var_export($list, true) . '</pre>';


   

    //Remove phone numbers from array
    $matchUrl = array();
    foreach($list as $item) {
       if(preg_match("(\/([a-z0-9+\$_-]\.?)+)", $item ) ){
            array_push($matchUrl, $item);
       }
    }

echo "<h2>FINAL URLS</h2><br />";
echo '<pre>' . var_export($matchUrl, true) . '</pre>';









   
 // grabLinks("https://fl.pluginkaraoke.com"); 
//grabLinks("http://aimediagroup.com");
//grabLinks("http://www.plazacollege.edu");
//grabLinks("https://www.tmh.org");


?> 