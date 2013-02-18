<?php
$carrierStyle = "";

//boolean variables to indicate error stati
$invalid_carrier = false;
$carrier_exists = false;
$boxes_dne = false;
$boxes_dup = false;

checkSubmit();

echo '
<h3>Crane Loading</h3>
<div class="lib-form">
	<h4>Carrier Style</h4>
	<form method="GET">
		<input type="hidden" name="p" value="craneloading">';

if($carrierStyle == "c02"){
	echo '<input type="radio" id="r-c02" name="c" onchange="this.form.submit();" value="c02" checked><label for="r-c02">C02</label>';
} else {
	echo '<input type="radio" id="r-c02" name="c" onchange="this.form.submit();" value="c02"><label for="r-c02">C02</label>';
}

echo '<br>';

if($carrierStyle == "c03"){
	echo '<input type="radio" id="r-c03" name="c" onchange="this.form.submit();" value="c03" checked><label for="r-c03">C03</label>';
} else {
	echo '<input type="radio" id="r-c03" name="c" onchange="this.form.submit();" value="c03"><label for="r-c03">C03</label>';
}

echo '<br>';

if($carrierStyle == "k02"){
	echo '<input type="radio" id="r-k02" name="c" onchange="this.form.submit();" value="k02" checked><label for="r-k02">K02</label>';
} else {
	echo '<input type="radio" id="r-k02" name="c" onchange="this.form.submit();" value="k02"><label for="r-k02">K02</label>';
}

echo '
	</form>
</div>
<div class="lib-form">';


if($invalid_carrier || $carrier_exists || $boxes_dne || $boxes_dup){
	echo '<div id="duplication-error" class="lib-error size1of4">';
	if($invalid_carrier){
		echo 'This is an invalid carrier label.';
	}
	if($carrier_exists){
		echo 'This carrier label has already been entered into the database.';
	}
	if($boxes_dne){
		if($carrier_exists) echo '<br><br>';
		echo 'One or more of these boxes does not exist in the database.';
	}
	if($boxes_dup){
		if($carrier_exists || $boxes_dne) echo '<br><br>';
		echo 'There is at least one duplicate box in this carrier.';
	}
	echo '</div>';
}

if($carrierStyle == "") {
	echo '<div class="lib-alert size1of4">Please choose a carrier style.</div>';
} else {
	echo '<form action="" method="POST" onsubmit="return validateCrane();">
		<h4>Carrier</h4>
		<div class="row"><label for="carrier-label">Carrier Label</label></span><br>
		<input type="text" id="carrier-label" name="carrier-label" class="first-focus carrier-input" maxlength="'.$CARRIER_LEN.'"><span id="carrier-icon"></div>
		<br>
		<label>Box Codes</label><br>
		<table>';
		
		//show the correct carrier inputs
		if($carrierStyle == "c03"){
			//3 rows from A to C
			showCarrier('A', 'C', 3);
		} else if($carrierStyle == "c02"){
			//2 rows from A to C
			showCarrier('A', 'C', 2);
		} else if($carrierStyle == "k02"){
			//2 rows from A to K
			showCarrier('A', 'K', 2);
		}
		
	echo '</table>
		<input type="hidden" name="c" value="'.$carrierStyle.'">
		<div class="lib-error" id="crane-err-all-fields" style="display:none;">You must fill in all the cells.</div>
		<div class="row"><input type="submit" class="lib-button-small" value="Submit"></div>
	</form>';
}

echo '</div>';


/*
 * Prints out a carrier given the start and end characters for the carrier cell letters
 * and the number of the top row of the carrier (it counts down to row 1)
 */
function showCarrier($f_ch, $l_ch, $top_row){
	$start = ord($f_ch);
	$end   = ord($l_ch);
	global $BOX_BC_LEN;
	//this method of numbering rows is valid for 0 through 9.
	for($row = $top_row; $row >= 1; $row--){
		echo '<tr>';
		for($i = $start; $i <= $end; $i++){
			echo '<td><input type="text" class="box-input" placeholder="'.chr($i).'0'.$row.'" id="'.strtolower(chr($i)).'0'.$row.'" name="'.chr($i).'0'.$row.'" maxlength="'.$BOX_BC_LEN.'"></td>';
		}
		echo '</tr>';
	}
}

/*
 * Inserts a carrier into the database.
 */
function ins_carr($box_code, $carrier_label, $cell_id){
	global $books_table;
	if($box_code != 0 && $box_code != ""){
		mysql_query("UPDATE $books_table SET carrier_label = '$carrier_label', cell_id = '$cell_id' WHERE box_code = '$box_code'") or die(mysql_error());
	}
}

