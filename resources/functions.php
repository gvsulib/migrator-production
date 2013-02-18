<?php

function boxAlreadyExists($bc){
	global $books_table;
	$val = mysql_num_rows(mysql_query("SELECT idbook FROM $books_table WHERE box_code = '$bc'"));
	if($val > 0){
		return true;
	} else {
		return false;
	}
}

/**
**Checks the specified action parameter--if it is set to logout,
**this method will destroy the current session and redirect the
**client to the home page.
*/

function logout(){
	/**
	 * Delete cookies - the time must be in the past,
	 * so just negate what you added when creating the
	 * cookie.
	 */
	if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass'])){
	   setcookie("cookname", "", time()-60*60*24*100, "/");
	   setcookie("cookpass", "", time()-60*60*24*100, "/");
	}

        /* Kill session variables */
	unset($_SESSION['username']);
	unset($_SESSION['password']);
	$_SESSION = array(); // reset session array
	session_destroy();   // destroy session.
}

?>