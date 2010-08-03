<?php defined('SYSPATH') or die('No direct script access.');

class Model_Results extends Model {
    
    public function get_keywords_phrases($project_id)
    {
        $result = DB::select()->from('keywords_phrases')->where('project_id','=',$project_id)->execute()->as_array();
        foreach($result as $keyword_phrase) {
            // Add quotes if necessary
            $keyword_phrase_q = ($keyword_phrase['exact_phrase']) ? '"'.$keyword_phrase['keyword_phrase'].'"' : $keyword_phrase['keyword_phrase'];
            $keywords_phrases[$keyword_phrase['keyword_id']] = $keyword_phrase_q;
        }
        return $keywords_phrases;
    }
    
    public function get_results($project_id, $params)
    {
        $query = DB::select('metadata.*', 'keyword_metadata.*', 'metadata_urls.url', 'api_sources.api_name')->from('metadata')
                     ->join('metadata_urls')->on('metadata.url_id','=','metadata_urls.url_id')
                     ->join('keyword_metadata')->on('keyword_metadata.meta_id','=','metadata.meta_id')
                     ->join('api_sources')->on('metadata.api_id','=','api_sources.api_id')
                     ->where('metadata.project_id','=',$project_id);
              
        if($params['date_from'] > 0) 
            $query->where('metadata.date_published','>=',$params['date_from']);
        if($params['date_to'] > 0) 
            $query->where('metadata.date_published','<=',$params['date_to']);
        
        if($params['num_results'] > 0)
            $query->limit($params['num_results']);
        
        $query->order_by('metadata.date_published', strtoupper($params['order']))
              ->order_by('keyword_metadata.meta_id'); // Groups `keyword_metadata` rows together for each `metadata` entry
        
        return $query->execute()->as_array();
    }
    
    public function get_keyword_metadata($meta_id)
    {
        return DB::select()->from('keyword_metadata')->where('meta_id','=',$meta_id)->execute()->as_array();
    }
    
    public function num_metadata_entries($project_id, $start_date, $end_date)
    {
        return DB::query(Database::SELECT, "SELECT COUNT(meta_id) AS `total` FROM `metadata` WHERE (`project_id` = $project_id AND `date_published` >= $start_date AND `date_published` < $end_date)")->execute()->get('total');
    }
    
    // Get date_published for oldest or most recently published metadata entry from given project
    public function metadata_edge_date($project_id, $edge)
    {
        $order_by = ($edge == 'oldest') ? 'ASC' : 'DESC';
        $result = DB::select('date_published')->from('metadata')->where('project_id','=',$project_id)->order_by('date_published', $order_by)->limit(1)->execute()->as_array();
        return $result[0]['date_published'];
    }
    
    public function get_cached_text($meta_id)
    {
        $result = DB::select('text')->from('cached_text')->where('meta_id','=',$meta_id)->limit(1)->execute()->as_array();
        return $result[0]['text'];
    }
    
    public function delete_clusters($project_id)
    {
        DB::delete('doc_clusters')->where('project_id','=',$project_id)->execute();
    }
    
    public function insert_clusters(Array $cluster_data, $project_id)
    {
        foreach($cluster_data as $cluster_pt) {
            $cluster_info = explode(" ", $cluster_pt);
            $cluster_data = array(
                'meta_id' => $cluster_info[0],
                'cluster_id' => $cluster_info[1],
                'score' => $cluster_info[2],
                'project_id' => $project_id
            );
            DB::insert('doc_clusters', array_keys($cluster_data))->values(array_values($cluster_data))->execute();
        }
    }
    
    public function get_clusters($project_id, $params = 0)
    {
        return DB::select()->from('doc_clusters')
                           ->where('project_id','=',$project_id)
                           ->order_by('cluster_id', 'ASC')->execute()->as_array();
    }
    
    public function get_cluster_summary($project_id, $cluster_id, $params)
    {
         $query = DB::select('doc_clusters.score', 'cached_text.text')->from('doc_clusters')
                              ->where('project_id','=',$project_id)
                              ->where('cluster_id','=',$cluster_id)
                              ->join('cached_text')->on('doc_clusters.meta_id','=','cached_text.meta_id');
        if($params['num_results'] > 0) 
            $query->limit($params['num_results']);
        
        $query->order_by('doc_clusters.score', $params['score_order']);
        
        return $query->execute()->as_array();
    }
    
    public function update_cluster_log($data)
    {
        if($this->cluster_log_exists($data['project_id']))
            DB::update('cluster_log')->set($data)->where('project_id','=',$data['project_id'])->execute();
        else
            DB::insert('cluster_log', array_keys($data))->values(array_values($data))->execute();
    }
    
    public function cluster_log_exists($project_id) {
        return DB::query(Database::SELECT, "SELECT COUNT(project_id) AS `total` FROM `cluster_log` WHERE `project_id` = $project_id")->execute()->get('total');
    }

    public function get_cluster_log($project_id)
    {
        return DB::select()->from('cluster_log')->where('project_id','=',$project_id)->limit(1)->execute()->as_array();
    }
}
