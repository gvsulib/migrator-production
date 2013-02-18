<?php
require "../resources/secret/mysqlconnect.php";
require "../resources/settings.php";

if(isset($_GET['bc']) && $_GET['bc'] != ""){
	$sku = addslashes($_GET['bc']);
	$count = mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE sku = '$sku'"));
	if($count > 0){
		echo '1';
	} else {
		//book does not exist
		echo '0';
	}
} else {
	//no book set
	echo '1';
}
?>