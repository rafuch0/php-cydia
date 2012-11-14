<?php

date_default_timezone_set('EST');

error_reporting(0);
set_time_limit(0);

define("MAXLINELENGTH", 12000);

define('SERVER','localhost');
define('DB', 'cydia');
define('TABLE', 'cydia');
define('USER', 'cydia-ro');
define('PASSWORD', 'cydia-ro-1337');

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

function getPackage($package)
{
	global $link;
	global $packageElements;

	$package = mysql_real_escape_string($package);

	$packageArray = array();

	$sqlQuery = 'SELECT * FROM '.TABLE.' WHERE package=\''.$package.'\';';
	$result = mysql_query($sqlQuery);

	$row = mysql_fetch_assoc($result);
	if($row)
	{
		foreach($packageElements as $validElement => $elementType)
		{
			$packageArray[$validElement] = $row[$validElement];
		}
	}

	return $packageArray;
}

function queryPackages($field, $query, $keep, &$count, $start, $step, $sort='')
{
	global $link;

	$field = mysql_real_escape_string($field);
	$query = mysql_real_escape_string($query);
	$keep = mysql_real_escape_string($keep);
	$start = mysql_real_escape_string($start);
	$step = mysql_real_escape_string($step);
	$sort = mysql_real_escape_string($sort);

	$keepVals = explode(',', $keep);

	if($field == 'nameanddescription')
	{
		$sqlQuery = 'SELECT count(package) FROM '.TABLE.' WHERE name LIKE \'%'.$query.'%\' OR description LIKE \'%'.$query.'%\';';
	}
	else
	{
		$sqlQuery = 'SELECT count(package) FROM '.TABLE.' WHERE '.$field.' LIKE \'%'.$query.'%\';';
	}

	$result = mysql_query($sqlQuery);
	$row = mysql_fetch_assoc($result);
	$count = $row['count(package)'];

	if($sort != '')
	{
		$sort = ' ORDER BY '.$sort.' ASC ';
	}

	if($field == 'nameanddescription')
	{
		$sqlQuery = 'SELECT '.$keep.' FROM '.TABLE.' WHERE name LIKE \'%'.$query.'%\' OR description LIKE \'%'.$query.'%\' GROUP BY '.$keep.' '.$sort.' LIMIT '.$start.' , '.$step.';';
	}
	else
	{
		$sqlQuery = 'SELECT '.$keep.' FROM '.TABLE.' WHERE '.$field.' LIKE \'%'.$query.'%\' GROUP BY '.$keep.' '.$sort.' LIMIT '.$start.' , '.$step.';';
	}

	$result = mysql_query($sqlQuery);

	$packages = array();
	while($row = mysql_fetch_assoc($result))
	{
		$package = array();

		foreach($keepVals as $entry)
		{
			$package[$entry] = $row[$entry];
		}
		$packages[] = $package;
	}

	return $packages;
}

function queryPackagesFeed($field, $query, $keep, $maxresults, $addedorupdated)
{
	global $link;

	$field = mysql_real_escape_string($field);
	$query = mysql_real_escape_string($query);
	$keep = mysql_real_escape_string($keep);
	$maxresults = mysql_real_escape_string($maxresults);

	$keepVals = explode(',', $keep);

	$packages = array();

	if($field == 'nameanddescription')
	{
		$sqlQuery = 'SELECT '.$keep.' FROM '.TABLE.' WHERE name LIKE \'%'.$query.'%\' OR description LIKE \'%'.$query.'%\'';
	}
	else
	{
		$sqlQuery = 'SELECT '.$keep.' FROM '.TABLE.' WHERE '.$field.' LIKE \'%'.$query.'%\'';
	}

	if($addedorupdated == 'added')
	{
		$sqlQuery .= ' ORDER BY addeddate DESC LIMIT 0 , '.$maxresults.';';
	}
	else if($addedorupdated == 'updated')
	{
		$sqlQuery .= ' ORDER BY addeddate DESC,updateddate DESC LIMIT 0 , '.$maxresults.';';
	}

	$result = mysql_query($sqlQuery);

	while($row = mysql_fetch_assoc($result))
	{
		$package = array();

		foreach($keepVals as $entry)
		{
			$package[$entry] = $row[$entry];
		}

		$packages[] = $package;
	}

	return $packages;
}

