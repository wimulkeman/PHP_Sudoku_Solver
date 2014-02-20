<?php
/**
 * This class will do the eventual calculation for the solution of the Sudoku
 *
 * @author  WIM
 * @version Release: $Id:$
 */
class SudokuCalculator
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
     * The generator for generating the possible solutions
     *
     * @var    object
     * @access private
     */
    private $Generator;

    /**
     * The painter object is used for drawing solutions onto a board
     *
     * @var    object
     * @access private
     */
    private $Painter;

    /**
     * The validator is used to validate a calculated solution
     *
     * @var    object
     * @access private
     */
    private $Validator;

    /**
     * The calculated possible solutions
     *
     * @var    array
     * @access private
     */
    private $solutions = array();

    /**
     * Keeps count of the number of examend possible solutions
     *
     * @var    integer
     * @access private
     */
    private $solutionCalculator = 0;

    /**
     * The final solution
     *
     * @var    array
     * @access private
     */
    private $finalSolutions = array();

    /**
     * Initiate the class and set the required handlers
     *
     * @throws exception
     * @return void
     * @access public
     * @author WIM
     */
    public function __construct($Sudoku, $Generator, $Painter, $Validator)
    {
        // Set the required handlers
        if (empty($Sudoku) || !is_object($Sudoku)) {
            throw new Exception('No Sudoku class has been defined');
        }

        $this->Sudoku = $Sudoku;
        $this->Generator = $Generator;
        $this->Painter = $Painter;
        $this->Validator = $Validator;

        // Define the pre-required values in this class
        $this->availableNumbers = $this->Sudoku->getAvailableNumbers();
        $this->countAvailableNumbers = count($this->availableNumbers);
        $this->squaresOnBoard = $this->Sudoku->getNumberOfSquaresOnBoard();
        $this->blockLength = $this->Sudoku->getBlockBorderLength();
        $this->squaresInBlock = $this->Sudoku->getNumberOfSquaresInBlock();
        $this->boardLength = $this->Sudoku->getBoardBorderLength();
        $this->blocksPerSide = $this->Sudoku->getNumberBlockPerSide();
        $this->squaresInBlockRow = $this->squaresInBlock * $this->blocksPerSide;
    }

    /**
     * Check if the string contains all the numbers and has no duplicates
     *
     * @param string $numberstring The string that needs validation
     *
     * @throws exception
     * @return boolean
     * @access public
     * @author WIM
     */
    public function checkCorrectnessNumberstring($numberstring = '')
    {
        if ($this->Validator->notEmptyString($numberstring) === false) {
            throw new Exception('No (valid) numeric string has been provided');
        }

        // Validate the string against the Sudoku game rules
        $checkRows = $this->checkRows($numberstring);

        if ($checkRows === false) {
            throw new Exception('A invalid numeric string has been provided');
        }
    }

    /**
     * Calculate the solution to the Sudoku
     *
     * @param string $numberstring The starting string from which the solutions needs
     *                             to be calculated
     *
     * @throws exception
     * @return string The eventual solution to the provided starting position
     * @access public
     * @author WIM
     */
    public function calculateSolution($numberstring = '')
    {
        if ($this->Validator->notEmptyString($numberstring) === false) {
            throw new Exception('No (valid) numeric string has been provided');
        }

        // Provide the script with endless runingtime
        set_time_limit(0);

        // Calculate all the possible solutions
        $this->backtrackSolution($numberstring);

        // Check wether a solution has been found
        if (empty($this->finalSolutions)) {
            throw new Exception('No solutions could be found for the provided gameset');
        }

        return $this->finalSolutions[0];
    }

    /**
     * Try to calculate the solution using a backtrace algoritm by changing one
     * number at the time
     *
     * @param string $numberstring The possible solution thus far
     *
     * @return void
     * @access private
     * @author WIM
     */
    private function backtrackSolution($numberstring)
    {
        // Find the next spot in the line to calculate
        $emptySpot = strpos($numberstring, '0');

        // A solution has been found
        if ($emptySpot === false) {
            $this->finalSolutions[] = $numberstring;
            return true;
        }

        // Replace the empty spot with a number and try to validate it
        foreach ($this->availableNumbers as $number) {
            $numberstring[$emptySpot] = $number;

            // DEBUG: Return the number string to the user
            //echo $numberstring . "\n";
            //flush();

            // Validate the number string
            if ($this->checkRows($numberstring) === false) {
                continue;
            }

            $foundSolution = $this->backtrackSolution($numberstring);
            if ($foundSolution === true) {
                return true;
            }
        }

        // Invalid solution
        return false;
    }

    /**
     * Validate a provided gameset
     *
     * @param string $numberstring Gameset that needs validation
     *
     * @throws Exception
     * @return void
     * @access private
     * @author WIM
     */
    private function checkRows($numberstring)
    {
        // Check if the string is not longer than the actual board
        if (strlen($numberstring) > $this->squaresOnBoard) {
            throw new Exception('The provided gameset is longer than the board');
        }
        if (strlen($numberstring) < $this->squaresOnBoard) {
            // Extend the gameset to match the length of the board
            $numberstring .= str_repeat('0', $this->squaresOnBoard - strlen($numberstring));
        }

        // Validate the columns and rows
        if ($this->checkBlocks($numberstring) === false
            || $this->checkHorizontalRows($numberstring) === false
            || $this->checkVerticalRows($numberstring) === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Validate if a row contains only unique numbers
     *
     * @param string $numberstring The row that needs validation
     *
     * @return boolean
     * @access private
     * @author WIM
     */
    private function checkRow($numberstring)
    {
        $numberstringArray = str_split($numberstring, 1);
        $filteredArray = array_filter($numberstringArray);

        return count($filteredArray) == count(array_unique($filteredArray));
    }

    /**
     * Validate the rows a gameset
     *
     * @param string $numberstring The full gameset
     *
     * @param  boolean
     * @access private
     * @author WIM
     */
    private function checkHorizontalRows($numberstring)
    {
        for ($i = 0; $i < $this->boardLength; $i++) {
            // If the count exceeds the upper part of the board, than multiply it to
            // compensate the jump to the next part
            $additionalIncrement = floor($i / $this->blockLength) * ($this->squaresInBlockRow - $this->squaresInBlock);
            // Calculate the first position of the row
            $start = ($i * $this->blockLength) + $additionalIncrement;

            // Get all the available blocks and there numbers
            $row = '';
            for ($b = 0; $b < $this->blocksPerSide; $b++) {
                $blockStart = $start + ($this->squaresInBlock * $b);
                $row .= substr($numberstring, $blockStart, $this->blockLength);
            }

            if ($this->checkRow($row) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate the columns a gameset
     *
     * @param string $numberstring The full gameset
     *
     * @param  boolean
     * @access private
     * @author WIM
     */
    private function checkVerticalRows($numberstring)
    {
        for ($i = 0; $i < $this->boardLength; $i++) {
            // If the count exceeds the upper part of the board, than multiply it to
            // compensate the jump to the next part
            $additionalIncrement = floor($i / $this->blockLength) * ($this->squaresInBlock - $this->blockLength);
            $start = $i + $additionalIncrement;

            // Get the vertical aligned numbers
            $row = '';
            for ($b = 0; $b < $this->boardLength; $b++) {
                // Get the number on the next row
                $additionalRowIncrement = floor($b / $this->blockLength) * ($this->squaresInBlockRow - $this->squaresInBlock);
                $rowStart = $start + ($b * $this->blockLength) + $additionalRowIncrement;

                // Use the calculated index to retrieve the next number in the line
                $row .= substr($numberstring, $rowStart, 1);
            }

            if ($this->checkRow($row) === false) {
                return false;
            }
        }

       return true;
    }

    /**
     * Check the provided input for the blocks
     *
     * @param string $numberstring The full gameset
     *
     * @return boolean
     * @access private
     * @author WIM
     */
    private function checkBlocks($numberstring)
    {
        // Split the string up and validate the pieces individualy
        $blocks = str_split($numberstring, count($this->Sudoku->getAvailableNumbers()));

        foreach ($blocks as $block) {
            if ($this->checkRow($block) === false) {
                return false;
            }
        }

        return true;
    }
}
