<?php
/*******************************************************************

Copyright 2010, Adrian Laurenzi

This file is part of ImpactProbe.

ImpactProbe is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
at your option) any later version.

ImpactProbe is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ImpactProbe. If not, see <http://www.gnu.org/licenses/>.

*******************************************************************/
defined('SYSPATH') or die('No direct script access.');

class Controller_Gather extends Controller {
    
    public function before()
    {
        parent::before();
        $this->model_gather = new Model_Gather;
        $this->model_params = new Model_Params;
        
        // API connection config settings
        $this->connection_retries = 4;
        $this->wait_before_retry = 200; // in seconds
    }
    
    // Execute each gathering method listed in db table `api_sources` for appropriate project(s). If $gather_from is a project_id then only execute for that project, otherwise gather data for all projects assigned to the given time interval (ex: 'daily').
    public function action_index($gather_from = "")
    {
        if($gather_from == "") {
            print "Gather interval or project ID not defined, cannot continue.\n";
        } else {
            if($gather_from > 0) {
                // Gather data for specific project
                $this->get_project_data($gather_from);
                if(!$this->project_data) {
                    print "Project with this ID does not exist\n";
                } else {
                    $api_sources = $this->model_params->get_active_api_sources($gather_from);
                    foreach($api_sources as $api_source) {
                        eval('$this->'.$api_source['gather_method_name'].'('.$gather_from.');');
                    }
                }
            } else {
                // Gather from all projects assigned to the given time interval (ex: 'daily')
                $active_projects = $this->model_gather->get_active_projects($gather_from);
                if(count($active_projects) > 0) {
                    foreach($active_projects as $project) {
                        $this->get_project_data($project['project_id']);
                        $api_sources = $this->model_params->get_active_api_sources($project['project_id']);
                        foreach($api_sources as $api_source) {
                            eval('$this->'.$api_source['gather_method_name'].'('.$project['project_id'].');');
                        }
                    }
                }
            }
        }
    }
    
    private function get_project_data($project_id = 0)
    {
        $project_data = $this->model_gather->get_project_data($project_id);
        if(count($project_data) > 0) {
            $this->project_data = array_pop($project_data);
            $this->project_id = $this->project_data['project_id'];
            
            $this->keywords_phrases = $this->model_gather->get_active_keywords($this->project_id); // Returns multidimensional array of data for each keyword/phrase
        } else {
            $this->project_data = '';
        } 
    }
    
    /* 
     * BEGIN DATA SOURCE API GATHERING METHODS 
     */
    private function twitter_search()
    {
        // Twitter Search API parameters:
        $api_id = 1; // api_id = 1 for Twitter Search API
        $api_url = "http://search.twitter.com/search.json";
        $results_per_page = "&rpp=100"; // Max is 100
        $lang = ""; //"&lang=en"; // Limit search to English tweets
        
        $total_results_gathered = 0;
        
        // Add keywords/phrases to query string 
        $keyword_str = "";
        $num_keywords = count($this->keywords_phrases);
        $i = 0;
        foreach($this->keywords_phrases as $keyword_phrase) {
            $i++;
            $word_split = explode(" ", $keyword_phrase['keyword_phrase']);
            if(count($word_split) > 1) { // Is phrase (more than 1 word)
                // Check if searching "exact phrase" -> if so add quotes
                if($keyword_phrase['exact_phrase'])
                    $keyword_str .= '"'.urlencode($keyword_phrase['keyword_phrase']).'"';
                else
                    $keyword_str .= '('.urlencode($keyword_phrase['keyword_phrase']).')';
            } 
            else { // Is single keyword
                $keyword_str .= urlencode($keyword_phrase['keyword_phrase']);
            }
            if($i < $num_keywords) {
                $keyword_str .= '+OR+';
            }
        }
        
        //
        // TO DO: Add negative keywords to query string
        // &q=...+-negkey1+-negkey2+...
        // 

        $this->api_connect_error = 0;
        $cur_page = 1;
        while(TRUE) {
            // Compile request URL
            $request_url = $api_url.'?q='.$keyword_str.$lang.$results_per_page.'&page='.$cur_page;
            print "Query: $request_url\n";
            
            $response = $this->api_connect($request_url, 'get');
            // Stop trying to gather if there was a connection failure
            if($this->api_connect_error) { break; }
            
            // Loop through each tweet (if there are results on this page)
            $json = json_decode($response, true);
            $num_results = count($json['results']);
            if($num_results > 0) {
                
                foreach($json['results'] as $tweet_data) {
                    
                    if(array_key_exists('from_user', $tweet_data) AND array_key_exists('id', $tweet_data)) {
                        $username = $tweet_data['from_user'];
                        $tweet_id = $tweet_data['id'];
                        $tweet_url = "http://twitter.com/$username/status/$tweet_id";
                    } else { 
                        print "ERROR: No tweet ID and/or user ID found. Cannot use.\n";
                        continue; 
                    }
                    if(array_key_exists('created_at', $tweet_data)) {
                        $date_published = $tweet_data['created_at'];
                        $date_published_timestamp = strtotime($date_published); // $this->date_to_timestamp($date_published);
                    } else { $date_published_timestamp = 0; }
                    $tweet_text = (array_key_exists('text', $tweet_data)) ? $tweet_data['text'] : '';
                    $tweet_lang = (array_key_exists('iso_language_code', $tweet_data)) ? $tweet_data['iso_language_code'] : '';
                    // Geolocation info
                    $place = "";
                    if(array_key_exists('place', $tweet_data)) {
                        foreach($tweet_data['place'] as $place_data) {
                            $place .= "$place_data ";
                        }
                    }
                    
                    $require_keywords = 0;
                    $total_results_gathered += $this->add_metadata($tweet_url, $tweet_text, $require_keywords, array(
                        'project_id' => $this->project_id,
                        'api_id' => $api_id,
                        'date_published' => $date_published_timestamp,
                        'date_retrieved' => time(),
                        'lang' => $tweet_lang,
                        'geolocation' => $place
                    ));
                    
                }
                $cur_page++;
                
            } else {
                // No results on this page so we are DONE!
                break;
            }
        }
        
        // Add entry to gather log (as long as no errors occurred)
        if(!$this->api_connect_error) {
            $this->model_gather->insert_gather_log(array(
                'project_id' => $this->project_id,
                'search_query' => $request_url,
                'date' => time(),
                'results_gathered' => $total_results_gathered
            ));
        }
    }
    
