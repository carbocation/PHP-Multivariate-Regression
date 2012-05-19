<?php

/**
 * CsvImport converts a CSV to a set of arrays suitable for use in 
 * Regression. 
 *
 * @author shankar<nshankar@ufl.edu>
 * @author James Pirruccello <james@carbocation.com>
 */

namespace Regression;

class CsvImport
{
    /**
     * @example $reg->loadCSV('abc.csv',array(0), array(1,2,3));
     * @param string $file
     * @param array $ycol
     * @param array $xcol
     * @param Boolean $hasHeader
     * @return array 
     */
    static public function loadCsv($file, array $ycol, array $xcol, $hasHeader = true)
    {
        $xarray = array();
        $yarray = array();
        $handle = fopen($file, "r");

        //if first row has headers.. ignore
        if($hasHeader){
            $data = fgetcsv($handle);
        }
        //get the data into array
        while(($data = fgetcsv($handle)) !== false){
            $rawData[] = array($data);
        }
        $sampleSize = count($rawData);

        $r = 0;
        while($r < $sampleSize){
            $xarray[] = self::getArray($rawData, $xcol, $r);
            $yarray[] = self::getArray($rawData, $ycol, $r);   //y always has 1 col!
            $r++;
        }
        
        return array(
            'x' => $xarray,
            'y' => $yarray,
        );
    }
    
    static public function getArray($rawData, $cols, $r)
    {
        $arr = array();
        foreach($cols as $val){
            $arr[] = $rawData[$r][0][$val];
        }
        return $arr;
    }
}
