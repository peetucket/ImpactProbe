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
