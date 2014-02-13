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
        
        for($i = 1; $i < count($tmp); $i++)
        {
            if(!empty($tmp[$i])) {
                $this->csvString .= $this->checkEndCsvString($tmp[$i]);
            }
        }
        
        $this->csvArray = str_getcsv(substr($this->csvString, 0, -2));
    }
    
    public function export(){
        
        return $this->csvArray;
    }
    
    public function getCsvString()
    {
        return $this->csvString;
    }
    
    protected function checkEndCsvString($string) {
        
        if(strpos($string, '","') === false) {
            $string .= ",";
        }
        
        if(substr($string, -1) != "\n") {
            $string .= "\n";
        }
        
        return $string;
    }
}
