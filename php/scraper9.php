<?php

    // Grab the url param value from URL 
    $path = $_GET['url'];
    $parsedPath = parse_url($path);
    $html = file_get_contents($path);
    $dom = new DOMDocument();
    $list = array();
    $dom->loadHTML($html);

    // grab all the paths on the page
    $xpath = new DOMXPath($dom);
    $hrefs = $xpath->evaluate("/html/body//a");

   /* XXX
    * For Loop => Remove all extrernal links
    *   If statement => if the parsedUrl has a host property & that host property 
    *       isn't equal to our root Url, don't add to array.
    * XXX
    */
    for ($i = 0; $i < $hrefs->length; $i++) {
    	$href = $hrefs->item($i);
    	$url = $href->getAttribute('href');
        $parsedUrl = parse_url($url);
        
        print_r($parsedUrl);

        if(isset($parsedUrl["host"]) && ( $parsedUrl["host"] != $parsedPath['host']) ){
            //this is a link to an external site - we dont want it, do nothing 
        }
        else {
            array_push($list, $href);
        }
    }

    $matchUrl = array();

    //Remove phone numbers from array
    foreach($list as $item) {
       if(preg_match("(\/([a-z0-9+\$_-]\.?)+)", $item->getAttribute('href') ) ){
            array_push($matchUrl, $item);
       }
    }
     
    $essentialURL = array();
    $duplicates = array();

    // Remove duplicates from array
    foreach ($matchUrl as $item) {
        $href = ($item->getAttribute('href'));

        if ( !in_array($href, $duplicates)) {
            array_push($duplicates, $href);
            array_push($essentialURL, $item);           
        } 

        echo $href;
        echo "<br />";
    }
    echo "Length: " . count($matchUrl);


    // JUST FOR CLARITY. CAN ERASE.
    echo "<h2>Essential URLs</h2>";
    foreach ($essentialURL as $item){
        print_r($item->getAttribute('href'));
        echo "<br / >";
    }
    echo "Length: " . count($essentialURL);

    




    // open the file "demosaved.csv" for writing
    $file = fopen('demosaved.csv', 'w');

    // save the column headers
    fputcsv($file, array('label', 'url', 'isLead'));

    foreach($essentialURL as $result) {
        $data = array();
        $path = parse_url($result->getAttribute('href'), PHP_URL_PATH);
        $params = parse_url($result->getAttribute('href'), PHP_URL_QUERY);

        // If the URL also contains params, append them to variable
        if (!empty($params)){
            $path = $path . "?" . $params;
        }

        // Remove special characters. If $label is blank, give it a value.
        $label = trim( preg_replace('/[^A-Za-z0-9\- ]/', '', $result->nodeValue) );
        if ($label == ''){$label = 'Blank';}

        array_push($data, $label);
        array_push($data, $path);
        array_push($data, 'no');

        // save each row of the data
        fputcsv($file, $data);
    } 

    // Close the file
    fclose($file);
?>




<!-- 
***TO DO***

- Build a frontend for program
- Refactor var names
- Wrap all code in functions
- Allow program to scrape 2 path layers into a website 
    (www.mywebsite.com  &  www.mywebsite.com/main-links)

 -->