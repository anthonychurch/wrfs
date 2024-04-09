<!DOCTYPE html>
<html>
<body>
<?php include("class_sql_utilities.php");?>
<?php include("class_folder_utilities.php");?>
<?php include("class_post_fb_mysql_utilties.php");?>
<?php
// SQL Variables -----------------------------------------------------------------------------------------------
$sqlHost='127.0.0.1';
$sqlPort=3306;
$sqlUser='###########';//
$sqlPasswd='###########';//
$sqlDatabase='###########';
$sqlTblePaging = '###########' ;
$sqlTblePosts = '###########' ;
$sqlTbleFrom = '###########' ;
$sqlTbleFolder = '###########' ;
// SQL Variables -----------------------------------------------------------------------------------------------

//Get post_fb_mysql instance
$fb_sql_utils = new postFbToMySQL();
//Get Folder instance
$flder = new folderUtilities();
//Get SQL connection and create table if not exist
$sql = new sql($sqlHost,$sqlPort,$sqlUser,$sqlPasswd);
$sql->connect($sqlDatabase);

// SQL Paging Variables ----------------------------------------------------------------------------------------
$paging_id = array("paging_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$paging_current = array("current","VARCHAR(172)");
$paging_next = array("next","VARCHAR(172)");
$paging_previous = array("previous","VARCHAR(172)");
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

// Get folder details on where to save
$thisdir = getcwd();
echo "$thisdir: ".$thisdir.'<br>';
$storeFBdata_folder = 'sites\\default\\files\\field\\image\\facebook\\posts';//$thisdir."\\facebook\\posts";
echo "storeFBdata_folder: ".$storeFBdata_folder.'<br>';

// Facebook post summary variables
$summaryStart = 'https://graph.facebook.com/';
$summaryEnd = '#################################################################################';
$comments_token = 'comments';
$likes_token = 'likes';
$summary = array($summaryStart,$summaryEnd,$comments_token,$likes_token);
//$post_id;// = '################_#########################/';
//Get master facebook posts content
$page_id = '####################################';
$access_token = '############################_#####################################';


//$json_object = @file_get_contents('https://graph.facebook.com/' . $page_id . '/posts?access_token=' . $access_token);
//$json_object = file_get_contents('https://graph.facebook.com/#################/posts?access_token=#############|###############_##############');
$startPage = "https://graph.facebook.com/#################/posts?access_token=#############|###############_##############";


$posts_id =  array("posts_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');//0
$posts_fromName_id = array("fromName_id",'INT');//1 FK from TABLE
$posts_fromFB_id = array("from_FB_id",'INT');//2 FK from TABLE
$posts_story = array("story",'VARCHAR(128)');//3
$posts_postFB_id = array("post_FB_id",'VARCHAR(48)');//4
$posts_folder_id = array("folder_id",'INT');//5 FK folder TABLE
$posts_message = array("message",'VARCHAR(10240)');//6
$posts_picture = array("picture",'VARCHAR(256)');//7
$posts_pictureName = array("picture_name",'VARCHAR(256)');//8
$posts_pictureLink = array("picture_link",'VARCHAR(512)');//9
$posts_pictureCaption = array("picture_caption",'VARCHAR(512)');//10
$posts_pictureDescription = array("picture_descrip",'VARCHAR(512)');//11
$posts_createdTime = array("created",'VARCHAR(24)');//12
$posts_updatedTime = array("updated",'VARCHAR(24)');//13
$posts_likesCount = array("likesCount",'VARCHAR(2)');//14
$posts_commentsCount = array("commentsCount",'VARCHAR(2)');//15
$posts_paging = array("paging_id",'INT');//16 FK paging TABLE
$posts_last = array("last",'INT');//17
$posts_fbNodeId = array("fbNodeId",'INT');//18
$posts_KeyArray = array(
		$posts_id[0],$posts_fromName_id[0],$posts_fromFB_id[0],
		$posts_story[0],$posts_postFB_id[0],$posts_folder_id[0],$posts_message[0],
		$posts_picture[0],$posts_pictureName[0],$posts_pictureLink[0],
		$posts_pictureCaption[0],$posts_pictureDescription[0],$posts_createdTime[0],
		$posts_updatedTime[0],$posts_likesCount[0],$posts_commentsCount[0],$posts_paging[0],
		$posts_last[0],$posts_fbNodeId[0]
		);
$posts_Array = array(
		array($posts_id[0],$posts_id[1]),//0
		array($posts_fromName_id[0],$posts_fromName_id[1]),//1
		array($posts_fromFB_id[0],$posts_fromFB_id[1]),//2
		array($posts_story[0],$posts_story[1]),//3
		array($posts_postFB_id[0],$posts_postFB_id[1]),//4
		array($posts_folder_id[0],$posts_folder_id[1]),//5
		array($posts_message[0],$posts_message[1]),//6
		array($posts_picture[0],$posts_picture[1]),//7
		array($posts_pictureName[0],$posts_pictureName[1]),//8
		array($posts_pictureLink[0],$posts_pictureLink[1]),//9
		array($posts_pictureCaption[0],$posts_pictureCaption[1]),//10
		array($posts_pictureDescription[0],$posts_pictureDescription[1]),//11
		array($posts_createdTime[0],$posts_createdTime[1]),//12
		array($posts_updatedTime[0],$posts_updatedTime[1]),//13
		array($posts_likesCount[0],$posts_likesCount[1]),//14
		array($posts_commentsCount[0],$posts_commentsCount[1]),//15
		array($posts_paging[0],$posts_paging[1]),//16
		array($posts_last[0],$posts_last[1]),//17
		array($posts_fbNodeId[0],$posts_fbNodeId[1])//18
		);
$andUntilToken = "&until=";

$from_id = array("from_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$from_name = array("name",'VARCHAR(48)');
$from_FB_id = array("FB_id",'LONG');
$from_Array = array(
		array($from_id[0],$from_id[1]),
		array($from_name[0],$from_name[1]),
		array($from_FB_id[0],$from_FB_id[1])
		);
$from_Keyay = array(
		$from_id[0],
		$from_name[0],
		$from_FB_id[0]
		);

$folder_id = array("folder_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$folder_path = array("path",'VARCHAR(128)');
$folder_Array = array(
		array($folder_id[0],$folder_id[1]),
		array($folder_path[0],$folder_path[1])
		);
$folder_KeyArray = array(
		$folder_id[0],
		$folder_path[0]
		);
		
$FKconstraintsArray = array("folder"=>"fk_from","from"=>"fk_folder");

//FK Arrays references $FKconstraintsArray
$posts_FKArray = array(
		array("FK_fromName",$posts_fromName_id[0],$from_id[0],$sqlTbleFrom),
		array("FK_from_id",$posts_fromFB_id[0],$from_id[0],$sqlTbleFrom),
		array("FK_folder_id",$posts_folder_id[0],$folder_id[0],$sqlTbleFolder)
		);


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
				$sql->insertValues($sqlTblePaging,$columnArray,$pagingArray[$i]);
			}
	}
//	Requires class_sql_utilities.php
function remove_last_status_on_post($table,$column,$setValue,$compareValue)
	{
		$query = "UPDATE ".$table." SET ".$column." = '".$setValue."' WHERE ".$column." = '".$compareValue."'";
		echo "query: ". $query . "<br>";
		$results = $sql->getQuery($query);
		return $results;
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
		$row = $sql->getFirstRow($results);
		$getLastPageTime = $row [$paging_time_posts[0]];
		if($row[$paging_current[0]] == $startPage)
			{
				$query = "DELETE FROM ".$sqlTblePaging." WHERE ".$paging_id[0]." = ".$maxIDCount;
				$result = $sql->query($query);
				$maxIDCount = $sql->getMaxColumnValue($paging_id[0],$sqlTblePaging);
				$query = "SELECT " . $paging_current[0] . " FROM " . $sqlTblePaging . " WHERE " . $paging_id[0] . " = " . $maxIDCount;
				$results = $sql->getQuery($query);
				$row = $sql->getFirstRow($results);
				$getLastPage = $row[$paging_current[0]];
				$maxIDCount += 1;
			}
		else
			{
				$getLastPage = $row[$paging_current[0]];
				$maxIDCount = $maxIDCount+1;
			}
	}
else
	{
		$maxIDCount = 1;
	}

echo "maxIDCount: ". $maxIDCount . "<br>";
echo "getLastPage: ". $getLastPage . "<br>";
// Get the paging_id of the last entry---------------------------------------------------------------------------------------------------


// Update paging table ------------------------------------------------------------------------------------------------------------------
// Go to the start page and get collect the list of next and previous pages
// Requires class_post_fb_mysql_utilties.php
$getPagingLinks = $fb_sql_utils->get_all_fb_paging_links($startPage, $getLastPage, $lastPageTime);
add_paging_links_to_sql($maxIDCount, $getPagingLinks, $sqlTblePaging, $paging_key_array, $sql);
echo "Setting page links - End:<br>";
// Update paging table ------------------------------------------------------------------------------------------------------------------
flush();


// Get a list of all current pages form the paging table that need their posts to be retrieved-------------------------------------------
$query = "SELECT ".$paging_id[0].",".$paging_current[0]." FROM ".$sqlTblePaging." WHERE ".$paging_got_posts[0]." = 0";
echo "query: ". $query . "<br>";
$allPages =  $sql->getAllRows($query);//NOTE: every second item in array $allPages is the page address, first is paging_id
echo "Get a list of all current pages - End:<br>";
// Get a list of all current pages form the paging table that need their posts to be retrieved-------------------------------------------


// Create tables if not exist------------------------------------------------------------------------------------------------------------
$query = $fb_sql_utils->buildQuery($posts_Array,Null,$sqlDatabase.".".$sqlTblePosts);
//echo "query: ". $query . "<br>";
$sql->query($query);
$query = $fb_sql_utils->buildQuery($from_Array,Null,$sqlDatabase.".".$sqlTbleFrom);
//echo "query: ". $query . "<br>";
$sql->query($query);
$query = $fb_sql_utils->buildQuery($folder_Array,Null,$sqlDatabase.".".$sqlTbleFolder);
//echo "query: ". $query . "<br>";
$sql->query($query);
// Create tables if not exist------------------------------------------------------------------------------------------------------------



// Get list of post from start page if they exist----------------------------------------------------------------------------------------
// This is to ensure the same items are not saved over by getting the posts Facebook ID
echo "Get list of post from start page if they exist - Start:<br>";
$query = "SELECT ".$posts_postFB_id[0]." FROM ".$sqlTblePosts." WHERE ".$posts_last[0]." = '1'";
//echo "query: ". $query . "<br>";
$getLast =  $sql->getAllRows($query);
//echo "getLast: ". $getLast[0] . "<br>";
echo "Get list of post from start page if they exist - End:<br>";
// Get list of post from start page if they exist----------------------------------------------------------------------------------------



// 
$postsArray;
$filledFKtables = False;
$FKids;
$postsContentArray;
$lastCount = 0;
$allFBPosts;
for ($i = 0;$i<sizeof($allPages);$i++)
	{
		$match = False;
		// This changes the post last status to false (0) to prevent the repeated posts
		echo "Boo 1<br>";
		//remove_last_status_on_post($sqlDatabase.".".$sqlTblePosts,$posts_last[0],"0","1");
		$query="UPDATE ".$sqlTblePosts." SET ".$posts_last[0]." = '0' WHERE ".$posts_last[0]." = 1";
		echo "query: ". $query . "<br>";
		$sql->query($query);
		echo "Boo 2<br>";
		// Test if there is a network connection
		echo "Test network connection to $allPages[$i][1] - Start:<br>";
		echo "Boo 3<br>";
		if(!$sock = pfsockopen("www.google.com", 80, $errno, $errstr))
			{
				echo "ERROR::$errstr($errno)<br>"; 
				break; 
			}
		echo "Test network connection to $allPages[$i][1] - End:<br>";
		// Get each pages json content for each post and add to an array
		echo "Get post from Page $allPages[$i][1] and assign to postsContentArray - Start:<br>";
		$json_object = file_get_contents( $allPages[$i][1]);
		$obj = json_decode($json_object);
		$pagesPostsContentArray = $fb_sql_utils->getPagePostContent($obj,$summary);
		echo "Get post from Page $allPages[$i][1] and assign to postsContentArray - End:<br>";
		echo $pagesPostsContentArray[0];
	}
		
echo "END: <br>";
?>

</body>
</html> 