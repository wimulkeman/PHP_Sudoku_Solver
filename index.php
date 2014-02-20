<?php
// A dummy gameset to solve
$blocks = array(
    '006000300', '309000080', '070132060',
    '009070465', '400000001', '000040900',
    '040927080', '060500902', '007000300',
);
$board = implode('', $blocks);

// Initiate the required classes
include('painter.php');
include('parser.php');
include('generator.php');
include('calculator.php');
include('sudoku.php');
include('validator.php');

$Validator = new Validator();

$Sudoku = new Sudoku();

$Generator = new SudokuGenerator($Sudoku);
$Parser = new SudokuParser($Sudoku);
$Painter = new SudokuPainter($Sudoku, $Parser);
$Calculator = new SudokuCalculator($Sudoku, $Generator, $Painter, $Validator);

// Cgecj if the board only needs to be drawn, or a solution to be calculated
if (!empty($_POST['square']) || PHP_SAPI == 'cli') {
    // If runned in CL mode, use the provided board input
    if (PHP_SAPI == 'cli') {
        $numberstring = $board;
    } else {
        // Retrieve the provided gameset
        $numberstring = $Generator->generateNumberStringFromArray($_POST['square']);
    }

    // Start with validating the provided gameset
    $Calculator->checkCorrectnessNumberstring($numberstring);

    // Try to calculate a solution
    $solution = $Calculator->calculateSolution($numberstring);

    // Draw the final solution onto a board
    echo $Painter->printSudokuBoard($solution, true);
} else {
    // Draw a empty board
    echo <<<html
        <form method="post">
            {$Painter->printSudokuBoard($board)}
            <input type="submit" value="Oplossen">
        </form>
html;
}

// Draw a Sudoku gameboard
// p102
//echo $Painter->printSudokuBoard($board, false);