function getTopRated(&$packages, $maxPackages)
{
	global $link;

	$maxPackages = mysql_real_escape_string($maxPackages);

	$sqlQuery = 'SELECT ratableKey AS package FROM rabid_ratings,rabid_ratables WHERE rabid_ratings.ratable_id=rabid_ratables.id GROUP BY ratableKey ORDER BY (sum(rating)/count(rabid_ratings.id))+count(rabid_ratings.id) DESC LIMIT 0 , '.$maxPackages.';';

	$result = mysql_query($sqlQuery);

	while($row = mysql_fetch_assoc($result))
	{
		$packages[] = array('package' => $row['package']);
	}
}

function getLatestChanges($oldestDate, &$packages, $maxPackages, $addedorupdated)
{
	$oldestDate = time() - ($oldestDate * 24 * 60 * 60);

	global $link;

	$oldestDate = mysql_real_escape_string($oldestDate);
	$maxPackages = mysql_real_escape_string($maxPackages);

	if($addedorupdated == 'added')
	{
		$sqlQuery = 'SELECT package,name,tag FROM '.TABLE.' WHERE addeddate >= FROM_UNIXTIME(\''.$oldestDate.'\') ORDER BY addeddate DESC LIMIT 0 , '.$maxPackages.';';
	}
	else if($addedorupdated == 'updated')
	{
		$sqlQuery = 'SELECT package,name,tag FROM '.TABLE.' WHERE updateddate >= FROM_UNIXTIME(\''.$oldestDate.'\') ORDER BY updateddate DESC LIMIT 0 , '.$maxPackages.';';
	}

	$result = mysql_query($sqlQuery);

	while($row = mysql_fetch_assoc($result))
	{
		$packages[] = array('package' => $row['package'], 'name' => $row['name'], 'tag' => $row['tag']);
	}
}

function getPagerHTML($urlparts, $count, $start, $step, $totalPages)
{
	$rowcount = $step;
	$range = $totalPages;
	$iRows = $count;

	$iPageNum = $start / $rowcount;
	if($iPageNum == 0) $iPageNum = 1;

	$iPages = (int) ceil($iRows / $rowcount);
	$iRange = min($iPages, $range);
	if ($iRange % 2 == 0)
	{
		$iRangeMin = (int) ($iRange / 2) - 1;
		$iRangeMax = $iRangeMin + 1;
	}
	else
	{
		$iRangeMin = (int) ($iRange - 1) / 2;
		$iRangeMax = $iRangeMin;
	}

	if ($iPageNum < ($iRangeMax + 1))
	{
		$iPageMin = 1;
		$iPageMax = $iRange;
	}
	else
	{
		$iPageMin = min(($iPageNum - $iRangeMin), ($iPages - ($iRange - 1)));
		$iPageMax = min(($iPageNum + $iRangeMax), $iPages);
	}

	$sPageButtons = '';
	if ($iPages > 1 )
	{
		$s = 0;
		$p = 0;
		if ($iPageMin > 1)
		{
			if ($iPageNum > 2)
			{
				$s = 1;

				$sPageButtons .= '<a href="'.$urlparts.'/'.(0).'">&lt;</a> ';
			}

			$s = $iPageNum - 1;
			$sPageButtons .= '<a href="'.$urlparts.'/'.$s*$rowcount.'">Prev</a> ';			
		}
		for ($i = $iPageMin; $i <= $iPageMax; $i++)
		{
			if ($i == ($start/$step + 1))
			{
				$sPageButtons .= '<span class="current">'.$i.'</span> ';
			}
			else
			{
				$s = $i;
				$sPageButtons .= '<a href="'.$urlparts.'/'.($s-1)*$rowcount.'">'.$i.'</a> ';
			}
		}
		if ($iPageMax < $iPages )
		{
			$s = $iPageNum - 1;
			$sPageButtons .= '<a href="'.$urlparts.'/'.($s+2)*$rowcount.'">Next</a> ';
			if ($s < $iPages)
			{
				$s = $iPages;
				$sPageButtons .= '<a href="'.$urlparts.'/'.($s - 1)*$rowcount.'">&gt;</a> ';
			}
		}
	}

	return $sPageButtons;
}

