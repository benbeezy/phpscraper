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
date_default_timezone_set("America/Denver");
    $continue = TRUE;
    $number = 0;
    while ($continue == TRUE) {
        
        
			$servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "prnewswire";
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $sql = "SELECT url FROM urls WHERE status = 'new' LIMIT 1";
        $result = $conn->query($sql);
        
        $sql_update = "UPDATE urls SET status='done' WHERE status = 'new' LIMIT 1";
        $conn->query($sql_update);
        
        
        $conn->close();
        
        
        
        $continue = TRUE;   // Assigning a boolean value of TRUE to the $continue variable
        
        
        
        
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $url = $row["url"];
            }
        } else {
            echo "Done and waiting for new urls";
            sleep(200);
        }
        
        // While $continue is TRUE, i.e. there are more search results pages
        $results_page = curl($url); // Downloading the results page using our curl() funtion
        
        $results_page = scrape_between($results_page, "<body>", "</body>"); // Scraping out only the middle section of the results page that contains our results
        
        $separate_results = explode("<main>", $results_page);   // Expploding the results into separate parts into an array
        
        // For each separate result, scrape the URL
        
        foreach ($separate_results as $separate_result) {
            if ($separate_result != "") {
                
                $results_title = scrape_between($separate_result, "<!--endclickprintexclude-->", "</h1>");
                $results_url = $url;
                $results_name = scrape_between($separate_result, "<span itemprop=\"name\">", "</span>");
                $results_email = scrape_between($separate_result, "mailto:", "\"");
                $results_date = date("h:i:sa");
                if($results_email == "" or "0" or NULL or FALSE or 0){
                    $results_email = scrape_between($separate_result, "Email: ", " ");
                    if($results_email == "" or "0" or NULL or FALSE or 0){
                        $results_email = scrape_between($separate_result, "email: ", " ");
                        if($results_email == "" or "0" or NULL or FALSE or 0){
                            $results_email = scrape_between($separate_result, "e: " , " ");
                            if($results_email == "" or "0" or NULL or FALSE or 0){
                                $results_email = scrape_between($separate_result, "Contact " , "</p>");
                            } else {
                                $results_email = "no Contact info found";
                            }
                        }
                    }
                }
                $results_website = scrape_between($separate_result, "RELATED LINKS", "<!--startclickprintexclude-->");
                
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "prnewswire";
                
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // prepare sql and bind parameters
                    $stmt = $conn->prepare("INSERT INTO article (title,url,person,email,website,date)
                                           VALUES (:results_title,:results_url,:results_name,:results_email,:results_website,:results_date)");
                                           
                                           $stmt->bindParam(':results_title', $results_title);
                                           $stmt->bindParam(':results_url', $results_url);
                                           $stmt->bindParam(':results_name', $results_name);
                                           $stmt->bindParam(':results_email', $results_email);
                                           $stmt->bindParam(':results_website', $results_website);
                                           $stmt->bindParam(':results_date', $results_date);
                                           
                                           // insert a row
                                           $results_title = $results_title;
                                           $results_url = $results_url;
                                           $results_name = $results_name;
                                           $results_email = $results_email;
                                           $results_website = $results_website;
                                           $results_date = date("h:i:sa");
                                           $stmt->execute();
                                           
                                           echo $number++;
                                           echo "\n";
                                           }
                                           catch(PDOException $e)
                                           {
                                           echo "Error: " . $e->getMessage();
                                           }
                                           $conn = null;
                                           
                                           }
                                           }
                                           }
                                           date_default_timezone_set("America/Denver");
                                           echo "Finished at " . date("m-d-y h:i:sa");
    ?>