    private function rss_feed()
    {
        // RSS Feed parameters:
        $api_id = 2; // api_id = 2 for RSS Feeds
        
        // Perform gathering for each active RSS feed URL for given project_id
        $rss_feeds = $this->model_gather->get_active_rss_feeds($this->project_id);
        
        foreach($rss_feeds as $rss_feed) {
            $total_results_gathered = 0;
            
            // If RSS feed was set searchable generate search query GET string
            $keyword_str = "";
            if($rss_feed['searchable']) {
                $num_keywords = count($this->keywords_phrases);
                $i = 0;
                foreach($this->keywords_phrases as $keyword_phrase) {
                    $i++;
                    $word_split = explode(" ", $keyword_phrase['keyword_phrase']);
                    if(count($word_split) > 1) { // Is phrase (more than 1 word)
                        // Check if searching "exact phrase" -> if so add quotes
                        if($keyword_phrase['exact_phrase'])
                            $keyword_str .= '"'.urlencode($keyword_phrase['keyword_phrase']).'"';
                        else
                            $keyword_str .= '('.urlencode($keyword_phrase['keyword_phrase']).')';
                    } 
                    else { // Is single keyword
                        $keyword_str .= urlencode($keyword_phrase['keyword_phrase']);
                    }
                    if($i < $num_keywords) {
                        $keyword_str .= '+OR+';
                    }
                }
            }
            
            $connection_retries = 0;
            $this->api_connect_error = 0;
            while(TRUE) {
                // Compile request URL
                $request_url = $rss_feed['url'].$keyword_str;
                print "Query: $request_url\n";
                
                // Check for connection errors
                try {
                    $status_code = Remote::status($request_url);
                } catch (Exception $e) {
                    $this->api_connect_error = "Error connecting to $request_url. Cannot locate host.";
                } 
                if(!$this->api_connect_error AND ($status_code < 200 OR $status_code > 299)) {
                    $this->api_connect_error = "Error connecting to $request_url. Status code: $status_code";
                }
                
                if(!$this->api_connect_error) {
                    $rss_output = Feed::parse($request_url);
                    $num_results = count($rss_output);
                    if($num_results > 0) {
                        // Loop through each result, parse, and store data
                        foreach($rss_output as $item) {
                            $title = (array_key_exists('title', $item)) ? $item['title'] : '';
                            $text = (array_key_exists('description', $item)) ? $item['description'] : '';
                            $date_published_timestamp = (array_key_exists('pubDate', $item)) ? strtotime($item['pubDate']) : 0;
                            
                            // Append title to text & strip all HTML tags except <br>'s
                            $text = "Title: $title<br>$text";
                            $text = strip_tags($text, "<br>");
                            
                            // Determine unique identifier, if no URL -> use guid -> if no GUID give error
                            if(array_key_exists('link', $item) AND $item['link'] != "") {
                                $url = $item['link'];
                            } else if(array_key_exists('guid', $item)) {
                                $url = $item['guid'];
                            } else { 
                                print "Error: item has no URL or GUID. Cannot use.\n";
                                continue;
                            }
                            
                            // Add each result to database
                            $require_keywords = ($rss_feed['searchable']) ? 0 : 1; // Only require keywords if RSS feed is NOT searchable
                            $total_results_gathered += $this->add_metadata($url, $text, $require_keywords, array(
                                'project_id' => $this->project_id,
                                'api_id' => $api_id,
                                'date_published' => $date_published_timestamp,
                                'date_retrieved' => time()
                            ));
                        }
                    }
                    break; 
                    
                } else {
                    // Retry connecting to API
                    $connection_retries++;
                    
                    // Connection error (only for non-searchable RSS feeds, it is assumed an error has occured if no item comes through the feed after multiple connection attempts)
                    if($connection_retries > $this->connection_retries) {
                        if(!$rss_feed['searchable']) {
                            $this->model_gather->insert_gather_log(array(
                                'project_id' => $this->project_id,
                                'search_query' => $this->api_connect_error,
                                'date' => time(),
                                'results_gathered' => 0,
                                'error' => 1
                            ));
                            $this->api_connect_error = 1;
                        }
                        break;
                    }
                }
            }
            
            // Add entry to gather log (as long as no errors occurred)
            if(!$this->api_connect_error) {
                $this->model_gather->insert_gather_log(array(
                    'project_id' => $this->project_id,
                    'search_query' => $request_url,
                    'date' => time(),
                    'results_gathered' => $total_results_gathered
                ));
            }
        }
    }
    
