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

use Regression\CsvImport;
use Regression\Matrix;
use Regression\Regression;
use Regression\RegressionException;

class RegressionTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @link http://davidmlane.com/hyperstat/prediction.html
     */
    public function testRegressionUsingCSV()
    {
        /* @var $reg Regression */
        $reg = new Regression();
        $inputs = CsvImport::loadCsv(__DIR__ . DIRECTORY_SEPARATOR . 'MyReg.csv', array(0), array(1, 2, 3));
        $reg->setX(new Matrix($inputs['x']));
        $reg->setY(new Matrix($inputs['y']));
        $reg->exec();
        $testCoeff = new Matrix(array(
            array(-0.1533), 
            array(0.3764), 
            array(0.0012), 
            array(0.0227),
        ));
        $this->assertEquals($testCoeff, $reg->getCoefficients(), '', .01);
        //Test predictions
        $correctPred = new Matrix(array(array(1.40379)));
        $pred = $reg->predict(new Matrix(array(array(1.2,864,2))));
        $this->assertEquals($correctPred, $pred, '', 0.0001);
        //Test multiple predictions at once
        $correctPreds = new Matrix(array(
            array(1.40379), 
            array(1.65637),
        ));
        $preds = $reg->predict(new Matrix(array(
            array(1.2,864,2),
            array(1.78,818,6),
        )));
        $this->assertEquals($correctPreds, $preds, '', 0.0001);
        //Test variance
        $testPredictionStandardErrors = array(
            0.0038498088711814,
            0.018299433470814,
            0.030324785704026,
            0.011657487032846,
            0.023266235535542,
            0.023456926429428,
            0.030995838187627,
            0.013034457542298,
            0.0075310300882851,
            0.015908893974037,
            0.012089520906566,
            0.01178346958416,
            0.0061310782645288,
            0.012719441143792,
            0.0083160606318891,
            0.016366625342742,
            0.006463074210298,
            0.022087716897928,
            0.0060491511434411,
            0.0066402097332393,
            0.0096630742614536,
            0.01142751247168,
            0.031243999241411,
            0.016573658079668,
            0.036506305518845,
            0.020471526520851,
            0.017249616748212,
            0.016097878718472,
            0.0054792821632401,
            0.019444488579239,
            0.041234608687803,
            0.0071297452106818,
            0.0044251644376152,
            0.006828445589008,
            0.011024204693821,
            0.006148359335231,
            0.0062501708780287,
            0.011459633513013,
            0.0065211702224301,
            0.0044282307634107,
            0.0092795302527959,
            0.012093752314078,
            0.0092727762594761,
            0.022873513812148,
            0.0044972318523185,
            0.0063724437626945,
            0.0092568020303543,
            0.0084228649684506,
            0.01500958450904,
            0.010241561306229,
            0.0055412274854279,
            0.0088051180632777,
            0.013395189576577,
            0.014039866586934,
            0.018804830630234,
            0.016894794503078,
            0.0086587697839662,
            0.016685295483652,
            0.020629539265687,
            0.021540949858001,
            0.01370025116302,
            0.023986529602609,
            0.020253059906565,
            0.022062194062494,
            0.0056329489761745,
            0.0058678520459685,
            0.0086836426669565,
            0.008934823388784,
            0.0085600104598513,
            0.018037260645529,
            0.03571022410914,
            0.006619523074058,
            0.020635863542516,
            0.02035027709489,
            0.0065929688704512,
            0.0086443651336172,
            0.0073940863329166,
            0.010196242290858,
            0.0079221384264438,
            0.0069186706192648,
            0.0093493852111766,
            0.018784517067726,
            0.021603190882945,
            0.010232276146514,
            0.0052373183147573,
            0.016568139527064,
            0.0046548097926346,
            0.0064564783474433,
            0.039090708334733,
            0.0070463222763373,
            0.018616637453404,
            0.036567854663536,
            0.010380252545671,
            0.010112486053719,
            0.0099438523710902,
            0.0093285360027556,
            0.01625372784545,
            0.014681237832734,
            0.0094137632808407,
            0.020011163777876,
        );
        $this->assertEquals($testPredictionStandardErrors, $reg->computePredictionVariances(), '', 0.01);
    }

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
        $reg = new Regression();
        $reg->setX(new Matrix($x));
        $reg->setY(new Matrix($y));
        $reg->exec();    //go!
        //all our expected values for the above dataset.
        $testSSEScalar = 447.808;
        $testSSRScalar = 1448.2166;
        $testSSTOScalar = 1896.025;
        $testRsquare = 0.76381;
        $testF = 11.3190;
        $testCoeff = new Matrix(array(
            array(1.564), 
            array(0.3787), 
            array(0.5747),
            ));
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
     * @expectedException Regression\RegressionException
     */
    public function testPredictionException()
    {
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
        $reg = new Regression();
        $reg->setX(new Matrix($x));
        $reg->setY(new Matrix($y));
        $reg->predict(new Matrix(array(array(3, 4))));
        
    }
    
    /**
     * @expectedException Regression\RegressionException
     */
    public function testExecException()
    {
        $reg = new Regression();
        $reg->exec();
        
    }

}
