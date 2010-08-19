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
        
        $api_sources = $this->model_params->get_api_sources();
        
        $this->errors = "";
        if($_POST) {
            // Form validation
            $post = Validate::factory($_POST);
            $this->field_data = $post->as_array(); // For form re-population
            $post->rule('project_title', 'not_empty')
                 ->rule('project_title', 'max_length', array(120))
                 ->rule('keywords_phrases', 'not_empty');
            // If RSS feed is checked -> make sure at least 1 feed URL has been entered 
            if(array_key_exists('api_rss_feed', $this->field_data))
                $post->rule('rss_feeds', 'not_empty');
            
            if ($post->check()) {
                
                $this->field_data['gather_now'] = (array_key_exists('gather_now', $this->field_data)) ? 1 : 0;
                
                $project_id = $this->model_params->insert_project(array(
                    'project_title' => $this->field_data['project_title'],
                    'date_created' => time(),
                    'gather_interval' => $this->field_data['gather_interval'],
                    'active' => $this->field_data['gather_now']
                ));
                
                $this->model_params->insert_keywords($project_id, $this->field_data['keywords_phrases']);
                
                // Enable selected API data sources for this project
                foreach($api_sources as $api_source) {
                    if(array_key_exists('api_'.$api_source['gather_method_name'], $this->field_data))
                        $this->model_params->insert_active_api_source($api_source['api_id'], $project_id);
                }
                
                if(array_key_exists('rss_feed', $this->field_data))
                    $this->model_params->insert_rss_feeds($project_id, $this->field_data['rss_feeds']);
                
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
                'gather_interval' => 'daily',
                'gather_now' => 1
            );
            // Set all APIs active (except RSS Feeds)
            foreach($api_sources as $api_source)
                 $this->field_data['api_'.$api_source['gather_method_name']] = 1; 
            unset($this->field_data['api_rss_feed']); // Disable RSS Feed by default
        } 
        
        $view->page_content->errors = $this->errors;
        $view->page_content->field_data = $this->field_data;
        $view->page_content->api_sources = $api_sources;
        $this->request->response = $view;
    }
    
    public function action_help_searchable()
    {
        $view = View::factory('message');
        $view->title = "Help - Searchable RSS Feed";
        $view->content = "<b>Searchable RSS Feed</b><p>Some RSS Feeds allow you to search through them using a GET query string of search keywords. An example is the Google News RSS Feed:<br> http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&output=rss</p><p>The end of the URL must include the search key, which in the case of the Google News RSS feed is <b>&amp;q=</b> so the full RSS Feed URL you would add is:<br>http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&output=rss&q=</p><p>
        The application will automatically append the keywords/phrases to the RSS Feed URL before collecting data. If none of this makes any sense or you are not sure just keep the &apos;searchable&apos; checkbox unchecked when adding an RSS Feed URL.</p>";
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
            
            $api_sources = $this->model_params->get_api_sources();
            $this->field_data['keyword_phrase_data'] = $this->model_params->get_keyword_phrase_data($project_id);
            $this->field_data['rss_feed_data'] = $this->model_params->get_rss_feed_data($project_id);
            
            $this->errors = "";
            if($_POST) {
                // Form validation
                $post = Validate::factory($_POST);
                $this->field_data = array_merge($this->field_data, $post->as_array()); // For form re-population
                $post->rule('project_title', 'not_empty')
                     ->rule('project_title', 'max_length', array(120))
                     ->rule('keywords_phrases', 'not_empty');
                // If RSS feed is checked -> make sure at least 1 feed URL has been entered 
                if(array_key_exists('api_rss_feed', $this->field_data))
                    $post->rule('rss_feeds', 'not_empty');
                
                if ($post->check()) {
                    
                    $this->model_params->update_project($project_id, array(
                        'project_title' => $this->field_data['project_title'],
                        'gather_interval' => $this->field_data['gather_interval']
                    ));
                    
                    // Add new keywords and activate/deactivate old
                    $new_keywords_phrases = array(); $updated_keywords_phrases = array(); 
                    if(array_key_exists('keywords_phrases', $this->field_data)) {
                        foreach($this->field_data['keywords_phrases'] as $keyword_phrase) {
                            if($keyword_phrase > 0)
                                $updated_keywords_phrases[$keyword_phrase] = 1; // Keyword/phrase set as active
                            else
                                array_push($new_keywords_phrases, $keyword_phrase); // New keyword/phrase
                        }
                    }
                    if(array_key_exists('deactivated_keywords_phrases', $this->field_data)) {
                        foreach($this->field_data['deactivated_keywords_phrases'] as $keyword_phrase)
                            $updated_keywords_phrases[$keyword_phrase] = 0; // Keyword set as deactivated
                    }
                    if(count($new_keywords_phrases) > 0)
                        $this->model_params->insert_keywords($project_id, $new_keywords_phrases); 
                    $this->model_params->update_keywords($updated_keywords_phrases); 
                    
                    // Add new RSS feed URLs and activate/deactivate old
                    $new_rss_feeds = array(); $updated_rss_feeds = array(); 
                    if(array_key_exists('rss_feeds', $this->field_data)) {
                        foreach($this->field_data['rss_feeds'] as $rss_feed) {
                            if($rss_feed > 0)
                                $updated_rss_feeds[$rss_feed] = 1; // RSS feed set as active
                            else
                                array_push($new_rss_feeds, $rss_feed); // New RSS feed
                        }
                    }
                    if(array_key_exists('deactivated_rss_feeds', $this->field_data)) {
                        foreach($this->field_data['deactivated_rss_feeds'] as $rss_feed)
                            $updated_rss_feeds[$rss_feed] = 0; // RSS feed set as deactivated
                    }
                    if(count($new_rss_feeds) > 0)
                        $this->model_params->insert_rss_feeds($project_id, $new_rss_feeds); 
                    $this->model_params->update_rss_feeds($updated_rss_feeds); 
                    
                    // Enable selected API data sources for this project
                    $this->model_params->delete_active_api_sources($project_id);
                    foreach($api_sources as $api_source) {
                        if(array_key_exists('api_'.$api_source['gather_method_name'], $this->field_data))
                            $this->model_params->insert_active_api_source($api_source['api_id'], $project_id);
                    }
                    
                    // Gather initial results
                    if(array_key_exists('gather_now', $this->field_data))
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
                    'deactivated_keywords_phrases' => $this->model_params->get_deactivated_keywords($project_id),
                    'rss_feeds' => $this->model_params->get_active_rss_feeds($project_id),
                    'deactivated_rss_feeds' => $this->model_params->get_deactivated_rss_feeds($project_id),
                    'gather_interval' => $this->project_data['gather_interval']
                ));
                $active_api_sources = $this->model_params->get_active_api_sources($project_id);
                foreach($active_api_sources as $api_source)
                    $this->field_data['api_'.$api_source['gather_method_name']] = 1; 
            }
            
            $this->field_data['project_id'] = $project_id; 
            $view->page_content->errors = $this->errors;
            $view->page_content->field_data = $this->field_data;
            $view->page_content->api_sources = $api_sources;
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