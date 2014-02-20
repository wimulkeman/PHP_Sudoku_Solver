<?php
/**
 * The generator class is used to create the numeric strings which are used for
 * calculating solutions
 *
 * @author  WIM
 * @version Release: $Id:$
 */
class SudokuGenerator
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
     * The generated combinactions for a block
     *
     * @var    array
     * @access private
     */
    private $generatedCombinations = array();

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

    /**
     * Generate all the possible combinations for a block
     *
     * @param string $fixedPositions The numeric string with all the positions
     *                               and numbers known
     *
     * @return array  The possible combinations for the block
     * @access public
     * @author WIM
     */
    public function generateBlockCombinations($fixedPositions = '')
    {
        // Reset all the possible solutions
        $this->generatedCombinations = array();

        // Retrieve the available numbers
        $numberRange = $this->Sudoku->getAvailableNumbers();

        // Remember the places which are allready filled in
        $fixedNumbers = array();
        if (strlen($fixedPositions) > 0) {
            for ($position = 0; strlen($fixedPositions) > $position; $position ++) {
                $number = $fixedPositions[$position];
                // Check if a number has been provided for this spot
                if ($number == 0) {
                    continue;
                }

                // Set this numbers position
                $fixedNumbers[$position] = $number;

                // Remove the number form the array with available numbers
                $numberPosition = array_search($number, $numberRange);
                if ($numberPosition === false) {
                    throw new Exception('A unknown number has been provided');
                }
                unset($numberRange[$numberPosition]);
            }
        }

        // Generate the available numeric strings
        $possibleCombinations = $this->generatePossibleNumberCombinations($numberRange, $fixedNumbers);

        // Return the possible combinations
        return $this->generatedCombinations;
    }

    /**
     * This functoin is used to calculate possible solutions based on a provided set
     * of numbers
     *
     * @param array  $numbers        The numbers still open for replacement
     * @param array  $fixedPositions The places with a number fixed upon it
     * @param string $combination    The combination created thus far
     *
     * @return array The possible combinations
     * @access private
     * @author WIM
     */
    private function generatePossibleNumberCombinations($numbers, $fixedPositions = array(), $combination = '')
    {
        $performLoop = true;
        // Check if this spot is allready filled
        if (isset($fixedPositions[strlen($combination)])) {
            // Add the predefined number to the array
            $combination = $combination . $fixedPositions[strlen($combination)];
            $performLoop = false;

            // Remove the nu,ber from the list with possible numbers
            unset($fixedPositions[strlen($combination) - 1]);

            // Check if this whas the last number from the list
            if (sizeof($numbers) === 0 && sizeof($fixedPositions) === 0) {
                // Add the solution to the possible combinations
                $this->generatedCombinations[] = $combination;

                return;
            }
        }

        // If there is more than 1 number still available, than keep trying to
        // generate a solution
        if (sizeof($numbers) > 1 || sizeof($fixedPositions) > 0) {
            if ($performLoop === false) {
                $this->generatePossibleNumberCombinations($numbers, $fixedPositions, $combination);
            } else {
                foreach ($numbers as $key => $number) {
                    // Create a temp of the available numbers
                    $numbersTemp = $numbers;
                    // Remote the current number from the temp var
                    unset($numbersTemp[$key]);
                    // Enrich the current solution and proceed with the solution
                    $newCombination = $combination . $number;
                    $this->generatePossibleNumberCombinations($numbersTemp, $fixedPositions, $newCombination);
                }
            }
        } else {
            // Add the last number to the solution
            reset($numbers);
            $combination .= current($numbers);

            // Add this solution to the possible combinations
            $this->generatedCombinations[] = $combination;
        }
    }

    /**
     * Convert a aray with numbers to a numeric string which can be used in the
     * Sudoku solver
     *
     * @param array $numberArray The number array as provided by browser board input
     *
     * @return string The converted numeric string
     * @access public
     * @author WIM
     */
    public function generateNumberStringFromArray(array $numberArray = array())
    {
        // Validate the format of the input
        if (empty($numberArray) || !is_array($numberArray)) {
            throw new Exception('No (valid) input given to convert');
        }

        // Ensure the array is in the correct order
        ksort($numberArray);

        return implode('', $numberArray);
    }
}
