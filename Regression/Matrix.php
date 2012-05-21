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
 * Simple matrix manipulation library
 *
 * @author shankar<nshankar@ufl.edu>
 * @author James Pirruccello <james@carbocation.com>
 */

namespace Regression;

use Regression\MatrixException;

class Matrix
{

    //global vars
    protected $rows;
    protected $columns;
    protected $MainMatrix = array();

    /**
     * Matrix Constructor
     * 
     * Initialize the Matrix object. Throw an exception if jagged array is passed.
     *
     * @param array $array - The array
     */
    public function __construct($array)
    {
        if(!is_array($array)){
            throw new MatrixException('Please provide an array of arrays.');
        }
        
        if(!is_array($array[0])){
            throw new MatrixException('Please provide an array of arrays.');
        }
        
        $numRows = count($array);
        $numCols = count($array[0]);
        
        for($i = 0; $i < $numRows; $i++){
            //Do this count on every row to check for jagged matrices
            for($j = 0; $j < count($array[$i]); $j++){
                $this->MainMatrix[$i][$j] = $array[$i][$j];
            }
        }
        $this->rows = $numRows;
        $this->columns = $numCols;
        if(!$this->isValidMatrix()){
            throw new MatrixException("Invalid matrix");
        }
    }
    
    public function copy()
    {
        return clone $this;
    }

    /**
     * Display the matrix
     * Formatted display of matrix for debugging.
     */
    public function displayMatrix()
    {
        $rows = $this->rows;
        $cols = $this->columns;
        echo "Order of the matrix is ($rows rows X $cols columns)\n";
        for($r = 0; $r < $rows; $r++){
            for($c = 0; $c < $cols; $c++){
                echo $this->MainMatrix[$r][$c];
            }
            echo PHP_EOL;
        }
    }

    /**
     * Get the inner array stored in matrix object
     * 
     * @return array 
     */
    public function getData()
    {
        return $this->MainMatrix;
    }

    /**
     * Number of rows in the matrix
     * @return integer 
     */
    public function getNumRows()
    {
        return count($this->MainMatrix);
    }

    /**
     * Number of columns in the matrix
     * @return integer
     */
    public function getNumColumns()
    {
        return count($this->MainMatrix[0]);
    }

    /**
     * Return element found at location $row, $col.
     * 
     * @param int $row
     * @param int $col
     * @return object(depends on input)
     */
    public function getEntry($row, $col)
    {
        return $this->MainMatrix[$row][$col];
    }

    /**
     * Is this a square matrix?
     * 
     * Determinants and inverses only exist for square matrices!
     * 
     * @return bool 
     */
    public function isSquareMatrix()
    {
        if($this->rows == $this->columns){
            return true;
        }

        return false;
    }

    /**
     * Subtract matrix2 from matrix object on which this method is called
     * @param Matrix $matrix2
     * @return Matrix Note that original matrix is left unchanged
     */
    public function subtract(Matrix $matrix2)
    {
        return $this->elementIterator($this, $matrix2, '-');
    }

    /**
     * Add matrix2 to matrix object that calls this method.
     * @param Model_Matrix $matrix2
     * @return Matrix Note that original matrix is left unchanged
     */
    public function add(Matrix $matrix2)
    {
        return $this->elementIterator($this, $matrix2, '+');
    }
    
    /**
     * Multiply each element of this matrix by the corresponding element 
     * of matrix2
     * @param Matrix $matrix2
     * @return \Regression\Matrix
     * @throws MatrixException 
     */
    public function elementMultiply(Matrix $matrix2)
    {
        return $this->elementIterator($this, $matrix2, '*');
    }
    
    /**
     * Divide each element of this matrix by the corresponding element 
     * of matrix2
     * @param Matrix $matrix2
     * @return \Regression\Matrix
     * @throws MatrixException 
     */
    public function elementDivide(Matrix $matrix2)
    {
        return $this->elementIterator($this, $matrix2, '/');
    }
    
