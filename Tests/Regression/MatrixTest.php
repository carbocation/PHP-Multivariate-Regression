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

use Regression\Matrix;
use Regression\MatrixException;

class MatrixTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreate2dMatrix()
    {
        $arr = array(
            array(1, 2),
            array(3, 4)
        );
        $m = new Matrix($arr);
        $this->assertInstanceOf('Regression\Matrix', $m);
    }

    /**
     * test console display function to ensure 100% code coverage
     */
    public function testMatrixDisplay()
    {
        $arr = array(
            array(1, 2),
            array(3, 4)
        );
        $m = new Matrix($arr);
        $m->displayMatrix();
    }

    /**
     * @expectedException Regression\MatrixException
     */
    public function testInvalidMatrixException()
    {
        $arr = array(
            array(2, 3),
            array(1)
        );
        $m = new Matrix($arr);
    }

    public function testNotSquare()
    {
        $arr = array(array(1, 2), array(3, 4), array(5, 6));
        $m = new Matrix($arr);
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
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->add($mat2);

        $testRet = array(array(2, 4, 6, 8), array(8, 10, 12, 14));
        $this->assertSame($ret->getData(), $testRet);
    }

    /**
     * @expectedException Regression\MatrixException
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
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->add($mat2);
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
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->subtract($mat2);

        $testRet = array(array(0, 0, 0, 0), array(0, 0, 0, 0));
        $this->assertSame($ret->getData(), $testRet);
    }

    /**
     * @expectedException Regression\MatrixException
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
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->subtract($mat2);
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
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->multiply($mat2);

        $testRet = array(array(4, 11, -15), array(5, 7, -7));
        $this->assertSame($ret->getData(), $testRet);
    }

    /**
     * @expectedException Regression\MatrixException
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
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->multiply($mat2);
    }

    public function testDeterminant()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4)
        );
        $mat1 = new Matrix($arr1);
        $det = $mat1->getDeterminant();
        $this->assertSame(-2, $det);
    }

    /**
     * @expectedException Regression\MatrixException
     */
    public function testDeterminantException()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4),
            array(5, 6)
        );
        $mat1 = new Matrix($arr1);
        $det = $mat1->getDeterminant();
    }

    public function testTranspose()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4)
        );
        $mat1 = new Matrix($arr1);
        $t = $mat1->transpose();
        $testarr = array(array(1, 3), array(2, 4));
        $this->assertSame($t->getData(), $testarr);
    }

    public function testInverse()
    {
        $arr1 = array(
            array(4, 3),
            array(3, 2)
        );
        $mat1 = new Matrix($arr1);
        $t = $mat1->invert()->getData();
        $exp = array(
            array(-2, 3),
            array(3, -4)
        );
        $this->assertSame($exp, $t);
    }

    /**
     * @expectedException Regression\MatrixException
     */
    public function testInverseException()
    {
        $arr1 = array(
            array(4, 3),
            array(3, 2),
            array(4, 5)
        );
        $mat1 = new Matrix($arr1);
        $t = $mat1->invert()->getData();
    }
    
    public function testTrace()
    {
        $arr = array(
            array(1,2,3),
            array(4,5,6),
            array(7,8,9),
        );
        $mat = new Matrix($arr);
        $t = $mat->getTrace();
        
        $this->assertSame(15, $t);
    }
    
    /**
     * @expectedException Regression\MatrixException
     */
    public function testNonArrayException()
    {
        $mat = new Matrix(5);
        
    }
    
    /**
     * @expectedException Regression\MatrixException
     */
    public function testNonArrayOfArraysException()
    {
        $mat = new Matrix(array(5));
        
    }
    
    /**
     * @expectedException Regression\MatrixException
     */
    public function testTraceException()
    {
        $arr = array(
            array(1,2,3),
            array(4,5,6),
            array(7,8,9),
            array(10,11,12),
        );
        $mat = new Matrix($arr);
        $t = $mat->getTrace();
        
    }
    
    public function testCopy()
    {
        $arr = array(
            array(1, 2),
            array(3, 4)
        );
        $m = new Matrix($arr);
        $n = $m->copy();
        $this->assertInstanceOf('Regression\Matrix', $n);
    }
    
    public function testAddRow()
    {
        $arr = array(
            array(1, 2),
            array(3, 4),
            array(5, 6),
        );
        $m = new Matrix($arr);
        $n = $m->addRow(array_fill(0, count($arr[0]), 1), 0);
        
        $arr2 = array(
            array(1, 1),
            array(1, 2),
            array(3, 4),
            array(5, 6),
        );
        $o = new Matrix($arr2);
        
        $this->assertEquals($o, $n);
    }
    
    public function testAddColumn()
    {
        $arr = array(
            array(1, 2),
            array(3, 4),
            array(5, 6),
        );
        $m = new Matrix($arr);
        $n = $m->addColumn(array_fill(0, count($arr), 1), 0);
        
        $arr2 = array(
            array(1, 1, 2),
            array(1, 3, 4),
            array(1, 5, 6),
        );
        $o = new Matrix($arr2);
        
        $this->assertEquals($o, $n);
    }
    
    /**
     * @expectedException Regression\MatrixException
     */
    public function testAddIllegalColumn()
    {
        $arr = array(
            array(1, 2),
            array(3, 4),
            array(5, 6),
        );
        $m = new Matrix($arr);
        $m->addColumn(array_fill(0, count($arr)-1, 1), 0);
    }
    
    /**
     * @expectedException Regression\MatrixException
     */
    public function testAddIllegalRow()
    {
        $arr = array(
            array(1, 2),
            array(3, 4),
            array(5, 6),
        );
        $m = new Matrix($arr);
        $n = $m->addRow(array_fill(0, count($arr[0])-1, 1), 0);
    }
    
    public function testElementMultiply()
    {
        $arr = array(
            array(1, 2),
            array(3, 4),
        );
        $m = new Matrix($arr);
        $n = $m->elementMultiply($m);
        
        $o = new Matrix(array(
            array(1, 4),
            array(9, 16),
        ));
        
        $this->assertEquals($n, $o);
    }
    
    public function testElementDivide()
    {
        $arr = array(
            array(1, 2),
            array(3, 4),
        );
        $m = new Matrix($arr);
        
        $arr2 = array(
            array(1, 4),
            array(9, 16),
        );
        $n = new Matrix($arr2);
        
        $o = $m->elementDivide($n);
        
        $p = new Matrix(array(
            array(1, 0.5),
            array(0.333333, 0.25),
        ));
        
        $this->assertEquals($o, $p, '', 0.0001);
    }
}