   /***********************************
    ** NEW GATHERING METHOD TEMPLATE **
    ***********************************
    Copy the method below and modify it as necessary (please leave a copy of the template). Rename the method to have the same name given in the column `gather_method_name` from the `api_sources` database table. 
    ***********************************
    private function method_name()
    {
        // API parameters:
        $api_id = ; // This value is listed in the database table `api_source` (if you haven't yet created a row for this gathering method do so now) 
        $api_url = "";
        
        $total_results_gathered = 0;
        
        // Generate [GET] query string from keywords defined by the user
        // If your API takes a POST query you will have to send your keyword data as an array of key/value pairs and pass it to the api_connect() method below 
        // You will like have to modify this section significantly according to the syntax of the API
        $keyword_str = "";
        $num_keywords = count($this->keywords_phrases);
        $i = 0;
        foreach($this->keywords_phrases as $keyword_phrase) {
            $i++;
            $word_split = explode(" ", $keyword_phrase['keyword_phrase']);
            if(count($word_split) > 1) { // Is phrase (more than 1 word)
                // Check if searching "exact phrase" -> if so add quotes
                if($keyword_phrase['exact_phrase'])
                    $keyword_str .= '"'.urlencode($keyword_phrase['keyword_phrase']).'"';
                else
                    $keyword_str .= '('.urlencode($keyword_phrase['keyword_phrase']).')';
            } 
            else { // Is single keyword
                $keyword_str .= urlencode($keyword_phrase['keyword_phrase']);
            }
            if($i < $num_keywords) {
                $keyword_str .= '+OR+';
            }
        }
        
        $this->api_connect_error = 0;
        $cur_page = 1;
        while(TRUE) {
            // Compile request URL
            $request_url = "$api_url?q=$keyword_str"; // Example of GET query string (you will almost certainly have to modify the syntax for your API 
            print "Query: $request_url\n";
            
            // Use the api_connect() method in one of two ways: if your API takes a GET request pass the URL as the first parameter and 'get' as the second; if your API takes a POST request pass the URL as the first parameter, 'post' as the second, and the key/value array of POST data as the third
            $response = $this->api_connect($request_url, 'get');
            $response = $this->api_connect($request_url, 'post', $post_data);
            
            // Stop trying to gather if there was a connection failure
            if($this->api_connect_error) { break; }
            
            // Determine number of results or somehow detect when there are no more results to gather
            // ***NOTICE: It is very important that break the `while(TRUE)` loop after determining there are no more results to gather or else the script will go into an infinite loop
            if($num_results > 0) {
                
                // Loop through each result, parse, and store data
                // BEGIN LOOP
                
                    // Add each result to database
                    $require_keywords = ; // Set this to 1 to make it so metadata is not added to the database unless at least 1 active keyword is found in the given text (set it to 0 to add given metadata to database regardless)
                    $total_results_gathered += $this->add_metadata($url, $text, $require_keywords, array(
                        'project_id' => $this->project_id,
                        'api_id' => $api_id,
                        'date_published' => $date_published_timestamp,
                        'date_retrieved' => time(),
                        'lang' => $lang,
                        'geolocation' => $geolocation
                    ));
                
                // END LOOP
                
            } else {
                // No results on this page so we are DONE!
                break;
            }
        }
        
        // Add entry to gather log (as long as no errors occurred)
        if(!$this->api_connect_error) {
            $this->model_gather->insert_gather_log(array(
                'project_id' => $this->project_id,
                'search_query' => $request_url,
                'date' => time(),
                'results_gathered' => $total_results_gathered
            ));
        }
    }
    ***************************************
    ** END NEW GATHERING METHOD TEMPLATE **
    ***************************************/
    
