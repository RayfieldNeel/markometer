<?php # header-login.php
// HTML header for the login page

//Start output buffering:
ob_start();

//Initialize a session if one not already in use:
if (session_id()=="") session_start();

//Include the configuration file:
require_once($_SERVER["DOCUMENT_ROOT"]."/inc/config.inc.php");

//If not logged in, or not an admin for this account, then redirect the user:
if (isset($_SESSION['id'])) {
	header("Location: /");
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>VIISE User Login - G & H International Services</title>

<link rel="stylesheet" href="/css/all.css" media="screen" type="text/css" />

<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="/css/ie.css" media="screen" /><![endif]-->

<script type="text/javascript" src="/js/prototype-1.6.1.js"></script>
<script type="text/javascript" src="/js/effects.js"></script>
<script type="text/javascript" src="/js/prototype.main.js"></script>

</head>

<body class="signin-page" onload="javascript:document.getElementById('emailinput').focus();">

<div id="wrapper" class="login-page">

<!-- Header -->
<div id="header">
	<div class="header-holder">
		<div class="bar">
			<strong class="logo"><img src="/images/default-logo.png" alt="logo" width="400" height="100" /></strong>
<?php
if (isset($_GET['email'])) {
	echo '<h1>You\'re Registered! Login Below</h1>';
} else {
	echo '<h1>Login to Your Account</h1>';
}
?>
		</div>
	</div>
</div>

<div class="login-box error">
<div class="login-holder">
<div class="login-frame">
<div class="signin-form">

<?php
$redirect = FALSE; $addtoLOGIN = '';
if(isset($_GET['returnpage'])) {
	$redirect = '?redirect=' . $_GET['returnpage'];
	$addtoLOGIN = '?returnpage=' . $_GET['returnpage'];
}	
?>

<form action="<?php echo LOGIN . $addtoLOGIN; ?>" name="login" id="login" method="post">
<fieldset>
	
<?php

if (isset($_POST['submitted']) || (isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass'])))
{	
	//database setup:
	require_once (MYSQL);
	
	$forgotpassworddone = FALSE;
	
	//testing submission of forgot password stuff on same page:
    if (!empty($_POST['forgotemail'])) {
	    //Email and Answer validated, query to check validity of answer to question:
	    $q = "SELECT DECODE(password,'{ENCODEPASS}') as pass, master_account FROM users WHERE email=" . 
		'"' . mysqli_real_escape_string($dbc, $_POST['forgotemail']) . '"';
	
	    $r = mysqli_query ($dbc, $q) or trigger_error("Query: 
	    $q\n<br />MySQL Error: " . mysqli_error($dbc));

	    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
	    $p = $row['pass'];
	    $masteraccount = $row['master_account'];
		
	    if (mysqli_num_rows($r) == 1)
	    {
	    	//query account to see if this is a private labeled account:
	    	$q2 = "SELECT company FROM accounts " . 
	    	"WHERE id='$masteraccount' AND privatelabel='Y'";
	    	
		    $r2 = mysqli_query ($dbc, $q2) or trigger_error("Query: 
		    $q2\n<br />MySQL Error: " . mysqli_error($dbc));
		    
		    $row2 = mysqli_fetch_array($r2, MYSQLI_ASSOC);
		    
		    if (mysqli_num_rows($r2) == 1) {
		    		$from = 'From:' . str_replace(" ","_",$row2['company']) . '@mp41.com';
		    		$subject = $row2['company'] . ' Password Recovery';
		    } else {
		    		$from = 'From:Markometer@mp41.com';
		    		$subject = 'Password Recovery';
		    }
	    
		    //Send an email:
		    $body = "Here is your password, as requested: \n\n$p\n 
		    You will be able to log in using your registered email address and this password.";
		  
		    mail ($_POST['forgotemail'], $subject, 
		    $body, $from);
		  
		    //Print a message and wrap up:
			echo '<div class="good-box"><div class="good-holder"><div class="good-frame"><span>Your password has been sent! When you get it you can log in below.</span></div></div></div>';
		    //exit(); //Stop the script
		}
		else
		{
			echo '<div class="error-box"><div class="error-holder"><div class="error-frame"><span>The email address you listed does not match our records. Please try again.</span></div></div></div>';
	        //exit();
		}	
	  	$forgotpassworddone = TRUE;	
	}

	$rememberlogin = FALSE;
	if (isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass']))
	{
		$e = base64_decode($_COOKIE['cookname']);
		$p = base64_decode($_COOKIE['cookpass']);
	}
	else
	{
	    //Validate the email address:
	    if (!empty($_POST['email'])) {
			$e = mysqli_real_escape_string ($dbc, $_POST['email']);
	    } else {
			$e = FALSE;  
			if(!$forgotpassworddone) {
				echo '<div class="error-box"><div class="error-holder"><div class="error-frame"><span>Oops! You forgot to enter your email address!</span></div></div></div>';					
			}
	    }
	  
	    //Validate the password:
	    if (!empty($_POST['pass'])) {
			$p = mysqli_real_escape_string ($dbc, $_POST['pass']);
	    } else {
			$p = FALSE; 
			if(!$forgotpassworddone) {
				echo '<div class="error-box"><div class="error-holder"><div class="error-frame"><span>Oops! You forgot to enter your password!</span></div></div></div>';					
			}
	    }
	}

    if ($e && $p)
    {
      //If everything's okay...
		//find domain info for query:
		$serverhost = explode('.',$_SERVER["HTTP_HOST"]);

	  //Query the database for USER info:  
	  $q = "SELECT users.id, users.first_name, users.last_name, 
	  users.email, users.default_account, users.timezone, 
	  users.title, users.office_number, users.cell_number, 
	  users.company, users.created_by, users.active, users.master_account, 
	  accounts.privatelabel, accounts.mp_subdomain
	  as master_account_subdomain, accounts.new as account_new 
	  FROM users 
	  INNER JOIN accounts 
	  ON users.master_account=accounts.id
	  WHERE email='$e' AND password=ENCODE('$p','{ENCODEPASS}') 
	  AND users.master_account=(SELECT id FROM accounts WHERE mp_subdomain='$serverhost[0]')";

	  $r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
	  Error: " . mysqli_error($dbc));
	
/*	echo $q;
	exit(); */
	    if (@mysqli_num_rows($r) == 1)
	    {		
            //A match was made
	        //Register the values and redirect:
		    $_SESSION = mysqli_fetch_array ($r, MYSQLI_ASSOC);
		    mysqli_free_result($r);
			
			$_SESSION['password'] = $p;
			
			//if(isset($_POST['remember'])) {
				//play with encryption of cookie content:
				//$_SESSION['testencrypt'] = base64_encode($_SESSION['password']);
				
				setcookie("cookname", base64_encode($_SESSION['email']), time()+60*60*24*100, "/");
				setcookie("cookpass", base64_encode($_SESSION['password']), time()+60*60*24*100, "/");			
			//}
			
			if ($_SESSION['active']!="D") //Make sure user is not flagged as Deleted
			{
				//Check for OpenID identity:
			    $q = "SELECT openid_url
					FROM user_openids
					WHERE user_id={$_SESSION['id']}";
					
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
					Error: " . mysqli_error($dbc));
				
				if (@mysqli_num_rows($r) == 1) { //If match found, set OpenID session variable:
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					$_SESSION['oid'] = $row['openid_url'];
					mysqli_free_result($r);
				}

				//Code for looking up accounts that relate to user id here.
				//table with: id, user_id, account_id....many to one relationship
				$q = "SELECT account_lookup.account_id, accounts.mp_subdomain, accounts.company 
					FROM account_lookup
					INNER JOIN accounts 
					ON account_lookup.account_id=accounts.id 
					WHERE account_lookup.user_id={$_SESSION['id']}
					ORDER BY account_lookup.account_id";
			
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
					Error: " . mysqli_error($dbc));
				
				if (@mysqli_num_rows($r) == 1) //If match found, set Account session variable
				{
					//Set session variable flag for a single account:
					$_SESSION['multiaccounts'] = FALSE;
				
					//look up single account based on acquired account id from above query		
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					//$_SESSION['master_account'] = $_SESSION['account_id'] = $row['account_id'];
					$_SESSION['account_id'] = $row['account_id'];
					$_SESSION['master_account_name'] = $row['company'];										
				}
				elseif ( @mysqli_num_rows($r) > 1)
				{
					//Set session variable to flag that user has multiple accounts:
					$_SESSION['multiaccounts'] = TRUE;
					$accountsarray = array();
					
					while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
					{
						//code here to make sql hit to accounts table, get company name (account name) and whatever 
						$q = "SELECT company, agency 
							FROM accounts
							WHERE id={$row['account_id']}";
						
						$r2 = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
							Error: " . mysqli_error($dbc));
						
						$row2 = mysqli_fetch_array($r2, MYSQLI_ASSOC);					
						$accountsarray[$row['account_id']] = $row2['company'];
						$testnumber = $row['account_id'];
						
						//find master account:
						if (trim($row2['agency']) == 'Y') {
							$_SESSION['master_account'] = $row['account_id'];
							$_SESSION['master_account_name'] = $row['company'];						
						}
						
					}

					//Set session variable to contain accounts:
					$_SESSION['accounts'] = $accountsarray;

					// decide how to select the account to log in to if default account no longer
					//available this user:
					$defaccount = $_SESSION['default_account'];
					//if the specified default account is available to the user, then make that the current account:
					
					if (array_key_exists($defaccount, $accountsarray)) {
						$_SESSION['account_id'] = $_SESSION['default_account'];
					} else {
						//else make the current account the one that was last itemized in the last loop:
						$_SESSION['default_account'] = $_SESSION['account_id'] = $testnumber;
						//$_SESSION['account_id'] = $_SESSION['default_account'];					
					}
				}
				else
				{
					//This should only happen if no accounts are found in account_lookup for this user:
					//Go find admin for this orphaned user:
					$q = "SELECT first_name, last_name, email 
					FROM users 
					WHERE id={$_SESSION['created_by']}";

					$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
					Error: " . mysqli_error($dbc));
					
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					
					echo '<div class="error-box"><div class="error-holder"><div class="error-frame"><span>No accounts were found for your email address. 
					Please contact the administrator for your accounts - ' . 
					$row['first_name'] . '&nbsp;' . $row['last_name'] . '&nbsp;<a href="mailto:' . 
					$row['email'] . '">(' . $row['email'] . ')</a>&nbsp;if this 
					needs to be corrected.</span></div></div></div>';
					
	  			    //Log out the user
				    $_SESSION = array(); //Destroy the variables
				    session_destroy(); //Destroy the session itself
				    setcookie (session_name(), '', time()-300);//Destroy the cookie
					if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass'])) {
					   setcookie("cookname", "", time()-60*60*24*100, "/");
					   setcookie("cookpass", "", time()-60*60*24*100, "/");
					}				
					exit();
				}
				//Hit account_lookup one more time to get role, assigns_leads stuff:
				$q = "SELECT role, assigns_leads 
					FROM account_lookup 
					WHERE account_id={$_SESSION['account_id']} 
					AND user_id={$_SESSION['id']}";
				
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
					Error: " . mysqli_error($dbc));
			
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
				$_SESSION['role'] = $row['role'];
				$_SESSION['assignsleads'] = $row['assigns_leads'];
				
				//SQL hit to extract account data:		
				$q = "SELECT company, mp_subdomain, ccexp_month, ccexp_year, active, "
					. "new, privatelabel, tabcolor, tabfontcolor " 
					. "FROM accounts "
					. "WHERE id={$_SESSION['account_id']}";
				
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
					Error: " . mysqli_error($dbc));
			
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
				$_SESSION['accountname'] = $row['company'];
				$_SESSION['mp_subdomain'] = $row['mp_subdomain'];
				$_SESSION['ccexp_month'] = $row['ccexp_month'];
				$_SESSION['ccexp_year'] = $row['ccexp_year'];
				$_SESSION['account_active'] = $row['active'];
				$_SESSION['newaccount'] = $row['new'];
				$_SESSION['privatelabel'] = $row['privatelabel'];
				$_SESSION['tabcolor'] = $row['tabcolor'];
				$_SESSION['tabfontcolor'] = $row['tabfontcolor'];
				
				//Regular Messaging system:
				//Go get messages and store in session variables, 1st 2 msgs set aside for delinquint cc people:
				$q = "SELECT id, message 
				FROM messages 
				WHERE active='Y' 
				AND id NOT IN (SELECT message_id FROM 
				message_user_lookup WHERE user_id={$_SESSION['id']}) 
				AND id>2";
					
				//If not an admin, filter out messages intended only for account owners:	
				if ($_SESSION['role']!='O') $q .= " AND adminonly!='Y'";
				$q .= " ORDER BY date_modified";
				
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
					Error: " . mysqli_error($dbc));
				
				//array to hold messages:
				$messagearray = array();
				while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
					$messagearray[$row['id']] = $row['message'];
				}
				
				//Store to Session variable:
				$_SESSION['messages'] = $messagearray;
				
				mysqli_free_result($r);
				mysqli_close($dbc);
				
				//Redirect to home page or requested page:
				$redirect = FALSE;
				if (isset($_POST['returnpage'])) $redirect = '?returnpage=' . $_POST['returnpage'];
				$url = '/' . $redirect;
				header("Location: $url");
			    exit(); //Quit the script
			}
			else
			{
				//User is flagged as being Deleted. Logout:
				//This should only happen if no accounts are found in account_lookup for this user:
				//Go find admin for this orphaned user:
				$q = "SELECT first_name, last_name, email 
				FROM users 
				WHERE id={$_SESSION['created_by']}";

				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL
				Error: " . mysqli_error($dbc));
				
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
				
				echo '<div class="error-box"><div class="error-holder"><div class="error-frame"><span>This account has been deleted.  
				Please contact the administrator for your accounts - ' . 
				$row['first_name'] . '&nbsp;' . $row['last_name'] . '&nbsp;<a href="mailto:' . 
				$row['email'] . '">(' . $row['email'] . ')</a>&nbsp;if this 
				needs to be corrected.</span></div></div></div>';
				
				//Log out the user
				$_SESSION = array(); //Destroy the variables
				session_destroy(); //Destroy the session itself
				setcookie (session_name(), '', time()-300);//Destroy the cookie
				if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass'])) {
				   setcookie("cookname", "", time()-60*60*24*100, "/");
				   setcookie("cookpass", "", time()-60*60*24*100, "/");
				}				
				exit();
			}
		}
		else
		{
	        //No match was made
			if(!$forgotpassworddone) {
				echo '<div class="error-box"><div class="error-holder"><div class="error-frame"><span>Sorry, your email or password was incorrect. If you\'re pretty sure your email is correct, please click the "I forgot my password" link below.</span></div></div></div>';					
			    $_SESSION['email'] = $_POST['email']; 
			}
		}
    }
    mysqli_close($dbc);
  
}//End of SUBMIT conditional

?>

<div class="block">
	<div class="row">
		<label>Email</label>
		 <input type="text" class="text" name="email" id="emailinput" maxlength="50" onblur="document.forgotpassword.forgotemail.value = document.login.email.value" value="<?php 
  if (isset($_POST['email'])) 
  {
	echo $_POST['email']; 
  }
  elseif (isset($_GET['email']))
  {
	echo $_GET['email'];
  }
  ?>" />
	</div>
	<div class="row">
		<label>Password</label>
		<input type="password" class="text" name="pass" maxlength="50" />
		<input type="hidden" name="submitted" value="TRUE" />
		<input type="hidden" name="returnpage" value="<?php if (isset($_GET['returnpage'])) echo $_GET['returnpage']; ?>" />
		<input type="submit" style="display:none"/>
	</div>
	<div class="row">
		<div class="content-link">
			<div class="link-holder">
				<div class="link-frame">
					<a href="#" class="btn" onclick="document.getElementById('login').submit()" /><span>Sign In &gt;</span></a>
				</div>
			</div>
		</div>
	</div>
	<div class="column">
		<span>Don't Have An Account?</span>
		<a href="http://signup.mp41.com/register/">Sign Up Now</a>
	</div>
	<div class="column">
		<span>Help:</span>
		<a href="#" class="forgot">I forgot my password</a>
	</div>
</div>

</fieldset>
<!-- </form>

<form action="" name="forgotpassword" id="forgot_password" method="post"> -->
<fieldset>
	
<!-- Forgot Password -->
<div class="error-block">
	<div class="holder">
		<h4>Forgot Your Password? <span>Enter it below and we'll send it over.</span></h4>
			<div class="block">
				<div class="row">
					<label>Email</label>
					<input type="text" name="forgotemail" class="text" maxlength="50" value="" autocomplete="off" />
				</div>
				<div class="row">
					<div class="content-link">
						<div class="link-holder">
							<div class="link-frame">
								<a href="#" class="btn" name="forgotpasswordsubmit" onclick="document.getElementById('login').submit()" value="testvalue "/><span>Send Password &gt;</span></a>
							</div>
						</div>
					</div>
				</div>
			</div>
	</div>
</div>

<input type="hidden" name="submitted" value="TRUE" />

</fieldset>
</form>

</div>
</div>
</div>
</div>

</div>

</body>
</html>
