<?php

class Lib_Matrixtest extends PHPUnit_Framework_TestCase
{
    public function testCanCreate2dMatrix()
    {
        $arr = array(
            array(1,2),
            array(3,4)
        );
        $m = new Lib_Matrix($arr);
        $this->assertInstanceOf('Lib_Matrix', $m);
    }
    /**
     * test console display function to ensure 100% code coverage
     */
    public function testMatrixDisplay()
    {
          $arr = array(
            array(1,2),
            array(3,4)
        );
        $m = new Lib_Matrix($arr);
        $m->DisplayMatrix();
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidMatrixException()
    {
        $arr = array(
            array(2, 3),
            array(1)
        );
        $m = new Lib_Matrix($arr);
    }

    public function testNotSquare()
    {
        $arr = array(array(1, 2), array(3, 4), array(5, 6));
        $m = new Lib_Matrix($arr);
        $this->assertFalse($m->isSquareMatrix());
    }

    public function testAdd()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $mat1 = new Lib_Matrix($arr1);
        $mat2 = new Lib_Matrix($arr2);
        $ret = $mat1->Add($mat2);

        $testRet = array(array(2, 4, 6, 8), array(8, 10, 12, 14));
        $this->assertSame($ret->GetInnerArray(), $testRet);
    }

    /**
     * @expectedException Exception
     */
    public function testAddException()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3)
        );
        $mat1 = new Lib_Matrix($arr1);
        $mat2 = new Lib_Matrix($arr2);
        $ret = $mat1->Add($mat2);
    }

    public function testSubtract()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $mat1 = new Lib_Matrix($arr1);
        $mat2 = new Lib_Matrix($arr2);
        $ret = $mat1->Subtract($mat2);

        $testRet = array(array(0, 0, 0, 0), array(0, 0, 0, 0));
        $this->assertSame($ret->GetInnerArray(), $testRet);
    }

    /**
     * @expectedException Exception
     */
    public function testSubtractException()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3)
        );
        $mat1 = new Lib_Matrix($arr1);
        $mat2 = new Lib_Matrix($arr2);
        $ret = $mat1->Subtract($mat2);
    }

    public function testMultiply()
    {
        $arr1 = array(
            array(2, 0, -1, 1),
            array(1, 2, 0, 1)
        );
        $arr2 = array(
            array(1, 5, -7),
            array(1, 1, 0),
            array(0, -1, 1),
            array(2, 0, 0),
        );
        $mat1 = new Lib_Matrix($arr1);
        $mat2 = new Lib_Matrix($arr2);
        $ret = $mat1->Multiply($mat2);

        $testRet = array(array(4, 11, -15), array(5, 7, -7));
        $this->assertSame($ret->GetInnerArray(), $testRet);
    }

    /**
     * @expectedException Exception
     */
    public function testMultiplyException()
    {
        $arr1 = array(
            array(2, 0, -1, 1),
            array(1, 2, 0, 1)
        );
        $arr2 = array(
            array(1, 5, -7),
            array(1, 1, 0),
            array(0, -1, 1)
        );
        $mat1 = new Lib_Matrix($arr1);
        $mat2 = new Lib_Matrix($arr2);
        $ret = $mat1->Multiply($mat2);
        //the above should throw an exception!
    }

    public function testDeterminant()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4)
        );
        $mat1 = new Lib_Matrix($arr1);
        $det = $mat1->Determinant();
        $this->assertSame(-2, $det);
    }

    /**
     * @expectedException Exception
     */
    public function testDeterminantException()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4),
            array(5, 6)
        );
        $mat1 = new Lib_Matrix($arr1);
        $det = $mat1->Determinant();
    }

    public function testTranspose()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4)
        );
        $mat1 = new Lib_Matrix($arr1);
        $t = $mat1->Transpose();
        $testarr = array(array(1, 3), array(2, 4));
        $this->assertSame($t->GetInnerArray(), $testarr);
    }

    public function testInverse()
    {
        $arr1 = array(
            array(4, 3),
            array(3, 2)
        );
        $mat1 = new Lib_Matrix($arr1);
        $t = $mat1->Inverse()->GetInnerArray();
        $exp = array(
            array(-2, 3),
            array(3, -4)
        );
        $this->assertSame($exp, $t);
    }

    /**
     * @expectedException Exception
     */
    public function testInverseException()
    {
        $arr1 = array(
            array(4, 3),
            array(3, 2),
            array(4, 5)
        );
        $mat1 = new Lib_Matrix($arr1);
        $t = $mat1->Inverse()->GetInnerArray();
    }
    /**
     * Tally using attached excel workbook  - It computes regression using excel
     */
    public function testRegressionPassingArrays()
    {
        //independent vars.. note 1 has been added to first column for computing
        //intercept
        $x = array(
            array(1, 8, 2),
            array(1, 40.5, 24.5),
            array(1, 4.5, .5),
            array(1, .5, 2),
            array(1, 4.5, 4.5),
            array(1, 7, 8),
            array(1, 24.5, 40.5),
            array(1, 4.5, 2),
            array(1, 32, 24.5),
            array(1, .5, 4.5),
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

        /* @var $reg Lib_Regression */
        //$reg = Lib_Regression::Getinstance();
        $reg = new Lib_Regression();
        $reg->setX($x);
        $reg->setY($y);
        $reg->Compute();    //go!

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
        /* @var $reg Lib_Regression */
        $reg = new Lib_Regression();
        $reg->LoadCSV('C:\Users\Shankar\Desktop\MyReg.csv', array(0), array(1, 2, 3));
        $reg->Compute();
        $testCoeff = array(-0.1533, 0.3764, 0.0012, 0.0227);
        $this->assertEquals($testCoeff, $reg->getCoefficients(), '', .01);
    }

}

?>