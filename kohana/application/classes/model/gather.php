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
    
    public function save_cached_text($data)
    {
        $this->doc_dir = Kohana::config('myconf.lemur.docs').'/'.$data['project_id'];
        // TODO: delete this because it will be created when project is first created
        if(!is_dir($this->doc_dir))
            mkdir($this->doc_dir);
        
        $this->new_doc = $this->doc_dir.'/'.$data['meta_id'].'.txt';
        $this->fh = fopen($this->new_doc, 'w') or die($this->new_doc.': cannot open file for writing'); fwrite($this->fh, "<DOC>\n<DOCNO>".$data['meta_id']."</DOCNO>\n<TEXT>\n".$data['text']."\n</TEXT>\n</DOC>");
        fclose($this->fh);
    }

    public function insert_gather_log($data)
    {
        DB::insert('gather_log', array_keys($data))->values(array_values($data))->execute();
    }
    
    public function get_gather_log($project_id, $params)
    {
        $query = DB::select()->from('gather_log')->where('project_id','=',$project_id);
              
        if($params['date_from'] > 0) 
            $query->where('date','>=',$params['date_from']);
        if($params['date_to'] > 0) 
            $query->where('date','<=',$params['date_to']);
        
        if($params['num_results'] > 0) 
            $query->limit($params['num_results']);
        
        $query->order_by('date', strtoupper($params['order']));
        
        return $query->execute()->as_array();
    }
}