function getPackagesRSS($field, $query, $keep, $maxresults, $addedorupdated)
{
	$packages = queryPackagesFeed($field, $query, $keep, $maxresults, $addedorupdated);

	$field = htmlentities($field);
	$query = htmlentities($query);

	$output = '';
	$output .= '<?xml version="1.0" encoding="utf-8"?>'."\n";
	$output .= '<rss version="2.0">'."\n";
	$output .= '<channel>'."\n";
	$output .= '<title>Cydia Updates - via '.$_SERVER['HTTP_HOST'].'</title>'."\n";
	$output .= '<link>http://'.$_SERVER['HTTP_HOST'].'/cydia/</link>'."\n";
	$output .= '<description>Package Updates for '.$field.' '.$query.'</description>'."\n";

	for($i = 0; $i < sizeof($packages); $i++)
	{
		$output .= '<item>'."\n";
		$output .= '<title>'.$packages[$i]['name'].'</title>'."\n";
		$output .= '<link>http://'.$_SERVER['HTTP_HOST'].'/cydia/id/'.$packages[$i]['package'].'</link>'."\n";
		$output .= '<guid>http://'.$_SERVER['HTTP_HOST'].'/cydia/id/'.$packages[$i]['package'].'</guid>'."\n";
		if($addedorupdated == 'added')
		{
			$output .= '<pubDate>'.date("D, d M o G:i:s T",strtotime($packages[$i]['addeddate'])).'</pubDate>'."\n";
		}
		else if($addedorupdated == 'updated')
		{
			$output .= '<pubDate>'.date("D, d M o G:i:s T",strtotime($packages[$i]['updateddate'])).'</pubDate>'."\n";
		}
		$output .= '<description><![CDATA['.$packages[$i]['description'].']]></description>'."\n";
		$output .= '</item>'."\n";
	}
	$output .= '</channel>'."\n";
	$output .= '</rss>'."\n";

	return $output;
}

function getSearchFormHTML()
{
	$output = '<div id="package_search_form_container">';

	$output .= '<form action="/cydia/" method="get" class="package_search_form" onSubmit="return checkForm(this); return false;" id="searchform">';
	$output .= '<div class="package_search_form_title">Search Cydia:</div>';
	$output .= '<div class="package_search_input"><input type="text" name="q" value="" onkeyup="checkForm2(getElementById(\'searchform\'));"></input></div>';
	$output .= '<div class="package_search_select"><select name="f">';

	$output .= '<option value="nameanddescription" selected>Name & Description</option>';
	$output .= '<option value="name">Name</option>';
	$output .= '<option value="description">Description</option>';	
	$output .= '<option value="author">Author</option>';		
	$output .= '<option value="package">Package ID</option>';
	$output .= '<option value="version">Version</option>';
	$output .= '<option value="maintainer">Maintainer</option>';
	$output .= '<option value="filename">Filename</option>';
	$output .= '<option value="depiction">Depiction</option>';
	$output .= '<option value="homepage">Homepage</option>';

	$output .= '</select></div>';
	
	$output .= '<div class="package_search_button"><input type="submit" value="Search" id="searchsubmit" disabled="true"></div>';
	$output .= '</form>';

	$output .= '</div>';

	return $output;
}

