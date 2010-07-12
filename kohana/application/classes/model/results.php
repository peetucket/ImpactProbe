<?php defined('SYSPATH') or die('No direct script access.');

class Model_Results extends Model {
    
    public function get_keywords_phrases($project_id)
    {
        $result = DB::select()->from('keywords_phrases')
                    ->where('project_id','=',$project_id)
                    ->execute()->as_array();
        //$keywords_phrases = array();
        foreach($result as $keyword_phrase) {
            $keywords_phrases[$keyword_phrase['keyword_id']] = $keyword_phrase['keyword_phrase'];
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
            $query->where('metadata.date_retrieved','>=',$params['date_from']);
        if($params['date_to'] > 0) 
            $query->where('metadata.date_retrieved','<=',$params['date_to']);
        
        if($params['num_results'] > 0) 
            $query->limit($params['num_results']);
        
        $query->order_by('metadata.date_retrieved', strtoupper($params['order']))
              ->order_by('keyword_metadata.meta_id'); // Groups `keyword_metadata` rows together for each `metadata` entry
        //echo $query;
        return $query->execute()->as_array();
    }
    
    public function get_keyword_metadata($meta_id)
    {
        return DB::select()->from('keyword_metadata')->where('meta_id','=',$meta_id)->execute()->as_array();
    }
}