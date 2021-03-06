<?php # Create New Form Chooser
$metatitle = 'Choose a New Form to Create';
$returnurl = '/login/?returnpage=build/forms/choose';
include ($_SERVER['DOCUMENT_ROOT'] . '/inc/header.php');
?>

<!-- Heading and Help Button -->

<div class="heading">
<h1>Choose a New Form to Create
<?php include ($_SERVER['DOCUMENT_ROOT'] . '/inc/current-account.php'); ?>
</h1>
<a href="/help/create-form-choose.html" class="btn-help" title="Help" onclick="Modalbox.show(this.href, {title: this.title, width: 600, evalScript: true}); return false;"><span>Help?</span></a>
</div>

<!-- Main Content -->

<?php

// Database:
require_once(MYSQL);

if (isset($_POST['submitprebuilt'])) {
	if(isset($_POST['chooseprebuilt'])) {
		//Move to next page in campaign startup:
		$chosen = trim($_POST['chooseprebuilt']);
		if($chosen==0) {
			$url = '/build/forms/new?formID=new&action=scratch';
		} else {
			$url = '/build/forms/new/?action=prebuilt&formID=' . $chosen;
		}
		if(isset($_REQUEST['actionid'])) {
			$url .= '&actionid=' . $_REQUEST['actionid'];
		}
		header("Location: $url");
		exit();
	}else{
		echo '
<div class="alert-holder">
	<div class="alert-box red">
		<div class="holder">
			<div class="frame">
			<a class="close" href="#">X</a>
			<p>Please make a selection!</p>
			</div>
		</div>
	</div>
</div>';
	}
}
if (isset($_POST['submitexisting'])) {
	if(isset($_POST['chooseexisting'])) {
		//Move to next page in campaign startup:
		$chosen = trim($_POST['chooseexisting']);
		$url = '/build/forms/new/?action=existing&formID=' . $chosen;
		if(isset($_REQUEST['actionid'])) {
			$url .= '&actionid=' . $_REQUEST['actionid'];
		}
		header("Location: $url");
		exit();
	}else{
		echo '
<div class="alert-holder">
	<div class="alert-box red">
		<div class="holder">
			<div class="frame">
			<a class="close" href="#">X</a>
			<p>Please make a selection!</p>
			</div>
		</div>
	</div>
</div>';
	}
}

// Evaluate whether or not camp variable was passed to this page, 
// indicating that this is part of a campaign creation sequence:
// $campaignid = FALSE;
// if(isset($_REQUEST['camp'])) {
// $campaignid = $_REQUEST['camp'];
//}

// Evaluate if actionid passed to this page:
$actionid = FALSE;
if(isset($_REQUEST['actionid'])) $actionid = $_REQUEST['actionid'];
?>

<div class="main-container choose-page">
<div class="main">
<div class="main-holder">
<div class="main-frame">

<!-- Pre-Built Template Box -->

<div class="main-item">
	<h2>Pre-Built Templates</h2>
	<p>Create a Form from Our Pre-Built Library of Forms (You Can Edit These).</p>

<form name="submitPrebuilt" id="choose_prebuilt_form" action="" method="post" class="select-form">
<fieldset>
	
<?php
// query for available forms for this account:
$q = "SELECT id, name " . 
"FROM forms " . 
"WHERE id<4 ORDER BY id";
	
$r = mysqli_query($dbc,$q) or trigger_error("Query: $q\n<br />MySQL Error: " . 
mysqli_error($dbc));

//if forms available, display in scrolling table:
if (mysqli_num_rows($r) > 0) {
echo '
<select name="chooseprebuilt" id="select-steps-1" size="5">
';
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
	echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
	}
echo '
</select>
<input type="hidden" name="submitprebuilt" value="TRUE" /> 
';

/*if($campaignid) {
	echo '<input type="hidden" name="campaignseq" value="' . $campaignid . '" />';
}*/

echo '
<div class="content-link">
	<div class="link-holder">
		<div class="link-frame">
			<a class="btn" href="#" onclick="document.getElementById(\'choose_prebuilt_form\').submit(); return false;" /><span>Choose &amp; Edit &gt;</span></a>
		</div>
	</div>
</div>

<div class="content-link">
	<div class="link-holder">
		<div class="link-frame">
			<em>or</em>
			<a href="/create/">Cancel</a>
		</div>
	</div>
</div>
';

}else{
	echo '
<h3>No Pre-Built<br/>Forms Found</h3>
';
}
?>
	
</fieldset>
</form>
</div>

<!-- / Pre-Built Template Box -->

<!-- Your Existing Forms Box -->

<div class="main-item">
	<h2>Your Existing Forms</h2>
	<p>Create a Copy of a Form You've Already Created (Why Reinvent the Wheel?).</p>

<form name="submitExisting" id="choose_existing_form" action="" method="post" class="select-form">
<fieldset>
			
<?php
// query for available forms for this account:
$q = "SELECT id, name " . 
"FROM forms " . 
"WHERE account_id='{$_SESSION['account_id']}' " . 
"AND id>20 " . 
"ORDER BY name";
	
$r = mysqli_query($dbc,$q) or trigger_error("Query: $q\n<br />MySQL Error: " . 
mysqli_error($dbc));

// if forms available, display in scrolling table:
if (mysqli_num_rows($r) > 0) {
	echo '<select name="chooseexisting" id="select-steps-2" size="5">';
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
	}
	echo '
</select>
<input type="hidden" name="submitexisting" value="TRUE" /> 
<div class="content-link">
	<div class="link-holder">
		<div class="link-frame">
			<a class="btn" href="#" onclick="document.getElementById(\'choose_existing_form\').submit(); return false;" /><span>Choose &amp; Edit &gt;</span></a>
		</div>
	</div>
</div>

<div class="content-link">
	<div class="link-holder">
		<div class="link-frame">
			<em>or</em>
			<a href="/create/">Cancel</a>
		</div>
	</div>
</div>
';

}else{
	echo '
<h3>No Existing<br/>Forms Found</h3>
';
}
?>

</fieldset>
</form>
</div>

<!-- / Your Existing Forms Box -->

<!-- Create From Scratch Box -->

<div class="main-item">
	<h2>Create From Scratch</h2>
	<p>A Blank Canvas to Create Your Perfect Form Masterpiece from Scratch.</p>

<div class="content-link">
	<div class="link-holder">
		<div class="link-frame">
			<a class="btn" href="/build/forms/new?formID=new&action=scratch<?php if($actionid) echo '&actionid=' . $actionid; ?>"><span>Choose &amp; Edit &gt;</span></a>
		</div>
	</div>
</div>

<div class="content-link">
	<div class="link-holder">
		<div class="link-frame">
			<em>or</em>
			<a href="/create/">Cancel</a>
		</div>
	</div>
</div>

</div>

<!-- / Create From Scratch Box -->

</div>
</div>
</div>
</div>

<!-- / Main Content -->

<?php include ($_SERVER['DOCUMENT_ROOT'] . '/inc/footer.php'); ?>
