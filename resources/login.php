<?php
require "settings.php";

/**
 * Checks to see if the user has submitted his
 * username and password through the login form,
 * if so, checks authenticity in database and
 * creates session.
 */
if(isset($_POST['sublogin'])){
   /* Check that all fields were typed in */
   if(!$_POST['user'] || !$_POST['pass']){
      $message="You didn't fill in all the fields.";
   
   }
   /* Spruce up username, check length */
   $_POST['user'] = trim($_POST['user']);
   if(strlen($_POST['user']) > 30){
    $message="Sorry, the username is longer than 30 characters, please shorten it.";
   
   }

   /* Checks that username is in database and password is correct */
   $sha1pass = sha1($_POST['pass']);
   $result = confirmUser($_POST['user'], $sha1pass);

   /* Check error codes */
   if($result == 1){
      $message="That username doesn't exist in our database.";
    
   }
   else if($result == 2){
       $message="Incorrect password&mdash;please try again.";
      
   }

   /* Username and password correct, register session variables */
   $_POST['user'] = stripslashes($_POST['user']);
   $_SESSION['username'] = $_POST['user'];
   $_SESSION['password'] = $sha1pass;

   /**
    * This is the cool part: the user has requested that we remember that
    * he's logged in, so we set two cookies. One to hold his username,
    * and one to hold his md5 encrypted password. We set them both to
    * expire in 100 days. Now, next time he comes to our site, we will
    * log him in automatically.
    */
   if(isset($_POST['remember'])){
      setcookie("cookname", $_SESSION['username'], time()+60*60*24*100, "/");
      setcookie("cookpass", $_SESSION['password'], time()+60*60*24*100, "/");
   }

  
}

/* Sets the value of the logged_in variable, which can be used in your code */
$logged_in = checkLogin();



/**
 * Checks whether or not the given username is in the
 * database, if so it checks if the given password is
 * the same password in the database for that user.
 * If the user doesn't exist or if the passwords don't
 * match up, it returns an error code (1 or 2). 
 * On success it returns 0.
 */
function confirmUser($username, $password){
   global $conn, $user_table;
   /* Add slashes if necessary (for query) */
   if(!get_magic_quotes_gpc()) {
	$username = addslashes($username);
   }

   /* Verify that user is in database */
   
   $query = "SELECT password FROM $user_table WHERE username='$username'";
   $result = mysql_query($query) or die(mysql_error());
   
   if(!$result || (mysql_numrows($result) < 1)){
      return 1; //Indicates username failure
   }

   /* Retrieve password from result, strip slashes */
   $dbarray = mysql_fetch_array($result);
   $dbarray['password']  = stripslashes($dbarray['password']);
   $password = stripslashes($password);

   /* Validate that password is correct */
   if($password == $dbarray['password']){
      return 0; //Success! Username and password confirmed
   }
   else{
      return 2; //Indicates password failure
   }
}

/**
 * checkLogin - Checks if the user has already previously
 * logged in, and a session with the user has already been
 * established. Also checks to see if user has been remembered.
 * If so, the database is queried to make sure of the user's 
 * authenticity. Returns true if the user has logged in.
 */
function checkLogin(){
   /* Check if user has been remembered */
   if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass'])){
      $_SESSION['username'] = $_COOKIE['cookname'];
      $_SESSION['password'] = $_COOKIE['cookpass'];
   }

   /* Username and password have been set */
   if(isset($_SESSION['username']) && isset($_SESSION['password'])){
      /* Confirm that username and password are valid */
      if(confirmUser($_SESSION['username'], $_SESSION['password']) != 0){
         /* Variables are incorrect, user not logged in */
         unset($_SESSION['username']);
         unset($_SESSION['password']);
         return false;
      }
      return true;
   }
   /* User not logged in */
   else{
      return false;
   }
}

/**
 * Determines whether or not to display the login
 * form or to show the user that he is logged in
 * based on if the session variables are set.
 */
function displayLogin(){
   global $logged_in;
   global $message;
   global $SITE_ROOT;
   if($logged_in){
     echo '<script>window.location="http://www.gvsulib.com/migrator/"</script>';
   } else {
	
	//display login page

	if (empty($message)) {
		$message="<h3>Please log in.</h3>"; 
	} else {
		$message= '<h3>' . $message . '</h3>';
	} 
		echo '
			<div id="login">
				<div id="login_form">
				'.$message.'
				<form action="" method="post">
					<div class="row"><label for="new_password1">Username:</label><input type="text" id="customid_username" name="user" tabindex="1" maxlength="30"/></div>
					<div class="row"><label for="new_password2">Password:</label><input type="password" name="pass" tabindex="2" maxlength="30"/></div>
						<a href="../labs/admin/forgot.php">Forgot your password?</a><br/><br/>
					<input type="submit" name="sublogin" id="sublogin" class="button_login" value="Login" tabindex="3" />
					<script type = "text/javascript">
						document.getElementById(\'customid_username\').focus();
					</script>
				</form>
				</div>
			</div>';
	}
}
?>
