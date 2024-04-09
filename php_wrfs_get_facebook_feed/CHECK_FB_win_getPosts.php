<!DOCTYPE html>
<html>
<body>
<?php include("class_sql_utilities.php");?>
<?php include("class_folder_utilities.php");?>
<?php include("class_post_fb_mysql_utilties.php");?>
<?php
/**
* Plugin Name: Facebook Get Post
* Plugin URI: http://anthonychurch.net
* Description: Get Facebook post feed and inject it into a holding database ready to be transfered to Word Press Databases.
* Version: 7.002
* Author: Anthony Church
* Author URI: http://anthonychurch.net
**/

/**
 * Post functions and post utility function.
 *
 * @package WordPress
 * @subpackage Post
 * @since 1.5.0
 */
// SQL Variables -----------------------------------------------------------------------------------------------
$sqlHost='localhost';//'127.0.0.1';
$sqlPort=3306;
$sqlUser='###########';
$sqlPasswd='###########';
$sqlDatabase='###########';
$sqlTblePaging ='###########';
$sqlTblePosts ='###########';
$sqlTbleFrom ='###########';
$sqlTbleFolder ='###########';
$sqlTbleComments ='###########';
$sqlTbleCommentsID ='###########';

$token = "/";

// Get folder details on where to save
$thisdir = getcwd();
echo "$thisdir: ".$thisdir.'<br>';
$wp_img_field_prefix_folder = 'wp/wp-content/uploads/facebook/posts';//this will be used by wp image field module to store location of images NOTE: FB Post id needs to be appended to
$fullPath = "";//needs the facebook post id as a sub folder //$thisdir.$wp_folder_prefix.$wp_field_img_folder;

echo "wp_img_field_prefix_folder: ".$wp_img_field_prefix_folder.'<br>';
$ext = "jpg";

$token = "/";
$img_max_size = 220;
$img_ext = array("jpg","gif","png","bmp","tif","iff");
$thisdir = getcwd();
$website = "http://www.winmaleerfs.com.au";
$int_image_folder = $thisdir.$token."wp/wp-content/uploads/facebook/posts";
$ext_image_folder = $website.$token."wp/wp-content/uploads/facebook/posts";
echo "int_image_folder = ".$int_image_folder."<br>";

$check_file_names = [];
// Facebook post summary variables
$summaryStart = 'https://graph.facebook.com/';
$summaryEnd = '?summary=true&access_token=##########################|#####################################';
$comments_token = 'comments';
$likes_token = 'likes';
$summary = array($summaryStart,$summaryEnd,$comments_token,$likes_token);
//Get master facebook posts content
$page_id = '####################';
$access_token = '#################|###############_##################t=25&until=###########';

$un_null_number = 0;

//$json_object = @file_get_contents('https://graph.facebook.com/' . $page_id . '/posts?access_token=' . $access_token);
//$json_object = file_get_contents('https://graph.facebook.com/#################/posts?access_token=#############|###############_##############');
$startPage = "https://graph.facebook.com/#################/posts?access_token=#############|###############_##############";

$all_sql_tables = array(
					array('paging' => 'paging'),
					array('posts' => 'posts'),
					array('fromFB' => 'fromFB'),
					array('folder' => 'folder'),
					array('comments' => 'comments'),
					array('commentsID' => 'commentsID')
					);

// SQL Variables -----------------------------------------------------------------------------------------------

//Get post_fb_mysql instance
$fb_sql_utils = new postFbToMySQL();
//Get Folder instance
$flder = new folderUtilities();
//Get SQL connection and create table if not exist
$sql = new sql($sqlHost,$sqlPort,$sqlUser,$sqlPasswd);
$sql->connect($sqlDatabase);

$batch = 100;//how many pages to cycle through 

