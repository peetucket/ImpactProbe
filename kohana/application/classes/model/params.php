<?php defined('SYSPATH') or die('No direct script access.');

class Model_Params extends Model {
    
    public function insert_project($data)
    {
        list($insert_id, $num_affected_rows) = DB::insert('projects', array_keys($data))->values(array_values($data))->execute();
        return $insert_id;
    }
    
    public function insert_keywords($project_id, Array $keywords_phrases)
    {
        foreach ($keywords_phrases as $keyword_phrase) {
            DB::insert('keywords_phrases', array('project_id', 'keyword_phrase', 'date_added'))->values(array($project_id, trim($keyword_phrase), time()))->execute();
        }
    }
    
    public function update_keywords(Array $keywords_phrases)
    {
        foreach ($keywords_phrases as $id => $keyword_phrase) {
            // ...
        }
    }
    
    public function get_active_keywords($project_id)
    {
        return DB::select()->from('keywords_phrases')
                           ->where('project_id','=',$project_id)
                           ->where('active','=',1)
                           ->execute()->as_array();
    }
    
    public function get_project_data($project_id)
    {
        return DB::select()->from('projects')->where('project_id','=',$project_id)->limit(1)->execute()->as_array();
    }

    public function get_projects()
    {
        return DB::select()->from('projects')->execute()->as_array();
    }
}