function getRecentChangesHTML($oldestDate, $maxPackages, $addedorupdated)
{
	$output .= '';
	$packages = array();
	
	getLatestChanges($oldestDate, $packages, $maxPackages, $addedorupdated);

	for($i = 0; $i < sizeof($packages); $i++)
	{
		if($packages[$i]['tag'] != '' && preg_match("/commercial/", strtolower($packages[$i]['tag'])))
		{
			$output .= '<span class="package_new_link"><a class="paid" href="/cydia/id/'.urlencode($packages[$i]['package']).'">'.$packages[$i]['name'].'</a></span>';
		}
		else
		{
			$output .= '<span class="package_new_link"><a class="free" href="/cydia/id/'.urlencode($packages[$i]['package']).'">'.$packages[$i]['name'].'</a></span>';
		}
	}

	return $output;
}

function getTopRatedHTML($maxPackages)
{
	$output .= '';
	$packages = array();
	
	getTopRated($packages, $maxPackages);

	for($i = 0; $i < sizeof($packages); $i++)
	{
		$parts = explode('XZX', $packages[$i]['package']);

		if($parts[2] == 'true')
		{
			$output .= '<span class="package_new_link"><a class="paid" href="/cydia/id/'.urlencode($parts[0]).'">'.$parts[1].'</a></span>';
		}
		else
		{
			$output .= '<span class="package_new_link"><a class="free" href="/cydia/id/'.urlencode($parts[0]).'">'.$parts[1].'</a></span>';
		}
	}

	return $output;
}

function getReposHTML(&$reposTrustedHTML, &$reposUntrustedHTML)
{
	$output .= '';

	$count = 0;
	$repos = queryPackages('repo','','trusted,repo', $count, 0, 10000, repo);

	for($i = 0; $i < sizeof($repos); $i++)
	{
		if($repos[$i]['trusted'])
		{
			$reposTrustedHTML .= '<span class="package_repositories_link"><a href="/cydia/repo/'.urlencode(strtolower($repos[$i]['repo'])).'">'.$repos[$i]['repo'].'</a></span>';
		}
		else
		{
			$reposUntrustedHTML .= '<span class="package_repositories_link"><a href="/cydia/repo/'.urlencode(strtolower($repos[$i]['repo'])).'">'.$repos[$i]['repo'].'</a></span>';
		}
	}
}

function getCategoriesHTML()
{
	$output .= '';

	$count = 0;
	$categories = queryPackages('section','','section', $count, 0, 1000, 'section');

	for($i = 0; $i < sizeof($categories); $i++)
	{
		foreach($categories[$i] as $key => $value)
		{
			$output .= '<span class="package_categories_link"><a href="/cydia/section/'.urlencode(strtolower($value)).'">'.$value.'</a></span>';
		}
	}

	return $output;
}

function getSearchResultsHTML(&$packages, $count, $start, $step)
{
	$output .= '';
	$output = getSearchFormHTML();
	$output .= '<div class="package_search_results_container">';

	$output .= '<div class="package_boxtitle"><h3>'.$count.' Results for ';
	if($_GET['f'] == 'nameanddescription')
	{
		$output .= 'Name and Description';
	}
	else $output .= ucfirst($_GET['f']);

	$output .= ' '.$_GET['q'].' ';

	$output .= '</h3><div class="rss_icon_title"><a href="/cydia/feed/'.$_GET['f'].'/'.urlencode($_GET['q']).'"><img src="/cydia/img/rss_pill_orange_32.png"></a></div>';
	$output .= '</div>';

	$output .= '<div class="package_search_results">';

	for($i = 0; $i < sizeof($packages); $i++)
	{
		if($packages[$i]['tag'] != '' && preg_match("/commercial/", strtolower($packages[$i]['tag'])))
		{
			$output .= '<a class="paid" href="/cydia/id/'.urlencode($packages[$i]['package']).'">'.$packages[$i]['name'].'</a><br>';
		}
		else
		{
			$output .= '<a class="free" href="/cydia/id/'.urlencode($packages[$i]['package']).'">'.$packages[$i]['name'].'</a><br>';
		}
	}
	$output .= '<div class="pagination">'; //begin pagination
	$output .= getPagerHTML('/cydia/'.$_GET['f'].'/'.urlencode($_GET['q']), $count, $start, $step, 10); //pagination links  10 = number of pages at bottom	
	$output .= '</div>'; //end pagination
	$output .= '</div>'; //end search results
	$output .= '</div>'; //end search results container

	return $output;
}

