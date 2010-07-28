<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller {
    
    public function before($project_id = 0)
    {
        parent::before();
        $this->model_params = new Model_Params;
    }
    
    public function action_index()
    {
        $view = View::factory('template');
        $view->page_title = "Home";
        $view->page_content = View::factory('pages/home');
        
        $projects = $this->model_params->get_projects();
        
        $view->page_content->projects = $projects;
        $this->request->response = $view;
    }
    
    public function action_project_change_state($project_id = 0)
    {
        $project_data = $this->model_params->get_project_data($project_id);
        if($project_data) {
            $project_data = array_pop($project_data);
            if($project_data['active']) 
                $this->model_params->deactivate_project($project_id);
            else 
                $this->model_params->activate_project($project_id);
        }
        $this->request->redirect(''); // Redirect to "Home" page
    }
}
