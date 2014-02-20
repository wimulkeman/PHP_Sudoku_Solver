<?php
/**
 * This class is used to provide other classes with default informartion about the
 * Sudoku game
 *
 * @author  WIM
 * @version Release: $Id:$
 */
class Sudoku
{
    /**
     * The maximum number that can be used in the game
     *
     * @var    integer
     * @access private
     */
    private $rangeMax = 9;

    /**
     * Provide the available numbers in the Sudoku game
     *
     * @return array   The available numbers
     * @access public
     * @author WIM
     */
    public function getAvailableNumbers()
    {
        return range(1, $this->rangeMax);
    }

    /**
     * Set the maximum number that can be used in this game
     *
     * @param integer $number The maximum number
     *
     * @return void
     * @access public
     * @author WIM
     */
    public function setMaxNumber($number)
    {
        if (empty($number) || !is_numeric($number)) {
            throw new Exception('No (valid) number has been provided');
        }

        $this->rangeMax = $number;
    }

    /**
     * Get the border length of the board
     *
     * @return integer The length of the border
     * @access public
     * @author WIM
     */
    public function getBoardBorderLength()
    {
        return count($this->getAvailableNumbers());
    }

    /**
     * Get the border length of a single block on the board
     *
     * @return integer The border length of a single block
     * @access public
     * @author WIM
     */
    public function getBlockBorderLength()
    {
        // Calculate the length of the border of the board
        $length = sqrt(count($this->getAvailableNumbers()));

        // Check if the recieved length is valid
        if ((int) $length != $length) {
            throw new Exception('No valid value for the border length could be calculated');
        }

        return $length;
    }

    /**
     * Calculate the number o fblocks provided at one side of the board
     *
     * @return integer The number of blocks at one side
     * @access public
     * @author WIM
     */
    public function getNumberBlockPerSide()
    {
        return $this->getBoardBorderLength() / $this->getBlockBorderLength();
    }

    /**
     * Calculate the number of squares on one side of the board
     *
     * @return integer The number of squares on one side
     * @access public
     * @author WIM
     */
    public function getNumberOfSquaresOnBoard()
    {
        return $this->getBoardBorderLength() * $this->getBoardBorderLength();
    }

    /**
     * Get the number of squares within a block
     *
     * @return integer The number of squares
     * @access public
     * @author WIM
     */
    public function getNumberOfSquaresInBlock()
    {
        return count($this->getAvailableNumbers());
    }
}
