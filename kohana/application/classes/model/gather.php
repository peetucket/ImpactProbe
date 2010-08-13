<?php defined('SYSPATH') or die('No direct script access.');

class Model_Gather extends Model {
    
    public function get_project_data($project_id)
    {
        return DB::select()->from('projects')->where('project_id','=',$project_id)->limit(1)->execute()->as_array();
    }
    
    public function get_active_projects($gather_interval)
    {
        return DB::select('project_id')->from('projects')
                                       ->where('gather_interval','=',$gather_interval)
                                       ->where('active','=',1)
                                       ->execute()->as_array();
    }
    
    public function get_api_sources()
    {
        return DB::select()->from('api_sources')->execute()->as_array();
    }
    
    public function get_active_keywords($project_id)
    {
        return DB::select()->from('keywords_phrases')->where('project_id','=',$project_id)->where('active','=',1)->execute()->as_array();
    }
    
    public function insert_url($data)
    {
        list($insert_id, $num_affected_rows) = DB::insert('metadata_urls', array_keys($data))->values(array_values($data))->execute();
        return $insert_id;
    }
    
    public function url_exists($project_id, $url)
    {
        return DB::select(DB::expr('COUNT(url) AS total'))->from('metadata_urls')->where('project_id','=',$project_id)->where('url','=',$url)->execute()->get('total');
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
        $doc_dir = Kohana::config('myconf.lemur.docs').'/'.$data['project_id'];
        if(is_writable($doc_dir)) {
            $new_doc = $doc_dir.'/'.$data['meta_id'].'.txt';
            $fh = fopen($new_doc, 'w') or die($new_doc.': cannot open file for writing'); 
            fwrite($fh, "<DOC>\n<DOCNO>".$data['meta_id']."</DOCNO>\n<TEXT>\n".$data['text']."\n</TEXT>\n</DOC>");
            fclose($fh);
        } else {
            $this->insert_gather_log(array(
                'project_id' => $data['project_id'],
                'search_query' => "Error saving file to $doc_dir: directory is not writable.",
                'date' => time(),
                'results_gathered' => 0,
                'error' => 1
            ));
            exit; // Stop trying to gather more results
        }
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