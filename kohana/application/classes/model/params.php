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

class Model_Params extends Model {
    
    public function insert_project($data)
    {
        list($insert_id, $num_affected_rows) = DB::insert('projects', array_keys($data))->values(array_values($data))->execute();
        return $insert_id;
    }
    
    public function update_project($project_id, $data)
    {
        DB::update('projects')->set($data)->where('project_id','=',$project_id)->execute();
    }
    
    public function delete_project($project_id)
    {
        // Delete all database entries related to project
        DB::delete('doc_clusters')->where('project_id','=',$project_id)->execute();
        DB::delete('active_api_sources')->where('project_id','=',$project_id)->execute();
        DB::delete('rss_feeds')->where('project_id','=',$project_id)->execute();
        DB::delete('gather_log')->where('project_id','=',$project_id)->execute();
        DB::delete('cluster_log')->where('project_id','=',$project_id)->execute();
        DB::delete('keywords_phrases')->where('project_id','=',$project_id)->execute();
        DB::delete('metadata_urls')->where('project_id','=',$project_id)->execute();
        DB::Query(Database::DELETE, "DELETE FROM keyword_metadata USING metadata INNER JOIN keyword_metadata WHERE (metadata.project_id = $project_id AND metadata.meta_id = keyword_metadata.meta_id)")->execute();
        DB::Query(Database::DELETE, "DELETE FROM cached_text USING metadata INNER JOIN cached_text WHERE (metadata.project_id = $project_id AND metadata.meta_id = cached_text.meta_id)")->execute();
        DB::delete('metadata')->where('project_id','=',$project_id)->execute();
        DB::delete('projects')->where('project_id','=',$project_id)->limit(1)->execute();
        
        // Delete all data files: lemur files + charts
        $this->remove_dir(Kohana::config('myconf.lemur.indexes')."/$project_id");
        $this->remove_dir(Kohana::config('myconf.lemur.docs')."/$project_id");
        $this->remove_dir(Kohana::config('myconf.lemur.params')."/$project_id");
        $this->remove_file(Kohana::config('myconf.path.charts')."/cluster_$project_id.gch");
    }
    private function remove_dir($dir) 
    {
        if(is_dir($dir)) {
            $system_cmd = "rm -r $dir";
            system($system_cmd, $return_code);
            if($return_code != 0) {
                echo 'Error when running command &lt;'.$system_cmd.'&gt;: '.$return_code.'<br><a href="'.Url::base().'">&laquo; Back</a>'; 
                exit;
            }
        }
    }
    private function remove_file($file) 
    {
        if(file_exists($file)) {
            $system_cmd = "rm $file";
            system($system_cmd, $return_code);
            if($return_code != 0) {
                echo 'Error when running command &lt;'.$system_cmd.'&gt;: '.$return_code.'<br><a href="'.Url::base().'">&laquo; Back</a>'; 
                exit;
            }
        }
    }
    
    public function get_project_data($project_id)
    {
        return DB::select()->from('projects')->where('project_id','=',$project_id)->limit(1)->execute()->as_array();
    }

    public function get_projects()
    {
        return DB::select()->from('projects')->order_by('project_id', 'DESC')->execute()->as_array();
    }
    
    public function activate_project($project_id)
    {
        DB::update('projects')->value('active', 1)->where('project_id','=',$project_id)->limit(1)->execute();
    }
    public function deactivate_project($project_id)
    {
        DB::update('projects')->value('active', 0)->where('project_id','=',$project_id)->limit(1)->execute();
    }
    
    public function get_api_sources()
    {
        return DB::select()->from('api_sources')->execute()->as_array();
    }

    public function get_active_api_sources($project_id)
    {
        return DB::select('api_sources.*')->from('active_api_sources')
                           ->join('api_sources')->on('active_api_sources.api_id','=','api_sources.api_id')
                           ->where('project_id','=',$project_id)->execute()->as_array();
    }
    public function insert_active_api_source($api_id, $project_id)
    {
        DB::insert('active_api_sources', array('api_id', 'project_id'))->values(array($api_id, $project_id))->execute();
    }
    // Deletes all non-RSS feed 
    public function delete_active_api_sources($project_id)
    {
        DB::delete('active_api_sources')->where('project_id','=',$project_id)->execute();
    }
    