/*
 * Returns true if the carrier label $cl already exists in the database; false otherwise.
 */
function carr_exist($cl){
	global $books_table;
	return mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE carrier_label = '$cl'")) > 0;
}

/*
 * Returns true if the box code $bc is invalid or if the box code exists in the database.  Returns false otherwise.
 */
function box_exists($bc){
	global $books_table;
	if($bc != "" && $bc != 0){
		return mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE box_code = '$bc'")) > 0;
	}
	return true;
}

/*
 * If an element exists at index in the arr array, the number there is incremented; else 
 * that index is created with an initial value of 1.  This is used by checkSubmit() and/or processBooks()
 * to track if there is more than one of each box code.
 */
function box_array_ins(&$arr, $index){
	if(!array_key_exists($index, $arr)){
		$arr[$index] = 1;
	} else {
		$arr[$index]++;
	}
}

/*
 * This function both validates the different carrier configurations and imports the books
 * into the database if the data is valid.
 */
function processBooks($carrier_label, $carrier_style){
	global $carrier_exists, $books_table, $boxes_dne, $boxes_dup, 
		$CARRIER_LEN, $BOX_BC_LEN, $CARRIER_PREFIXES, $invalid_carrier;
	
	//validate the carrier label
	//make sure the carrier label has the proper form as given...
	$invalid_carrier = true;
	foreach($CARRIER_PREFIXES AS $key => $prefix){
		if(strcmp(substr($carrier_label, 0, strlen($prefix)), $prefix) == 0){
			$invalid_carrier = false;
		}
	}
	//check the length of the carrier label...
	if(strlen($carrier_label) != $CARRIER_LEN){
		$invalid_carrier = true;
	}
	$carrier_exists = carr_exist($carrier_label);
	
	if($carrier_exists || $invalid_carrier){
		return false;	//no sense evaluating the books if the carrier label is wrong already...
	}
	
	//array that specifies the number of each box code
	$box_array = array();
	//ASCII codes for capital letters for the carrier cells...
	$start = 65; //A
	if($carrier_style == "c02"){
		$end = 67; //C
		$firstRow = 2;
	} elseif($carrier_style == "c03"){
		$end = 67; //C
		$firstRow = 3;
	} elseif($carrier_style == "k02"){
		$end = 75; //K
		$firstRow = 2;
	}
	//iterate through all the box inputs as specified by the parameters immediately above.
	//go through the rows by numbers...
	for($row = $firstRow; $row >= 1; $row--){
		// and the columns by letters...
		for($i = $start; $i <= $end; $i++){
			//check if any of the specified box inputs are empty, have a string length different from the standard,
			//or if the box codes do not exist in the database
			$cell_str = chr($i).'0'.$row;
			$box_code = $_POST[$cell_str];
			if(empty($box_code) || strlen($box_code) != $BOX_BC_LEN ||
					!box_exists($box_code)){
				$boxes_dne = true;
			}
			box_array_ins($box_array, $box_code);
		}
	}
	//check that there is only one entry per box code
	foreach($box_array as $key => $value){
		if($value != 1 && $key != "0"){
			$boxes_dup = true;
			break;
		}
	}
	
	
	if($invalid_carrier || $carrier_exists || $boxes_dne || $boxes_dup) return false;	//don't update database
	
	/*
	 * Otherwise, all things appear to be in the clear and we are ready for takeoff.
	 * Fasten your seatbelts and prepare for takeoff as we taxi toward the database and...
	 */
	
	//iterate through all the box inputs as specified by the parameters above.
	//go through the rows by numbers...
	for($row = $firstRow; $row >= 1; $row--){
		// and the columns by letters...
		for($i = $start; $i <= $end; $i++){
			$cell_id = chr($i).'0'.$row;
			$box_code = $_POST[$cell_id];
			//update the database! Mark this box code with the carrier label and cell_id
			$sql = "UPDATE $books_table SET carrier_label = '$carrier_label', cell_id = '$cell_id' WHERE box_code = '$box_code'";
			mysql_query($sql) or die(mysql_error());
		}
	}
}
	
function checkSubmit(){
	global $carrierStyle, $carrier_exists, $boxes_dne, $boxes_dup, $BOX_BC_LEN;
	if(isset($_GET['c']) && !empty($_GET['c'])){
		$carrierStyle = trim(addslashes($_GET['c']));
	}
	
	//carrier submission...
	if(isset($_POST['carrier-label'])){
		$carrier_label = addslashes($_POST['carrier-label']);
		processBooks($carrier_label, $carrierStyle);
	}
}
?>