<?php defined('SYSPATH') or die('No direct script access.');

class Model_Gather extends Model {
    
    public function get_active_projects($gather_interval)
    {
        return DB::select('project_id')->from('projects')
                                       ->where('gather_interval','=',$gather_interval)
                                       ->where('active','=',1)
                                       ->execute()->as_array();
    }
    
    public function insert_url($data)
    {
        list($insert_id, $num_affected_rows) = DB::insert('metadata_urls', array_keys($data))->values(array_values($data))->execute();
        return $insert_id;
    }
    
    public function url_exists($project_id, $url)
    {
        // Check if the url already exists in the database for this project
        return DB::query(Database::SELECT, "SELECT COUNT(url) AS `total` FROM `metadata_urls` WHERE (`project_id` = $project_id AND `url` = '$url')")->execute()->get('total');
    }
    
    public function insert_metadata($data)
    {
        list($insert_id, $num_affected_rows) = DB::insert('metadata', array_keys($data))->values(array_values($data))->execute();
        return $insert_id;
    }
    
    public function insert_keyword_metadata($data)
    {
        DB::insert('keyword_metadata', array_keys($data))->values(array_values($data))->execute();
    }
    
    public function insert_cached_text($data)
    {
        DB::insert('cached_text', array_keys($data))->values(array_values($data))->execute();
    } 
}