    /**
     * Multiply matrix2 into matrix object that calls this method
     * @param Model_Matrix $matrix2
     * @return Matrix Note that original matrix is left unaltered
     */
    public function multiply(Matrix $matrix2)
    {
        $newMatrix = array();
        $rows1 = $this->rows;
        $columns1 = $this->columns;

        $columns2 = $matrix2->getNumColumns();
        $rows2 = $matrix2->getNumRows();
        if($columns1 != $rows2){
            throw new MatrixException('Incompatible matrix types supplied');
        }
        for($i = 0; $i < $rows1; $i++){
            for($j = 0; $j < $columns2; $j++){
                $newMatrix[$i][$j] = 0;
                for($ctr = 0; $ctr < $columns1; $ctr++){
                    $newMatrix[$i][$j] += $this->MainMatrix[$i][$ctr] *
                            $matrix2->getEntry($ctr, $j);
                }
            }
        }
        return new Matrix($newMatrix);
    }

    /**
     * Multiply every element of matrix on which this method is called by the scalar
     * @param object $scalar
     * @return Matrix 
     */
    public function scalarMultiply($scalar)
    {
        $newMatrix = array();
        $rows = $this->rows;
        $columns = $this->columns;

        $newMatrix = array();
        for($i = 0; $i < $rows; $i++){
            for($j = 0; $j < $columns; $j++){
                $newMatrix[$i][$j] = $this->MainMatrix[$i][$j] * $scalar;
            }
        }
        return new Matrix($newMatrix);
    }

    /**
     * Divide every element of matrix on which this method is called by the scalar
     * @param object $scalar
     * @return Matrix 
     */
    public function scalarDivide($scalar)
    {
        $newMatrix = array();
        $rows = $this->rows;
        $columns = $this->columns;

        for($i = 0; $i < $rows; $i++){
            for($j = 0; $j < $columns; $j++){
                $newMatrix[$i][$j] = $this->MainMatrix[$i][$j] / $scalar;
            }
        }
        return new Matrix($newMatrix);
    }

    /**
     * Return the sub-matrix after crossing out the $crossx and $crossy row and column respectively
     * Part of determinant expansion by minors method
     * @param int $crossX
     * @param int $crossY
     * @return Matrix 
     */
    public function getSubMatrix($crossX, $crossY)
    {
        $newMatrix = array();
        $rows = $this->rows;
        $columns = $this->columns;

        $p = 0; // submatrix row counter
        for($i = 0; $i < $rows; $i++){
            $q = 0; // submatrix col counter
            if($crossX != $i){
                for($j = 0; $j < $columns; $j++){
                    if($crossY != $j){
                        $newMatrix[$p][$q] = $this->getEntry($i, $j);
                        //$matrix[$i][$j];
                        $q++;
                    }
                }
                $p++;
            }
        }
        return new Matrix($newMatrix);
    }

    /**
     * Compute the determinant of the square matrix on which this method is called
     * @link http://mathworld.wolfram.com/DeterminantExpansionbyMinors.html
     * @return object(depends on input)
     */
    public function getDeterminant()
    {
        if(!$this->isSquareMatrix()){
            throw new MatrixException("Not a square matrix!");
        }
        $rows = $this->rows;
        $columns = $this->columns;
        $determinant = 0;
        if($rows == 1 && $columns == 1){
            return $this->MainMatrix[0][0];
        }
        if($rows == 2 && $columns == 2){
            $determinant = $this->MainMatrix[0][0] * $this->MainMatrix[1][1] -
                    $this->MainMatrix[0][1] * $this->MainMatrix[1][0];
        }else{
            for($j = 0; $j < $columns; $j++){
                $subMatrix = $this->getSubMatrix(0, $j);
                if(fmod($j, 2) == 0){
                    $determinant += $this->MainMatrix[0][$j] * $subMatrix->getDeterminant();
                }else{
                    $determinant -= $this->MainMatrix[0][$j] * $subMatrix->getDeterminant();
                }
            }
        }
        return $determinant;
    }

    /**
     * Compute the transpose of matrix on which this method is called (invert rows and columns)
     * @return Matrix 
     */
    public function transpose()
    {
        $newArray = array();
        $rows = $this->rows;
        $columns = $this->columns;
        
        for($i = 0; $i < $rows; $i++){
            for($j = 0; $j < $columns; $j++){
                $newArray[$j][$i] = $this->MainMatrix[$i][$j];
            }
        }
        return new Matrix($newArray);
    }

