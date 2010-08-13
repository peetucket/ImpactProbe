<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Results extends Controller {
    
    public function before() {
        parent::before();
        $this->model_results = new Model_Results;
        
        $this->chart_api_url = "http://chart.apis.google.com/chart";
        $this->date_format = "M-d-Y";
        $this->date_format_chart = 'MMM-dd-yyyy';
        $this->chart_w = 800; // (in px) > 800px may cause problems 
        $this->chart_h = 370; // (in px) > 370px may cause problems
    }
    
    public function action_index()
    {
        echo '<a href="'.Url::base().'">&laquo; Back to home</a><p>Nothing to display.</p>';
    }

    public function action_view($project_id = 0)
    {
        $project_data = $this->model_results->get_project_data($project_id);
        
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $project_data = array_pop($project_data);
            
            $view = View::factory('template');
            $view->page_title = $project_data['project_title']." - View Results";
            
            $view->page_content = View::factory('pages/results_basic');
            
            // Default results display
            $result_params = array(
                'date_from' => 0, 'date_to' => 0,
                'num_results' => 100,
                'order' => 'desc',
                'download_mode' => 'summary_csv'
            );
             
            $form_errors = "";
            $download_results = 0;
            if($_POST) {
                // Form validation
                $post = Validate::factory($_POST)
                      ->rule('datef_m', 'digit')->rule('datef_d', 'digit')->rule('datef_y', 'digit')
                      ->rule('datet_m', 'digit')->rule('datet_d', 'digit')->rule('datet_y', 'digit');
                 
                $field_data = $post->as_array(); // For form re-population
                
                if ($post->check()) {
                    
                    // Process results display parameters
                    if($field_data['datef_m'] > 0 AND $field_data['datef_y'] > 0 AND $field_data['datef_y'] > 0)
                        $result_params['date_from'] = mktime(0, 0, 0, $field_data['datef_m'], $field_data['datef_d'], $field_data['datef_y']);
                    
                    if($field_data['datet_m'] > 0 AND $field_data['datet_y'] > 0 AND $field_data['datet_y'] > 0)
                        $result_params['date_to'] = mktime(0, 0, 0, $field_data['datet_m'], $field_data['datet_d'], $field_data['datet_y']);
                    
                    if(array_key_exists('Download', $field_data)) // Download button was clicked
                        $download_results = 1;
                    
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
                    'order' => $result_params['order'],
                    'download_mode' => $result_params['download_mode']
                );
            } 
            
            $total_results = $this->model_results->num_metadata_entries($project_id, $result_params['date_from'], $result_params['date_to']);
            
            // Get set of keyword_id's corresponding to each keyword/phrase in project
            $keywords_phrases = $this->model_results->get_keywords_phrases($project_data['project_id']);
            
            // Determine date published range
            $edge_date_to = $this->model_results->metadata_edge_date($project_id, 'most_recent');
            $edge_date_from = $this->model_results->metadata_edge_date($project_id, 'oldest');
            if($result_params['date_from'] > 0 AND $result_params['date_from'] > $edge_date_from)
                $edge_date_from = $result_params['date_from'];
            if($result_params['date_to'] > 0 AND $result_params['date_to'] < $edge_date_to)
                $edge_date_to = $result_params['date_to'];
            $date_published_range = date($this->date_format, $edge_date_from)." - ".date($this->date_format, $edge_date_to);
            
            if($download_results) {
                // Generate result output file
                if($result_params['download_mode'] == 'summary_csv') {
                    $download_type = 'text/csv'; $download_ext = 'csv';
                    $download_file = Kohana::config('myconf.path.charts')."/results_$project_id.$download_ext";
                    
                    // TO DO: write out results in stints of 500!!! and append 'a' output file
                    $result_params['num_results'] = $total_results; // So it outputs ALL results (within given date range)
                    list($results, $keyword_occurrence_totals) = $this->generate_results_array($project_id, $keywords_phrases, $result_params);
                    
                    $this->write_results_csv($download_file, $keywords_phrases, $results);
                } else {
                    $download_type = 'text/plain'; $download_ext = 'txt';
                    $download_file = Kohana::config('myconf.path.charts')."/results_$project_id.$download_ext";
                    
                    // TO DO!!!
                    // ...
                }
                
                // Begin download
                $download_filename = $result_params['download_mode']."_".date($this->date_format, $edge_date_from)."_".date($this->date_format, $edge_date_to);
                $this->request->redirect(Kohana::config('myconf.url.download')."?file=$download_file&type=$download_type&name=$download_filename.$download_ext&delete_file=1");
            } else {
                // Get array of results to display
                list($results, $keyword_occurrence_totals) = $this->generate_results_array($project_id, $keywords_phrases, $result_params);
            }
            
            $view->page_content->project_data              = $project_data;
            $view->page_content->field_data                = $field_data;
            $view->page_content->date_format               = $this->date_format;
            $view->page_content->results                   = $results;
            $view->page_content->total_results             = $total_results;
            $view->page_content->date_published_range      = $date_published_range;
            $view->page_content->keywords_phrases          = $keywords_phrases;
            $view->page_content->keyword_occurrence_totals = $keyword_occurrence_totals;
            $view->page_content->errors                    = $form_errors;
            $view->page_content->clustered = $this->model_results->cluster_log_exists($project_id);
            $this->request->response = $view;
        }
    }
    
    // Get results & re-organize them so there is only one row per metadata entry & calculate keyword stats
    private function generate_results_array($project_id, $keywords_phrases, $result_params) {
        // Create array to count total occurrences of each keyword
        $keyword_occurrence_totals = array();
        foreach(array_keys($keywords_phrases) as $keyword_id) {
            $keyword_occurrence_totals[$keyword_id] = 0;
        }
        
        $results = array();
        $i = 1;
        $result_params['limit'] = 500;
        $result_params['offset'] = 0;
        do { // Collect 500 database results at a time to prevent MySQL SELECT failure
            $results_db = $this->model_results->get_results($project_id, $result_params);
            
            $result_params['offset'] += $result_params['limit'];
            $num_sub_results = count($results_db);
            foreach($results_db as $row) {
                
                // Do not generate full results display (only summary) if displaying 'all' results
                if($result_params['num_results'] > 0) {
                    if($i > $result_params['num_results'])
                        break;
                     
                    if(array_key_exists($row['meta_id'], $results)) {
                        // Add keyword metadata to existing metadata entry
                        array_push($results[$row['meta_id']]['keywords_phrases'], 
                        array(
                            'keyword_id' => $row['keyword_id'],
                            'keyword' => $keywords_phrases[$row['keyword_id']],
                            'num_occurrences' => $row['num_occurrences']
                        ));
                    } else {
                        // Add new metadata entry
                        $results[$row['meta_id']] = array(
                            'meta_id' => $row['meta_id'],
                            'url' => $row['url'],
                            'api_name' => $row['api_name'],
                            'date_retrieved' => $row['date_retrieved'],
                            'date_published' => $row['date_published'],
                            'total_words' => $row['total_words'],
                            'keywords_phrases' => array(array(
                                'keyword_id' => $row['keyword_id'],
                                'keyword' => $keywords_phrases[$row['keyword_id']],
                                'num_occurrences' => $row['num_occurrences']
                            ))
                        );
                        $i++;
                    }
                }
                $keyword_occurrence_totals[$row['keyword_id']] += $row['num_occurrences'];
            }
        } while($num_sub_results == $result_params['limit']);
        return array($results, $keyword_occurrence_totals);
    }
    
    // Create csv file of results 
    private function write_results_csv($download_file, $keywords_phrases, $results) {
        $fh_download_file = fopen($download_file, 'w') or die("$download_file: cannot open file for writing");
        fwrite($fh_download_file, "Date published,Date retrieved,URL");
        // Create a col to store occurrances of each keyword/phrase
        foreach($keywords_phrases as $keyword_phrase) {
            fwrite($fh_download_file, ",$keyword_phrase");
        }
        fwrite($fh_download_file, "\n");
        foreach($results as $result) {
            $date_published = ($result['date_published'] > 0) ? date($this->date_format, $result['date_published']) : 'N/A';
            $date_retrieved = date($this->date_format, $result['date_retrieved']);
            fwrite($fh_download_file, "$date_published,$date_retrieved,".$result['url']);
            // Put occurrances of each keyword/phrase in its own column
            foreach(array_keys($keywords_phrases) as $keyword_id) {
                // Check if keyword occurrances value exists for this entry (if not occurrances = 0)
                $num_occurrances_s = 0;
                foreach($result['keywords_phrases'] as $keyword_phrase_s) {
                    if($keyword_phrase_s['keyword_id'] == $keyword_id)
                        $num_occurrances_s = $keyword_phrase_s['num_occurrences'];
                }
                fwrite($fh_download_file, ",$num_occurrances_s");
            }
            fwrite($fh_download_file, "\n");
        } 
        fclose($fh_download_file);
    }
    
    // TO DO: allow user to view document with keywords_phrases highlighted
    public function action_view_document($project_id = 0, $meta_id = 0)
    {
        $view = View::factory('pages/view_document');
        
        $text = $this->model_results->get_cached_text($meta_id);
        /* 
        //TO DO: bold each keyword in document  
        $keywords_phrases = $this->model_results->get_keywords_phrases($project_id);
        foreach($keywords_phrases as $keyword_phrase) {
            $text = preg_replace("/\b($keyword_phrase)\b/ie", "<b>$keyword_phrase</b>", $text); // Doesn't work!
        }
        */
        $text = $this->make_urls_clickable($text);
        $view->text = $text;
        $this->request->response = $view;
    } 
    
    public function action_trendline($project_id = 0)
    {
        $project_data = $this->model_results->get_project_data($project_id);
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $project_data = array_pop($project_data);
            
            $view = View::factory('template');
            $view->page_title = $project_data['project_title']." - View Trendline";
            
            $view->page_content = View::factory('pages/trendline_view');
            
            // Default results display
            $result_params = array(
                'date_from' => 0, 'date_to' => 0,
                'display_mode' => 'consensus'
            );
            
            $form_errors = "";
            $download_results = 0;
            if($_POST) {
                // Form validation
                $post = Validate::factory($_POST)
                      ->rule('datef_m', 'digit')->rule('datef_d', 'digit')->rule('datef_y', 'digit')
                      ->rule('datet_m', 'digit')->rule('datet_d', 'digit')->rule('datet_y', 'digit');
                 
                $field_data = $post->as_array(); // For form re-population
                
                if ($post->check()) {
                    // Process results display parameters
                    if($field_data['datef_m'] > 0 AND $field_data['datef_y'] > 0 AND $field_data['datef_y'] > 0)
                        $result_params['date_from'] = mktime(0, 0, 0, $field_data['datef_m'], $field_data['datef_d'], $field_data['datef_y']);
                    
                    if($field_data['datet_m'] > 0 AND $field_data['datet_y'] > 0 AND $field_data['datet_y'] > 0)
                        $result_params['date_to'] = mktime(0, 0, 0, $field_data['datet_m'], $field_data['datet_d'], $field_data['datet_y']);
                    
                    $result_params['display_mode'] = $field_data['display_mode'];
                    
                    if(array_key_exists('Download', $field_data)) // Download button was clicked
                        $download_results = 1;
                    
                } else { 
                    $form_errors = $post->errors('results');
                } 
            } else {
                // Populate form w/ empty values
                $field_data = array(
                    'datef_m' => '', 'datef_d' => '', 'datef_y' => '', 
                    'datet_m' => '', 'datet_d' => '', 'datet_y' => '',
                    'display_mode' => $result_params['display_mode']
                );
            } 
            
            // Get chart data & params
            if($result_params['date_from'] == 0)
                $result_params['date_from'] = $this->model_results->metadata_edge_date($project_id, 'oldest');
            if($result_params['date_to'] == 0)
                $result_params['date_to'] = $this->model_results->metadata_edge_date($project_id, 'most_recent');
            
            if($download_results) {
                // Generate .csv file
                $csv_file = Kohana::config('myconf.path.charts')."/trendline_$project_id.csv";
                $fh_csv_file = fopen($csv_file, 'w') or die("$csv_file: cannot open file for writing");

                if($result_params['display_mode'] == 'by_keyword') {
                    fwrite($fh_csv_file, $this->get_trendline_data_by_keyword($project_id, $result_params['date_from'], $result_params['date_to'], 'csv'));
                } else {
                    fwrite($fh_csv_file, $this->get_trendline_data_consensus($project_id, $result_params['date_from'], $result_params['date_to'], 'csv'));
                }
                fclose($fh_csv_file);
                
                // Begin download
                $csv_filename = ($result_params['display_mode'] == 'by_keyword') ? 'trendline_by_keyword_' : 'trendline_consensus_';
                $csv_filename .= date($this->date_format, $result_params['date_from'])."_".date($this->date_format, $result_params['date_to']);
                $this->request->redirect(Kohana::config('myconf.url.download')."?file=$csv_file&type=text/csv&name=$csv_filename.csv&delete_file=1");
                
            } else {
                // Generate chart data (as javascript)
                if($result_params['display_mode'] == 'by_keyword') {
                    $chart_data_js = $this->get_trendline_data_by_keyword($project_id, $result_params['date_from'], $result_params['date_to'], 'chart_js');
                } else {
                    $chart_data_js = $this->get_trendline_data_consensus($project_id, $result_params['date_from'], $result_params['date_to'], 'chart_js');
                }
            } 
            
            $view->page_content->errors = $form_errors;
            $view->page_content->field_data = $field_data;
            $view->page_content->project_data = $project_data;
            $view->page_content->chart_data_js = $chart_data_js;
            $view->page_content->chart_dimensions = "width: ".$this->chart_w."px; height: ".$this->chart_h."px;";
            $view->page_content->date_range = date($this->date_format, $result_params['date_from'])." - ".date($this->date_format, $result_params['date_to']);
            $view->page_content->date_format_chart = $this->date_format_chart;
            $this->request->response = $view;
        }
    }
    
    private function get_trendline_data_consensus($project_id = 0, $date_from, $date_to, $output_mode = 'chart_js')
    {
        $trendline_data = ($output_mode == 'csv') ? "Date,Number of results\n" : "data.addColumn('date', 'Date');\ndata.addColumn('number', 'Consensus:');\ndata.addRows([\n";
        
        // Gather total number of metadata entries from each day
        $cur_date = mktime(0, 0, 0, date("m", $date_from), date("d", $date_from), date("Y", $date_from)); 
        $secs_in_day = 24*60*60;
        while($cur_date <= $date_to) {
            $num_metadata_entries = $this->model_results->num_metadata_entries($project_id, $cur_date, ($cur_date+$secs_in_day));
            if($output_mode == 'csv') {
                $trendline_data .= date($this->date_format, $cur_date).",$num_metadata_entries\n";
            } else {
                // Date format (year, month (0-11), day)
                $trendline_data .= "\t[new Date(".date("Y", $cur_date).",".(date("m", $cur_date)-1).",".date("d", $cur_date)."), $num_metadata_entries],\n";
            }
            $cur_date += $secs_in_day;
        }
        $trendline_data .= ($output_mode == 'csv') ? "" : "]);\n";
        return $trendline_data;
    }
    
    private function get_trendline_data_by_keyword($project_id = 0, $date_from, $date_to, $output_mode = 'chart_js')
    {
        $trendline_data = ($output_mode == 'csv') ? "Date," : "data.addColumn('date', 'Date');\n";
        
        $keywords_phrases = $this->model_results->get_keywords_phrases($project_id);
        foreach($keywords_phrases as $keyword_phrase) {
            if($output_mode == 'csv') {
                $trendline_data .= "$keyword_phrase,";
            } else {
                $trendline_data .= "data.addColumn('number', '$keyword_phrase:');\n";
            } 
        }
        if($output_mode == 'csv') {
            $trendline_data = substr($trendline_data, 0, -1); // Remove trailing comma
            $trendline_data .= "\n";
        } else {
            $trendline_data .= "data.addRows([\n";
        }
        
        // Gather total number of metadata entries from each day
        $cur_date = mktime(0, 0, 0, date("m", $date_from), date("d", $date_from), date("Y", $date_from)); 
        $secs_in_day = 24*60*60;
        while($cur_date <= $date_to) {
            if($output_mode == 'csv') {
                $trendline_data .= date($this->date_format, $cur_date).",";
            } else {
                // Date format (year, month (0-11), day)
                $trendline_data .= "\t[new Date(".date("Y", $cur_date).",".(date("m", $cur_date)-1).",".date("d", $cur_date)."),";
            }
            foreach(array_keys($keywords_phrases) as $keyword_id) {
                $num_metadata_entries = $this->model_results->num_metadata_entries_by_keyword($project_id, $keyword_id, $cur_date, ($cur_date+$secs_in_day));
                $trendline_data .= "$num_metadata_entries,";
            }
            $trendline_data = substr($trendline_data, 0, -1); // Remove trailing comma
            $trendline_data .= ($output_mode == 'csv') ? "\n" : "],\n";
            $cur_date += $secs_in_day; 
        }
        $trendline_data .= ($output_mode == 'csv') ? "" : "]);\n";
        return $trendline_data;
    }
    
    public function action_cluster($project_id = 0)
    {
        // The lower $cluster_threshold => the less clusters
        $cluster_threshold = 0.25;
        $cluster_order = 'arbitrarily';
        if($_POST) {
            $_POST['cluster_threshold'] = trim($_POST['cluster_threshold']);
            if(is_numeric($_POST['cluster_threshold']))
                $cluster_threshold = $_POST['cluster_threshold'];
            if($_POST['cluster_order'] == 'cluster_size')
                $cluster_order = 'cluster_size';
        }
        
        // TO DO: parameterize clustering
        //        ex: limit to only docments within a certain time window
        
        $this->build_lemur_index($project_id);
        
        // Generate cluster params & perform clustering
        // NOTE: must delete clusterIndex.cl in order to re-cluster (which is created in dir where script was executed)
        $this->cluster_params = $this->params_dir."/cluster.params";
        $fh_cluster_params = fopen($this->cluster_params, 'w') or die($this->cluster_params.': cannot open file for writing');
        fwrite($fh_cluster_params, "<parameters>\n\t<index>".$this->index_dir."</index>\n\t<threshold>$cluster_threshold</threshold>\n</parameters>");
        fclose($fh_cluster_params);
        
        chdir($this->index_dir); // clusterIndex.cl file will be created here
        $system_cmd = Kohana::config('myconf.lemur.bin')."/Cluster ".$this->cluster_params; // 2>&1 = put stderr in stdout
        exec($system_cmd, $cluster_data, $return_code);
        if($return_code != 0) {
            echo "Error when running command &lt;$system_cmd&gt;: $return_code<br>";
            exit;
        }
        
        // Delete old cluster data & add new cluster data to database
        //set_time_limit(40); // Extend max execution time by 40 secs
        $this->model_results->delete_clusters($project_id);
        $this->model_results->insert_clusters($cluster_data, $project_id);
        
        $this->model_results->update_cluster_log(array(
            'project_id' => $project_id,
            'threshold' => $cluster_threshold,
            'order' => $cluster_order,
            'num_docs' => count($cluster_data),
            'date_clustered' => time()
        ));
        
        // Delete chart file if it exists
        $chart_file = Kohana::config('myconf.path.charts')."/cluster_$project_id.gch";
        if(file_exists($chart_file))
            unlink($chart_file);
        
        // Redirect to cluster view
        //$this->request->redirect("results/cluster_view/$project_id/$cluster_order");
    }

    // Build Lemur Index from a directory of cached text documents
    private function build_lemur_index($project_id) 
    {
        // Ensure dir of text docs exists
        $this->docs_dir = Kohana::config('myconf.lemur.docs')."/$project_id";
        if(!is_dir($this->docs_dir)) {
            echo $this->docs_dir.": directory does not exist. Cannot continue.<br>";
            exit;
        }
        
        // Create params directory if it does not exist already
        $this->params_dir = Kohana::config('myconf.lemur.params')."/$project_id";
        if(!is_dir($this->params_dir))
            mkdir($this->params_dir);
        
        if($dh_docs = opendir($this->docs_dir)) {
            // Create list of documents to index (overwrite existing)
            $this->docs_list = $this->params_dir.'/index.list';
            $fh_doclist = fopen($this->docs_list, 'w') or die($this->docs_list.': cannot open file for writing');
            while (false !== ($doc_filename = readdir($dh_docs))) {
                if ($doc_filename != "." AND $doc_filename != "..")
                    fwrite($fh_doclist, $this->docs_dir."/$doc_filename\n");
            }
            closedir($dh_docs); 
            fclose($fh_doclist);
        }
        
        // Generate index params & build index
        $this->index_params = $this->params_dir."/index.params";
        $this->index_dir = Kohana::config('myconf.lemur.indexes')."/$project_id";
        if(!file_exists($this->index_params)) {
            $fh_index = fopen($this->index_params, 'w') or die($this->index_params.': cannot open file for writing');
            fwrite($fh_index, "<parameters>\n\t<index>".$this->index_dir."</index>\n\t<indexType>indri</indexType>\n\t<memory>512000000</memory>\n\t<dataFiles>".$this->docs_list."</dataFiles>\n\t<stopwords>".Kohana::config('myconf.lemur.stopwords_list')."</stopwords>\n\t<docFormat>trec</docFormat>\n\t<stemmer>krovetz</stemmer>\n</parameters>");
            fclose($fh_index);
        }
        
        // Remove old index directory (containing clusterIndex.cl) otherwise we will get duplicate entries
        if (is_dir($this->index_dir)) {
            $system_cmd = "rm -r ".$this->index_dir;
            system($system_cmd, $return_code);
            if($return_code != 0) {
                echo "Error when running command &lt;$system_cmd&gt;: $return_code<br>";
                exit;
            }
        }
        
        // Ensure directory where indexes are created (Kohana::config('myconf.lemur.indexes')) has 777 permissions (writeable)
        $system_cmd = Kohana::config('myconf.lemur.bin')."/BuildIndex ".$this->index_params; // 2>&1 = put stderr in stdout
        system($system_cmd, $return_code);
        if($return_code != 0) {
            echo "Error when running command &lt;$system_cmd&gt;: $return_code<br>";
            exit;
        }
    }
    
    public function action_cluster_view($project_id = 0, $cluster_order = 'arbitrarily')
    { 
        $project_data = $this->model_results->get_project_data($project_id);
        $cluster_log = $this->model_results->get_cluster_log($project_id);
        
        // Verify that project exists
        if(count($project_data) == 0 || count($cluster_log) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $project_data = array_pop($project_data);
            $cluster_log = array_pop($cluster_log);
            $cluster_log['date_clustered'] = date($this->date_format, $cluster_log['date_clustered']);
            
            $view = View::factory('template');
            $view->page_title = $project_data['project_title']." - View Clusters";
            
            $view->page_content = View::factory('pages/cluster_view');
            
            $clusters = $this->get_cluster_metadata($project_id);
            
            // Find total number of singleton clusters
            $singleton_clusters = 0;
            foreach($clusters as $cluster) {
                if($cluster['num_docs'] == 1)
                    $singleton_clusters++;
            }
            
            // Get chart data & params
            $chart_file = Kohana::config('myconf.path.charts')."/cluster_$project_id.gch";
            if(!file_exists($chart_file)) {
                // Find max & min cluster sizes (for normalization)
                $clusters_sorted_asc = $this->order_array_numeric($clusters, 'num_docs', 'ASC');
                $min_cluster_size_data = current($clusters_sorted_asc);
                $min_cluster_size = $min_cluster_size_data['num_docs'];
                $max_cluster_size_data = end($clusters_sorted_asc);
                $max_cluster_size = $max_cluster_size_data['num_docs'];
                
                // Check if user selected to order points by cluster size
                if($cluster_order == 'cluster_size')
                    $clusters = $clusters_sorted_asc;
                unset($clusters_sorted_asc); // ..To save memory
                
                // Generate chart data
                $num_clusters = 0;
                $x_vals = ""; $y_vals = ""; $size_vals = ""; $cluster_ids = ""; $cluster_sizes = "";
                $min_dot_size = 4; // So the smallest can actually be seen
                foreach($clusters as $cluster_id => $cluster_data) {
                    if($cluster_data['num_docs'] > 1) {
                        $num_clusters++;
                       
                        // Normalize dot sizes they are between 0-100
                        if($max_cluster_size == $min_cluster_size) {
                            $dot_size_normalized = 50; // All clusters have size 1
                        } else {
                            $dot_size_normalized = (round((($cluster_data['num_docs'] - $min_cluster_size)/($max_cluster_size - $min_cluster_size)), 2))*100;
                            if($dot_size_normalized < $min_dot_size)
                                $dot_size_normalized = $min_dot_size;
                        }
                        
                        $cluster_quality = round(($cluster_data['total_score'] / $cluster_data['num_docs']), 2);
                        $cluster_ids .= "$cluster_id,";
                        $cluster_sizes .= $cluster_data['num_docs'].",";
                        $x_vals .= "$num_clusters,";
                        $y_vals .= "$cluster_quality,";
                        $size_vals .= "$dot_size_normalized,";
                    }
                }
                // Remove trailing commas
                $x_vals = substr($x_vals, 0, -1); $y_vals = substr($y_vals, 0, -1); $size_vals = substr($size_vals, 0, -1); $cluster_ids = substr($cluster_ids, 0, -1); $cluster_sizes = substr($cluster_sizes, 0, -1); 
                
                $x_axis_midpoint = round(($num_clusters/2));
                $chart_data = array(
                    "type" => "s",
                    "axes" => "x,x,y,y",
                    "axis_labels" => "1:|Cluster number|3:|Lowest quality|Highest quality",
                    "label_pos" => "1,50|3,0,100",
                    "size" => $this->chart_w."x".$this->chart_h, // `width` x `height` (in px)
                    "range" => "0,$num_clusters,0,1,1,100", // min,max(x-axis), min,max(y-axis), min,max(dot size)
                    "range_display" => "0,1,$num_clusters|2,0,1", // axis_id,min,max|...
                    "dot_style" => "o,0000FF,0,,80",
                    "data" => "t:$x_vals|$y_vals|$size_vals" // x-values | y-values | dot size (0-100)
                );
                
                // Save chart data as text file (.gch) to be read by show_chart.php
                $fh_chartfile = fopen($chart_file, 'w') or die("$chart_file: cannot open file for writing");
                fwrite($fh_chartfile, "cht=".$chart_data['type']."\nchs=".$chart_data['size']."\nchxt=".$chart_data['axes']."\nchxl=".$chart_data['axis_labels']."\nchxp=".$chart_data['label_pos']."\nchds=".$chart_data['range']."\nchxr=".$chart_data['range_display']."\nchm=".$chart_data['dot_style']."\nchd=".$chart_data['data']."\nmpids=$cluster_ids\nmps=$cluster_sizes");
                fclose($fh_chartfile);
            }
            
            // Generate chart HTML
            $chid = md5(uniqid(rand(), true)); // Chart ID sent to Google Chart API
            $chart_html = '<div><img src="'.Kohana::config('myconf.url.show_chart').'?datafile='.$chart_file.'&chid='.$chid.'" width="'.$this->chart_w.'" height="'.$this->chart_h.'" class="mapper" usemap="#chart_map"></div>
<map name="chart_map">'.$this->generate_cluster_map($project_id, $chid, $chart_file).'</map>';
            
            $view->page_content->project_data = $project_data;
            $view->page_content->cluster_log = $cluster_log;
            $view->page_content->singleton_clusters = $singleton_clusters;
            $view->page_content->chart_html = $chart_html;
            $this->request->response = $view;
        }
    }
    
    // Get clustering data & re-organize results so there is only one row per metadata entry & calculate stats
    private function get_cluster_metadata($project_id, $result_params = 0) 
    {
        //TO DO: add $result_params (date range, specific keywords_phrases, etc)
        
        $cluster_db = $this->model_results->get_clusters($project_id);
        
        $clusters = array();
        foreach($cluster_db as $row) {
            if(array_key_exists($row['cluster_id'], $clusters)) {
                // Add cluster ata to existing cluster entry
                array_push($clusters[$row['cluster_id']]['docs'], array($row['meta_id'] => $row['score']));
                $clusters[$row['cluster_id']]['total_score'] += $row['score'];
                $clusters[$row['cluster_id']]['num_docs']++;
            } else {
                // Add new cluster entry
                $clusters[$row['cluster_id']] = array(
                    'docs' => array($row['meta_id'] => $row['score']),
                    'total_score' => $row['score'],
                    'num_docs' => 1
                );
            }
        }
        return $clusters;
    }
    
    // Generate chart image map HTML for cluster plot (make plot clickable)
    private function generate_cluster_map($project_id, $chid, $chart_file) 
    {
        $api_url = $this->chart_api_url."?chid=$chid";

        // Open chart file and extract data
        $file_handle = fopen($chart_file, "r");
        $chart_params = array();
        while (!feof($file_handle)) {
            $line = rtrim(fgets($file_handle));
            $param_ex = explode("=", $line);
            $param_name = $param_ex[0]; 
            $param_vals = $param_ex[1];
            if($param_name == "mpids") { 
                // List of cluster_ids in order displayed on chart
                $cluster_ids = explode(",", $param_vals);
            } else if($param_name == "mps") {
                // List of cluster sizes (number of documents) in order displayed on chart
                $cluster_sizes = explode(",", $param_vals);
            } else { 
                // Parameter is chart param 
                $chart_params[$param_name] = $param_vals;
                if($param_name == "chd") {
                    $chd_ex = explode("|", substr($param_vals, 2));
                    $cluster_scores = explode(",", $chd_ex[1]);
                }
            }
        }
        fclose($file_handle);
        $chart_params['chof'] = 'json'; // tell API to return image map HTML
        
        // Send the POST request, parse json data, & compile image map HTML
        $response = Remote::get($api_url, array(
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => http_build_query($chart_params)
        ));
        
        $image_map_html = '';
        $json = json_decode($response, true);
        $num_results = count($json['chartshape']);
        if($num_results > 0) {
            $i = 0;
            foreach($json['chartshape'] as $map_item) {
                if($map_item['type'] == "CIRCLE") {
                    $coords_str = implode(",", $map_item['coords']);
                    $title = $cluster_sizes[$i]." documents (score: ".$cluster_scores[$i].")";
                    $href = "javascript:startLyteframe('".$title."', '".Url::base()."index.php/results/cluster_summary/$project_id/".$cluster_ids[$i]."')";
                    $image_map_html .= '<area name="'.$map_item['name'].'" shape="'.$map_item['type'].'" class="noborder icolorff0000" coords="'.$coords_str.'" href="'.$href.'" title="'.$title.'">';
                    $i++;
                }
            }
        }
        return $image_map_html;
    }
    
    public function action_singleton_clusters($project_id = 0)
    {
        $view = View::factory('pages/cluster_text');
        
        $clusters = $this->get_cluster_metadata($project_id);
        $singleton_clusters = array();
        foreach($clusters as $cluster) {
            if($cluster['num_docs'] == 1) {
                $meta_id = key($cluster['docs']);
                $text = $this->model_results->get_cached_text($meta_id);
                array_push($singleton_clusters, array(
                    'meta_id' => $meta_id,
                    'text' => $text
                ));
            }
        }
        
        $view->singleton_display = 1;
        $view->cluster_data = $singleton_clusters;
        $this->request->response = $view;
    }
    
    public function action_cluster_summary($project_id, $cluster_id)
    {
        $view = View::factory('pages/cluster_text');
        
        // Default results display
        $params = array(
            'num_results' => 25,
            'score_order' => 'desc'
        );
        
        $form_errors = "";
        if($_POST) {
            $post = Validate::factory($_POST);
            $field_data = $post->as_array(); // For form re-population
            
            // TO DO: Form validation
            //if ($post->check()) { } else { $form_errors = $post->errors('results'); }
            
            // Process results display parameters
            $params['num_results'] = $field_data['num_results'];
            $params['score_order'] = strtoupper($field_data['score_order']);
        } else {
            // Populate form w/ empty values
            $field_data = array(
                'num_results' => $params['num_results'],
                'score_order' => $params['score_order']
            );
        } 
        
        $view->singleton_display = 0;
        $view->field_data = $field_data;
        $view->errors = $form_errors;
        $view->cluster_data = $this->model_results->get_cluster_summary($project_id, $cluster_id, $params);
        $this->request->response = $view;
    }
    
    public function make_urls_clickable($text) 
    { 
        $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1" target="_blank">\\1</a>', $text); 
        $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2" target="_blank">\\2</a>', $text);
        return $text; 
    }
    
    private function order_array_numeric($array, $key, $order = "ASC") 
    { 
        $tmp = array(); 
        foreach($array as $akey => $array2) { 
            $tmp[$akey] = $array2[$key]; 
        } 
        
        if($order == "DESC")
            arsort($tmp, SORT_NUMERIC);
        else 
            asort($tmp, SORT_NUMERIC);

        $tmp2 = array();
        foreach($tmp as $key => $value) { 
            $tmp2[$key] = $array[$key]; 
        }
        return $tmp2; 
    } 
}