    public function insert_rss_feeds($project_id, Array $rss_feeds)
    {
        foreach($rss_feeds as $rss_feed_url) {
            $rss_feed_url = trim($rss_feed_url);
            // Check if RSS is searchable or not
            if(substr($rss_feed_url, 0, 11) == "Searchable:") {
                $searchable = 1;
                $rss_feed_url = str_replace("Searchable: ", "", $rss_feed_url); // Remove Search
            } else {
                $searchable = 0;
            } 
            DB::insert('rss_feeds', array('project_id', 'date_added', 'url', 'searchable', 'active' ))->values(array($project_id, time(), $rss_feed_url, $searchable, 1))->execute();
        }
    }
    
    public function get_rss_feed_data($project_id)
    {
        $rss_feed_db = DB::select()->from('rss_feeds')->where('project_id','=',$project_id)->execute()->as_array(); 
        $rss_feed_data = array();
        foreach($rss_feed_db as $rss_feed) {
            $rss_feed_data[$rss_feed['feed_id']] = array(
                'searchable' => $rss_feed['searchable'],
                'url' => $rss_feed['url']
            );
        }
        return $rss_feed_data;
    }
    
    public function update_rss_feeds(Array $rss_feeds)
    {
        foreach ($rss_feeds as $feed_id => $active)
            DB::update('rss_feeds')->set(array('active' => $active))->where('feed_id','=',$feed_id)->execute();
    }
    
    public function get_active_rss_feeds($project_id)
    {
        return $this->create_feed_id_array(DB::select('feed_id')->from('rss_feeds')->where('project_id','=',$project_id)->where('active','=',1)->execute()->as_array());
    }
    public function get_deactivated_rss_feeds($project_id)
    {
        return $this->create_feed_id_array(DB::select('feed_id')->from('rss_feeds')->where('project_id','=',$project_id)->where('active','=',0)->execute()->as_array());
    }
    private function create_feed_id_array(Array $rss_feed_rows)
    {
        $feed_ids = array();
        foreach($rss_feed_rows as $rss_feed) {
            array_push($feed_ids, $rss_feed['feed_id']);
        }
        return $feed_ids;
    }
    
    public function insert_keywords($project_id, Array $keywords_phrases)
    {
        foreach($keywords_phrases as $keyword_phrase) {
            $keyword_phrase = trim($keyword_phrase);
            // Check if exact phrase or not
            if(count(explode(" ", $keyword_phrase)) > 1 AND substr($keyword_phrase, 0, 1) == '"' AND substr($keyword_phrase, -1, 1) == '"') {
                $exact_phrase = 1;
                $keyword_phrase = str_replace('"', '', $keyword_phrase); // Remove quotes
            } else {
                $exact_phrase = 0;
            } 
            DB::insert('keywords_phrases', array('project_id', 'keyword_phrase', 'exact_phrase',  'date_added'))->values(array($project_id, $keyword_phrase, $exact_phrase, time()))->execute();
        }
    }
    
    public function update_keywords(Array $keywords_phrases)
    {
        foreach ($keywords_phrases as $keyword_id => $active)
            DB::update('keywords_phrases')->set(array('active' => $active))->where('keyword_id','=',$keyword_id)->execute();
    }
    
    public function get_keyword_phrase_data($project_id)
    {
        $keyword_phrase_db = DB::select()->from('keywords_phrases')->where('project_id','=',$project_id)->execute()->as_array(); 
        $keyword_phrase_data = array();
        foreach($keyword_phrase_db as $keyword_phrase) {
            $keyword_phrase_data[$keyword_phrase['keyword_id']] = array(
                'exact_phrase' => $keyword_phrase['exact_phrase'],
                'keyword_phrase' => $keyword_phrase['keyword_phrase']
            );
        }
        return $keyword_phrase_data;
    }
    
    public function get_active_keywords($project_id)
    {
        return $this->create_keyword_id_array(DB::select('keyword_id')->from('keywords_phrases')->where('project_id','=',$project_id)->where('active','=',1)->execute()->as_array());
    }
    public function get_deactivated_keywords($project_id)
    {
        return $this->create_keyword_id_array(DB::select('keyword_id')->from('keywords_phrases')->where('project_id','=',$project_id)->where('active','=',0)->execute()->as_array());
    }
    private function create_keyword_id_array($keyword_phrase_rows)
    {
        $keyword_phrase_ids = array();
        foreach($keyword_phrase_rows as $keyword_phrase) {
            array_push($keyword_phrase_ids, $keyword_phrase['keyword_id']);
        }
        return $keyword_phrase_ids;
    }
}