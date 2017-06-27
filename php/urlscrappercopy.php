<?php
set_time_limit(0);
class crawler
{
    protected $_url;
    protected $_depth;
    protected $_host;
    protected $_useHttpAuth = false;
    protected $_seen = array();
    protected $_filter = array();

    public function __construct($url, $depth = 5)
    {
        $this->_url = $url;
        $this->_depth = $depth;
        $parse = parse_url($url);
        $this->_host = $parse['host'];
    }

    protected function _processAnchors($content, $url, $depth)
    {
        $dom = new DOMDocument('1.0');
        @$dom->loadHTML($content);
        $anchors = $dom->getElementsByTagName('a');
        $NEWARRAY = array();


        foreach ($anchors as $element) {
            $href = $element->getAttribute('href');
            if (0 !== strpos($href, 'http')) {
                $path = '/' . ltrim($href, '/');
                if (extension_loaded('http')) {
                    $href = http_build_url($url, array('path' => $path));
                } else {
                    $parts = parse_url($url);
                    $href = $parts['scheme'] . '://';
                    if (isset($parts['user']) && isset($parts['pass'])) {
                        $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                    }
                    $href .= $parts['host'];
                    if (isset($parts['port'])) {
                        $href .= ':' . $parts['port'];
                    }
                    $href .= $path;
                }
            }
            // Crawl only link that belongs to the start domain
            $this->crawl_page($href, $depth - 1, $NEWARRAY);

        }
    }

    protected function _getContent($url)
    {
        $handle = curl_init($url);
        if ($this->_useHttpAuth) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($handle, CURLOPT_USERPWD, $this->_user . ":" . $this->_pass);
        }
        // follows 302 redirect, creates problem wiht authentication
//        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, TRUE);
        // return the content
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        curl_close($handle);
        
        return array($response);
    }

    protected function _printResult($url, $depth)
    {
        ob_end_flush();
        $currentDepth = $this->_depth - $depth;
        $count = count($this->_seen);
        
        echo "$count -> $url <br>";
        
        ob_start();
        flush();
    }

    protected function isValid($url, $depth)
    {
        if (strpos($url, $this->_host) === false
            || $depth === 0
            || isset($this->_seen[$url])
        ) {
            return false;
        }
        foreach ($this->_filter as $excludePath) {
            if (strpos($url, $excludePath) !== false) {
                return false;
            }
        }
        return true;
    }

    public function crawl_page($url, $depth)
    {
        if (!$this->isValid($url, $depth)) {
            return;
        }
        // add to the seen URL
        $this->_seen[$url] = true;
        // get Content and Return Code
        list($content) = $this->_getContent($url);
        // print Result for current Page
        $this->_printResult($url, $depth);
        // process subPages
        $this->_processAnchors($content, $url, $depth);
    }


    public function run()
    {
        $this->crawl_page($this->_url, $this->_depth);
    }
}

// USAGE
$startURL = 'http://www.plazacollege.edu';
$depth = 6;
$crawler = new crawler($startURL, $depth);
$crawler->run();


// https://fl.pluginkaraoke.com 
// http://aimediagroup.com
// http://www.plazacollege.edu
// https://www.tmh.org


// write function to turn links to an array. 

//http://fl.pluginkaraoke.com


?>