<?php
session_start();
require "resources/settings.php";
require "resources/secret/mysqlconnect.php";
require "resources/functions.php";
require "resources/login.php";

//the new line character sequence
$newline = "\r\n";

if($logged_in){
	if(isset($_POST['lim'])){
		//this means the form for downloading was submitted... process it
		$lim = addslashes($_POST['lim']);
		(isset($_POST['new-entries'])) ? $new_entries = true : $new_entries = false;
		if(isset($_POST['database'])){
			exportdb($lim);
		} else if(isset($_POST['barcodes'])){
			exportSKUs($lim);
		}
	}
} else {
	echo 'Access denied.';
}

/*
 * Exports a list of the SKUs in the database, one per line.
 */
function exportSKUs($exp_limit){
	global $books_table, $new_entries, $newline;
	$gzipfn = 'migrator-SKUs-'.date('Y-m-d').'-'.time().'.txt.gzip';
	$mimeType = 'application/x-gzip';
	
	$zh = gzopen($gzipfn,'w');
	
	if(!$new_entries){
		$sql = "SELECT sku FROM $books_table";	//production
	} else {
		$sql = "SELECT sku FROM $books_table WHERE sku_exported = '0'";	//production
	}
	if($exp_limit > 0){
		$sql .= " LIMIT ".$exp_limit;
	}
	$query = mysql_query($sql) or die(mysql_error());
	while($result = mysql_fetch_array($query)){
		$curr_line = $result['sku'].$newline;
		gzwrite($zh, $curr_line);
	}
	gzclose($zh);
	
	//mark the SKUs that were just exported as exported in the database
	mysql_query("UPDATE $books_table SET sku_exported = '1' WHERE sku_exported = '0'") or die(mysql_error());
	
	promptDownload($gzipfn, $mimeType);
}

/*
 * This method exports the database in the format Dematic needs.
 * Dematic needs a text file that line by line has:
 * carrier_code,cell_id,sku,dedicated
 */
function exportdb($exp_limit){
	global $EXP_DELIM, $books_table, $new_entries, $newline;
	$gzipfn = 'migrator-'.date('Y-m-d').'-'.time().'.txt.gzip';
	$mimeType = 'application/x-gzip';
	
	$zh = gzopen($gzipfn,'w');
	
	if(!$new_entries){
		$sql = "SELECT sku, box_code, dedicated, carrier_label, cell_id FROM $books_table WHERE carrier_label != '' AND carrier_label != '0'";	//production
	} else {
		$sql = "SELECT sku, box_code, dedicated, carrier_label, cell_id FROM $books_table WHERE exported = '0'
			AND carrier_label != '' AND carrier_label != '0'";	//production
	}
	if($exp_limit > 0){
		$sql .= " LIMIT ".$exp_limit;
	}
	$query = mysql_query($sql) or die(mysql_error());
	while($result = mysql_fetch_array($query)){
		$curr_line = $result['carrier_label'].$EXP_DELIM.$result['cell_id'].$EXP_DELIM.$result['sku'].$EXP_DELIM.$result['dedicated'].$newline;
		gzwrite($zh, $curr_line);
	}
	gzclose($zh);
	
	//mark the books that were just exported as exported in the database
	mysql_query("UPDATE $books_table SET exported = '1' WHERE exported = '0' AND carrier_label != '0' AND carrier_label != ''") or die(mysql_error());
	
	promptDownload($gzipfn, $mimeType);
}

function promptDownload($fn, $mType){
	if(!file_exists($fn)) {
		// File doesn't exist, output error
		die('file not found');
	} else {
		// Set headers
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$fn");
		header("Content-Type: ".$mType);
		// Read the file from disk
		readfile($fn);
		//delete the file
		unlink($fn);
	}
}
?>