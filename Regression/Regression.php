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
 * @author James Pirruccello <james@carbocation.com>
 * 
 */

namespace Regression;

use Regression\Matrix;
use Regression\RegressionException;

class Regression
{

    protected $SSEScalar;           //sum of squares due to error
    protected $SSRScalar;           //sum of squares due to regression
    protected $SSTOScalar;          //Total sum of squares
    protected $RSquare;             //R square
    protected $F;                   //F statistic
    protected $stderrors = array(); //standard errror array
    protected $tstats = array();    //t statistics array
    protected $pvalues = array();   //p values array
    protected $coefficients;        //regression coefficients Matrix object
    protected $covariance;          //covariance Matrix object
    protected $x;                   //Matrix object holding independent vars
    protected $y;                   //Matrix object holding dependent vars
    
    /*
     * Prepend a column of 1s to the matrix of independent variables?
     */
    protected $generateIntercept = true;
    
    public function setX(Matrix $x)
    {
        if($this->generateIntercept){
            $x = $this->generateInterceptColumn($x);
        }
        $this->x = $x;
    }
    
    public function generateInterceptColumn(Matrix $m)
    {
        return $m->addColumn(array_fill(0, $m->getNumRows(), 1), 0);
    }

    public function setY(Matrix $y)
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

    public function exec()
    {
        if(!($this->x instanceof Matrix)
                || !($this->y instanceof Matrix)){
            throw new RegressionException('X and Y must be matrices.');
        }
        //(X'X)-1
        $XtXInv = $this->x->transpose()->multiply($this->x)->invert();
        
        //coefficients = b = (X'X)-1 X'Y 
        $this->coefficients = $XtXInv->multiply($this->x->transpose()->multiply($this->y));
        
        //Generate b'X'Y, which we will reuse
        // b'X'Y = (Xb)'Y = (predictions)'Y
        $btXtY = $this->coefficients
                ->transpose()
                ->multiply(
                        $this->x
                        ->transpose())
                ->multiply($this->y);
        
        $num_independent = $this->x->getNumColumns();   //note: intercept is included
        $sample_size = $this->x->getNumRows();
        $dfTotal = $sample_size - 1;
        $dfModel = $num_independent - 1;
        $dfResidual = $dfTotal - $dfModel;
        
        /*
         * Create the unit vector, one row per sample
         */
        $um = new Matrix(array_fill(0, $sample_size, array(1)));
        
        //SSR = b'X'Y - (Y'U(U')Y)/n
        $this->SSRScalar = $btXtY
                ->subtract(
                        $this->y->transpose()
                        ->multiply($um)
                        ->multiply($um->transpose())
                        ->multiply($this->y)
                        ->scalarDivide($sample_size))
                ->getEntry(0, 0);
        
        //SSE = Y'Y - b'X'Y
        $this->SSEScalar = $this->y
                ->transpose()
                ->multiply($this->y)
                ->subtract($btXtY)
                ->getEntry(0, 0);

        $this->SSTOScalar = $this->SSRScalar + $this->SSEScalar;
        $this->RSquare = $this->SSRScalar / $this->SSTOScalar;
        $this->F = ($this->SSRScalar / $dfModel) / ($this->SSEScalar / $dfResidual);
        
        //MSE = SSE/(df)
        $MSE = $this->SSEScalar / $dfResidual;
        $this->covariance = $XtXInv->scalarMultiply($MSE);
        
        for($i = 0; $i < $num_independent; $i++){
            //get the diagonal elements of the standard errors
            $searray[] = array(sqrt($this->covariance->getEntry($i, $i)));
            //compute the t-statistic
            $tstat[] = array($this->coefficients->getEntry($i, 0) / $searray[$i][0]);
            //compute the student p-value from the t-stat
            $pvalue[] = array($this->getStudentPValue($tstat[$i][0], $dfResidual));
            
            //convert into 1-d vectors and store
            $this->stderrors[] = $searray[$i][0];
            $this->tstats[] = $tstat[$i][0];
            $this->pvalues[] = $pvalue[$i][0];
        }
        
        return $this;
    }
    
    public function predict(Matrix $m)
    {
        if(!($this->coefficients instanceof Matrix)){
            throw new RegressionException('Must run exec before calling predict');
        }
        
        $m = $this->generateInterceptColumn($m);
        return $m->multiply($this->coefficients);
    }
    
    /**
     * Calculate the standard error for each observation in the training dataset,
     * given the covariance matrix.
     * 
     * @return array One row per observation in the training data set
     */
    public function computePredictionVariances()
    {
        $predictionVariances = array();
        
        $unitCol = new Matrix(array_fill(0, $this->covariance->getNumColumns(), array(1)));
        $unitRow = new Matrix(array_fill(0, $this->covariance->getNumRows(), array(1)));
        
        $independentVariables = $this->x->getData();
        
        foreach($independentVariables AS $k => $v){
            $currentRowX = new Matrix(array($v));
            
            //Multiply the elements of the covariance matrix by the square of 
            //the predictor matrix on an elemental basis
            $rowVarianceMatrix = $this->covariance
                    ->elementMultiply($currentRowX
                            ->transpose()
                            ->multiply($currentRowX));
            
            //Get the sum of $predictionVariances via matrix math with unit vectors
            $predictionVariances[$k] = $unitRow
                    ->transpose()
                    ->multiply($rowVarianceMatrix)
                    ->multiply($unitCol)
                    ->getEntry(0, 0);
        }
        
        return $predictionVariances;
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
