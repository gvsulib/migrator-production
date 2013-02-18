<?php
require "../resources/secret/mysqlconnect.php";
require "../resources/settings.php";

if(isset($_GET['carrier']) && $_GET['carrier'] != ""){
	$carrier_code = addslashes($_GET['carrier']);
	$count = mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE carrier_label = '$carrier_code'"));
	if($count > 0){
		//carrier exists
		echo '1';
	} else {
		//carrier does not exist
		echo '0';
	}
} else {
	//no carrier set
	echo '1';
}
?>