    /* 
     * END DATA SOURCE API GATHERING METHODS 
     */
    
    // Returns response or adds error to gather_log after connecting to API via GET or POST
    public function api_connect($request_url, $method, Array $post_data = array())
    {
        $method = strtolower($method);
        if($method != 'get' AND $method != 'post') {
            throw new Exception("api_connect: method supplied must be either 'post' or 'get'");
        }
        
        $num_requests_sent = 0;
        $response = "";
        while(TRUE) {
            if($num_requests_sent > $this->connection_retries) {
                print "Could not connect to API with request: $request_url\n";
                
                // Add ERROR entry to gather log
                $this->model_gather->insert_gather_log(array(
                    'project_id' => $this->project_id,
                    'search_query' => $error, // Pass last error message instead of query URL
                    'date' => time(),
                    'results_gathered' => 0,
                    'error' => 1
                ));
                $this->api_connect_error = 1;
                break;
            } else {
                if($num_requests_sent > 0)
                    print "Re-trying ($num_requests_sent)...\n";
                
                // Try connecting to API
                $error = "";
                try {
                    if($method == "get") {
                        $response = Remote::get($request_url, array(
                            CURLOPT_RETURNTRANSFER => TRUE
                        ));
                    } else if($method == "post") {
                        $response = Remote::get($request_url, array(
                            CURLOPT_POST       => TRUE,
                            CURLOPT_POSTFIELDS => http_build_query($post_data)
                        ));
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    $num_requests_sent++;
                    sleep($this->wait_before_retry); // Wait before trying to reconnect
                }
                if(!$error) {
                    print "Successfully connected to API!\n";
                    break;
                }
            }
        }
        return $response;
    }
    
    // Adds new metadata entry and returns 1 if new metadata was added and 0 if nothing was added
    // If $require_keywords = 1 then metadata will not be added unless at least 1 active keyword is found in the given text (if $require_keywords = 0 then given metadata will be added to database regardless)
    private function add_metadata($url, $cache_text, $require_keywords, Array $metadata)
    {
        $new_entry_added = 0; // if value is 0 then $cache_text either didn't contain any keywords or URL entry already existed
        
        // Find total number of words in $cached_text and add to metadata
        $metadata['total_words'] = count(explode(" ", $cache_text));
        
        // Check if URL already exists in database for this project
        if($this->model_gather->url_exists($this->project_id, $url)) {
            print "exists: $url\n";
        } else {
            $keyword_metadata_entries = $this->generate_keyword_metadata($cache_text, $require_keywords);
            
            if($require_keywords AND count($keyword_metadata_entries) == 0) {
                // No keywords/phrases found & $require_keywords set to 1
                print "no keywords/phrases: $url\n";
            } else {
                print "added: $url\n";
                $new_entry_added = 1;
                
                // Add new URL & metadata entry 
                $url_id = $this->model_gather->insert_url(array(
                    'project_id' => $this->project_id, 
                    'url' => $url
                ));
                $metadata['url_id'] = $url_id;
                $meta_id = $this->model_gather->insert_metadata($metadata);
                
                // Add metadata for each keyword found in $cache_text
                if(count($keyword_metadata_entries) > 0) {
                    foreach($keyword_metadata_entries as $keyword_metadata_entry) {
                        $keyword_metadata_entry['meta_id'] = $meta_id;
                        $this->model_gather->insert_keyword_metadata($keyword_metadata_entry);
                    }
                }
                
                $this->model_gather->insert_cached_text(array(
                    'meta_id' => $meta_id,
                    'text' => $cache_text
                ));
                $this->model_gather->save_cached_text(array(
                    'project_id' => $this->project_id,
                    'meta_id' => $meta_id,
                    'text' => $cache_text
                ));
            }
        }
        return $new_entry_added;
    }
    
    // Counts total number of occurances of each [active] keyword in given $text and adds an keyword entry to array for each where count > 0
    private function generate_keyword_metadata($text, $require_keywords) 
    {
        $keyword_metadata = array();
        foreach($this->keywords_phrases as $keyword_phrase) {
            $num_occurances = 0;
            if($keyword_phrase['exact_phrase']) {
                // Phrase set as exact: find total number of occurances
                $num_occurances = preg_match_all("/\b(".$keyword_phrase['keyword_phrase'].")\b/ie", $text, $matches);
            } else {
                // Phrase NOT set as exact: make sure post contains ALL words in phrase -> if it does then set $num_occurances to 1 (for simplicity) otherwise set to 0
                $keywords_phrases_arr = explode(" ", $keyword_phrase['keyword_phrase']); 
                $num_occurances = 1;
                foreach($keywords_phrases_arr as $keyword_phrase_sub) {
                    $num_occurances_sub = preg_match_all("/\b(".$keyword_phrase_sub.")\b/ie", $text, $matches);
                    if(!$num_occurances_sub) {
                        $num_occurances = 0;
                        break;
                    }
                }
            }
            
            if(!$require_keywords OR $num_occurances > 0) {
                array_push($keyword_metadata, array(
                    'keyword_id' => $keyword_phrase['keyword_id'],
                    'num_occurrences' => $num_occurances
                ));
            }
        }
        return $keyword_metadata;
    }
    
    private function date_to_timestamp($date_str) 
    { 
            list($D, $d, $M, $y, $h, $m, $s, $z) = sscanf($date_str, "%3s, %2d %3s %4d %2d:%2d:%2d %5s"); 
            return strtotime("$d $M $y $h:$m:$s $z");
    } 
    
    public function action_log($project_id = 0)
    {
        // Get project data
        $project_data = $this->model_gather->get_project_data($project_id);
        
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $project_data = array_pop($project_data);
            
            $view = View::factory('template');
            $view->page_title = $project_data['project_title']." - Gather Log";
            
            $view->page_content = View::factory('pages/gather_log');
            
            // Default results display
            $result_params = array(
                'date_from' => 0, 'date_to' => 0,
                'num_results' => 100,
                'order' => 'desc'
            );
            
            $form_errors = "";
            if($_POST) {
                // Form validation
                $post = new Validate($_POST);
                $post->rule('datef_m', 'digit')->rule('datef_d', 'digit')->rule('datef_y', 'digit')
                     ->rule('datet_m', 'digit')->rule('datet_d', 'digit')->rule('datet_y', 'digit');
                
                $field_data = $post->as_array(); // For form re-population
                
                if ($post->check()) {
                    
                    // Process results display parameters
                    if($field_data['datef_m'] > 0 AND $field_data['datef_y'] > 0 AND $field_data['datef_y'] > 0)
                        $result_params['date_from'] = mktime(0, 0, 0, $field_data['datef_m'], $field_data['datef_d'], $field_data['datef_y']);
                    
                    if($field_data['datet_m'] > 0 AND $field_data['datet_y'] > 0 AND $field_data['datet_y'] > 0)
                        $result_params['date_to'] = mktime(0, 0, 0, $field_data['datet_m'], $field_data['datet_d'], $field_data['datet_y']);
                    
                    $result_params['num_results'] = $field_data['num_results'];
                    $result_params['order'] = strtoupper($field_data['order']);
                    
                } else { 
                    $form_errors = $post->errors('results');
                } 
            } else {
                // Populate form w/ empty values
                $field_data = array(
                    'datef_m' => '', 'datef_d' => '', 'datef_y' => '', 
                    'datet_m' => '', 'datet_d' => '', 'datet_y' => '',
                    'num_results' => $result_params['num_results'],
                    'order' => $result_params['order']
                );
            } 
            $results = $this->model_gather->get_gather_log($project_id, $result_params);
            
            $view->page_content->field_data                = $field_data;
            $view->page_content->results                   = $results;
            $view->page_content->errors                    = $form_errors;
            $this->request->response = $view;
        }
        
    }
}
