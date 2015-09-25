<?php
    // Defining the basic scraping function
    function scrape_between($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }
    ?>


<?php
    // Defining the basic cURL function
    function curl($url) {
        // Assigning cURL options to an array
        $options = Array(
                         CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
                         CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
                         CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
                         CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
                         CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
                         CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
                         CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
                         CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
                         );
        
        $ch = curl_init();  // Initialising cURL
        curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        curl_close($ch);    // Closing cURL
        return $data;   // Returning the data from the function
    }
    ?>



<?php
    $scraped_page = curl("http://www.prnewswire.com");    // Downloading IMDB home page to variable $scraped_page
    $scraped_data = scrape_between($scraped_page, "<title>", "</title>");   // Scraping downloaded data in $scraped_page for content between <title> and </title> tags
    
    echo $scraped_data; // Echoing $scraped data, should show the title of the webpage
    ?>

<?php
    
    $continue = TRUE;   // Assigning a boolean value of TRUE to the $continue variable
    
    
    
    
    
    
    
    
    $url = "http://www.prnewswire.com/news-releases/general-business-latest-news/general-business-latest-news-list/?page=1&pagesize=1000";    // Assigning the URL we want to scrape to the variable $url
    $pages = 1;
    
    
    
    
    
    
    
    $page = 1;
    // While $continue is TRUE, i.e. there are more search results pages
    while ($continue == TRUE) {
        echo "page " . $page++ . "   ";
        $results_page = curl($url); // Downloading the results page using our curl() function
        $results_page = scrape_between($results_page, "<div class=\"col-sm-9\">", "</main>"); // Scraping out only the middle section of the results page that contains our results
        
        $separate_results = explode("<a class=\"news-release\" title=\"", $results_page);   // Expploding the results into separate parts into an array
        
        // For each separate result, scrape the URL
        foreach ($separate_results as $separate_result) {
            if ($separate_result != "") {
                $results_urls = scrape_between($separate_result, "href=\"", ".html\">") . ".html";
                $results_urls = (string)$results_urls;
            }
            
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "prnewswire";
            
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = "INSERT INTO urls (url)
                VALUES ('$results_urls')";
                // use exec() because no results are returned
                $conn->exec($sql);
                echo "New record created successfully" . "\n";
            }
            catch(PDOException $e)
            {
                echo $sql . "<br>" . $e->getMessage();
            }
            
            $conn = null;
            
        }
        sleep(43200);
    }
    
    ?>
