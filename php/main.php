<?php
   /* HELPER FUNCTIONS */      


   /* XXX
    *   printResults => print array contents to screen for debugging.
    * XXX
    */   
    function printResults($titleMessage, $bigArray, $attribute){
        echo "<h2>" . $titleMessage . "</h2>";
        foreach ($bigArray as $item){
            print_r($item->getAttribute($attribute));
            echo "<br / >";
        }
        echo "Length: " . count($bigArray);
    }


   /* XXX
    *   printResultsSimple => print string value to screen for debugging.
    * XXX
    */    
    function printResultsSimple($value){
        echo $value . "<br />";
    }

    function printVarExport($titleMesage, $var){
        echo "<h2>" . $titleMesage . "</h2>";
        echo '<pre>' . var_export($var, true) . '</pre>';
    }



   /* MAIN FUNCTIONS */      


    function grabURLPath(){
        return $path = $_GET['url'];

    }

    function grabParsedPath($path){
        return $parsedPath = parse_url($path);
    }
        
    function grabHrefs($path){
        $html = file_get_contents($path);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        // grab all the paths on the page
        $xpath = new DOMXPath($dom);
        return $hrefs = $xpath->evaluate("/html/body//a");
    }


   /* XXX
    * RemoveExternalLinks => Keep only array items that are internal links
    *   For Loop => Remove all extrernal links
    *       If statement => if the parsedUrl has a host property & that host property 
    *           isn't equal to our root Url, don't add to array.
    * XXX
    */
    function removeExternalLinks($oldURLs, $parsedPath){
        $list = array();

        // printResultsSimple("<h2>Full Array before removeExternalLinks</h2>");

        for ($i = 0; $i < $oldURLs->length; $i++) {
            $href = $oldURLs->item($i);
            $url = $href->getAttribute('href');
            $parsedUrl = parse_url($url);

            // printResultsSimple($url);
            
            if(isset($parsedUrl["host"]) && ( $parsedUrl["host"] != $parsedPath['host']) ){
                //this is a link to an external site - we dont want it, do nothing 
            }
            else {
                array_push($list, $href);
            }
        }

        // echo "Length: " . $i . "<br />";
        return $list;
    }


   /* 
    * XXX  removePhoneNums => Remove phone numbers from array.
    *   if => If href is a legitimate path, add to array.
    */   
    function removePhoneNums($oldURLs){
        // printResultsSimple("<h2>Array before removePhoneNums</h2>");
        $matchUrl = array();
        $i = 0;

        foreach($oldURLs as $item) {
            // printResultsSimple($item->getAttribute('href'));
             $url = grabURLPath();
            var_dump($item);
             $cleanURL = str_replace($url, "", $item->getAttribute('href')); //Replace the url to empty string
            echo $cleanURL;
            if(preg_match("(\/([a-zA-Z0-9+\$_-]\.?)+|(\D\.\D))", $cleanURL->getAttribute('href') ) ){
                array_push($matchUrl, $cleanURL);
           }
           $i++;
        }
        // echo "Length: " . $i . "<br />";
        return $matchUrl;
    }


   /* 
    * XXX  removeWhiteSpace => Remove any starting or trailing whitespaces from URLs, and replace all mid-string whitespace with %20.
    */   
    function removeWhiteSpace($oldURLs){
        foreach ($oldURLs as $item) {
            for ($i = 0; $i < $item->attributes->length; ++$i) {
                $item->attributes->item($i)->nodeValue = str_replace(' ', '%20', trim($item->attributes->item($i)->nodeValue));
            }
        }
        return $oldURLs;
    }


   /* 
    * XXX  removeDuplicates => Remove duplicate objects from array.
    */   
    function removeDuplicates($oldURLs){
        $cleanArray = array();
        $duplicates = array();
        $i = 0;

        printResultsSimple("<h2>Array before removeDuplicates</h2>");
        foreach ($oldURLs as $item) {
            $href = ($item->getAttribute('href'));

            if ( !in_array($href, $duplicates)) {
                array_push($duplicates, $href);
                array_push($cleanArray, $item);           
            } 
            printResultsSimple($href);
            $i++;
        }
        echo "Length: " . $i . "<br />";

        echo "<h2>DUPLICATES</h2>";
        foreach ($duplicates as $duplicate) {
            $href = ($item->getAttribute('href'));
            echo $duplicate . "<br />";
        }

        echo "<h2>CLEAN ARRAY</h2>"; 
        foreach ($cleanArray as $item) {
            $href = ($item->getAttribute('href'));

            printResultsSimple($href);
        }
        return $cleanArray;
    }


   /* 
    * XXX  addForwardSlashs => If a URL doesn't begin with a "/", add that character.
    */   
    function addForwardSlashs($oldURLs){
        // printResultsSimple("<h2>Array before addForwardSlashes</h2>");

        $cleanArray = array();

        foreach ($oldURLs as $item) {
            $href = ($item->getAttribute('href'));
            $firstChar = (substr( $href, 0, 1 ));
            
            // echo $href . "<br />";
            // echo $firstChar . "<br /><br />";

            if( $firstChar !== "/" ){
                for ($i = 0; $i < $item->attributes->length; ++$i) {
                    $item->attributes->item($i)->nodeValue = "/" . $item->attributes->item($i)->nodeValue;

                    // echo "NEW HREF VALUE<br />";
                    // echo $item->attributes->item($i)->nodeValue;
                    // echo "<br /><br />";
                }
            }
        }

        foreach ($oldURLs as $item){
            $href = ($item->getAttribute('href'));
            // echo $href . "<br />";
        }
        return $oldURLs;
    }

   /* XXX
    * createCSV => Add our labels to a CSV file and save it to computer.   
    *   $file = fopen('demosaved.csv', 'w'); => open the file "demosaved.csv" for writing
    *   fputcsv => Save the column headers
    *   foreach => Loop through urls, split url into path and param.
    *       if (!empty($params)) => If the URL also contains params, append them to variable.
    *       $label = trim( preg_replace...; => Remove special characters. 
    *       fputcsv($file, $data); => Save each row of the data
    *       fclose($file); => Close the file.
    * XXX
    */
    function createCSV($finalURLs){
        $file = fopen('demosaved.csv', 'w');
        fputcsv($file, array('label', 'url', 'campaignID', 'isLead', 'isEmail'));

        foreach($finalURLs as $result) {
            $data = array();
           
            $path = parse_url($result->getAttribute('href'), PHP_URL_PATH);
            $params = parse_url($result->getAttribute('href'), PHP_URL_QUERY);

            if (!empty($params)){
                $path = $path . "?" . $params;
            }
            
            $label = trim( preg_replace('/[^A-Za-z0-9\- ]/', '', $result->nodeValue) );
            if ($label == ''){$label = 'Blank';}

            array_push($data, $label);
            array_push($data, $path);
            array_push($data, '');
            array_push($data, 'N');
            array_push($data, 'N');

            fputcsv($file, $data);
        } 
        fclose($file);
    }

    $path = grabURLPath();
    $parsedPath = grabParsedPath($path);
    $hrefs = grabHrefs($path);

    $essentialURLs = removeExternalLinks($hrefs, $parsedPath);
    $essentialURLs = removePhoneNums($essentialURLs);
    $essentialURLs = removeWhiteSpace($essentialURLs);
    $essentialURLs = addForwardSlashs($essentialURLs);
    $essentialURLs = removeDuplicates($essentialURLs);
    printResults("Essential URLS", $essentialURLs, 'href');
    createCSV($essentialURLs);
?>




<!-- 
***TO DO***
- Make sure removeDuplicates isn't removing too many.
- Allow program to scrape the second level of urls on any site, not just the root URL

- Test on multiple sites to make sure we're not loosing any links that we want to keep. 

- Clean up addForwardSlashes function


- Build a frontend for program
- Refactor var names
- Comment functions
 -->