 <?php

//CONSTANTS
$SITE_NAME = "Library Migrator";
$SITE_ROOT = "http://".$_SERVER['SERVER_NAME']."/migrator/";
$CONTROLLER_ROOT = $SITE_ROOT."controllers/";

//MySQL Tables
$user_table = 'users';
$books_table = 'mig_books';

$EXP_DELIM = ',';

$BARC_FIRST_DIGITS = 32260;
$BOOK_BC_LEN = 14;
$BOX_BC_LEN  = 6;

$CARRIER_LEN = 10;
$CARRIER_PREFIXES = array('M01W', 'M01E', 'M02W', 'M02E');

//Error icon names
$RED_ERROR_ICON    = 'pc-ghost-red.png';
$BLUE_ERROR_ICON   = 'pc-ghost-blue.png';
$YELLOW_ERROR_ICON = 'pc-ghost-yellow.png';
$GREEN_ERROR_ICON  = 'pc-ghost-green.png';

//Carrier cell array of valid cell labels
$CARRIER_CELL_ARRAY = array("A01", "B01", "C01", "D01", "E01", "F01", "G01", "H01", "I01", "J01", "K01", 
						"A02", "B02", "C02", "D02", "E02", "F02", "G02", "H02",
						"A03", "B03", "C03");

//Empty carrier cell string
$EMPTY_CARRIER_CELL_STR = 'e';
?>