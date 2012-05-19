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
 * @author shankar<nshankar@ufl.edu>
 * @author carbocation<james@carbocation.com>
 * 
 */

namespace Tests\Regression;

use Regression\Regression;

class RegressionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tally using attached excel workbook  - It computes regression using excel
     */
    public function testRegressionPassingArrays()
    {
        /*
         * independent vars.. Note that 1 has *not* been added to the 
         * first column for computing the intercept. This is done internally 
         * and automatically for the independent variables.
         */
        $x = array(
            array(8, 2),
            array(40.5, 24.5),
            array(4.5, .5),
            array(.5, 2),
            array(4.5, 4.5),
            array(7, 8),
            array(24.5, 40.5),
            array(4.5, 2),
            array(32, 24.5),
            array(.5, 4.5),
        );
        //dependent matrix..note it is a 2d array
        $y = array(array(4.5),
            array(22.5),
            array(2),
            array(.5),
            array(18),
            array(2),
            array(32),
            array(4.5),
            array(40.5),
            array(2));

        /* @var $reg Regression */
        //$reg = Regression::Getinstance();
        $reg = new Regression();
        $reg->setX($x);
        $reg->setY($y);
        $reg->exec();    //go!
        //all our expected values for the above dataset.
        $testSSEScalar = 447.808;
        $testSSRScalar = 1448.2166;
        $testSSTOScalar = 1896.025;
        $testRsquare = 0.76381;
        $testF = 11.3190;
        $testCoeff = array(1.564, 0.3787, 0.5747);
        $testStdErr = array(3.4901, 0.3279, 0.34303);
        $testTstats = array(0.4483, 1.1546, 1.6754);
        $testPValues = array(0.6674, 0.2861, 0.1377);

        //confirm our math..note use of foating point accuracy flag in tests!
        $this->assertEquals($testSSEScalar, $reg->getSSE(), '', .01);
        $this->assertEquals($testSSRScalar, $reg->getSSR(), '', .01);
        $this->assertEquals($testSSTOScalar, $reg->getSSTO(), '', .01);
        $this->assertEquals($testRsquare, $reg->getRSQUARE(), '', .01);
        $this->assertEquals($testF, $reg->getF(), '', .01);
        $this->assertEquals($testCoeff, $reg->getCoefficients(), '', .01);
        $this->assertEquals($testStdErr, $reg->getStandardError(), '', .01);
        $this->assertEquals($testTstats, $reg->getTStats(), '', .01);
        $this->assertEquals($testPValues, $reg->getPValues(), '', .01);
    }

    /**
     * @link http://davidmlane.com/hyperstat/prediction.html
     */
    public function testRegressionUsingCSV()
    {
        /* @var $reg Regression */
        $reg = new Regression();
        $reg->loadCsv(__DIR__ . DIRECTORY_SEPARATOR . 'MyReg.csv', array(0), array(1, 2, 3));
        $reg->exec();
        $testCoeff = array(-0.1533, 0.3764, 0.0012, 0.0227);
        $this->assertEquals($testCoeff, $reg->getCoefficients(), '', .01);
    }

}
