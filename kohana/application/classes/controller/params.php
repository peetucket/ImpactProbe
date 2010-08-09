<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Params extends Controller {
    
    public function before() {
        parent::before();
        $this->model_params = new Model_Params;
    }
    
    public function action_index()
    {
        $this->request->redirect('params/new'); // Redirect to "New Monitoring Project" page
    }

    public function action_new()
    {
        $view = View::factory('template');
        $view->page_title = "New Monitoring Project";
        
        $view->page_content = View::factory('pages/param_form');
        $view->page_content->mode = "New";
        
        $this->errors = "";
        if($_POST) {
            // Form validation
            $post = Validate::factory($_POST)
                  ->rule('project_title', 'not_empty')
                  ->rule('project_title', 'max_length', array(120))
                  ->rule('keywords_phrases', 'not_empty');
            
            $this->field_data = $post->as_array(); // For form re-population
            
            if ($post->check()) {
                
                $this->field_data['gather_now'] = (array_key_exists('gather_now', $this->field_data)) ? 1 : 0;
                
                $project_id = $this->model_params->insert_project(array(
                    'project_title' => $this->field_data['project_title'],
                    'date_created' => time(),
                    'gather_interval' => 'daily',
                    'gather_now' => $this->field_data['gather_now']
                ));
                
                $this->model_params->insert_keywords($project_id, $this->field_data['keywords_phrases']);
                
                // Create directory to store cached text & make it writable (must be done using system command for permissions work properly)
                $system_cmd = "mkdir -m 777 ".Kohana::config('myconf.lemur.docs')."/$project_id";
                system($system_cmd, $return_code);
                if($return_code != 0) {
                    echo "Error when running command &lt;$system_cmd&gt;: $return_code<br>";
                    exit;
                }
                
                // Gather initial results
                if($this->field_data['gather_now'])
                     Request::factory('gather/index/'.$project_id)->execute();
                
                $this->request->redirect(''); // Redirect to "Home" page
                
            } else { 
                $this->errors = $post->errors('params');
            }
        } else {
            // Populate form w/ empty values
            $this->field_data = array(
                'project_title' => '',
                'gather_now' => 1
            );
        } 
        
        $view->page_content->errors = $this->errors;
        $view->page_content->field_data = $this->field_data;
        $this->request->response = $view;
    }
    
    public function action_modify($project_id = 0)
    {
        $project_data = $this->model_params->get_project_data($project_id);
        
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $view = View::factory('template');
            $view->page_title = "Modify Monitoring Project";
            
            $view->page_content = View::factory('pages/param_form');
            $view->page_content->mode = "Modify";
            
            $this->project_data = array_pop($project_data);
            
            $this->field_data['keyword_phrase_data'] = $this->model_params->get_keyword_phrase_data($project_id);
            
            $this->errors = "";
            if($_POST) {
                // Form validation
                $post = Validate::factory($_POST)
                      ->rule('project_title', 'not_empty')
                      ->rule('project_title', 'max_length', array(120))
                      ->rule('keywords_phrases', 'not_empty');
                
                // For form re-population
                $this->field_data = array_merge($this->field_data, $post->as_array()); 
                
                if ($post->check()) {
                    
                    $this->model_params->update_project($project_id, array(
                        'project_title' => $this->field_data['project_title']
                    ));
                    
                    // Add new keywords and activate/deactivate old
                    $new_keywords_phrases = array();
                    $updated_keywords_phrases = array(); 
                    if(array_key_exists('keywords_phrases', $this->field_data)) {
                        foreach($this->field_data['keywords_phrases'] as $keyword_phrase) {
                            if($keyword_phrase > 0)
                                $updated_keywords_phrases[$keyword_phrase] = 1; // Keyword set as active
                            else
                                array_push($new_keywords_phrases, $keyword_phrase);
                        }
                    }
                    if(array_key_exists('deactivated_keywords_phrases', $this->field_data)) {
                        foreach($this->field_data['deactivated_keywords_phrases'] as $keyword_phrase)
                            $updated_keywords_phrases[$keyword_phrase] = 0; // Keyword set as deactivated
                    }
                    
                    if(count($new_keywords_phrases) > 0)
                        $this->model_params->insert_keywords($project_id, $new_keywords_phrases); 
                    $this->model_params->update_keywords($updated_keywords_phrases); 
                    
                    // Gather initial results
                    if($this->field_data['gather_now'])
                        Request::factory('gather/index/'.$project_id)->execute();
                    
                    $this->request->redirect(''); // Redirect to "Home" page
                    
                } else { 
                    $this->errors = $post->errors('params');
                }
            } else {
                // Populate form w/ values from database
                $this->field_data = array_merge($this->field_data, array(
                    'project_title' => $this->project_data['project_title'],
                    'keywords_phrases' => $this->model_params->get_active_keywords($project_id),
                    'deactivated_keywords_phrases' => $this->model_params->get_deactivated_keywords($project_id)
                ));
            }
            
            $this->field_data['project_id'] = $project_id; 
            $view->page_content->errors = $this->errors;
            $view->page_content->field_data = $this->field_data;
            $this->request->response = $view;
        }
    }
    
    public function action_delete($project_id)
    {
        $project_data = $this->model_params->get_project_data($project_id);
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $this->model_params->delete_project($project_id);
            $this->request->redirect(''); // Redirect to "Home" page
        }
    }
}