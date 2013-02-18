<?php
require "../resources/secret/mysqlconnect.php";
require "../resources/settings.php";

if(isset($_GET['box_c']) && $_GET['box_c'] != ""){
	$boxcode = addslashes($_GET['box_c']);
	$count = mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE box_code = '$boxcode'"));
	if($count > 0){
		echo '1';
	} else {
		//box does not exist
		echo '0';
	}
} else {
	//no box set
	echo '1';
}
?>