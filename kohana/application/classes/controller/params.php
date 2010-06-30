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
        $this->field_data = "";
        if($_POST) {
            
            // Form validation
            $post = new Validate($_POST); // array_merge($_POST, $_FILES)
            $post->filter(TRUE, 'trim');  // (NOT WORKING!!!) Apply trim() to all input values 
            $post->rule('project_title', 'not_empty')
                 ->rule('project_title', 'max_length', array(120));
                 //->callback('keywords_phrases', array($this, 'keywords_not_empty')); (NOT WORKING!!)
            // TEMP hack fix...
            if(!array_key_exists('keywords_phrases', $this->field_data))
                $array->error($field, 'keywords_not_empty', array($array[$field]));
            
            $this->field_data = $post->as_array(); // For form re-population
            
            if ($post->check()) {
                
                $project_data = array(
                    $this->field_data['project_title']
                );
                $project_id = $this->model_params->insert_project($project_data);
                
                $this->model_params->insert_keywords($project_id, $this->field_data['keywords_phrases']);
                
                $this->request->redirect(''); // Redirect to "Home" page
                
            } else { 
                $this->errors = $post->errors('params');
            }
        }
            
        $view->page_content->errors = $this->errors;
        $view->page_content->field_data = $this->field_data;
        $this->request->response = $view;
    }
    
    public function action_modify($project_id = 0)
    {
        // Get project data
        $project_data = $this->model_params->get_project_data($project_id);
        
        // Verify that project exists
        if(count($project_data) == 0) {
            echo "<p>Project with this ID does not exist.</p>"; 
        } else {
            $view = View::factory('template');
            $view->page_title = "Modify Monitoring Project";
            
            $view->page_content = View::factory('pages/param_form');
            $view->page_content->mode = "Modify";
            
            $this->errors = "";
            $this->field_data = $project_data[0];
            
            // Get keywords from database
            $this->active_keywords = $this->model_params->get_active_keywords($project_id);
            $this->field_data['keywords_phrases'] = $this->active_keywords;
            
            
            /*
            // NOTE: Allow user to add or deactivate keywords (do not allow keyword removal)
            
            
            $active_keyword_phrases = array(); // Keywords that have already been added (will be made active or deactive)
            $new_keyword_phrases = array(); // New keywords
            foreach ($keywords_phrases as $keyword_phrase) {
                if(is_int($keyword_phrase)) {
                    array_push($active_keyword_phrases, $keyword_phrase);
                } else {
                    array_push($new_keyword_phrases, $keyword_phrase);
                } 
            }
            
            
            $this->request->redirect(Url::base()); // Redirect to "Home" page
            
            */
                
            $view->page_content->errors = $this->errors;
            $view->page_content->field_data = $this->field_data;
            $this->request->response = $view;
        }
    }
    
    // (NOT WORKING!!) Callback function to ensure there was at least one keyword active/entered 
    public function keywords_not_empty(Validate $array, $field)
    {
        if(!array_key_exists('keywords_phrases', $this->field_data))
            $array->error($field, 'keywords_not_empty', array($array[$field]));
    }
}