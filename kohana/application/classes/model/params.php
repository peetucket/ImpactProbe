<?php defined('SYSPATH') or die('No direct script access.');

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
        //DB::delete('doc_clusters')->where('project_id','=',$project_id)->execute(); 
        DB::delete('projects')->where('project_id','=',$project_id)->limit(1)->execute();
        DB::delete('metadata')->where('project_id','=',$project_id)->execute();
        DB::delete('metadata_urls')->where('project_id','=',$project_id)->execute();
    }
    
    public function insert_keywords($project_id, Array $keywords_phrases)
    {
        foreach ($keywords_phrases as $keyword_phrase) {
            $keyword_phrase = trim($keyword_phrase);
            // Check if exact phrase or not
            if(str_word_count($keyword_phrase) > 1 && substr($keyword_phrase, 0, 1) == '"' && substr($keyword_phrase, -1, 1) == '"') {
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
    
    public function get_active_keywords($project_id)
    {
        return DB::select()->from('keywords_phrases')->where('project_id','=',$project_id)->where('active','=',1)->execute()->as_array();
    }
    public function get_deactivated_keywords($project_id)
    {
        return DB::select()->from('keywords_phrases')->where('project_id','=',$project_id)->where('active','=',0)->execute()->as_array();
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
}