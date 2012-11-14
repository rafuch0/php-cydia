<?
error_reporting(0);
require_once("ratings.php");
$rr = new RabidRatings();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
<meta http-equiv="Pragma" content="no-cache">
<script type="text/javascript" src="/cydia/js/mootools-1.2b1.js"></script>
<script type="text/javascript" src="/cydia/js/ratings.js"></script>
<link rel="stylesheet" type="text/css" href="/cydia/css/ratings.css" />
</head><body>

<?
$rr->showStars($_GET['pid']);
?>

</body></html>
