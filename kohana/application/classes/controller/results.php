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
                'order' => 'desc'
            );
             
            // Get set of keyword_id corresponding to keyword/phrases for the project
            $keywords_phrases = $this->model_results->get_keywords_phrases($project_data['project_id']);
            // Create array to count total occurrences of each keyword
            foreach(array_keys($keywords_phrases) as $keyword_id) {
                $keyword_occurrences[$keyword_id] = 0;
            }
            
            $this->errors = "";
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
                    $this->errors = $post->errors('results');
                } 
            } else {
                // Populate form w/ empty values
                $field_data = array(
                    'datef_m' => '', 'datef_d' => '', 'datef_y' => '', 
                    'datet_m' => '', 'datet_d' => '', 'datet_y' => '',
                    'num_results' => 100,
                    'order' => 'desc',
                    'display' => 'individual entries'
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
            $view->page_content->errors                    = $this->errors;
            $view->page_content->clustered = $this->model_results->cluster_log_exists($project_id);
            $this->request->response = $view;
        }
        
        //
        // TO DO:
        // $highlighted_text = preg_replace("/\b($keyword)\b/ie", "<b>$keyword???</b>", "test.test tester; tbhis is a TEST; TEst ME.");
        // 
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
        // -> Might want to use OfflineCluster (but it only clusters first 100 docs in index)
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
            'date_clustered' => time()
        ));
        
        // Delete chart file if it exists
        $cluster_chartfile = Kohana::config('myconf.path.charts')."/cluster_$project_id.gch";
        if(file_exists($cluster_chartfile))
            unlink($cluster_chartfile);
        
        //echo "<br>Clustered ".count($cluster_data)." documents<br>";

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
        // TO DO: add stopwords file...???
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
            $project_data['cluster_threshold'] = $cluster_log['threshold'];
            $project_data['date_clustered'] = $cluster_log['date_clustered'];
            
            $view = View::factory('template');
            $view->page_title = $project_data['project_title']." - View Clusters";
            
            $view->page_content = View::factory('pages/cluster_view');
            
            $clusters = $this->get_cluster_data($project_id);
            
            // Find total number of singleton clusters
            $singleton_clusters = 0;
            foreach($clusters as $cluster) {
                if($cluster['num_docs'] == 1)
                    $singleton_clusters++;
            }
            
            // Get chart data & params
            $cluster_chartfile = Kohana::config('myconf.path.charts')."/cluster_$project_id.gch";
            if(!file_exists($cluster_chartfile)) {
                // Find max cluster size (for normalization)
                $clusters_sorted_desc = $this->order_array_num($clusters, 'num_docs', "DESC");
                foreach($clusters_sorted_desc as $cluster) {
                    $max_cluster_size = $cluster['num_docs'];
                    break;
                }
                $clusters_sorted_asc = array(); // ..To save memory
                
                // Generate chart data
                $clusters_sorted_asc = $this->order_array_num($clusters, 'num_docs');
                $num_clusters = 0;
                $x_vals = ""; $y_vals = ""; $size_vals = "";
                $min_cluster_size = 0;
                $min_dot_size = 4; // So the smallest can actually be seen
                foreach($clusters_sorted_asc as $cluster) {
                    if($cluster['num_docs'] > 1) {
                        $num_clusters++;
                        
                        if($min_cluster_size == 0)
                            $min_cluster_size = $cluster['num_docs'];
                       
                        // Normalize dot sizes they are between 0-100
                        if($max_cluster_size == $min_cluster_size) {
                            $dot_size_normalized = 50; // All clusters have size 1
                        } else {
                            $dot_size_normalized = (round((($cluster['num_docs'] - $min_cluster_size)/($max_cluster_size - $min_cluster_size)), 2))*100;
                            if($dot_size_normalized < $min_dot_size)
                                $dot_size_normalized = $min_dot_size;
                        }
                        
                        $cluster_quality = round(($cluster['total_score'] / $cluster['num_docs']), 2);
                        $x_vals .= "$num_clusters,";
                        $y_vals .= "$cluster_quality,";
                        $size_vals .= "$dot_size_normalized,";
                    }
                }
                
                // Remove trailing commas
                $x_vals = substr($x_vals, 0, -1); $y_vals = substr($y_vals, 0, -1); $size_vals = substr($size_vals, 0, -1); 
                
                /*
                    Make axis labels:
                    chxt=x,x,y,y
                    chxl=1:|<X-AXIS TITLE>|3:|<Y-AXIS TITLE>
                    chxp=1,50|3,50
                */
                
                //$num_clusters = round($num_clusters, -1); // Round range to nearest 10; DOES SOMETHING WIERD IF NUM < 10
                $chart_data = array(
                    "size" => "800x350", // `width` x `height` (in px)
                    "range" => "0,$num_clusters,0,1,1,100", // min,max(x-axis),min,max(y-axis),min,max(dot size)
                    "range_display" => "0,1,$num_clusters|1,0,1", // axis_id,min,max|...
                    "dot_style" => "o,0000FF,0,,80",
                    "data" => "t:$x_vals|$y_vals|$size_vals" // x-values | y-values | dot size (0-100)
                );
                
                // Save chart data as text file (.gch) to be read by show_chart.php
                $fh_chartfile = fopen($cluster_chartfile, 'w') or die("$cluster_chartfile: cannot open file for writing");
                fwrite($fh_chartfile, "cht=s\nchs=".$chart_data['size']."\nchxt=x,y\nchds=".$chart_data['range']."\nchxr=".$chart_data['range_display']."\nchm=".$chart_data['dot_style']."\nchd=".$chart_data['data']);
                fclose($fh_chartfile);
            }
            
            $view->page_content->project_data = $project_data;
            $view->page_content->singleton_clusters = $singleton_clusters;
            $view->page_content->chart_datafile = $cluster_chartfile;
            $this->request->response = $view;
        }
    }
    
    public function action_singleton_clusters($project_id = 0)
    {
        
    }
    
    // Get clustering data & re-organize results so there is only one row per metadata entry & calculate stats
    private function get_cluster_data($project_id, $result_params = 0) 
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
    
    private function order_array_num($array, $key, $order = "ASC") 
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