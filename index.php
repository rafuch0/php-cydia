<?php header('content-type: text/html');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title>Cydia Search
<?php
	$output = '';

	if(isset($_GET['f']))
	{
		$output .= ' | ';

		if($_GET['f'] == 'nameanddescription')
		{
			$output .= 'Name and Description';
		}
		else $output .= ucfirst($_GET['f']);

		$output .= ' | '.$_GET['q'];
	}
	else if(isset($_GET['id']) && $_GET['id'] != '')
	{
		$output .= ' | Package | '.$_GET['id'];
	}
	else if(isset($_GET['page']) && $_GET['page'] = 'faqs')
	{
		$output .= " | FAQs";
	}	
	else
	{
		echo " | Comprehensive Package Database";
	}
	
	echo $output;
?>

</title>

<link rel="stylesheet" href="/cydia/css/style.css" type="text/css" media="screen" />
	
<script type="text/javascript">

function checkForm(f)
{
    if (f.elements['q'].value == "")
    {
        alert("You have not specified a search query.");
        return false;
    }
    else
    {
        f.submit();
        return false;
    }
}

function checkForm2(f)
{
	if(f.elements['q'].value.length >= 2)
	{
		f.elements['searchsubmit'].disabled = false;
	}
	else
	{
		f.elements['searchsubmit'].disabled = true;
	}
}

</script>
</head>

<?php $doindex = true; include('packages.php'); ?>

</body>
</html>
