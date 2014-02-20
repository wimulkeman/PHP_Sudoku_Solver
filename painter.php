<?php
/**
 * This class is used for drawing a Sudoku gameboard or parts of it
 *
 * The board in the views is constructed in the following way
 * ------------------------------------------------
 * | 1  | 2  | 3  || 10 | 11 | 12 || 19 | 20 | 21 |
 * |----|----|----||----|----|----||----|----|----|
 * | 4  | 5  | 6  || 13 | 14 | 15 || 22 | 23 | 24 |
 * |----|----|----||----|----|----||----|----|----|
 * | 7  | 8  | 9  || 16 | 17 | 18 || 25 | 26 | 27 |
 * |==============||==============||==============|
 * | 28 | 29 | 30 || 37 | 38 | 39 || 46 | 47 | 48 |
 * |----|----|----||----|----|----||----|----|----|
 * | 31 | 32 | 33 || 40 | 41 | 42 || 49 | 50 | 51 |
 * |----|----|----||----|----|----||----|----|----|
 * | 34 | 35 | 36 || 43 | 44 | 45 || 52 | 53 | 54 |
 * |==============||==============||==============|
 * | 55 | 56 | 57 || 64 | 65 | 66 || 73 | 74 | 75 |
 * |----|----|----||----|----|----||----|----|----|
 * | 58 | 59 | 60 || 67 | 68 | 69 || 76 | 77 | 78 |
 * |----|----|----||----|----|----||----|----|----|
 * | 61 | 62 | 63 || 70 | 71 | 72 || 79 | 80 | 81 |
 * ------------------------------------------------
 *
 * The main blocks are devided in this way
 * ---------------
 * | 1 || 2 || 3 |
 * |===||===||===|
 * | 4 || 5 || 6 |
 * |===||===||===|
 * | 7 || 8 || 9 |
 * ---------------
 *
 *
 * @author  WIM
 * @version Release: $Id:$
 */
class SudokuPainter
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
    * The parser is used to divide a numeric string into pieces for the gameboard
     *
     * @var    object
     * @access private
     */
    private $Parser;

    /**
     * Memorize the number used for the next square
     *
     * @var    integer
     * @access private
     */
    private $squareIndex = 1;

    /**
     * Initiate the class and set the required handlers
     *
     * @return void
     * @access public
     * @author WIM
     */
    public function __construct($Sudoku, $Parser)
    {
        // Set the required handlers
        if (empty($Sudoku) || !is_object($Sudoku)) {
            throw new Exception('No Sudoku class has been defined');
        }
        if (empty($Parser) || !is_object($Parser)) {
            throw new Exception('No Parser class has been defined');
        }

        $this->Sudoku = $Sudoku;
        $this->Parser = $Parser;
    }

    /**
     * This solution is used to draw a single block within the gameboard
     *
     * @param string  $numberSequence The sequence of numbers that needs to be drawn
     * @param boolean $fixedPositions Defines if the numbers are placed on a fixed
     *                                position within the board
     *
     * @return string The view for the block
     * @access public
     * @author WIM
     */
    public function printSingleBlock($numberSequence, $fixedPositions = false)
    {
        if (empty($numberSequence)) {
            throw new Exception('No numeric string has been provided');
        }

        if (strlen($numberSequence) < count($this->Sudoku->getAvailableNumbers())) {
            throw new Exception('The length of the provided string is less than the length of the board');
        }

        // Get all the available playable numbers
        $availableNumbers = $this->Sudoku->getAvailableNumbers();

        // Construct the HTML for the view
        $sudokuHtml = '<table style="border-spacing: 0;">';
        for ($squareRow = 0; $squareRow < $this->Sudoku->getBlockBorderLength(); $squareRow++) {
            $sudokuHtml .= '<tr>';
            // Now construct the blocks in the board
            for ($squareColumn = 0; $squareColumn < $this->Sudoku->getBlockBorderLength(); $squareColumn++) {
                $sudokuHtml .= '<td style="border: solid 1px #000; width: 30px; height: 30px; v-align: middle; text-align: center;">';
                // Check if the number needs to displayed fixed or as a dropdown
                $rowSquareAdd = $squareRow === 0 ? 0 : $squareRow * 3 ;
                $squareStringIndex = $rowSquareAdd + $squareColumn;
                // The number within the string
                $number = empty($numberSequence[$squareStringIndex])
                    ? 0 : $numberSequence[$squareStringIndex];
                // Validate if the number is available
                $number = $number == 0 && !in_array($number, $availableNumbers) ? '-' : $number;

                if ($fixedPositions === true) {
                    $sudokuHtml .= $number;
                } else {
                    $sudokuHtml .= '<select name="square[' . $this->squareIndex .']">';
                    $sudokuHtml .= '<option value="0">-</value>';
                    foreach ($availableNumbers as $aNumber) {
                        $selected = $aNumber == $number ? ' selected="selected"' : '';
                        $sudokuHtml .= '<option value="' . $aNumber .'"' . $selected . '>' . $aNumber .'</option>';
                    }
                    $sudokuHtml .= '</select';
                }

                $sudokuHtml .= '</td>';

                // Raise the square index for the board
                $this->squareIndex ++;
            }
            $sudokuHtml .= '</tr>';
        }
        $sudokuHtml .= '</table>';

        return $sudokuHtml;
    }

    /**
    * This functoin is used to generate the view of a Sudoku board
     *
     * @param string  $numberSequence The sequence of the numbers as they should be
                                      displayed
     * @param boolean $fixedPositions Defines if the numbers are placed on a fixed
     *                                position within the board
     *
     * @return string The view of the board with the provided numeric string
     * @access public
     * @author WIM
     */
    public function printSudokuBoard($numberSequence = '', $fixedPositions = false)
    {
        // Check if the provided numeric string is long enough. Else expand it to
        // match the board length
        if (strlen($numberSequence) < $this->Sudoku->getNumberOfSquaresOnBoard()) {
            $numberSequence .= str_repeat(
                '0',
                ($this->Sudoku->getNumberOfSquaresOnBoard() - strlen($numberSequence))
            );
        }

        // Keep track of the total number of squares allready drawn
        $this->squareIndex = 1;
        // Start with the first row of blocks
        $sudokuHtml = '<table style="border-spacing: 0;">';
        for ($blockRow = 0; $blockRow < $this->Sudoku->getNumberBlockPerSide(); $blockRow++) {
            $sudokuHtml .= '<tr>';
            // Render the columns within the blocks
            for ($blockColumn = 0; $blockColumn < $this->Sudoku->getNumberBlockPerSide(); $blockColumn++) {
                $sudokuHtml .= '<td style="border: solid 2px #000;">';

                // Retrieve the numbers which should be displayed within this block
                $blockNumber = $blockRow === 0
                    ? $blockColumn : $blockRow * $this->Sudoku->getNumberBlockPerSide() + $blockColumn;
                $squaresPerBlock = $this->Sudoku->getNumberOfSquaresInBlock();
                $blockNumber ++;
                $blockSquareMax = $squaresPerBlock * $blockNumber;
                $blockNumberSequence = substr(
                    $numberSequence,
                    $blockSquareMax - $squaresPerBlock,
                    $blockSquareMax
                );
                // Generate the view for this block
                $sudokuHtml .= $this->printSingleBlock($blockNumberSequence, $fixedPositions);

                $sudokuHtml .= '</td>';
            }
            $sudokuHtml .= '</tr>';
        }
        $sudokuHtml .= '</table>';

        // Return the generated view
        return $sudokuHtml;
    }
}
