<?php

/**
 * Copyright (c)  2011 Shankar Manamalkav <nshankar@ufl.edu>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Class for computing multiple linear regression of the form
 * y=a+b1x1+b2x2+b3x3...
 *
 * @author shankar<nshankar@ufl.edu>
 * @author carbocation<james@carbocation.com>
 * 
 */

namespace Regression;

use Regression\Matrix;
use Regression\RegressionException;

class Regression
{

    protected $SSEScalar; //sum of squares due to error
    protected $SSRScalar; //sum of squares due to regression
    protected $SSTOScalar; //Total sum of squares
    protected $RSquare;         //R square
    protected $F;               //F statistic
    protected $coefficients;    //regression coefficients array
    protected $stderrors;    //standard errror array
    protected $tstats;     //t statistics array
    protected $pvalues;     //p values array
    protected $x = array();
    protected $y = array();
    
    /*
     * Prepend a column of 1s to the matrix of independent variables?
     */
    protected $generateIntercept = true;
    
    public function setX(array $x)
    {
        if($this->generateIntercept){
            foreach($x AS $row => $element){
                array_unshift($x[$row], 1);
            }
        }
        $this->x = $x;
    }

    public function setY($y)
    {
        $this->y = $y;
    }

    public function getSSE()
    {
        return $this->SSEScalar;
    }

    public function getSSR()
    {
        return $this->SSRScalar;
    }

    public function getSSTO()
    {
        return $this->SSTOScalar;
    }

    public function getRSQUARE()
    {
        return $this->RSquare;
    }

    public function getF()
    {
        return $this->F;
    }

    public function getCoefficients()
    {
        return $this->coefficients;
    }

    public function getStandardError()
    {
        return $this->stderrors;
    }

    public function getTStats()
    {
        return $this->tstats;
    }

    public function getPValues()
    {
        return $this->pvalues;
    }

    /**
     * @example $reg->loadCSV('abc.csv',array(0), array(1,2,3));
     * @param type $file
     * @param array $xcolnumbers
     * @param type $ycolnumber 
     */
    public function loadCsv($file, array $ycol, array $xcol, $hasHeader = true)
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
            $xarray[] = $this->getArray($rawData, $xcol, $r);
            $yarray[] = $this->getArray($rawData, $ycol, $r);   //y always has 1 col!
            $r++;
        }
        $this->setX($xarray);
        $this->setY($yarray);
    }

    public function exec()
    {
        if((count($this->x) == 0) || (count($this->y) == 0)){
            throw new RegressionException('Please supply valid X and Y arrays');
        }
        $mx = new Matrix($this->x);
        $my = new Matrix($this->y);

        //coefficient(b) = (X'X)-1X'Y 
        $xTx = $mx->transpose()->multiply($mx)->invert();
        $xTy = $mx->transpose()->multiply($my);

        $coeff = $xTx->multiply($xTy);

        $num_independent = $mx->getNumColumns();   //note: intercept is included
        $sample_size = $mx->getNumRows();
        $dfTotal = $sample_size - 1;
        $dfModel = $num_independent - 1;
        $dfResidual = $dfTotal - $dfModel;
        //create unit vector..
        for($ctr = 0; $ctr < $sample_size; $ctr++){
            $u[] = array(1);
        }

        $um = new Matrix($u);
        //SSR = b(t)X(t)Y - (Y(t)UU(T)Y)/n        
        //MSE = SSE/(df)
        $SSR = $coeff->transpose()->multiply($mx->transpose())->multiply($my)
                ->subtract(
                ($my->transpose()
                ->multiply($um)
                ->multiply($um->transpose())
                ->multiply($my)
                ->scalarDivide($sample_size))
        );

        $SSE = $my->transpose()->multiply($my)->subtract(
                $coeff->transpose()
                        ->multiply($mx->transpose())
                        ->multiply($my)
        );

        $SSTO = $SSR->add($SSE);
        $this->SSEScalar = $SSE->getEntry(0, 0);
        $this->SSRScalar = $SSR->getEntry(0, 0);
        $this->SSTOScalar = $SSTO->getEntry(0, 0);

        $this->RSquare = $this->SSRScalar / $this->SSTOScalar;


        $this->F = (($this->SSRScalar / ($dfModel)) / ($this->SSEScalar / ($dfResidual)));
        $MSE = $SSE->scalarDivide($dfResidual);
        //this is a scalar.. get element
        $e = ($MSE->getEntry(0, 0));

        $stdErr = $xTx->scalarMultiply($e);
        for($i = 0; $i < $num_independent; $i++){
            //get the diagonal elements
            $searray[] = array(sqrt($stdErr->getEntry($i, $i)));
            //compute the t-statistic
            $tstat[] = array($coeff->getEntry($i, 0) / $searray[$i][0]);
            //compute the student p-value from the t-stat
            $pvalue[] = array($this->getStudentPValue($tstat[$i][0], $dfResidual));
        }
        //convert into 1-d vectors and store
        for($ctr = 0; $ctr < $num_independent; $ctr++){
            $this->coefficients[] = $coeff->getEntry($ctr, 0);
            $this->stderrors[] = $searray[$ctr][0];
            $this->tstats[] = $tstat[$ctr][0];
            $this->pvalues[] = $pvalue[$ctr][0];
        }
    }
    
    protected function getArray($rawData, $cols, $r)
    {
        $arr = array();
        foreach($cols as $key => $val){
            $arr[] = $rawData[$r][0][$val];
        }
        return $arr;
    }

    /**
     * @link http://home.ubalt.edu/ntsbarsh/Business-stat/otherapplets/pvalues.htm#rtdist
     * @param float $t_stat
     * @param float $deg_F
     * @return float 
     */
    protected function getStudentPValue($t_stat, $deg_F)
    {
        $t_stat = abs($t_stat);
        $mw = $t_stat / sqrt($deg_F);
        $th = atan2($mw, 1);
        if($deg_F == 1){
            return 1.0 - $th / (M_PI / 2.0);
        }
        $sth = sin($th);
        $cth = cos($th);
        if($deg_F % 2 == 1){
            return 1.0 - ($th + $sth * $cth * $this->statCom($cth * $cth, 2, $deg_F - 3, -1)) / (M_PI / 2.0);
        }else{
            return 1.0 - ($sth * $this->statCom($cth * $cth, 1, $deg_F - 3, -1));
        }
    }

    /**
     * @link http://home.ubalt.edu/ntsbarsh/Business-stat/otherapplets/pvalues.htm#rtdist
     * @param float $q
     * @param float $i
     * @param float $j
     * @param float $b
     * @return float 
     */
    protected function statCom($q, $i, $j, $b)
    {
        $zz = 1;
        $z = $zz;
        $k = $i;
        while($k <= $j){
            $zz = $zz * $q * $k / ( $k - $b);
            $z = $z + $zz;
            $k = $k + 2;
        }
        return $z;
    }

}
