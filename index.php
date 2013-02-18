<?php
require "resources/secret/mysqlconnect.php";
require "resources/header.php";

if(!$logged_in){
	displayLogin();
	exit();
}
echo '<div id="welcome-span">Welcome, '.$current_usr_fn.' <a href="'.$SITE_ROOT.'?logout=1">Logout</a></div>';

echo '<div id="navigation"><a href="'.$SITE_ROOT.'">Home</a> | <a href="'.$SITE_ROOT.'?p=boxloading">Box Loading</a> | <a href="'.$SITE_ROOT.'?p=craneloading">Crane Loading</a>';
if($logged_in){
	if($current_usr_admin >= 5){
		echo ' | <a href="'.$SITE_ROOT.'?p=export">Export</a>';
	}
}
echo '</div>';

$bookNumQuery = mysql_query("SELECT COUNT(idbook) AS num FROM $books_table") or die(mysql_error());
$b_result = mysql_fetch_array($bookNumQuery);
$b_num = $b_result['num'];
echo '<div id="books-scanned"><br>Books Scanned: '.number_format($b_num, 0).'</div>';

//load the page, if one is requested.

if(isset($_GET['p']) && $_GET['p'] != ""){
	$page = addslashes($_GET['p']);
	$filename = getcwd()."/pages/".$page.".php";
	if(file_exists($filename)){
		include ($filename);
	} else {
		//output a 404 not found error (or for that matter, be silent about the subject... you decide)
		echo '<h2>404 file not found</h2>';
	}
} else {
	//output the main landing page
	echo '<p>Welcome to the Library Migrator.  Please select the "Box Loading" tab for loading the "shoeboxes" in Zumberge or the "Crane Loading" tab for the loading of the "shoeboxes" into the ASRS carriers.</p>';
}

require "resources/footer.php";
?>