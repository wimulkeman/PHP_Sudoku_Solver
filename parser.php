<?php
/**
 * This class is used to parse a numeric input
 *
 * @author  WIM
 * @version Release: $Id:$
 */
class SudokuParser
{
    /**
     * The Sudoku handler required for retrieving the default information about the
     * Suokdu puzzle
     *
     * @var    object
     * @access private
     */
    private $Sudoku;

    /**
     * Initiate the class and set the required handlers
     *
     * @return void
     * @access public
     * @author WIM
     */
    public function __construct($Sudoku)
    {
        // Set the required handlers
        if (empty($Sudoku) || !is_object($Sudoku)) {
            throw new Exception('No Sudoku class has been defined');
        }

        $this->Sudoku = $Sudoku;
    }
}
