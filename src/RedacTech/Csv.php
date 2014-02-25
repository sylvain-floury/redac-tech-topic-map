<?php
namespace RedacTech;

/**
 * Description of Csv
 *
 * @author sylvain
 */
class Csv {
    protected $csvString;
    
    protected $csvArray = array();
    
    public function import($string) {
         
        $tmp = explode("\n", $string);
        
        for($i = 0; $i < count($tmp); $i++)
        {
            if(!empty($tmp[$i])) {
                $this->csvArray[] = str_getcsv($tmp[$i]);
            }
        }
        
    }
    
    public function export(){
        
        return $this->csvArray;
    }
    
    public function getCsvString()
    {
        return $this->csvString;
    }
}
