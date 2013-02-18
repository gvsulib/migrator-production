<?php
require "../settings.php";
Header("content-type: application/x-javascript");
echo '
/* GLOBAL VARIABLES */

var box_bc_len  = '.$BOX_BC_LEN.';
var book_bc_len = '.$BOOK_BC_LEN.';
var carrier_len = '.$CARRIER_LEN.';

//box loading validation
var is_box_original = false;

//carrier loading validation
var carr_in_db = true;

/* END VARIABLES */

if($(".first-focus").length > 0){
	$(".first-focus")[0].focus();	//focus on the first element of the class "first-focus"
}

/* Follow the box loading text inputs */
$("#box-barcode").keyup(function(e){
	if($("#box-barcode").val().length == box_bc_len){	//when the box-barcode input is filled...
		doesBoxAlreadyExist();
		followBookSKUInput(0);	//advance to listening to the individual book inputs
	}
});

$(".box-input").change(function(){
	//validateBoxCodes();
	markDuplicateBoxes();
});

/* Follow the crane loading text inputs */
if($("#carrier-label").length > 0){
	$("#carrier-label").keyup(function(e){
		if($("#carrier-label").val().length == carrier_len){
			validateCLabel($("#carrier-label").val());
		}
	});
}

/* BOX LOADING FUNCTIONS */

