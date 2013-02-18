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
?>