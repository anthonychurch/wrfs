<!DOCTYPE html>
<html>
<body>
<?php include("class_sql_utilities.php");?>
<?php include("class_folder_utilities.php");?>
<?php include("class_post_fb.php");?>
<?php include("class_post_fb_mysql_utilties.php");?>
<?php
// SQL Variables -----------------------------------------------------------------------------------------------
$sqlHost='localhost';//'127.0.0.1';
$sqlPort=3306;
$sqlUser='###########';//
$sqlPasswd='###########';//
$sqlDatabase='###########';
$sqlTblePaging = '###########';
$sqlTblePosts ='###########';
$sqlTbleFrom ='###########';
$sqlTbleFolder ='###########';
// SQL Variables -----------------------------------------------------------------------------------------------

$token = "/";

// Get folder details on where to save
$thisdir = getcwd();
echo "$thisdir: ".$thisdir.'<br>';
$storeFBdata_folder = 'sites/all/libraries/images/facebook/posts';
echo "storeFBdata_folder: ".$storeFBdata_folder.'<br>';

// Facebook post summary variables
$summaryStart = 'https://graph.facebook.com/';
$summaryEnd = '?summary=true&access_token=#####################|#####################';
$comments_token = 'comments';
$likes_token = 'likes';
$summary = array($summaryStart,$summaryEnd,$comments_token,$likes_token);
//Get master facebook posts content
$page_id = '####################';
$access_token = '#################|###############_##################t=25&until=###########';

//$json_object = @file_get_contents('https://graph.facebook.com/' . $page_id . '/posts?access_token=' . $access_token);
//$json_object = file_get_contents('https://graph.facebook.com/#################/posts?access_token=#############|###############_##############');
$startPage = "https://graph.facebook.com/#################/posts?access_token=#############|###############_##############";

$fb_time_t = "T";
$fb_time_suffix = "+";

//Get post_fb_mysql instance
$fb_sql_utils = new postFbToMySQL();
//Get Folder instance
$flder = new folderUtilities();
//Get SQL connection and create table if not exist
$sql = new sql($sqlHost,$sqlPort,$sqlUser,$sqlPasswd);
$sql->connect($sqlDatabase);
$fb = new postFb();

// SQL Paging Variables ----------------------------------------------------------------------------------------
$paging_id = array("paging_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$paging_current = array("current","VARCHAR(1024)");
$paging_next = array("next","VARCHAR(1024)");
$paging_previous = array("previous","VARCHAR(1024)");
$paging_time_posts = array("time","VARCHAR(32)");
$paging_got_posts = array("got_posts","VARCHAR(2)");
// This variable array is used to create the MySQL Paging Table
$paging_array = array(
	array($paging_id[0],$paging_id[1]),
	array($paging_current[0],$paging_current[1]),
	array($paging_next[0],$paging_next[1]),
	array($paging_previous[0],$paging_previous[1]),
	array($paging_time_posts[0],$paging_time_posts[1]),
	array($paging_got_posts[0],$paging_got_posts[1])
	);
// This variable array only holds the key for the paging
$paging_key_array = array(
	$paging_id[0],
	$paging_current[0],
	$paging_next[0],
	$paging_previous[0],
	$paging_time_posts[0],
	$paging_got_posts[0]
	);
$getLastPage = "";
$getLastPageTime = "";
//$gotPost = "0";
// SQL Paging Variables ----------------------------------------------------------------------------------------

$andUntilToken = "&until=";

// METHODS---------------------------------------------------------------------------------------------------------------------------------
// Requires class_sql_utilities.php
function add_paging_links_to_sql($id, $pagingArray, $sqlTblePaging, $columnArray, $sql)
	{
		//Insert data into sql add last array item first
		for ($i = sizeof($pagingArray)-1; $i > -1; $i--)
			{
				// Add table field ID to arrays
				$pagingArray[$i][0] = $id;
				$id += 1;//For paging_id in paging table
				
				//Remove the last post created time entry in the pagingArray
				//unset($pagingArray[$i][5]);
				$sql->insertValues($sqlTblePaging,$columnArray,$pagingArray[$i]);
			}
	}
// METHODS---------------------------------------------------------------------------------------------------------------------------------
	
// Get the paging_id of the last entry----------------------------------------------------------------------------------------------------
// Make sure Paging Table exists if not create
$query = $fb_sql_utils->buildQuery($paging_array,Null,$sqlTblePaging);
$sql->query($query);
echo "fb_sql_utils->buildQuery :: query: ". $query . "<br>";


// Get the paging_id of the last page entry
// Note: $maxIDCount != the id number of the $startPosts page link
$maxIDCount = $sql->getMaxColumnValue($paging_id[0],$sqlDatabase.".".$sqlTblePaging);
echo "sql->getMaxColumnValue :: maxIDCount: ". $maxIDCount . "<br>";

// Remove last row as it is the master page that needs to be over written and
// get the new $maxIDCount
if($maxIDCount > 0)
	{
		// Note: $paging_current[0] = "current"
		$query = "SELECT " . $paging_current[0].",".$paging_time_posts[0] . " FROM " . $sqlTblePaging . " WHERE " . $paging_id[0] . " = " . $maxIDCount;
		$results = $sql->getQuery($query);
		echo '<span style="color: green;">';
		echo "Query = ".$query."<br>";
		$row = $sql->getFirstRow($results);
		var_dump($row);
		echo "<br>";
		$getLastPageTime = $row [$paging_time_posts[0]];
		//$getLastPageTime = $fb->time_fb_convert($getLastPageTime,$fb_time_t,$fb_time_suffix);
		echo "getLastPageTime = ".$getLastPageTime."<br>";
		echo "strtotime = ".strtotime($getLastPageTime)."<br>";
		// Check to see if the current facebook JSON feed is the same as the start page
		// If so remove the row
		// Else, it becomes the last JSON page access
		if($row[$paging_current[0]] == $startPage)
			{
				$query = "DELETE FROM ".$sqlTblePaging." WHERE ".$paging_id[0]." = ".$maxIDCount;
				echo "Query = ".$query."<br>";
				$result = $sql->query($query);
				$maxIDCount = $sql->getMaxColumnValue($paging_id[0],$sqlTblePaging);
				echo "maxIDCount = ".$maxIDCount."<br>";
				$query = "SELECT " . $paging_current[0].",".$paging_time_posts[0] . " FROM " . $sqlTblePaging . " WHERE " . $paging_id[0] . " = " . $maxIDCount;
				$results = $sql->getQuery($query);
				echo "Query = ".$query."<br>";
				$row = $sql->getFirstRow($results);
				$getLastPage = $row[$paging_current[0]];
				$getLastPageTime = $row[$paging_time_posts[0]];
				$maxIDCount += 1;
			}
		else
			{
				$getLastPage = $row[$paging_current[0]];
				$getLastPageTime = $row[$paging_time_posts[0]];
				$maxIDCount = $maxIDCount+1;
			}
	}
else
	{
		$maxIDCount = 1;
	}

echo "maxIDCount: ". $maxIDCount . "<br>";
echo "getLastPage: ". $getLastPage . "<br>";
echo "getLastPageTime: ". $getLastPageTime . "<br>";
echo "startPage: ". $startPage . "<br>";
echo '</span>';
echo "END: <br>";
?>

</body>
</html> 