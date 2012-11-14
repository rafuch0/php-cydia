<?php

date_default_timezone_set('EST');

error_reporting(0);
set_time_limit(0);

define("MAXLINELENGTH", 12000);

define('SERVER','localhost');
define('DB', 'cydia');
define('TABLE', 'cydia');
define('USER', 'cydia');
define('PASSWORD', 'cydia-1337');

define('DEBUG', true);
define('ENDLINE', "\n");

$packageElements = array(
'package' => 'text',
'repo' => 'text',
'name' => 'text',
'version' => 'text',
'section' => 'text',
'maintainer' => 'text',
'filename' => 'text',
'size' => 'text',
'description' => 'text',
'author' => 'text',
'depiction' => 'text',
'homepage' => 'text',
'tag' => 'text',
'sponsor' => 'text',
'addeddate' => 'datetime',
'updateddate' => 'datetime',
'trusted' => 'bool'
);

$link = mysql_connect(SERVER, USER, PASSWORD);
if ($link)
{
	mysql_select_db(DB, $link);
}
else die('Could not connect: '.mysql_error());

function initdb()
{
	global $link;
	global $packageElements;

	$sqlQuery = 'DROP TABLE '.TABLE.';';
	if(DEBUG) echo $sqlQuery.ENDLINE;
	mysql_query($sqlQuery);

	$dataEntries = '';

	foreach($packageElements as $validElement => $elementType)
	{
		$dataEntries .= $validElement.' '.$elementType.',';	
	}
	$dataEntries = rtrim($dataEntries,',');

	$sqlQuery = 'CREATE TABLE '.TABLE.' ('.$dataEntries.');';
	if(DEBUG) echo $sqlQuery.ENDLINE;
	$result = mysql_query($sqlQuery);
}

function doupdate($filename, $repo, $trusted)
{
	global $link;
	global $packageElements;

	$dataHandle = fopen($filename, 'r');

	$isPackage = false;

	if($dataHandle)
	{
		while(!feof($dataHandle))
		{
			$isPackage = false;
			unset($package);
			$package = array();

			while(($line = fgets($dataHandle, MAXLINELENGTH)) !== false)
			{
				if($line == "\n")
				{
					break;
				}
				else
				{
					$isPackage = true;

					$package['repo'] = mysql_real_escape_string($repo);

					$time = time();

					$package['addeddate'] = mysql_real_escape_string($time);
					$package['updateddate'] = $time;

					$parts = explode(':', $line);
					$key = strtolower(trim($parts[0]));
					$value = '';

					for($i = 1; $i < sizeof($parts); $i++)
					{
						$value .= trim($parts[$i]).':';
					}
					$value = rtrim($value,':');
					$package[$key] = $value;
				}
			}

			if($isPackage)
			{
				$package['author'] = trim(preg_replace('/<.*>/', '', $package['author']));
				$package['maintainer'] = trim(preg_replace('/<.*>/', '', $package['maintainer']));
				$package['sponsor'] = trim(preg_replace('/<.*>/', '', $package['sponsor']));
				$package['description'] = htmlentities(strip_tags(preg_replace('/[^A-Za-z0-9\_\ \`\~\!\@\#\$\%\^\&\*\(\)\-\=\+\[\]\\\{\}\|\;\'\:\"\,\.\/\<\>\?]/', '', html_entity_decode($package['description']))));
				$package['filename'] = basename($package['filename']);

				if($package['name'] == '') $package['name'] = $package['package'];
				$package['name'] = htmlentities(strip_tags(preg_replace('/[^A-Za-z0-9\_\ \`\~\!\@\#\$\%\^\&\*\(\)\-\=\+\[\]\\\{\}\|\;\'\:\"\,\.\/\<\>\?]/', '', html_entity_decode($package['name']))));

				$sqlQuery = 'SELECT version FROM '.TABLE.' WHERE package=\''.$package['package'].'\';';
				if(DEBUG) echo $sqlQuery.ENDLINE;

				$result = mysql_query($sqlQuery);

				$row = mysql_fetch_assoc($result);

				if(mysql_num_rows($result) == 0)
				{
					$sqlQuery = 'INSERT INTO '.TABLE.' (package,addeddate) VALUES (\''.$package['package'].'\',FROM_UNIXTIME(\''.$package['addeddate'].'\'));';
					if(DEBUG) echo $sqlQuery.ENDLINE;
					mysql_query($sqlQuery);

					$package['updateddate'] = 0;
				}

		                if($row['version'] < $package['version'])
		                {
					$sqlQuery = 'UPDATE '.TABLE.' SET ';

					foreach($packageElements as $validElement => $elementType)
					{
						if($validElement != 'trusted')
						{
							if($validElement != 'addeddate')
							{
								if($validElement == 'updateddate')
								{
									$sqlQuery .= $validElement.'=FROM_UNIXTIME(\''.mysql_real_escape_string($package[$validElement]).'\'),';
								}
								else
								{
									$sqlQuery .= $validElement.'=\''.mysql_real_escape_string($package[$validElement]).'\',';
								}
							}
						}
						else
						{
							if($trusted)
							{
								$sqlQuery .= 'trusted=true,';
							}
							else
							{
								$sqlQuery .= 'trusted=false,';
							}
						}
					}

					$sqlQuery = rtrim($sqlQuery, ',').' WHERE package=\''.$package['package'].'\';';

					if(DEBUG) echo $sqlQuery.ENDLINE;
					mysql_query($sqlQuery);
				}
			}
		}
	}
}

