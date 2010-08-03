<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Results extends Controller {
    
    public function before() {
        parent::before();
        $this->model_results = new Model_Results;
        $this->model_params  = new Model_Params;
    }
    
    public function action_index()
    {
        echo "Nothing to display.";
    }

    public function action_view($project_id = 0)
    {
        $project_data = $this->model_params->get_project_data($project_id);
        
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
                'display' => 'individual entries'
            );
             
            // Get set of keyword_id corresponding to keyword/phrases for the project
            $keywords_phrases = $this->model_results->get_keywords_phrases($project_data['project_id']);
            // Create array to count total occurrences of each keyword
            foreach(array_keys($keywords_phrases) as $keyword_id) {
                $keyword_occurrences[$keyword_id] = 0;
            }
            
            $form_errors = "";
            if($_POST) {
                // Form validation
                $post = new Validate($_POST);
                $post->rule('datef_m', 'digit')->rule('datef_d', 'digit')->rule('datef_y', 'digit')
                     ->rule('datet_m', 'digit')->rule('datet_d', 'digit')->rule('datet_y', 'digit');
                 
                $field_data = $post->as_array(); // For form re-population
                
                if ($post->check()) {
                    
                    // Process results display parameters
                    if($field_data['datef_m'] > 0 && $field_data['datef_y'] > 0 && $field_data['datef_y'] > 0)
                        $result_params['date_from'] = mktime(0, 0, 0, $field_data['datef_m'], $field_data['datef_d'], $field_data['datef_y']);
                    
                    if($field_data['datet_m'] > 0 && $field_data['datet_y'] > 0 && $field_data['datet_y'] > 0)
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
                    'order' => $result_params['order'],
                    'display' => $result_params['display']
                );
            } 
            
            // Get results & re-organize results so there is only one row per metadata entry & calculate stats
            $results_db = $this->model_results->get_results($project_id, $result_params);
            $results = array();
            foreach($results_db as $row) {
                if(array_key_exists($row['meta_id'], $results)) {
                    // Add keyword metadata to existing metadata entry
                    array_push($results[$row['meta_id']]['keywords'], 
                    array(
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
                        'keywords' => array(array(
                            'keyword' => $keywords_phrases[$row['keyword_id']],
                            'num_occurrences' => $row['num_occurrences']
                        ))
                    );
                }
                $keyword_occurrences[$row['keyword_id']] += $row['num_occurrences'];
            }
            
            $view->page_content->project_data              = $project_data;
            $view->page_content->field_data                = $field_data;
            $view->page_content->results                   = $results;
            $view->page_content->keywords_phrases          = $keywords_phrases;
            $view->page_content->keyword_occurrences       = $keyword_occurrences;
            $view->page_content->errors                    = $form_errors;
            $view->page_content->clustered = $this->model_results->cluster_log_exists($project_id);
            $this->request->response = $view;
        }
        
        //
        // TO DO:
        // $highlighted_text = preg_replace("/\b($keyword)\b/ie", "<b>$keyword???</b>", "test.test tester; tbhis is a TEST; TEst ME.");
        // 
    }
    
    public function action_trendline($project_id = 0)
    {
        $project_data = $this->model_params->get_project_data($project_id);
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $project_data = array_pop($project_data);
            
            $view = View::factory('template');
            $view->page_title = $project_data['project_title']." - View Trendline";
            
            $view->page_content = View::factory('pages/trendline_view');
            
            // Get chart data & params
            $chart_w = 800; 
            $chart_h = 350;
            $chartfile = Kohana::config('myconf.path.charts')."/trendline_$project_id.gch";
            //if(!file_exists($chartfile)) {
                $start_date = $this->model_results->metadata_edge_date($project_id, 'oldest');
                $end_date = $this->model_results->metadata_edge_date($project_id, 'most_recent');
                $trendline_data = $this->get_trendline_data($project_id, $start_date, $end_date);
                
                // Generate chart data
                $x_vals = ""; $y_vals = "";
                $num_days = 0;
                $max_entries = 0;
                //$trendline_data = array(3,4,8,0,2,9,10);
                foreach($trendline_data as $num_entries) {
                    $num_days++;
                    if($num_entries > $max_entries)
                        $max_entries = $num_entries;
                    
                    $x_vals .= "$num_days,";
                    $y_vals .= "$num_entries,";
                }
                // Remove trailing commas
                $x_vals = substr($x_vals, 0, -1); $y_vals = substr($y_vals, 0, -1);
                
                /***Make axis labels:
                    chxt=x,x,y,y
                    chxl=1:|<X-AXIS TITLE>|3:|<Y-AXIS TITLE>
                    chxp=1,50|3,50
                
                    chxt=x,y,x
                    chxl=
                    0:|Jan|July|Jan|July|Jan|
                    1:|0|50|100|
                    2:|2005|2006|2007
                ***/
                
                $chart_data = array(
                    "type" => "lxy",
                    "axes" => "x,y",
                    "size" => $chart_w."x".$chart_h, // `width` x `height` (in px)
                    "range" => "1,$num_days,0,$max_entries", // min,max(x-axis),min,max(y-axis)
                    "range_display" => "0,1,$num_days|1,0,$max_entries", // axis_id,min,max|...
                    "data" => "t:$x_vals|$y_vals"
                );
                
                // Save chart data as text file (.gch) to be read by show_chart.php
                $fh_chartfile = fopen($chartfile, 'w') or die("$chartfile: cannot open file for writing");
                fwrite($fh_chartfile, "cht=".$chart_data['type']."\nchs=".$chart_data['size']."\nchxt=".$chart_data['axes']."\nchds=".$chart_data['range']."\nchxr=".$chart_data['range_display']."\nchd=".$chart_data['data']);
                fclose($fh_chartfile);
            //}
            
            // Generate chart HTML
            $chid = md5(uniqid(rand(), true)); // Chart ID sent to Google Chart API
            $chart_html = '<img src="'.Kohana::config('myconf.url.show_chart').'?datafile='.$chartfile.'&chid='.$chid.'" width="'.$chart_w.'" height="'.$chart_h.'">';
            
            $date_range = date("M d, Y", $start_date)." (day 1) - ".date("M d, Y", $end_date)." (day $num_days)";
            
            $view->page_content->project_data = $project_data;
            $view->page_content->chart_html = $chart_html;
            $view->page_content->date_range = $date_range;
            $this->request->response = $view;
        }
    }
    
    private function get_trendline_data($project_id = 0, $start_date, $end_date)
    {
        // Gather total number of metadata entries from each day
        $trendline_data = array();
        $cur_date = mktime(0, 0, 0, date("m", $start_date), date("d", $start_date), date("Y", $start_date)); 
        $secs_in_day = 24*60*60;
        while($cur_date <= $end_date) {
            $num_metadata_entries = $this->model_results->num_metadata_entries($project_id, $cur_date, ($cur_date+$secs_in_day));
            array_push($trendline_data, $num_metadata_entries);
            $cur_date += $secs_in_day; 
        }
        return $trendline_data;
    }
    
    public function action_cluster($project_id = 0)
    {
        // The lower $cluster_threshold => the less clusters
        $cluster_threshold = 0.25;
        if($_POST) {
            $_POST['cluster_threshold'] = trim($_POST['cluster_threshold']);
            if(is_numeric($_POST['cluster_threshold']))
                $cluster_threshold = $_POST['cluster_threshold'];
        }
        
        // TO DO: parameterize clustering
        //        ex: limit to only docments within a certain time window
        
        // Build Lemur Index
        $this->build_lemur_index($project_id);
        
        //
        // Generate cluster params & perform clustering
        // -> NOTE: might want to use OfflineCluster (but it only clusters first 100 docs in index)
        // -> NOTE: must delete clusterIndex.cl (which is created in dir where script was executed)
        // 
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
        $this->model_results->delete_clusters($project_id);
        $this->model_results->insert_clusters($cluster_data, $project_id);
        
        $this->model_results->update_cluster_log(array(
            'project_id' => $project_id,
            'threshold' => $cluster_threshold,
            'num_docs' => count($cluster_data),
            'date_clustered' => time()
        ));
        
        // Delete chart file if it exists
        $chartfile = Kohana::config('myconf.path.charts')."/cluster_$project_id.gch";
        if(file_exists($chartfile))
            unlink($chartfile);
        
        // Redirect to cluster view
        $this->request->redirect("results/cluster_view/$project_id");
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
                if ($doc_filename != "." && $doc_filename != "..")
                    fwrite($fh_doclist, $this->docs_dir."/$doc_filename\n");
            }
            closedir($dh_docs); 
            fclose($fh_doclist);
        }
        
        // Generate index params & build index
        // TO DO: add stopwords file...
        $this->index_params = $this->params_dir."/index.params";
        $this->index_dir = Kohana::config('myconf.lemur.indexes')."/$project_id";
        if(!file_exists($this->index_params)) {
            $fh_index = fopen($this->index_params, 'w') or die($this->index_params.': cannot open file for writing');
            fwrite($fh_index, "<parameters>\n\t<index>".$this->index_dir."</index>\n\t<indexType>indri</indexType>\n\t<memory>512000000</memory>\n\t<dataFiles>".$this->docs_list."</dataFiles>\n\t<docFormat>trec</docFormat>\n\t<stemmer>krovetz</stemmer>\n</parameters>");
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
    
    public function action_cluster_view($project_id = 0)
    { 
        $project_data = $this->model_params->get_project_data($project_id);
        $cluster_log = $this->model_results->get_cluster_log($project_id);
        
        // Verify that project exists
        if(count($project_data) == 0 || count($cluster_log) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $project_data = array_pop($project_data);
            $cluster_log = array_pop($cluster_log);
            
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
            $chart_w = 800; 
            $chart_h = 350;
            $chartfile = Kohana::config('myconf.path.charts')."/cluster_$project_id.gch";
            if(!file_exists($chartfile)) {
                // Find max cluster size (for normalization)
                $clusters_sorted_desc = $this->order_array_numeric($clusters, 'num_docs', "DESC");
                foreach($clusters_sorted_desc as $cluster) {
                    $max_cluster_size = $cluster['num_docs'];
                    break;
                }
                $clusters_sorted_asc = array(); // ..To save memory
                
                // Generate chart data
                $clusters_sorted_asc = $this->order_array_numeric($clusters, 'num_docs');
                $num_clusters = 0;
                $x_vals = ""; $y_vals = ""; $size_vals = ""; $cluster_ids = ""; $cluster_sizes = "";
                $min_cluster_size = 0;
                $min_dot_size = 4; // So the smallest can actually be seen
                foreach($clusters_sorted_asc as $cluster_id => $cluster_data) {
                    if($cluster_data['num_docs'] > 1) {
                        $num_clusters++;
                        
                        if($min_cluster_size == 0)
                            $min_cluster_size = $cluster_data['num_docs'];
                       
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
                
                /***Make axis labels:
                    chxt=x,x,y,y
                    chxl=1:|<X-AXIS TITLE>|3:|<Y-AXIS TITLE>
                    chxp=1,50|3,50
                    chxs=... (axes label number formatting)
                ***/
                
                $chart_data = array(
                    "type" => "s",
                    "axes" => "x,y",
                    "size" => $chart_w."x".$chart_h, // `width` x `height` (in px)
                    "range" => "0,$num_clusters,0,1,1,100", // min,max(x-axis),min,max(y-axis),min,max(dot size)
                    "range_display" => "0,1,$num_clusters|1,0,1", // axis_id,min,max|...
                    "dot_style" => "o,0000FF,0,,80",
                    "data" => "t:$x_vals|$y_vals|$size_vals" // x-values | y-values | dot size (0-100)
                );
                
                // Save chart data as text file (.gch) to be read by show_chart.php
                $fh_chartfile = fopen($chartfile, 'w') or die("$chartfile: cannot open file for writing");
                fwrite($fh_chartfile, "cht=".$chart_data['type']."\nchs=".$chart_data['size']."\nchxt=".$chart_data['axes']."\nchds=".$chart_data['range']."\nchxr=".$chart_data['range_display']."\nchm=".$chart_data['dot_style']."\nchd=".$chart_data['data']."\nmpids=$cluster_ids\nmps=$cluster_sizes");
                fclose($fh_chartfile);
            }
            
            // Generate chart HTML
            $chid = md5(uniqid(rand(), true)); // Chart ID sent to Google Chart API
            $image_map_html = $this->generate_chart_map($project_id, $chid, $chartfile);
            $chart_html = '<img src="'.Kohana::config('myconf.url.show_chart').'?datafile='.$chartfile.'&chid='.$chid.'" width="'.$chart_w.'" height="'.$chart_h.'" usemap="#chart_map">
<map name="chart_map">'.$image_map_html.'</map>';
            
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
        //TO DO: add $result_params (date, specific keywords, etc)
        
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
    
    // Generate chart image map HTML (make plot clickable)
    private function generate_chart_map($project_id, $chid, $chart_file) 
    {
        $api_url = "http://chart.apis.google.com/chart?chid=$chid";

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
        
        $image_map_html = "";
        $json = json_decode($response, true);
        $num_results = count($json['chartshape']);
        if($num_results > 0) {
            $i = 0;
            foreach($json['chartshape'] as $map_item) {
                if($map_item['type'] == "CIRCLE") {
                    $coords_str = implode(",", $map_item['coords']);
                    $title = $cluster_sizes[$i]." documents (score: ".$cluster_scores[$i].")";
                    $href = "javascript:startLyteframe('".$title."', '".Url::base()."index.php/results/cluster_summary/$project_id/".$cluster_ids[$i]."')";
                    $image_map_html .= '<area name="'.$map_item['name'].'" shape="'.$map_item['type'].'" coords="'.$coords_str.'" href="'.$href.'" title="'.$title.'">';
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
            $post = new Validate($_POST);
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