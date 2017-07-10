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

    function printResultsSimple($value){
        echo $value . "<br />";
    }

    function printVarExport($titleMesage, $var){
        echo "<h2>" . $titleMesage . "</h2>";
        echo '<pre>' . var_export($var, true) . '</pre>';
    }

    function myDump($parsedPath, $var){
        echo $var . '<br />';
        var_dump($parsedPath[$var]);
        echo '<br />';
    }
    function startTimer($time_pre){
        return $time_pre = microtime(true);
    }
    function stopTimer($time_pre){
        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        echo "<br>Total Time: " . $exec_time;
    }

   /* MAIN FUNCTIONS */      

    function grabURL(){
        $path = $_GET['url'];
        echo "<br>Original Path: " . $path;
        return $path;
    }

    function grabParsedUrl($url){
        $parsedUrl = parse_url($url);
        echo "<br> Parsed URL Host: " . $parsedUrl['host'];
        return $parsedUrl;
    }
        
    function grabHrefs($url){
        $html = file_get_contents($url);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        // grab all the paths on the page
        $xpath = new DOMXPath($dom);
        return $hrefs = $xpath->evaluate("/html/body//a");
    }

   /* XXX
    *   findFirstLevelPaths => Run through program and strip hrefs down to essentials
    * XXX
    */
    function findFirstLevelPaths($hrefs, $parsedUrl){
        $essentialURLs = removeExternalLinks($hrefs, $parsedUrl);
        $essentialURLs = removeBadLinks($essentialURLs);
        $essentialURLs = removeWhiteSpace($essentialURLs);
        $essentialURLs = removePrePath($essentialURLs, $parsedUrl);
        $essentialURLs = addForwardSlashs($essentialURLs);
        $essentialURLs = removeDuplicates($essentialURLs);
        printResults("Essential URLS", $essentialURLs, 'href');
        return $essentialURLs;
    }

   /* XXX
    * RemoveExternalLinks => Keep only array items that are internal links
    *   For Loop => Remove all extrernal links
    *       If statement => if the parsedUrl has a host property & that host property 
    *           isn't equal to our root Url, don't add to array.
    * XXX
    */
    function removeExternalLinks($oldURLs, $parsedUrl){
        $list = array();
        printResultsSimple("<h2>Full Array before removeExternalLinks</h2>");

        for ($i = 0; $i < $oldURLs->length; $i++) {
            $item = $oldURLs->item($i);

            // Print attribute values of DOM element to screen
            echo "<br />HREF from First Pass ". $i . ":<br>";
            if ($item->hasAttributes()) {
              foreach ($item->attributes as $attr) {
                $name = $attr->nodeName;
                $value = $attr->nodeValue;
                echo "'$name' :: '$value'<br />";
              }
            }

            echo "<br><br>";

            $href = $item->getAttribute('href');
            $parsedHref = parse_url($href);

            printResultsSimple($href);

            if(isset($parsedHref["host"]) && ( $parsedHref["host"] != $parsedUrl['host']) ){
                //this is a link to an external site - we dont want it, do nothing 
            } else {
                array_push($list, $item);
            } 
        }
        return $list;
    }


   /* 
    * XXX  removeBadLinks => Remove phone numbers from array.
    *   if => If href is a legitimate path, add to array.
    */   
    function removeBadLinks($oldURLs){
        printResultsSimple("<h2>Array before removeBadLinks</h2>");
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
    * XXX   removePrePath => Remove everything from the URL except from the path and params.
    *           if the url's host (www.anything.com) is within the href...
                    - strstr(...) => Remove any possible protocols (http, etc).
                    - str_replace(...) => remove the host from the href.
    */ 
    function removePrePath($oldURLs, $parsedPath){
        $i = 0;
        printResultsSimple("<h2>Array before removePrePath</h2>");
        foreach ($oldURLs as $item) {
            $i++;
            $href = ($item->getAttribute('href'));
            printResultsSimple($href);
            

            if (strpos($href, $parsedPath['host']) !== false) {
                $newHref = strstr($href, $parsedPath['host']);
                echo "NEW HREF: " . $newHref . "<br />";
                $newHref = str_replace($parsedPath['host'], '', $newHref);
                echo "NEWER HREF: " . $newHref . "<br />";

                for ($i = 0; $i < $item->attributes->length; ++$i){
                    $item->attributes->item($i)->nodeValue = $newHref;
                }

                $printIt = $item->getAttribute('href');
                echo "No Pre PATH: ";
                printResultsSimple($printIt);
            }
            echo "<br />";
        }

        printResultsSimple("<h2>Array after removePrePath</h2>");
        foreach ($oldURLs as $item) {
            $href = ($item->getAttribute('href'));
            printResultsSimple($href);
        }

        echo "Length: " . $i . "<br />";
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
        }

        return $cleanArray;
    }


   /* 
    * XXX  addForwardSlashs => If a URL doesn't begin with a "/", add that character.
    */   
   function addForwardSlashs($oldURLs){
        printResultsSimple("<h2>Array before addForwardSlashes</h2>");

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
            echo $href . "<br />";
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
            if ($path == '/'){$label = 'Home';}
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

    function findDeaperLevelPaths($firstLevelPaths, $path, $parsedUrl){
        echo "<h2>All Second Level HREFS:</h2>";

        $deaperLevelPaths = array();
        $totalNumOfLinks = 0;

        // Itterate through firstLevelPaths and grab href from each.
        foreach ($firstLevelPaths as $firstLevelPath){
            $href = $firstLevelPath->getAttribute('href');
            $url = $path . $href;

            // Scrape URL and grab all Hrefs from that page.
            $hrefs = grabHrefs($url);

            // Itterate through those Hrefs and print each to screen.
            foreach ($hrefs as $hrefObject){
                $totalNumOfLinks++;

                $href = $hrefObject->getAttribute('href');
                echo "<br /><br />" . $href;

                $href = deleteIfExternalLink($href, $parsedUrl);
                echo "<br />After delete External Link: " . $href;

                $href = deleteIfBadLink($href);
                echo "<br />After delete Bad Link: " . $href;

                $href = removeHrefWhiteSpace($href);
                echo "<br />After remove White Space: " . $href;

                $href = removeHrefPrePath($href, $parsedUrl);
                echo "<br />After remove Pre Path: " . $href;

                $href = addForwardSlash($href);
                echo "<br />After Add Forward Slash: " . $href;

                if ($href != ''){
                    $hrefObject->attributes->item(0)->nodeValue = $href;
                    echo "<br>New Href Property: " . $hrefObject->getAttribute('href');
                    array_push($deaperLevelPaths, $hrefObject);
                }

            }

            echo "Length: " . $totalNumOfLinks;
        }

        foreach($firstLevelPath as $firstLevelPath){
          array_push($deaperLevelPaths, $firstLevelPath);
        }

        $j = 0;
        echo "<h2>Second Level Paths full list before duplicate removal</h2>";
        foreach ($deaperLevelPaths as $deaperLevelPath) {
            $href = $deaperLevelPath->getAttribute('href');
            echo "<br>" . $href;
            $j++;
        }
        echo "<br>Length: " . $j;


        $masterURLS = removeDeaperDuplicates($deaperLevelPaths);
        $masterURLS = addForwardSlashs($masterURLS);

        $k = 0;
        echo "<h2>Master Level Paths after remove duplicates</h2>";
        foreach ($masterURLS as $masterURL) {
            $href = $masterURL->getAttribute('href');
            echo "<br>" . $href;
            $k++;
        }
        echo "<br>Length: " . $k;

        return $masterURLS;
    }

    function deleteIfExternalLink($href, $parsedUrl){
        $parsedHref = parse_url($href);

        if(isset($parsedHref["host"]) && ( $parsedHref["host"] != $parsedUrl['host']) ){
            //this is a link to an external site - we dont want it, do nothing
            echo "<br>Deleted External Link: " . $href; 
        } else {
            return $href;
        } 
    }

    function deleteIfBadLink($href){
        if (preg_match("(\/([a-zA-Z0-9+\$_-]\.?)+|(\D\.\D))", $href ) ){
            if(!preg_match_all("/\<|javascript:void|mailto:|tel:/", $href )){
                return $href;
            }
       } else {
        echo "<br>Deleted Bad Link: " . $href;
       }
    }

    function removeHrefWhiteSpace($href){
        echo "<br>REMOVING WHITE SPACE: " . $href;
        return str_replace(' ', '%20', trim($href));
    }

    function removeHrefPrePath($href, $parsedUrl){
        if (strpos($href, $parsedUrl['host']) !== false) {
            $newHref = strstr($href, $parsedUrl['host']);
            echo "NEW HREF: " . $newHref . "<br />";
            $newHref = str_replace($parsedUrl['host'], '', $newHref);
            echo "NEWER HREF: " . $newHref . "<br />";
        }
        return $href;
    }

   function addForwardSlash($href){
        $firstChar = (substr( $href, 0, 1 ));

        if (($href !== '') && ($firstChar !== "/")){
            $href = "/" . $href;
        }
        return $href;
    }

    // Removes Second Level Duplicates
    function removeDeaperDuplicates($oldURLs){
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
        }

        return $cleanArray;
    }


    //Extend max time a loop will run before it's shutdown
    ini_set('max_execution_time', 300); //300 seconds = 5 minutes

    $time_pre;
    $time_pre = startTimer($time_pre); microtime(true);

    $url = grabURL();
    $parsedUrl = grabParsedUrl($url);
    $hrefs = grabHrefs($url);

    $essentialURLs = findFirstLevelPaths($hrefs, $parsedUrl);
    $masterURLS = findDeaperLevelPaths($essentialURLs, $url, $parsedUrl);

    createCSV($masterURLS);
    stopTimer($time_pre);
?>




<!-- 
***TO DO***

- Add function to add a trailing slash onto every entry that isn't an image, and vice versa (remove a trailing slash for all entries that have one)

- Taylor deleteBadLinks to not delete entries with '#' in the title. 
    - make sure this is working correctly

- make sure Prepath is being removed for each entry.


- make the scrapper add in a blank entry & an "/" entry for every site.

- Test on multiple sites to make sure we're not loosing any links that we want to keep. 


- Build a frontend for program
- Consolidate functions (there's a ton of repitition)
- Refactor var names
- Comment functions

- Rejigger first level URL parser to use the code of the deapaerLevel URL parsers. Should make program much faster.


CHECKLIST
    Number of links each site should have:
        - Plugin Karaoke = 19 w/o root urls ('', '/', '/#');  ==>  NEW VERSION: 38
        - Plaza College = About 83   
 -->