function getSinglePackageResultHTML(&$package)
{
	$output .= '';

	if($package['trusted'])
	{
		$output .= '<center><div class="trusted_package">';
		$output .= '<div class="trusted_package_icon">This package is from a trusted repository.</div>';
		$output .= '</div></center>';	
	
	}
	else
	{
		$output .= '<div class="untrusted_package">';
		$output .= '<div class="untrusted_package_icon">This package is NOT from a trusted repository.</div>';
		$output .= '</div>';	
	}
		
	$output .= '<div class="package_info_container">';

		$output .= '<h3>'.$package['name'].'</h3>';
		$output .= '<div class="package_details_version">v'.$package['version'].'</div>';
			$output .= '<div class="package_info">';
					
				if($package['tag'] != '' && preg_match("/commercial/", strtolower($package['tag'])))
				{
					$output .= '<div class="package_type_paid">This is Cydia Store package!</div>';
				}
				else
				{
					$output .= '<div class="package_type_free">This package is FREE!</div>';
				}
									$output .= '<iframe src="/cydia/doRating.php?pid='.urlencode($package['package'].'XZX'.$package['name'].'XZX'.$commercial).'" width=170 frameBorder="0" margin="0" height=55 scrolling="no"></iframe>';	
				$output .= '<div class="package_description">';		
					$output .= $package['description'].'';
				$output .= '</div>';
					
				$output .= '<![if ! IE]><div class="package_details">';

					$updateddate = explode(' ', $package['updateddate']);
					if($updateddate[0] == '1969-12-31')
					{
						$updateddate[0] = 'N/A';
					}
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_date"></div><div class="package_details_text_title">Last Updated: </div><div class="package_details_text">'.$updateddate[0].'</div></div></div>';

					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_author"></div><div class="package_details_text_title">Author: </div><div class="package_details_text">'.'<a href="/cydia/author/'.urlencode($package['author']).'">'.$package['author'].'</a>'.'</div></div></div>';
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_id"></div><div class="package_details_text_title">Identifier: </div><div class="package_details_text">'.$package['package'].'</div></div></div>';
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_source"></div><div class="package_details_text_title">Repository: </div><div class="package_details_text">'.'<a href="/cydia/repo/'.urlencode($package['repo']).'">'.$package['repo'].'</a>'.'</div></div></div>';
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_section"></div><div class="package_details_text_title">Section: </div><div class="package_details_text">'.'<a href="/cydia/section/'.urlencode($package['section']).'">'.$package['section'].'</a>'.'</div></div></div>';
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_maintainer"></div><div class="package_details_text_title">Maintainer: </div><div class="package_details_text">'.'<a href="/cydia/maintainer/'.urlencode($package['maintainer']).'">'.$package['maintainer'].'</a>'.'</div></div></div>';
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_filename"></div><div class="package_details_text_title">Filename: </div><div class="package_details_text">'.$package['filename'].'</div></div></div>';
					$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_size"></div><div class="package_details_text_title">Size: </div><div class="package_details_text">'.$package['size'].' Bytes</div></div></div>';

					if($package['homepage'] != '')
					{
						$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_homepage"></div><div class="package_details_text_title">Homepage: </div><div class="package_details_text">'.'<a href="'.$package['homepage'].'">'.$package['homepage'].'</a>'.'</div></div></div>';
					}
						
					if($package['sponsor'] != '')
					{
						$output .= '<div class="package_details_table"><div class="package_details_row"><div class="package_details_icon_sponsor"></div><div class="package_details_text_title">Sponsor: </div><div class="package_details_text">'.$package['sponsor'].'</div></div></div>';
					}

					$commercial = (preg_match("/commercial/", strtolower($package['tag'])) == 1)?'true':'false';


				$output .= '</div><![endif]>';	//end of package details
				
				$output .= '<!--[if IE]><div class="package_details">';

					$updateddate = explode(' ', $package['updateddate']);
					if($updateddate[0] == '1969-12-31')
					{
						$updateddate[0] = 'N/A';
					}
					$output .= '<table>';
					$output .= '<tr>';
					$output .= '<td><div class="package_details_icon_date"></div></td><td class="package_details_text_table"><b>Last Updated:</b> '.$updateddate[0].'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_author"></div></td><td class="package_details_text_table"><b>Author:</b> '.'<a href="/cydia/author/'.urlencode($package['author']).'">'.$package['author'].'</a>'.'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_id"></div></td><td class="package_details_text_table"><b>Identifier:</b> '.$package['package'].'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_source"></div></td><td class="package_details_text_table"><b>Repository:</b> '.'<a href="/cydia/repo/'.urlencode($package['repo']).'">'.$package['repo'].'</a>'.'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_section"></div></td><td class="package_details_text_table"><b>Section:</b> '.'<a href="/cydia/section/'.urlencode($package['section']).'">'.$package['section'].'</a>'.'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_maintainer"></div></td><td class="package_details_text_table"><b>Maintainer:</b> '.'<a href="/cydia/maintainer/'.urlencode($package['maintainer']).'">'.$package['maintainer'].'</a>'.'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_filename"></div></td><td class="package_details_text_table"><b>Filename:</b> '.$package['filename'].'</td>';
					$output .= '</tr><tr>';
					$output .= '<td><div class="package_details_icon_size"></div></td><td class="package_details_text_table"><b>Size:</b> '.$package['size'].' Bytes</td>';
					$output .= '</tr>';

					if($package['homepage'] != '')
					{
						$output .= '<tr>';
						$output .= '<td><div class="package_details_icon_homepage"></div></td><td class="package_details_text_table"><b>Homepage:</b> '.'<a href="'.$package['homepage'].'">'.$package['homepage'].'</a>'.'</td>';
						$output .= '</tr>';
					}
					if($package['sponsor'] != '')
					{
						$output .= '<tr>';						
						$output .= '<td><div class="package_details_icon_sponsor"></div></td><td class="package_details_text_table"><b>Sponsor:</b> '.$package['sponsor'].'</td>';
						$output .= '</tr>';						
					}

					$output .= '</table>';



				$output .= '</div><![endif]-->';	//end of package details

			$output .= '</div>';	//end of package info

		$output .= '<div class="package_depiction_bg">';
			$output .= '<div id="package_depiction_view">';
				$output .='<iframe src="'.$package['depiction'].'" class="package_depiction_iframe" scrolling="yes"></iframe>';
			$output .= '</div>';
		$output .= '</div>';

	$output .= '</div>';	//end of package info container
		
	return $output;
}