function removeEntries($field, $value)
{
	global $link;

	$field = mysql_real_escape_string($field);
	$value = mysql_real_escape_string($value);

	$sqlQuery = 'DELETE FROM '.TABLE.' WHERE '.$field.'=\''.$value.'\';';
	if(DEBUG) echo $sqlQuery.ENDLINE;
	mysql_query($sqlQuery);

	echo 'Total Rows Affected '.mysql_affected_rows()."\n";
}

if(isset($_GET['remove']) && ($_GET['remove'] == 'remove'))
{
	header('content-type: text/plain');

	$field = '';

	if(isset($_GET['repo']) && ($_GET['repo'] != '')) $field = 'repo';
	else
	if(isset($_GET['section']) && ($_GET['section'] != '')) $field = 'section';
	else
	if(isset($_GET['author']) && ($_GET['author'] != '')) $field = 'author';

	if($field != '')
	{
		$value = $_GET[$field];

		echo 'REMOVING '.strtoupper($field).' '.$value.' ';
        	echo date("D, d M o G:i:s T",time())."\n";

		flush();
		removeEntries($field, $value);
		flush();

		echo 'DONE'."\n";
	}
}

if(isset($_GET['init']) && ($_GET['init'] == 'init'))
{
	header('content-type: text/plain');

	echo 'INITIALIZING ';
        echo date("D, d M o G:i:s T",time())."\n";

	flush();
	initdb();
	flush();

	echo 'DONE'."\n";
}

if(isset($_GET['update']) && ($_GET['update'] == 'update'))
{
	header('content-type: text/plain');
	echo 'UPDATING ';
        echo date("D, d M o G:i:s T",time())."\n";

doupdate('djayb6-packages.txt', 'djayb6-packages', false);
doupdate('dreamboard-packages.txt', 'dreamboard-packages', false);
doupdate('hitoriblog-packages.txt', 'hitoriblog-packages', false);
doupdate('iarabia-packages.txt', 'iarabia-packages', false);
doupdate('modyouri-packages.txt', 'modyouri-packages', false);
doupdate('applizing-packages.txt', 'applizing-packages', false);
doupdate('bigboss-packages.txt', 'bigboss-packages', true);
doupdate('chronzz-packages.txt', 'chronzz-packages', false);
doupdate('dba-technologies-packages.txt', 'dba-technologies-packages', false);
doupdate('iappdev-packages.txt', 'iappdev-packages', false);
doupdate('iphoneislam-packages.txt', 'iphoneislam-packages', false);
doupdate('ispazio-packages.txt', 'ispazio-packages', false);
doupdate('iwazowski-packages.txt', 'iwazowski-packages', false);
doupdate('modmyi-packages.txt', 'modmyi-packages', true);
doupdate('peterhajas-packages.txt', 'peterhajas-packages', false);
doupdate('pushfix-packages.txt', 'pushfix-packages', false);
doupdate('styletap-packages.txt', 'styletap-packages', false);
doupdate('tangelo-packages.txt', 'tangelo-packages', true);
doupdate('ultrasn0w-packages.txt', 'ultrasn0w-packages', true);
doupdate('zodttd-packages.txt', 'zodttd-packages', true);


//	echo 'TEST'."\n";
//	doupdate('test-packages', '', true);', 'test', false);
//	flush();

	echo 'DONE'."\n";
}

mysql_close($link);

?>