    /**
     * Compute the inverse of the matrix on which this method is found (A*A(-1)=I)
     * (cofactor(a))T/(det a)
     * @link http://www.mathwords.com/i/inverse_of_a_matrix.htm
     * @return Matrix 
     */
    public function invert()
    {
        if(!$this->isSquareMatrix()){
            throw new MatrixException("Not a square matrix!");
        }
        
        $newMatrix = array();
        $rows = $this->rows;
        $columns = $this->columns;
        
        for($i = 0; $i < $rows; $i++){
            for($j = 0; $j < $columns; $j++){
                $subMatrix = $this->getSubMatrix($i, $j);
                if(fmod($i + $j, 2) == 0){
                    $newMatrix[$i][$j] = ($subMatrix->getDeterminant());
                }else{
                    $newMatrix[$i][$j] = -($subMatrix->getDeterminant());
                }
            }
        }
        $cofactorMatrix = new Matrix($newMatrix);
        return $cofactorMatrix->transpose()
                        ->scalarDivide($this->getDeterminant());
    }
    
    public function getTrace()
    {
        if(!$this->isSquareMatrix()){
            throw new MatrixException("Not a square matrix.");
        }
        
        return array_sum($this->getDiagonal());
    }
    
    /**
     * Returns a copy of the matrix with a new column added before the current 
     * column $beforeColumn.
     * 
     * Number of rows in newColumn must match the number of rows in this Matrix.
     * 
     * @param array $newColumn
     * @param int $beforeColumn
     * @return \Regression\Matrix 
     */
    public function addColumn(array $newColumn, $beforeColumn)
    {
        if($this->rows != count($newColumn)){
            throw new MatrixException('New column does not have the same number ' 
                    . 'of rows as the current Matrix.');
        }
        
        $newMatrix = array();
        for($i = 0; $i < $this->rows; $i++){
            $row = $this->MainMatrix[$i];
            $part = array_splice($row, $beforeColumn, $this->columns);
            $row = array_merge($row, (array)$newColumn[$i], $part);
            
            $newMatrix[] = $row;
        }
        
        return new Matrix($newMatrix);
    }
    
    /**
     * Returns a copy of the matrix with a new row added before the current 
     * row $beforeRow.
     * 
     * Number of columns in newRow must match the number of columns in this Matrix.
     * 
     * @param array $newRow
     * @param int $beforeRow
     * @return \Regression\Matrix 
     */
    public function addRow(array $newRow, $beforeRow)
    {
        if($this->columns != count($newRow)){
            throw new MatrixException('New row does not have the same number ' 
                    . 'of columns as the current Matrix.');
        }
        
        $newMatrix = $this->getData();
        $part = array_splice($newMatrix, $beforeRow, count($newMatrix));
        $newMatrix = array_merge($newMatrix, array($newRow), $part);
        
        return new Matrix($newMatrix);
    }
    
    protected function elementIterator(Matrix $matrix1, Matrix $matrix2, $operator)
    {
        $newMatrix = array();
        $rows1 = $matrix1->rows;
        $rows2 = $matrix2->getNumRows();
        $columns1 = $this->columns;
        $columns2 = $matrix2->getNumColumns();
        
        if(($rows1 != $rows2) || ($columns1 != $columns2)){
            throw new MatrixException('Matrices are not the same size!');
        }

        for($i = 0; $i < $rows1; $i++){
            for($j = 0; $j < $columns1; $j++){
                $newMatrix[$i][$j] = $this->elementOperator(
                        $matrix1->MainMatrix[$i][$j],
                        $matrix2->getEntry($i, $j),
                        $operator);
            }
        }
        return new Matrix($newMatrix);
    }
    
    protected function elementOperator($element1, $element2, $operator)
    {
        switch($operator){
            case '*':
                return $element1 * $element2;
                break;
            case '+':
                return $element1 + $element2;
                break;
            case '-':
                return $element1 - $element2;
                break;
            case '/':
                return $element1 / $element2;
                break;
            default:
                throw new MatrixException('Invalid elemental operator specified');
        }
    }
    
    /**
     * Is it a valid matrix?
     * 
     * Returns 'False' if it is not a rectangular matrix
     *
     * @return bool
     */
    protected function isValidMatrix()
    {
        for($i = 0; $i < $this->rows; $i++){
            $numCol = count($this->MainMatrix [$i]);
            if($this->columns != $numCol){
                return false;
            }
        }
        return true;
    }
    
    protected function getDiagonal()
    {
        $diagonal = array();
        
        for($i = 0; $i < $this->getNumRows(); $i++){
            for($j = 0; $j < $this->getNumColumns(); $j++){
                if($i == $j){
                    $diagonal[] = $this->MainMatrix[$i][$j];
                }
            }
        }
        
        return $diagonal;
    }

}