if(isset($_GET['f']))
{
	$output = '';

	if(isset($_GET['rss']) && ($_GET['f'] != '')) //RSS Feed Search
	{
		header('Content-Type: application/rss+xml');

		if(!isset($_GET['q'])) $query = '';
		else $query = $_GET['q'];

		if($_GET['rss'] == 'added')
		{
			$output .= getPackagesRSS($_GET['f'], $query, 'package,name,description,addeddate', 100, 'added'); //Number of Feed Results
		}
		else
		{
			$output .= getPackagesRSS($_GET['f'], $query, 'package,name,description,updateddate', 100, 'updated'); //Number of Feed Results
		}
	}
	else if(isset($_GET['q']) && ($_GET['f'] != '') && ($_GET['q'] != '')) //HTML Form Search
	{
		header('content-type: text/html');

		$step = 100;
		if(isset($_GET['p']))
		{
			$start = $_GET['p'];
		}
		else
		{
			$start = 0;
		}

		$count = 0; // needs to be here
		$packages = queryPackages($_GET['f'], $_GET['q'], 'package,name,tag', $count, $start, $step, 'name');
		$output .= getSearchResultsHTML($packages, $count, $start, $step);
	}
	else 
	{
		$output .= getSearchFormHTML();
		$output .= 'No Results!';
	}

	echo $output;
}
else if(isset($_GET['id']) && ($_GET['id'] != ''))
{
	header('content-type: text/html');

	$package = getPackage($_GET['id']);

	$output = '';
	if($package)
	{
		$output .= getSinglePackageResultHTML($package);
	}
	else
	{
		$output .= 'Package Doesn\'t Exist!'."\n";
	}

	echo $output;
}
else if(isset($_GET['pageid']))
{
	if ($_GET['pageid'] == 'faqs')
	{
		header('content-type: text/html');

		$output = '';

		$output .= file_get_contents('faqs.data');

		echo $output;
	}
}
else if(isset($_GET['index']) || $doindex)
{
	header('content-type: text/html');
	$output = '';

	$output .= file_get_contents('overview.data');

	$output .= getSearchFormHTML();

	$reposTrustedHTML = '';
	$reposUntrustedHTML = '';
	getReposHTML($reposTrustedHTML, $reposUntrustedHTML);

	$output .= '<div class="cydia_search_main_data">';	
	$output .= '<div class="package_box_repositories">';
	$output .= '<div class="package_boxtitle"><h3>Default Repositories</h3></div>';
		$output .= '<div class="package_boxtext">';
		$output .= $reposTrustedHTML;
	$output .= '</div><br /><br />';
	
	$output .= '<div class="package_boxtitle"><h3>Other Repositories</h3></div>';
		$output .= '<div class="package_boxtext">';
		$output .= $reposUntrustedHTML;
	$output .= '</div><br /><br />';
	
	$output .= '<div class="package_boxtext">';
	$output .= '<div class="package_boxtitle"><h3><a name="addrepo">Add Your Repository</a></h3></div>';
		$output .= '<div class="package_boxtext">';
	$output .= '<iframe src="/cydia/repo-form.php" width="250" height="300" frameborder=none" scrolling="no"></iframe>';
	$output .= '</div></div></div>';
		
	$output .= '<div class="package_box_categories">';
	$output .= '<div class="package_boxtitle"><h3>Categories</h3></div>';
		$output .= '<div class="package_boxtext">';
		$output .= getCategoriesHTML();
	$output .= '</div></div>';

	$output .= '<div class="package_box_new">';
	
	$output .= '<div class="package_boxtitle"><h3>Top Rated Packages</h3></div>';
	$output .= '<div class="package_boxtext">';
		$output .= getTopRatedHTML(10);
	$output .= '</div>';

	$output .= '<br /><br />';
	
	$output .= '<div class="package_boxtitle"><h3>Newest Packages</h3><div class="rss_icon_title"><a href="/cydia/feed/new/package"><img src="/cydia/img/rss_pill_orange_32.png"></a></div></div>';
	$output .= '<div class="package_boxtext">';
		$output .= getRecentChangesHTML(7, 35, 'added');
	$output .= '</div>';

	$output .= '<br /><br />';

	$output .= '<div class="package_boxtitle"><h3>Updated Packages</h3><div class="rss_icon_title"><a href="/cydia/feed/package"><img src="/cydia/img/rss_pill_orange_32.png"></a></div></div>';
	$output .= '<div class="package_boxtext">';
		$output .= getRecentChangesHTML(7, 35, 'updated');
	$output .= '</div>';

	$output .= '</div>';
	$output .= '</div>';

	echo $output;
}

mysql_close($link);

?>
