<?php
namespace RedacTech;

/**
 * Description of Query
 *
 * @author sylvain
 */
class Query {
    
    
    protected $topic;
    
    protected $query;
    
    protected $url;
    
    public function init($topic) {
        $this->topic = $topic;
    }
    
    public function setQuery($query) {
        $this->query = $query;
    }
    
    public function getQueryUrl() {
        $this->url  = "http://localhost:8080/omnigator/plugins/query/csv.jsp?processor=tolog";
        $this->url .= '&query='. urlencode($this->query); 
        $this->url .= "&tm=" . $this->topic;
        return $this->url;
    }
    
    public function execute() {
        return utf8_encode(file_get_contents($this->getQueryUrl()));
    }
}
