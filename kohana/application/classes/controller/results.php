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
        // Get project data
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
                //     ->rule('project_title', 'max_length', array(120));
                
                $this->field_data = $post->as_array(); // For form re-population
                
                if ($post->check()) {
                    
                    // Process results display parameters
                    if($this->field_data['datef_m'] > 0 && $this->field_data['datef_y'] > 0 && $this->field_data['datef_y'] > 0)
                        $result_params['date_from'] = mktime(0, 0, 0, $this->field_data['datef_m'], $this->field_data['datef_d'], $this->field_data['datef_y']);
                    
                    if($this->field_data['datet_m'] > 0 && $this->field_data['datet_y'] > 0 && $this->field_data['datet_y'] > 0)
                        $result_params['date_to'] = mktime(0, 0, 0, $this->field_data['datet_m'], $this->field_data['datet_d'], $this->field_data['datet_y']);
                    
                    if($this->field_data['num_results'] > 0) 
                        $result_params['num_results'] = $this->field_data['num_results'];
                    
                    $result_params['order'] = strtoupper($this->field_data['order']);
                    
                } else { 
                    $this->errors = $post->errors('results');
                } 
            } else {
                // Populate form w/ empty values
                $this->field_data = array(
                    'datef_m' => '', 'datef_d' => '', 'datef_y' => '', 
                    'datet_m' => '', 'datet_d' => '', 'datet_y' => '',
                    'num_results' => 100,
                    'order' => 'desc',
                    'display' => 'individual entries'
                );
            } 
            
            // Get results
            $results_db = $this->model_results->get_results($project_id, $result_params);
            
            // Re-organize results so there is only one row per metadata entry & calculate stats
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
            
            $view->page_content->field_data                = $this->field_data;
            $view->page_content->results                   = $results;
            $view->page_content->keywords_phrases          = $keywords_phrases;
            $view->page_content->keyword_occurrences       = $keyword_occurrences;
            $view->page_content->errors                    = $this->errors;
            $this->request->response = $view;
        }
        
        //
        // TO DO:
        // $highlighted_text = preg_replace("/\b($keyword)\b/ie", "<b>$keyword???</b>", "test.test tester; tbhis is a TEST; TEst ME.");
        // 
    }
}