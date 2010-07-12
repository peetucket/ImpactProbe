<?php 
// Add new entry to crontab
if(file_exists($this->cron_file)) {
    $cron_fh = fopen($this->cron_file, 'a') or die($this->cron_file.": cannot open file for writing"); // 'a' = append existing file; 'w' = write new file;
    fwrite($cron_fh, "0 0 * * * php ".Kohana::config('myconf.path.kohana')."/index.php --uri=gather/twitter/$project_id\n"); // Will execute daily
    fclose($cron_fh);
    
    system("crontab ".$this->cron_file, $return_code);
    if($return_code != 0) {
        echo "Error when running command &lt;crontab ".$this->cron_file."&gt;: $return_code\n";
    }
    echo system("crontab -l");
} else {
    echo "Error: cannot find cron file in its default location ".$this->cron_file; 
}


// Twitter Search API XML parser:
// Generate query string
$lang = "lang=en";
$query_str = $lang."&q=".urlencode("ubuntu");
$request = "http://search.twitter.com/search.atom?$query_str";

$curl = curl_init();
curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($curl, CURLOPT_URL,$request);
$response = curl_exec ($curl);
curl_close($curl);

$response = str_replace("twitter:", "", $response); // Remove "twitter:" from the $response string
$xml = simplexml_load_string($response); // Convert response XML into an object
for($i=0;$i<count($xml->entry);$i++) { // Loop through each entry(s)/tweet in the feed
    
    // UNUSED METADATA
    $id = $xml->entry[$i]->id;
    $id_parts = explode(":",$id);
    $tweet_id = array_pop($id_parts);
    
    $account_link = $xml->entry[$i]->author->uri;
    $image_link = $xml->entry[$i]->link[1]->attributes()->href;
    
    $username = trim($xml->entry[$i]->author->name, ")");
    $username_parts = explode("(", $username);
    $real_name = trim(array_pop($username_parts));
    $screen_name = trim(array_pop($username_parts));
    
    $tweet_source = $xml->entry[$i]->source;
    
    // USEFUL METADATA
    $tweet_url = $xml->entry[$i]->link[0]->attributes()->href;
    
    // Get the published time & convert to timestamp format
    $published_time = trim(str_replace(array("T","Z")," ",$xml->entry[$i]->published));
    
    // Get tweet text & remove <b> and </b> from search terms
    $tweet_text = $xml->entry[$i]->content;
    $tweet_text = str_replace(array("<b>", "</b>"), "", $tweet_text);
}
?>
