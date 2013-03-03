<?php
$box_already_exists = false;
$invalid_barcode = false;
$duplicate_skus = array();	//array of duplicate skus found

checkSubmit();

echo '<h3>Box Loading</h3>';

if(count($duplicate_skus) != 0 || $box_already_exists || $invalid_barcode){
	echo '<div id="duplication-error" class="lib-error size1of4">';
	if($box_already_exists){
		echo 'That box was already scanned into the system.';
	}
	if($invalid_barcode){
		echo 'At least one of the barcodes did not have the leading '.$BARC_FIRST_DIGITS.' string of digits.  Please scan again.';
	}
	if(count($duplicate_skus) != 0){
		if($box_already_exists) echo '<br><br>';
		echo 'The book(s) with SKU = "';
		for($i = 0; $i < count($duplicate_skus); $i++){
			if($i + 1 != count($duplicate_skus)){
				echo $duplicate_skus[$i].', ';
			} else {
				echo ' and '.$duplicate_skus[$i];
			}
		}
		echo '" are duplicates.';
	}
	echo '</div>';
}

echo '
<div class="lib-form">
	<form action="" method="POST" onsubmit="return validateBox();">
		<div class="row size1of4">
			<label>Size: </label><br>
			<input type="radio" id="c02" name="box-size" value="C02"><label for="c02">CO2</label><br>
			<input type="radio" id="c03" name="box-size" value="C03"><label for="c03">CO3</label><br>
			<input type="radio" id="k02" name="box-size" value="K02"><label for="k02">K02</label><br>
			<span id="radio-button-error" class="lib-error" style="display:none;">Please select a box size.</span>
			<br>
			<span id="box-size-error" class="lib-error" style="display:none;">Please choose a box size.<br></span>
			<div class="row"><input type="checkbox" tabindex="2" id="is-dedicated" name="is-dedicated"> <label for="is-dedicated" class="lib-inline">Is this a dedicated box?</label></div>
			<label for="box-barcode">Box Code: </label><br>
			<input type="text" tabindex="3" name="box-barcode" id="box-barcode" maxlength="'.$BOX_BC_LEN.'">
			<span id="box-barcode-error" class="lib-error" style="display:none;">This is an invalid box code.</span>
			<span id="icon-box"></span>
		</div>
		<h4>Books</h4>
		<ul id="book-list" class="size1of1">
			<li id="li-0" class="size1of4"><input class="book-input" id="0" tabindex="4" name="book_0" type="text" maxlength="'.$BOOK_BC_LEN.'"><span class="check-book-icon" id="icon-0"></span>
			<span id="error-empty-box" class="lib-error" style="display:none;">This box cannot be empty.</span>
			<span id="error-0" class="lib-error" style="display:none;">This is an invalid SKU.</span></li>
		</ul>
		<div class="row"><input type="submit" id="boxloading-submit" class="lib-button-small" value="Submit"></div>
	</form>
</div>';
	
function checkSubmit(){
	global $books_table, $duplicate_skus,
           $box_already_exists, $invalid_barcode, $BARC_FIRST_DIGITS, $BOOK_BC_LEN;
	if(isset($_POST['box-barcode']) && isset($_POST['book_0']) && $_POST['box-barcode'] != ""
			&& isset($_POST['box-size']) && $_POST['box-size'] != "-"){
		$box_bc = addslashes($_POST['box-barcode']);
		if(isset($_POST['is-dedicated'])){
			$is_dedicated = 1;
		} else {
			$is_dedicated = 0;
		}
		if(boxAlreadyExists($box_bc)){
			$box_already_exists = true;
		}
		
		//check for duplicate books in the POST array
		$dup_books_arr = array();
		$x = 0;
		while(isset($_POST['book_'.$x])){
			$barcode = addslashes($_POST['book_'.$x]);
			if($barcode == "") break;	//this is the last book barcode
			
			//check if the barcode has the correct leading digits
			if(strcmp(substr($barcode, 0, strlen($BARC_FIRST_DIGITS)), $BARC_FIRST_DIGITS) != 0 ||
					strlen($barcode) != $BOOK_BC_LEN){
				$invalid_barcode = true;
			}
			
			if(!array_key_exists($barcode, $dup_books_arr)){
				$dup_books_arr[$barcode] = 1;
			} else {
				$dup_books_arr[$barcode]++;
			}
			$x++;
		}
		foreach($dup_books_arr as $key => $value){
			if($value != 1){
				array_push($duplicate_skus, $value);
			}
		}
		
		//check if any of the books are already in the database, if so, don't insert any of them
		$j = 0;
		while(isset($_POST['book_'.$j])){
			if($_POST['book_'.$j] != ""){
				$sku = addslashes($_POST['book_'.$j]);
				if(mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE sku = '$sku'")) != 0){
					array_push($duplicate_skus, $sku);
				}
			}
			$j++;
		}
		
		if(count($duplicate_skus) != 0 || $box_already_exists || $invalid_barcode){
			return false; //end function before inserting data into db
		}
		
		//insert the books into the database
		$i = 0;
		while(isset($_POST['book_'.$i])){	//iterate through the $_POST array
			if($_POST['book_'.$i] != ""){
				$sku = addslashes($_POST['book_'.$i]);
				$box_size = addslashes($_POST['box-size']);
				mysql_query("INSERT INTO $books_table (sku, box_code, box_size, dedicated)
				 				VALUES('$sku', '$box_bc', '$box_size', '$is_dedicated')") or die(mysql_error());
			}
			$i++;
		}
	}
}
?>