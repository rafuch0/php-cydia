<?php
$admin_email ='admin@localhost';// <<=== update to your email address
$blacklist = array('http://www.sinfuliphonerepo.com','http://repo.insanelyi.com','http://iphoneame.com/repo','http://repo.biteyourapple.net','http://apt.macosmovil.com','iphone3gsystem.fr/cydia','http://ihacksrepo.com','http://irepo.us','http://repo.hackyouriphone.org','http://repocydios.com','http://theiphonespotrepo.net/apt','http://h7v.org','http://idwaneo.org/repo','http://p0dulo.com','http://cydia.myrepospace.com/Tonix95NYC','http://theiphonespotrepo.net/apt','http://stable.szifon.com','http://repo.apple-thom.fr','http://cydia.xsellize.com','http://sinfuliphonerepo.com');

//$their_email ='';// <<=== the submitter's email
session_start();
$errors = '';
//$reponame = '';
$repourl = '';
//$repoemail = '';

if(isset($_POST['submit']))
{

//	$reponame = $_POST['reponame'];
	$repourl = $_POST['repourl'];
	$their_email = $_POST['their_email'];
	//$repoemail = $_POST['repoemail'];
	///------------Do Validations-------------

	//Function to validate the URL
	function isValidURL($url)
	{
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}
	if(!isValidURL($repourl))
	{
	$errors .= "Valid URL including http:// required!<br>";
	}


	if(empty($reponame)||empty($repourl))
	{
		//$errors .= "\n All fields are required. ";
	}
	if(empty($repourl))
	{
		//$errors .= "\n Valid repository name and url are required. ";
	}
	/*if(empty($reponame)||empty($repoemail))
	{
		$errors .= "\n Valid repository name and email are required. ";
	}*/
	/*if(empty($repourl)||empty($repoemail))
	{
	$errors .= "\n Valid repository name and email are required. ";
	}*/
/*	if(empty($reponame))
	{
	$errors .= "\n Valid repository name is required. ";
	}
*/
	if(empty($repourl))
	{
	//$errors .= "\n Valid repository URL is required. ";
	}
	if(empty($their_email))
	{
	$errors .= "\n Valid email is required. ";
	}
	if(IsInjected($repoemail))
	{
		$errors .= "\n Valid email is required. ";
	}
	if(empty($_SESSION['6_letters_code'] ) ||
	  strcasecmp($_SESSION['6_letters_code'], $_POST['6_letters_code']) != 0)
	{
	//Note: the captcha code is compared case insensitively.
	//if you want case sensitive match, update the check above to
	// strcmp()
		$errors .= "\n The captcha code does not match!";
	}

	if(empty($errors))
	{
		//send the email
		$to = $admin_email;
		$subject="Cydia Search - Repo Submission";
		$from = $their_email;
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

		$body = "Repo URL: $repourl \n\n".
		"From: $their_email \n\n".
		"IP: $ip\n\n";

		$headers = "From: $their_email \r\n";
		$headers .= "Reply-To: $admin_email \r\n";

                if(!in_array(str_replace('www.','',$repourl),$blacklist))
                {
                        mail($to, $subject, $body,$headers);
                }
                else
                {
                        die('This repo is known for containing illegal or otherwise unapproved packages. Your submission was denied.<br /><br /><a href=""javascript: history.go(-1)"">Back</a>');
                }

		header('Location: repo-form-thanks.php');
	}
}

// Function to validate against any email injection attempts
function IsInjected($str)
{
  $injections = array('(\n+)',
              '(\r+)',
              '(\t+)',
              '(%0A+)',
              '(%0D+)',
              '(%08+)',
              '(%09+)'
              );
  $inject = join('|', $injections);
  $inject = "/$inject/i";
  if(preg_match($inject,$str))
    {
    return true;
  }
  else
    {
    return false;
  }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<!-- a helper script for vaidating the form-->
<script language="JavaScript" src="/cydia/js/gen_validatorv31.js" type="text/javascript"></script>

	<link rel="stylesheet" href="/cydia/css/repoform.css" type="text/css" media="screen" />
</head>

<body>
<?php
if(!empty($errors)){
echo "<p class='err'>".nl2br($errors)."</p>";
}
?>
<div id='contact_form_errorloc' class='err'></div>
<form method="POST" name="contact_form" class="repo_form" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
<!--<p>
<label for='name'>Repository Name: </label><br>
<input type="text" name="reponame" size="15" value='<?php //echo htmlentities($reponame) ?>'>
</p>-->
<p>
<label for='name'><div class="repo_form_label_url">Repository URL:</div></label>
<!--<input type="text" name="repourl" size="20" value='<?php echo htmlentities($repourl) ?>'>-->
<input type="text" name="repourl" size="20" value="http://">
</p>

<p>
<label for='name'>Your Email: </label><br>
<input type="text" name="their_email" size="15" value='<?php //echo htmlentities($their_email) ?>'>
</p>

<p>
<img src="/cydia/captcha_code_file.php?rand=<?php echo rand(); ?>" id='captchaimg' ><br>
<label for='message'><div class="repo_form_label_captcha">Enter the captcha code:</div></label>
<input size="20" id="6_letters_code" name="6_letters_code" type="text"><br>
<div class="repo_form_label_small"><small>Can't read the image? Click <a href='javascript: refreshCaptcha();'>here</a> to refresh.</small></div>
</p>
<input type="submit" value="Submit" name='submit'>
</form>
<script language="JavaScript">
// Code for validating the form
// Visit http://www.javascript-coder.com/html-form/javascript-form-validation.phtml
// for details
var frmvalidator  = new Validator("contact_form");
//remove the following two lines if you like error message box popups
frmvalidator.EnableOnPageErrorDisplaySingleBox();
frmvalidator.EnableMsgsTogether();
frmvalidator.addValidation("their_email","req", "Valid email is required!");
frmvalidator.addValidation("repourl","req","Repository URL field cannot be empty!");
</script>
<script language='JavaScript' type='text/javascript'>
function refreshCaptcha()
{
	var img = document.images['captchaimg'];
	img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
}
</script>
</body>
</html>
