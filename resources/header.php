<?php
session_start();
$m = NULL; // Reset the error messages
require "settings.php";
require "login.php";
require "functions.php";
if($logged_in) { include "../labs/includes/current_user.php"; }

if(isset($_GET['logout']) && $_GET['logout'] == "1"){
	logout();
	header("Location:".$SITE_ROOT);
}

echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<title>'.$SITE_NAME.'</title>
			<link rel="stylesheet" type="text/css" href="http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/libui.css" />
			<link rel="stylesheet" type="text/css" href="'.$SITE_ROOT.'resources/css/styles.css">
		</head>
		<body>
		<h2>'.$SITE_NAME.'</h2>';
?>