$getPostColour = array("purple","blue");
$postColourToggle = 0;

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
$posts_id =  array("posts_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');//0
$posts_fromName_id = array("fromName_id",'INT');//1 FK from TABLE
$posts_fromFB_id = array("from_FB_id",'INT');//2 FK from TABLE
$posts_story = array("story",'VARCHAR(512)');//3
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
$posts_sharesCount = array("sharesCount",'VARCHAR(2)');//15
$posts_commentsCount = array("commentsCount",'VARCHAR(2)');//16
$posts_paging = array("paging_id",'INT');//17 FK paging TABLE
$posts_last = array("last",'INT');//18
$posts_fbNodeId = array("fbNodeId",'INT');//19
$posts_keep = array("keep",'INT');//20
$posts_disable = array("disable",'INT');//21
$posts_wp = array("wp",'INT');//22
$posts_drupal = array("drupal",'INT');//23
//$posts_title = array("title",'VARCHAR(512)');//24
$posts_key_array = array(
		$posts_id[0],$posts_fromName_id[0],$posts_fromFB_id[0],
		$posts_story[0],$posts_postFB_id[0],$posts_folder_id[0],$posts_message[0],
		$posts_picture[0],$posts_pictureName[0],$posts_pictureLink[0],
		$posts_pictureCaption[0],$posts_pictureDescription[0],$posts_createdTime[0],
		$posts_updatedTime[0],$posts_likesCount[0],$posts_sharesCount[0],$posts_commentsCount[0],
		$posts_paging[0],$posts_last[0],$posts_fbNodeId[0],$posts_keep[0],$posts_disable[0],$posts_wp[0],$posts_drupal[0]//,$posts_title[0]
		);

$posts_array = array(
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
		array($posts_sharesCount[0],$posts_sharesCount[1]),//15
		array($posts_commentsCount[0],$posts_commentsCount[1]),//16
		array($posts_paging[0],$posts_paging[1]),//17
		array($posts_last[0],$posts_last[1]),//18
		array($posts_fbNodeId[0],$posts_fbNodeId[1]),//19
		array($posts_keep[0],$posts_keep[1]),//20
		array($posts_disable[0],$posts_disable[1]),//21
		array($posts_wp[0],$posts_wp[1]),//,//22
		array($posts_drupal[0],$posts_drupal[1])//,//23
		//array($posts_title[0],$posts_title[1])//24
		);

$andUntilToken = "&until=";

$from_id = array("from_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$from_name = array("name",'VARCHAR(48)');
$from_FB_id = array("FB_id",'LONG');
$from_array = array(
		array($from_id[0],$from_id[1]),
		array($from_name[0],$from_name[1]),
		array($from_FB_id[0],$from_FB_id[1])
		);
$from_key_array = array(
		$from_id[0],
		$from_name[0],
		$from_FB_id[0]
		);

$folder_id = array("folder_id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$folder_path = array("path",'VARCHAR(128)');
$folder_path_full = array("full_path",'VARCHAR(512)');
$folder_array = array(
		array($folder_id[0],$folder_id[1]),
		array($folder_path[0],$folder_path[1])//,array($folder_path_full[0],$folder_path_full[1])
		);
$folder_key_array = array(
		$folder_id[0],
		$folder_path[0]//,$folder_path_full[0]
		);
		
$FKconstraintsArray = array("folder"=>"fk_from","from"=>"fk_folder");

//FK Arrays references $FKconstraintsArray
$posts_fk_array = array(
		array("FK_posts_fromName",$posts_fromName_id[0],$from_id[0],$sqlTbleFrom),
		array("FK_posts_from_id",$posts_fromFB_id[0],$from_id[0],$sqlTbleFrom),
		array("FK_posts_folder_id",$posts_folder_id[0],$folder_id[0],$sqlTbleFolder)
		);

$comments_id = array("id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
$comments_cmnt_id = array("commentId",'VARCHAR(48)');
$comments_post_id = array("postId",'INT');//Foreign Key
$comments_from = array("fromName",'INT');//Foreign Key
$comments_fb_id = array("fromId",'INT');//Foreign Key
$comments_msg = array("message",'VARCHAR(1024)');
$comments_time = array("created",'VARCHAR(24)');//12
$comments_last = array("last",'INT');//17
$comments_fbNodeId = array("fbNodeId",'INT');//18
$comments_ignore = array("doNotUse",'INT');//18
$comments_array = array(
		array($comments_id[0],$comments_id[1]),
		array($comments_cmnt_id[0],$comments_cmnt_id[1]),
		array($comments_post_id[0],$comments_post_id[1]),
		array($comments_from[0],$comments_from[1]),
		array($comments_fb_id[0],$comments_fb_id[1]),
		array($comments_msg[0],$comments_msg[1]),
		array($comments_time[0],$comments_time[1]),
		array($comments_last[0],$comments_last[1]),
		array($comments_fbNodeId[0],$comments_fbNodeId[1]),
		array($comments_ignore[0],$comments_ignore[1])
		);
$comments_fk_array = array(	
		array("FK_comments_fromName",$comments_from[0],$from_id[0],$sqlTbleFrom),
		array("FK_comments_from_id",$comments_fb_id[0],$from_id[0],$sqlTbleFrom),
		array("FK_comments_post_id",$comments_post_id[0],$posts_id[0],$sqlTblePosts)
		);
$comments_key_array = array(
		$comments_id[0],
		$comments_cmnt_id[0],
		$comments_post_id[0],
		$comments_from[0],
		$comments_fb_id[0],
		$comments_msg[0],
		$comments_time[0],
		$comments_last[0],
		$comments_fbNodeId[0],
		$comments_ignore[0]
		);

$comments_id_array = array(
		array("id",'INT NOT NULL AUTO_INCREMENT PRIMARY KEY'),
		array("commentId",'VARCHAR(48)')
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
function check_fk_connection_sql($query,$idArray,$id,$sql)
	{
		$results = $sql->getQuery($query);
		$row = $sql->getRow($results);
		var_dump($row);
		echo "<br>";
		$idArray[$id] = $row;
		$results = $row[$id];
		return $results;
	}

function printMatch($match)
	{
		if(!$match)
			{
				echo '<p style="color: red;">match: False</p><br><br><br><br>';
			}
		else
			{
				echo '<p style="color: red;">match: True</p><br><br><br><br>';
			}
	}
// $sql_key_array and $sql_value_array will be variable length and are mapped to one another
// $sql_value_array[0] needs to be empty so that a new id value can be injected
function get_foriegn_key($maxID,$id_array,$sql_table,$sql_key_array,$sql_value_array,$sql)
	{
		$query = "SELECT " . $sql_key_array[0]  . " FROM " . $sql_table . " WHERE " . $sql_key_array[1] . " = " . '"'.$sql_value_array[1].'"';
		echo "query: ". $query . "<br>";
		$results = check_fk_connection_sql($query,$id_array,$sql_key_array[0],$sql);
		var_dump($results)."<br>";
		$id_array[$sql_key_array[0]] = Null;
		if($results == Null)
			{
				//Add the id value for the SQL id column
				$sql_value_array[0] = $maxID+1;
				// $sql_key_array and $sql_value_array will be variable length and are mapped to one another
				$sql->insertValues($sql_table,$sql_key_array,$sql_value_array );
				$id_array[$sql_key_array[0]] = $maxID+1;
			}
		else
			{
				$id_array[$sql_key_array[0]] = $results;
			}
		return $id_array[$sql_key_array[0]];
	}
	
function get_pages_posts_comments($obj)
	{
		$allComments = array();
		foreach ($obj->data as $obj_data)
			{
				$c = array();
				$com = $obj_data->comments;
				$postID = $obj_data->id;
				//echo "com->data->id: ".$com->data->id;
				//echo $postID . "<br>";
				$pId = array("postID" => $postID);
				echo '<span style="color:Red">pId["postID"]: '.$pId["postID"]. '</span><br>';
				array_push($c,$com);
				array_push($c,$pId);
				array_push($allComments,$c);
			}
		return $allComments;
	}

// Win RFS Comments
// Need to map $comments_fb_id[0] ("fromId") to "from" tables id
// Need to map $comments_from[0] ("fromName") to "from" tables id
// Need to map $comments_post_id[0] ("postId") to "posts" tables id - get current post id
// $from_fb_id = is an array facebook id numbers eg ''. This is preparing a future implementation for more than one users comments to be added
// $maxID = the last free id from the comments table. This will be incremented
// This function on filters through on posts content array
// $commentsArray[1]["postID"] = post id number
// $commentsArray[0]->data as $cm_data :: $cm_data->id = comment id number
// This function on filters through on posts content array
function prepare_post_comments_sql($commentsArray,$from_fb_id,$maxID)
	{
		$returnArray = array();
		//echo "boo1"."<br>";
		//var_dump($commentsArray);
		//echo "<br>";
		foreach ($commentsArray[0]->data as $cm_data)
			{
				//echo "cm_data->id = ".$cm_data->id . "<br>";
				//echo "commentsArray[1]['postID'] = ".$commentsArray[1]["postID"] . "<br>";
				//echo "boo2"."<br>";
				//echo $cm_data->from->id . " = " . $from_fb_id . ".... ".$cm[1]["postID"]."; ";
				if($cm_data->from->id == $from_fb_id)
					{
						// Array order: id, comment id, post id, from name, from id,  message, created time, last, fbNodeId, ignore
						$maxID+=1;
						$arr = array($maxID,$cm_data->id, $commentsArray[1]["postID"], $cm_data->from->name, $cm_data->from->id, $cm_data->message, $cm_data->created_time, "0", "0", "0");
						echo "-----------------------------------------------------------------------<br>";
						echo "Item: ". $maxID.": <br>";
						echo "Comment ID: ". $cm_data->id.": <br>";
						echo "From ". $cm_data->from->name.": <br>";
						echo "From ID: ".$cm_data->from->id.": <br>";
						echo "Post id: ".$commentsArray[1]["postID"]."<br>";
						echo $cm_data->message . "<br>";
						echo $cm_data->created_time . "<br>";
						echo "-----------------------------------------------------------------------<br>";
						array_push($returnArray,$arr);
						
					}
			}
		return array($returnArray,$maxID);
	}
		
function get_next_free_number($id)
	{
		if($id == Null)
			{
				$id = 1;
			}
		else
			{
				$id += 1;//get first free row
			}
		return $id;
	}
	
function un_null_number($id,$value)
	{
		if($id == Null)
			{
				$id = $value;
			}
		return $id;
	}
	
function save_fk_comments_id($array,$commentsId_id,$commentsId_array,$commentsId_table,$sql)
	{
		// SELECT posts_id FROM winmalee_facebook.posts WHERE post_FB_id = '224561940891324_224561950891323';
		echo '<span style="color: Red;">';
		$query = "SELECT ". $commentsId_array[0][0] ." FROM ". $commentsId_table ." WHERE ". $commentsId_array[1][0] ." = '". $array[1]."'";
		$results =  $sql->getQuery($query);
		echo "query: " .$query."<br>";
		$comID =  $sql->getRow($results)[ $commentsId_array[0][0]];
		//var_dump($comID)."<br>";
		echo "comID: " .$comID."<br>";
		echo "array[1]: " .$array[1]."<br>";
		if($comID == Null)
			{
				// INSERT commentsid SET id = 1, commentId = '12233213_123244';
				// Use new ID for comments ID
				echo "commentsId_array[0][0]: " .$commentsId_array[0][0]."<br>";
				echo "commentsId_array[0][1]: " .$commentsId_array[0][1]."<br>";
				$commentsId_id+=1;
				$query= "INSERT ".$commentsId_table." SET ".$commentsId_array[0][0]." = '".$commentsId_id."', ".$commentsId_array[1][0]." = '".$array[1]."'";
				$sql->query($query);
				echo "query: " .$query."<br>";
				// Upadate array to be injected into comments with Foreign Key for comments ID
				$array[1] = $commentsId_id;
				echo "array[1]: " .$array[1]."<br>";
			}
		else
			{
				// Use existing ID for comments ID
				echo "commentsId_array[0][0]: " .$commentsId_array[0][0]."<br>";
				echo "commentsId_array[0][1]: " .$commentsId_array[0][1]."<br>";
				//$query= "INSERT ".$commentsId_table." SET ".$comments_id_array[0][0]." = '".$comID."', ".$commentsId_array[1][0]." = '".$array[1]."'";
				//echo "query: " .$query."<br>";
				//$sql->query($query);
				// Upadate array to be injected into comments with Foreign Key for comments ID
				$array[1] = $comID;
				echo "array[1]: " .$array[1]."<br>";
			}
		echo '</span>';
		return $array;
	}
	
function save_fk_from_id($array,$from_id,$from_array,$from_table,$sql)
	{
		// SELECT posts_id FROM winmalee_facebook.posts WHERE post_FB_id = '224561940891324_224561950891323';
		echo '<span style="color: Red;">';
		$query = "SELECT ". $from_array[0][0] ." FROM ". $from_table ." WHERE ". $from_array[1][0] ." = '". $array[3]."'";
		$results =  $sql->getQuery($query);
		$fromID =  $sql->getRow($results)[$from_array[0][0]];
		//var_dump($fromID)."<br>";
		echo "query: " .$query."<br>";
		echo "fromID: " .$fromID."<br>";
		echo "from_array[2][0]: " .$from_array[2][0]."<br>";
		echo "from_array[1][0]: " .$from_array[1][0]."<br>";
		echo "from_array[0][0]: " .$from_array[0][0]."<br>";
		echo "array[3]: " .$array[3]."<br>";
		if($fromID == Null)
			{
				// INSERT fromfb SET from_id = 2, name = 'Winmalee Rural Fire Brigade', FB_id = '224561940891324';
				// Use new ID for from ID
				echo "from_array[0][0]: " .$from_array[0][0]."<br>";
				echo "from_array[1][0]: " .$from_array[1][0]."<br>";
				// $array[3] = name = 'Winmalee Rural Fire Brigade' = $from_array[0][1]
				// $array[2] = name = FB_id = '224561940891324' = $from_array[0][2]
				echo "from_array[0][0]: " .$from_array[0][0]."<br>";
				echo "from_array[1][0]: " .$from_array[1][0]."<br>";
				$query= "INSERT ".$from_table." SET ".$from_array[0][0]." = '".$from_id."', ".$from_array[1][0]." = '".$array[3]."', ".$from_array[2][0]." = '".$array[4]."'";
				$sql->query($query);
				echo "query: " .$query."<br>";
				// Upadate array to be injected into from with Foreign Key for comments ID
				$array[3] = $from_id;
				$array[4] = $from_id;
				echo "array[3]: " .$array[3]."<br>";
				echo "array[4]: " .$array[4]."<br>";
			}
		else
			{
				// Use existing ID for from ID
				//$query= "INSERT ".$from_table." SET ".$from_array[0][0]." = '".$fromID."', ".$from_array[1][0]." = '".$array[3]."', ".$from_array[2][0]." = '".$array[4]."'";
				//$sql->query($query);
				// Upadate array to be injected into from with Foreign Key for comments ID
				$array[3] = $fromID;
				$array[4] = $fromID;
				echo "array[3]: " .$array[3]."<br>";
				echo "array[4]: " .$array[4]."<br>";
			}
		echo '</span>';
		return $array;
	}
	
function save_fk_post_id($array,$post_array,$post_table,$sql)
	{
		// SELECT posts_id FROM winmalee_facebook.posts WHERE post_FB_id = '224561940891324_224561950891323';
		echo '<span style="color: Red;">';
		$query = "SELECT ". $post_array[0][0] ." FROM ". $post_table ." WHERE ". $post_array[4][0] ." = '". $array[2]."'";
		$results =  $sql->getQuery($query);
		$postID =  $sql->getRow($results)[$post_array[0][0]];
		//var_dump($fromID)."<br>";
		echo "query: " .$query."<br>";
		echo "postID: " .$postID."<br>";
		echo "post_array[4][0]: " .$post_array[4][0]."<br>";
		echo "post_array[1][0]: " .$post_array[1][0]."<br>";
		echo "post_array[0][0]: " .$post_array[0][0]."<br>";
		echo "array[3]: " .$array[3]."<br>";
		if($postID == Null)
			{
				$array[2] = 0;
			}
		else
			{
				$array[2] = $postID;
				echo "array[2]: " .$array[2]."<br>";
			}
		echo '</span>';
		return $array;
	}

// METHODS---------------------------------------------------------------------------------------------------------------------------------
	

// Get a list of all current pages form the paging table that need their posts to be retrieved-------------------------------------------
$query = "SELECT ".$paging_id[0].",".$paging_current[0]." FROM ".$sqlTblePaging." WHERE ".$paging_got_posts[0]." = 0 ORDER BY ".$paging_id[0];
echo "query: ". $query . "<br>";
$allPages =  $sql->getAllRows($query);//NOTE: every second item in array $allPages is the page address, first is paging_id
echo "allPages: ". $allPages . "<br>";
echo "Get a list of all current pages - End:<br>";
// Get a list of all current pages form the paging table that need their posts to be retrieved-------------------------------------------


// Create tables if not exist------------------------------------------------------------------------------------------------------------

$query = $fb_sql_utils->buildQuery($from_array,Null,$sqlDatabase.".".$sqlTbleFrom);
$sql->query($query);
echo "query: ". $query . "<br><br>";
$query = $fb_sql_utils->buildQuery($folder_array,Null,$sqlDatabase.".".$sqlTbleFolder);
$sql->query($query);
echo "query: ". $query . "<br><br>";
$query = $fb_sql_utils->buildQuery($comments_id_array,Null,$sqlDatabase.".".$sqlTbleCommentsID);
$sql->query($query);
$query = $fb_sql_utils->buildQuery($posts_array,$posts_fk_array,$sqlDatabase.".".$sqlTblePosts);
echo "query: ". $query . "<br><br>";
$sql->query($query);
$query = $fb_sql_utils->buildQuery($comments_array,$comments_fk_array,$sqlDatabase.".".$sqlTbleComments);
echo "query: ". $query . "<br><br>";
$sql->query($query);
// Create tables if not exist------------------------------------------------------------------------------------------------------------


// Get the id of the last entry------------------------------------------------------------------------------------------------------------
$maxIDCountFromFB = $sql->getMaxColumnValue($from_id[0],$sqlTbleFrom);
$maxIDCountFolder = $sql->getMaxColumnValue($folder_id[0],$sqlTbleFolder);
$maxIDCountPosts = $sql->getMaxColumnValue($posts_id[0],$sqlTblePosts);
$maxIDCountsComments = $sql->getMaxColumnValue($comments_id[0],$sqlTbleComments);
$maxIDCountsCommentsId = $sql->getMaxColumnValue($comments_id_array[0][0],$sqlTbleCommentsID);
echo "maxIDCountPosts: ". $maxIDCountPosts . "<br>";
echo "maxIDCountsComments: ". $maxIDCountsComments . "<br>";
echo "maxIDCountFolder: ". $maxIDCountFolder . "<br>";
echo "maxIDCountsCommentsId: ". $maxIDCountsCommentsId . "<br>";
echo "maxIDCountFromFB: ". $maxIDCountFromFB . "<br>";
// Get the id of the last entry------------------------------------------------------------------------------------------------------------


// Get list of post from start page if they exist----------------------------------------------------------------------------------------
// This is to ensure the same items are not saved over by getting the posts Facebook ID
echo "Get list of post from start page if they exist - Start:<br>";
//$query = "SELECT ".$posts_postFB_id[0]." FROM ".$sqlDatabase.".".$sqlTblePosts." WHERE ".$posts_last[0]." = '1'";
$query = "SELECT ".$posts_postFB_id[0]." FROM ".$sqlDatabase.".".$sqlTblePosts." WHERE posts_id > '". ($maxIDCountPosts-25)."'";
echo "query: ". $query . "<br>";
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
$count = 0;
//$sqlStoreFBdata_folder = "";
//for ($i = 0;$i<sizeof($allPages);$i++)

// if $allPages is not set, do not continue
if (isset($allPages)) 
	{
		for ($i = 0;$i<$batch;$i++)
			{
				$match = False;
				//printMatch($match);
				// Reset Last field in table------------------------------------------------------------------------------------
				// This changes the post last status to false (0) to prevent the repeated posts
				$query="UPDATE ".$sqlTblePosts." SET ".$posts_last[0]." = '0' WHERE ".$posts_last[0]." = 1";
				echo "query: ". $query . "<br>";
				$sql->query($query);
				// Repeat the process for the comments table. This changes the post last status to false (0) to prevent the repeated posts
				$query="UPDATE ".$sqlTbleComments." SET ".$comments_id[0]." = '0' WHERE ".$comments_last[0]." = 1";
				echo "query: ". $query . "<br>";
				$sql->query($query);
				// Reset Last field in table------------------------------------------------------------------------------------
				
				
				// Test if there is a network connection------------------------------------------------------------------------
				echo "Test network connection to ".$allPages[$i][1]." - Start:<br>";
				if(!$sock = pfsockopen("www.google.com", 80, $errno, $errstr))
					{
						echo "ERROR::$errstr($errno)<br>"; 
						break; 
					}
				echo "Test network connection to ".$allPages[$i][1]." - End:<br>";
				// Test if there is a network connection------------------------------------------------------------------------
				
				
				// Get each pages json content for each post and add to an array------------------------------------------------
				echo "Get post from Page ".$allPages[$i][1]." and assign to postsContentArray - Start:<br>";
				echo '<span style="color:Red">allPages[i][1]: '.$allPages[$i][1]. '</span><br>';
				$json_object = file_get_contents( $allPages[$i][1]);
				$obj = json_decode($json_object);
				$pagesPostsContentArray = $fb_sql_utils->getPagePostContent($obj,$summary);
				//echo "Get post from Page ".$allPages[$i][1]." and assign to postsContentArray - End:<br>";
				// Get each pages json content for each post and add to an array------------------------------------------------
				
				
				// Get Pages Post Comments and place into an Array--------------------------------------------------------------
				$pagesPostsCommentsArray = get_pages_posts_comments($obj);
				// Get Pages Post Comments and place into an Array--------------------------------------------------------------
				
				
				// flush memory-------------------------------------------------------------------------------------------------
				if($i % 5)
					{
						flush();
					}
				// flush memory-------------------------------------------------------------------------------------------------
				
				
				// Filter through Pages and start getting Post Content----------------------------------------------------------
				echo "pagesPostsContentArray: ".$pagesPostsContentArray.":<br>";
				// UPDATE TO isset() ******
				if($pagesPostsContentArray != Null)
					{
						//Fill FK folders
						echo  '<span style="color: '.$getPostColour[$postColourToggle].';">';
						echo '<span style="color: '.$getPostColour[$postColourToggle].';">Test and retreive Foriegn Key Data for Folder and Facebook ID - Start:</span><br>';
						// Test if Foriegn Keys exist and if not add Foriegn Keys and add to the appropriate table-------------------------------------------
						// filledFKtables is intially set to False to force a check
						if($filledFKtables == False)
							{
								// Check if Foriegn Key fields exist and are connected
								// If not create and add them in
								$value_array = array("",$wp_img_field_prefix_folder);
								$FKids[$folder_key_array[0]] = get_foriegn_key($maxIDCountFolder,$FKids,$sqlTbleFolder,$folder_key_array,$value_array,$sql);
								$value_array = array("",$pagesPostsContentArray[0][1],$pagesPostsContentArray[0][2]);
								$FKids[$from_id[0]] = get_foriegn_key($maxIDCountFromFB,$FKids,$sqlTbleFrom,$from_key_array,$value_array,$sql);
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">FKids[$from_id[0]]: '.$FKids[$from_id[0]] . '</span><br>';
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">FKids[$folder_id[0]]: '.$FKids[$folder_id[0]] . '</span><br>';
								// Prevents further checks in this session
								$filledFKtables = True;
							}
						echo '<span style="color: '.$getPostColour[$postColourToggle].';">Test and retreive Foriegn Key Data for Folder and Facebook ID - End:</span><br>';
						// Test if Foriegn Keys exist and if not add Foriegn Keys and add to the appropriate table-------------------------------------------
						
						
						// Get all Post that are on the Master FB page to ensure that we do not save twice---------------------------------------------------
						//echo '<span style="color: '.$getPostColour[$postColourToggle].';">Retreive all posts from post table that are on page '.$allPages[$i][0].' to make sure we dont save double - Start:</span><br>';
						//$query = "SELECT ".$posts_postFB_id[0]." FROM ".$sqlTblePosts." WHERE ".$posts_paging[0]." = ".$allPages[$i][0];
						//echo '<span style="color: '.$getPostColour[$postColourToggle].';">query: '. $query . '</span><br>';
						//$curPagePosts =  $sql->getAllRows($query);
						//var_dump($curPagePosts);
						//echo "<br>";
						//echo '<span style="color: '.$getPostColour[$postColourToggle].';">Retreive all posts from post table that are on page '.$allPages[$i][0].' to make sure we dont save double - End:</span><br>';
						//echo '<span style="color: '.$getPostColour[$postColourToggle].';">pagesPostsContentArray: '. $pagesPostsContentArray . '</span><br>';
						// Get all Post that are on the Master FB page to ensure that we do not save twice---------------------------------------------------
						
						
						//($sqlStoreFBdata_folder,$maxIDCountPosts,$curPagePosts,$pagesPostsContentArray,$FKids,$allPages,$getLast,$match,$sqlTblePosts,$posts_key_array,$sql)
						// Loop through each page and retreive posts-----------------------------------------------------------------------------------------
						// Interation is reversed to ensure that the last item is injected into SQL DB first-------------------------------------------------
						//for ($j = sizeof($pagesPostsContentArray)-1; $j > -1; $j--)
						for ($j = sizeof($pagesPostsContentArray); $j > -1; $j--)
							{
								
								// Add the Foriegn Key data to $pagesPostsContentArray[$j]----------------------------------------------------------------------------------------------
								// This will include:
								// 	1. ID for post table
								//	2. ID from the fromFB table. This the FB name (Winmalee Rural Fire Brigade) and FB ID (224561940891324)
								// 	3. ID from the folder table. This is the folder where the posts images are saved under their post id folder
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">Add extra field data to each post such as ID, folder ID.... - Start:</span><br>';
								//printMatch($match);
								//$maxIDCountPosts+1;//Posts ID to be incremented
								//$pagesPostsContentArray[$j][0] = $maxIDCountPosts;
								echo "maxIDCountPosts =".$maxIDCountPosts."<br>";
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">pagesPostsContentArray[$j][0]: '. $pagesPostsContentArray[$j][0] . '</span><br>';
								$pagesPostsContentArray[$j][1] = (string)$FKids[$from_id[0]];//"fromName_id" = (string)$FKids[$from_id[0]];
								$pagesPostsContentArray[$j][2] = (string)$FKids[$from_id[0]];//"fromFB_id"
								$pagesPostsContentArray[$j][5] = (string)$FKids[$folder_id[0]];//"folder_id"] = (string)$FKids[$folder_id[0]];
								$pagesPostsContentArray[$j][17] = $allPages[$i][0];//This is the page_id of current page
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">pagesPostsContentArray[$j][16]: '. $pagesPostsContentArray[$j][16] . '</span><br>';
								$pagesPostsContentArray[$j][18] = 1;//"last" = (string)$pageCount;
								echo "pagesPostsContentArray[$j][18] =".$pagesPostsContentArray[$j][18]."<br>";
								$pagesPostsContentArray[$j][19] = 0;//"used" = (string)$lastCount;
								$pagesPostsContentArray[$j][20] = 0;//"keep"
								$pagesPostsContentArray[$j][21] = 0;//"disable"
								$pagesPostsContentArray[$j][22] = 0;//"not sent to wordpress cms"
								$pagesPostsContentArray[$j][23] = 0;//"not sent to drupal cms"
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">Add extra field data to each post such as ID, folder ID.... - End</span><br>';
								// Add the Foriegn Key data to $pagesPostsContentArray[$j]----------------------------------------------------------------------------------------------
								
								
								//Check to see is individual post has been saved--------------------------------------------------------------------------------------------------------
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">Check to see is individual post has been saved - Start:</span><br>';
								echo '<span style="color: '.$getPostColour[$postColourToggle].';">Test to see if getLast Array is Null - Start:</span><br>';
								if($getLast != Null)
									{
										echo '<span style="color: '.$getPostColour[$postColourToggle].';">Loop through getLast Array to make sure post isnt already saved - Start:</span><br>';
										foreach($getLast as $last)
											{
												//Test to see if has been saved by testing the Facebook ID
												echo "last[0]: ". $last[0] . "<br>";
												echo "pagesPostsContentArray[$j][4]: ". $pagesPostsContentArray[$j][4] . "<br>";
												if($last[0] == $pagesPostsContentArray[$j][4])
													{
														//$match = True;
														echo '<span style="color: red;">MATCH!!!!<span>';
														break;
													}
											}
										echo '<span style="color: '.$getPostColour[$postColourToggle].';">Loop through getLast Array to make sure post isnt already saved - End:</span><br>';
									}
								//Check to see is individual post has been saved--------------------------------------------------------------------------------------------------------
								
								
								
							}
					}
				else
					{
						echo '<p style="color: red;">facebook page held no Posts</p><br><br><br><br>';
						echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";
					}
				// Filter through Pages and start getting Post Content----------------------------------------------------------
				
				
				// Flush memory-------------------------------------------------------------------------------------------------
				unset($pagesPostsContentArray);
				flush();
				// Flush memory-------------------------------------------------------------------------------------------------
				
				
				// Update the pages got_posts status in the paging---------------------------------------------------------------
				// Need to test connection to SQL server
				echo "If match is False, update pages got post status - Start:<br>";
				if(!$match)
					{
						echo "Updating pages got post status - Start:<br>";
						//Need to test connection to SQL server
						$query="UPDATE ".$sqlTblePaging." SET ".$paging_got_posts[0]." = '1' WHERE ".$paging_id[0]." = ".$allPages[$i][0];
						echo "<p style='color: red;'>query: ". $query . "</p><br>";
						$sql->query($query);
						echo "Updating pages got post status - end<br>";
					}
				echo "If match is False, update pages got post status -End<br>";
				echo "<br>END POST<br><br><br><br><br><br><br><br><br><br><br><br><br>";
				// Update the pages got_posts status in the paging---------------------------------------------------------------
				
				
			}
	}
else
	{
		echo "No pages to loop through<br>";
	}
unset($getLast);
echo "END: <br>";
?>

</body>
</html> 