<?php

// Pull information on the current user

	$usr=$_SESSION['username'];

	$user_result = mysql_query("SELECT * FROM users WHERE username='$usr' LIMIT 1");
	if($user_result) {
			while ($row = mysql_fetch_assoc($user_result)) {

				$current_usr_id = $row['user_id'];
				$current_username = $row['username'];
				$current_usr_fn = $row['user_fn'];
				$current_usr_ln = $row['user_ln'];
				$current_usr_admin = $row['user_admin'];
				$current_usr_email = $row['user_email'];
			
				// Make sure the numerical bits are numerical
				
				if(!is_numeric($current_usr_id)) { $current_usr_id = NULL; } 
				if(!is_numeric($current_usr_admin)) { $current_usr_admin = 0; } 
				
				// Look out for the naughty bits!
				
				if(get_magic_quotes_gpc()) {
					$current_username = stripslashes($current_username);
					$current_usr_fn = stripslashes($current_usr_fn);
					$current_usr_ln = stripslashes($current_usr_ln);
					$current_usr_email = stripslashes($current_usr_email);
				}
			}
		}
?>