<?php
    // Testing New Code
    function newStuff(){
        $swissNumberStr = "044 668 18 00";
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $swissNumberProto = $phoneUtil->parse($swissNumberStr, "CH");
            // var_dump($swissNumberProto);
        } catch (\libphonenumber\NumberParseException $e) {
            // var_dump($e);
        }

        $isValid = $phoneUtil->isValidNumber($swissNumberProto);
        var_dump($isValid); // true
    }


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
        
            print_r($xpath);
            echo "<br / >";

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

        printResultsSimple("<h2>Full Array before removeExternalLinks</h2>");

        for ($i = 0; $i < $oldURLs->length; $i++) {
        	$href = $oldURLs->item($i);
        	$url = $href->getAttribute('href');
            $parsedUrl = parse_url($url);

            printResultsSimple($url);
            
            if(isset($parsedUrl["host"]) && ( $parsedUrl["host"] != $parsedPath['host']) ){
                //this is a link to an external site - we dont want it, do nothing 
            }
            else {
                array_push($list, $href);
            }
        }

        echo "Length: " . $i . "<br />";
        return $list;
    }


   /* 
    * XXX  removePhoneNums => Remove phone numbers from array.
    *   if => If href is a legitimate path, add to array.
    */   
    function removePhoneNums($oldURLs){
        printResultsSimple("<h2>Array before removePhoneNums</h2>");
        $matchUrl = array();
        $i = 0;

        foreach($oldURLs as $item) {
            printResultsSimple($item->getAttribute('href'));

            if(preg_match("(\/([a-zA-Z0-9+\$_-]\.?)+|(\D\.\D))", $item->getAttribute('href') ) ){
                array_push($matchUrl, $item);
           }
           $i++;
        }
        echo "Length: " . $i . "<br />";
        return $matchUrl;
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
        return $cleanArray;
    }

    function addForwardSlashs($oldURLs){
        printResultsSimple("<h2>Array before addForwardSlashes</h2>");

        $cleanArray = array();

        foreach ($oldURLs as $item) {
            $href = ($item->getAttribute('href'));
            $firstChar = (substr( $href, 0, 1 ));
            if( $firstChar !== "/" ){
                 // $item['href'] = "/" . $href; 


                 //FINISH WRITING THIS CODE. Add new href value into $item object.
                echo $item;
            }
            array_push($cleanArray, $item);
        }
        return $cleanArray;
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
        fputcsv($file, array('label', 'url', 'isLead'));

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
            array_push($data, 'no');

            fputcsv($file, $data);
        } 
        fclose($file);
    }


    newStuff();

    $path = grabURLPath();
    $parsedPath = grabParsedPath($path);
    $hrefs = grabHrefs($path);

    $essentialURLs = removeExternalLinks($hrefs, $parsedPath);
    $essentialURLs = removePhoneNums($essentialURLs);
    $essentialURLs = removeDuplicates($essentialURLs);
    // $essentialURLs = addForwardSlashs($essentialURLs);
    printResults("Essential URLS", $essentialURLs, 'href');
    createCSV($essentialURLs);
?>




<!-- 
***TO DO***
- Test this on https://fl.pluginkaraoke.com . Make sure we're grabbing all internal links.
    - WE're losing links because of the removeNums function. It's too agressive. Make it more specific. Instead of Regex, a good solution might be libphonenumber by google.

- Build a frontend for program
- Refactor var names
- Comment functions
 -->