function doesBoxAlreadyExist(){
	var datastring = "box_c="+$("#box-barcode").val();
	$.get(  
       "'.$CONTROLLER_ROOT.'checkbox.php?"+datastring,  
       {language: "php", version: 5},  
       function(data){
			if(data == "0"){
				//good
				is_box_original = true;
				$("span#icon-box").hide();
			} else {
				//oops--this is bad the box is already in the database
				is_box_original = false;
				$("span#icon-box").html(\'<img class="box-already-exists" src="'.$SITE_ROOT.'resources/img/'.$RED_ERROR_ICON.'">\').show();
				$("img.box-already-exists").qtip({
					content: \'This box is already in the database.\',
					show: \'mouseover\',
					hide: \'mouseout\',
					style: {
						name:\'red\',
						tip:\'leftMiddle\'
					},
					position :{
						corner: {
							target: \'rightMiddle\',
							tooltip: \'leftMiddle\'
						}
					}
				});
			}
		}
	 );
}

function bookExistsOnPage(b_code){
	var count = 0;
	$(".book-input").each(function(e){
		if($(this).val() == b_code){
			count++;
		}
	});
	if(count > 1){
		return true;	//more than one instance of the book\'s sku
	} else {
		return false;	//only one instance of the book\'s sku
	}
}

function bookExistsInDB(id){
	var datastring = "bc="+$("#"+id).val();
	$.get(  
       "'.$CONTROLLER_ROOT.'checkbook.php?"+datastring,  
       {language: "php", version: 5},  
       function(data){
			if(data == "0"){
				//good---> display green checkmark
				$("span#icon-"+id).hide();
			} else {
				//oops!  There was at least 1 match to the SKU in the database
				$("span#icon-"+id).html(\'<img class="book-already-exists" src="'.$SITE_ROOT.'resources/img/'.$YELLOW_ERROR_ICON.'">\').show();
				$("img.book-already-exists").qtip({
					content: \'This sku is already in the database.\',
					show: \'mouseover\',
				 	hide: \'mouseout\',
					style: {
						name:\'cream\',
						tip:\'leftMiddle\'
					},
					position :{
						corner: {
							target: \'rightMiddle\',
							tooltip: \'leftMiddle\'
						}
					}
				});
			}
		}
	 );
}

function followBookSKUInput(id){
	var new_id = parseInt(id) + 1;	//get the next id
	var new_ti = new_id + 4;		//new tab index
	$("#"+id).focus();	//focus this input
	if($("#"+id).val().length == book_bc_len){	//this input is full already...
		followBookSKUInput(new_id);
	}
	$("#"+id).keyup(function(e){	//every time a keyup event triggers...
		var sku = $("#"+id).val();
		if(sku.length == book_bc_len){	//this book sku input is full...
			bookExistsInDB(id);
			validateBox();
			if(!$("#"+new_id).length > 0){
				//create a new list element because this is the last one
				var new_element = "<li id=\"li-"+new_id+"\"  class=\"size1of4\"><input class=\"book-input\" id=\""+new_id+"\" tabindex=\""+new_ti+
				"\" name=\"book_"+new_id+"\" type=\"text\" maxlength=\""+book_bc_len+"\"><span class=\"check-book-icon\" id=\"icon-"+new_id+"\"></span>"+
				"<span id=\"error-"+new_id+"\" class=\"lib-error\" style=\"display:none;\">This is an invalid sku.</span>"+
				"</li>";
				$(new_element).insertAfter("#li-"+id);	//insert a new li element with an input in it with the new id
			}
			followBookSKUInput(new_id);	//listen on that element
		} else if($("#"+id).val().length == 0){
			$("span#icon-"+id).html("");	//empty the icon area
		}
	});
}

//validates the form for putting books into boxes
function validateBox(){
	var pass = 1;

	if(!is_box_original){
		doesBoxAlreadyExist();	//check again...
	}
	if(!is_box_original){
		pass = 0;	//box already exists in database
	}
	if(!$("#c02")[0].checked && !$("#c03")[0].checked && !$("#k02")[0].checked){
		pass = 0;	//none of the radio buttons for box size are checked
		$("#radio-button-error").show();
	} else {
		$("#radio-button-error").hide();
	}
	if($("#box-barcode").val().length != box_bc_len || !$.isNumeric( $("#box-barcode").val() )){	//if the box barcode is not full or is not a number...
		pass = 0;
		$("#box-barcode-error").show();
	} else {
		$("#box-barcode-error").hide();
	}
	if($("#0").val().length == 0){
		pass = 0;	//there must at least be one book in this box...
		$("#error-empty-box").show();
	} else {
		$("#error-empty-box").hide();
	}
	if($("#box-size").val() == "-"){
		$("#box-size-error").show();
		pass = 0;	//the user must choose a box size...
	} else {
		$("#box-size-error").hide();
	}
	$(".book-input").each(function(){
		var sku = $(this).val();
		var sku_len = sku.length;
		var id = $(this).attr("id");
		if(sku_len < book_bc_len && sku_len > 0){	//sku is partially full
			pass = 0;
			$("#error-"+id).show();
		} else {
			$("#error-"+id).hide();
		}
		
		if(bookExistsOnPage(sku) ){	//sku already exists on the page
			pass = 0;
			$("#"+id).css("color", "red");
		} else {
			$("#"+id).css("color", "black");
		}
		
	});
	
	if(pass == 1){
		return true;	//A value of "true" means the form will submit.
	}
	return false;	//validation failed--the user must fix the form input
}

/* END BOX LOADING SCRIPTS */



/* CARRIER FUNCTIONS */

function validateCLabel(c_label){
	var datastring = "carrier="+c_label;
	$.get(  
       "'.$CONTROLLER_ROOT.'checkcarrier.php?"+datastring,  
       {language: "php", version: 5},  
       function(data){
			if(data == "1"){
				$("span#carrier-icon").html(\'<img class="carrier-already-exists" src="'.$SITE_ROOT.'resources/img/'.$RED_ERROR_ICON.'">\').show();
				$("img.carrier-already-exists").qtip({
					content: \'This carrier is already in the database.\',
					show: \'mouseover\',
					hide: \'mouseout\',
					style: {
						name:\'red\',
						tip:\'leftMiddle\'
					},
					position :{
						corner: {
							target: \'rightMiddle\',
							tooltip: \'leftMiddle\'
						}
					}
				});
				carr_in_db = true;
			} else {
				$("span#carrier-icon").hide();
				carr_in_db = false;
			}
		}
		);
}

function validateBoxCodes(){
	$(".box-input").each(function(){
		var code = $(this).val();
		var datastring = "box_c=" + code;
		$.get(  
		   "'.$CONTROLLER_ROOT.'checkbox.php?"+datastring,  
		   {language: "php", version: 5},  
		   function(data){
				if(data == "0" || code.length != box_bc_len){
					$(this).css("color", "red");
					$(this).qtip({
						content: \'This box is not in the database.\',
						show: \'mouseover\',
						hide: \'mouseout\',
						style: {
							name:\'red\',
							tip:\'bottomMiddle\'
						},
						position :{
							corner: {
								target: \'topMiddle\',
								tooltip: \'bottomMiddle\'
							}
						}
					});
				} else {
					$(this).css("color", "black");
					try{
						$(this).qtip(\'destroy\');
					} catch(err){}
				}
			}
			);
	});
}

function markDuplicateBoxes(){
	$(".box-input").each(function(index, element){
		if(  checkUniqueBoxes( $(this).val() )  ){
			pass = 0;
			$(this).css("background", "#ff7f7f");
			$(this).qtip({
				position: {
					corner: {
				        target: \'topMiddle\',
				       	tooltip: \'bottomMiddle\'
				    }
				},
				content: \'Duplicate assignment.\',
				show: \'mouseover\',
				hide: \'mouseout\',
				style: {
					name:\'red\',
					tip:\'bottomMiddle\'
				}
			});
		} else {
			$(this).css("background", "white");
			try{
				$(this).qtip("destroy");
			}
			catch (err){}
		}
	});
}

//checks if the same value is present in more than one text input, either
//the carrier label or the box inputs
function checkUniqueBoxes(b_code){
	var count = 0;
	$(".box-input").each(function(e){
		if($(this).val() == b_code && $(this).val().length != 0){
			count++;
		}
	});
	if(count >= 2){
		return true;
	} else {
		return false;
	}
}

//validates the form for putting boxes into the crane carriers
function validateCarrier(){
	var pass = 1;
	$(".box-input").each(function(index, element){
		if($(this).val().length == 0){	//you must fill in all fields
			pass = 0;
			$("#crane-err-all-fields").show();
		} else {
			$("#crane-err-all-fields").hide();
		}
	});
	
	if(carr_in_db){
		pass = 0;
	}
	
	if(pass == 1){
		return true;
	}
	return false;
}

/* END CARRIER FUNCTIONS */
';

?>