
<?php include("class_sql_utilities.php");?>
<?php include("class_post_fb.php");?>
<?php include("class_folder_utilities.php");?>
<?php
/**
* Plugin Name: Facebook Post Capture 
* Plugin URI: http://anthonychurch.net
* Description: Get Facebook post feed.
* Version: 1.0
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

// WORD PRESS FUNCTION - START ===============================================================================================================

/**
This function converts any string of characters whose structure reflects that of an html hyper link to a html link.
NOTE: This function can not cater for a string that is encapsulated with HTML tag:-
e.g. <a href="http://www.google.com" >http://google.com</a>
**/
function convert_string_to_HTML_link($text, $regX, $html_attributes)
	{
		/**
		Reg expression
		**/
		if (preg_match_all($regX, $text, $matches)) 
			{
				foreach($matches[0] as $key => $match)
					{
						$text = str_replace($match,'<a href='.$match.' '.$html_attributes.'">'.$match.'</a>',$text);
					}
			}	
		return $text;
	}

function add_taxonomy_term($name, $vid, $tablePrefix, $database, $sql_conx)
	{
		$wp_tbl_taxonomy_term_data = $tablePrefix.'taxonomy_term_data';
		$wp_tbl_taxonomy_term_hierarchy = $tablePrefix.'taxonomy_term_hierarchy';
		
		$tid = '';
		$maxTidData = $sql_conx->getMaxColumnValue('tid',$database.".".$wp_tbl_taxonomy_term_data);
		$maxTidHi = $sql_conx->getMaxColumnValue('tid',$database.".".$wp_tbl_taxonomy_term_hierarchy);
		if($maxTidData >= $maxTidHi)
			{
				$tid = strval($maxTidData+1);
			}
		else
			{
				$tid = strval($maxTidHi+1);
			}
		$wp_kv_taxonomy_term_data = array('tid'=>$tid,'vid'=>'1','name'=>$name,'description'=>'','format'=>'full_html','weight'=>'0');
		$wp_kv_taxonomy_term_hierarchy = array('tid'=>$tid,'parent'=>'0');
		$sql_conx->insertValues_kv($database.".".$wp_tbl_taxonomy_term_data,$wp_kv_taxonomy_term_data,True);
		$sql_conx->insertValues_kv($database.".".$wp_tbl_taxonomy_term_hierarchy,$wp_kv_taxonomy_term_hierarchy,True);
	}
// WORD PRESS FUNCTION - END  ===============================================================================================================

//
// Post Type Registration
//

// Main SQL Variables
// SQL Server host name and port number
$sqlHost= 'localhost';//'127.0.0.1';
$sqlPort= 3306;
// Common password for used across the SQL Databases
$sqlPasswd= '###########';//
// winmalee_fb is the pool database for all the Facebook posts
$sqlDatabase= '###########';//
// winmalee_fb field names
$sqlTblePaging = '###########';
$sqlTblePosts = '###########';
$sqlTbleFrom = '###########';
$sqlTbleFolder = '###########';
$sqlTbleComments = '###########';
$sqlTbleCommentsID = '###########';

// WORD PRESS DATABASE - START ===============================================================================================================
// Word Press Database names. This is redundant for the wordpress sight
$sqlUser= '###########';//
$wp_db_name = '###########';
$wp_winrfs_db = '###########';
$wp_winrfs_tble_prefix = "wp_";
// WORD PRESS DATABASE - END  ===============================================================================================================

//Get SQL connection and create table if not exist
$sql = new sql($sqlHost,$sqlPort,$sqlUser,$sqlPasswd);
$sql->connect($sqlDatabase);
$fb = new postFb();

$fb_time_t = "T";
$fb_time_suffix = "+";

$token = "/";
$img_max_size = 220;
$img_ext = array("jpg","gif","png","bmp","tif","iff");
$thisdir = getcwd();
$website = "http://www.winmaleerfs.com.au";
$int_image_folder = $thisdir.$token."wp/wp-content/uploads/facebook/posts";
$ext_image_folder = $website.$token."wp/wp-content/uploads/facebook/posts";
echo "int_image_folder = ".$int_image_folder."<br>";

// WORD PRESS TABLE NAMES - START ===============================================================================================================
//$wp_tbl_block_custom =$wp_winrfs_tble_prefix.'block_custom';
$wp_tbl_field_data_body = $wp_winrfs_tble_prefix.'field_data_body';
//$wp_tbl_field_data_field_category = $wp_winrfs_tble_prefix.'field_data_field_category';
$wp_tbl_field_data_field_divider1 = $wp_winrfs_tble_prefix.'field_data_field_divider1';
$wp_tbl_field_data_field_image = $wp_winrfs_tble_prefix.'field_data_field_image';
$wp_tbl_field_data_field_likes = $wp_winrfs_tble_prefix.'field_data_field_likes';
$wp_tbl_field_data_news_post_number = $wp_winrfs_tble_prefix.'field_data_field_news_post_number';
$wp_tbl_field_data_field_shares = $wp_winrfs_tble_prefix.'field_data_field_shares';
$wp_tbl_field_data_field_tags = $wp_winrfs_tble_prefix.'field_data_field_tags';
$wp_tbl_field_data_field_taxonomy = $wp_winrfs_tble_prefix.'field_data_field_taxonomy';
$wp_tbl_field_data_field_teaser_image = $wp_winrfs_tble_prefix.'field_data_field_teaser_image';
$wp_tbl_field_data_field_updates = $wp_winrfs_tble_prefix.'field_data_field_updates';

$wp_tbl_field_revision_field_body =$wp_winrfs_tble_prefix.'field_revision_body';
$wp_tbl_field_revision_field_divider1 = $wp_winrfs_tble_prefix.'field_revision_field_divider1';
$wp_tbl_field_revision_field_image =$wp_winrfs_tble_prefix.'field_revision_field_image';
$wp_tbl_field_revision_field_likes = $wp_winrfs_tble_prefix.'field_data_field_likes';
$wp_tbl_field_revision_field_news_post_number = $wp_winrfs_tble_prefix.'field_data_field_news_post_number';
$wp_tbl_field_revision_field_shares = $wp_winrfs_tble_prefix.'field_data_field_shares';
$wp_tbl_field_revision_field_tags = $wp_winrfs_tble_prefix.'field_revision_field_tags';//NEED TO IMPLEMENT
//$wp_tbl_field_data_field_taxonomy = $wp_winrfs_tble_prefix.'field_data_field_taxonomy';//Taxonomy mispelt
$wp_tbl_field_revision_field_taxonomy = $wp_winrfs_tble_prefix.'field_revision_field_taxonomy';//NEED TO IMPLEMENT
$wp_tbl_field_revision_field_teaser_image = $wp_winrfs_tble_prefix.'field_revision_field_teaser_image';
$wp_tbl_field_revision_field_updates = $wp_winrfs_tble_prefix.'field_revision_field_updates';

$wp_tbl_file_managed = $wp_winrfs_tble_prefix.'file_managed';
$wp_tbl_file_usage = $wp_winrfs_tble_prefix.'file_usage';

//$wp_tbl_field_revision_field_taxonomy = $wp_winrfs_tble_prefix.'field_revision_field_taxonomy';//NEED TO IMPLEMENT
//$wp_tbl_front_page =$wp_winrfs_tble_prefix.'front_page';//ON HOLD
$wp_tbl_history =$wp_winrfs_tble_prefix.'history';
$wp_tbl_node =$wp_winrfs_tble_prefix.'node';
$wp_tbl_node_comment_statistics =$wp_winrfs_tble_prefix.'node_comment_statistics';
$wp_tbl_node_revision =$wp_winrfs_tble_prefix.'node_revision';
//$wp_tbl_search_dataset = $wp_winrfs_tble_prefix.'search_dataset';
$wp_tbl_taxonomy_index = $wp_winrfs_tble_prefix.'taxonomy_index';//NEED TO IMPLEMENT
$wp_tbl_taxonomy_term_data = $wp_winrfs_tble_prefix.'taxonomy_term_data';
$wp_tbl_taxonomy_term_hierarchy = $wp_winrfs_tble_prefix.'taxonomy_term_hierarchy';
$wp_tbl_taxonomy_vocabulary = $wp_winrfs_tble_prefix.'taxonomy_vocabulary';
$wp_tbl_tracker_node =$wp_winrfs_tble_prefix.'tracker_node';
$wp_tbl_tracker_user =$wp_winrfs_tble_prefix.'tracker_user';
//$wp_tbl_search_index = $wp_winrfs_tble_prefix.'search_index';
//$wp_tbl_search_total = $wp_winrfs_tble_prefix.'search_total';
$wp_tbl_url_alias =$wp_winrfs_tble_prefix.'url_alias';

// WORD PRESS TABLE NAMES - END  ===============================================================================================================

// WORD PRESS SERVER DIRECTORIES - START ===============================================================================================================
# Bootstrap start
/*
define('DRUPAL_ROOT', $thisdir.'/drupal/');
$_SERVER['REMOTE_ADDR'] = "localhost"; // Necessary if running from command line
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
*/
# Bootstrap end
// WORD PRESS SERVER DIRECTORIES - END  ===============================================================================================================

// WORDPRESS SERVER DIRECTORIES - START ===============================================================================================================
/**
 * Creates the initial post types when 'init' action is fired.
 *
 * @since 2.9.0
 */
require('wp/wp-blog-header.php');
global $user_ID;
// WORDPRESS SERVER DIRECTORIES - END  ===============================================================================================================

$max_title_len = 8;
$max_summary_len = 48;

// Get single post data from Database ------------------------------------------------------------------------------------------------
$query = "SELECT * FROM ".$sqlDatabase.".".$sqlTblePosts." WHERE wp = 0 ORDER BY `posts_id` ASC LIMIT 50";
//$query = "SELECT * FROM ".$sqlDatabase.".".$sqlTblePosts." WHERE drupal = 0 ORDER BY `posts_id` ASC LIMIT 50";
echo "query: ". $query . "<br>";
$query_update_last = "UPDATE ".$sqlDatabase.".".$sqlTblePosts." SET last = 1 WHERE posts_id = ";
$query_update_wp = "UPDATE ".$sqlDatabase.".".$sqlTblePosts." SET wp = 1 WHERE posts_id = ";
$query_update_drupal = "UPDATE ".$sqlDatabase.".".$sqlTblePosts." SET drupal = 1 WHERE posts_id = ";
$results = $sql->getQuery($query);
$count = 0;
$css_color = array('#000000','#FF0000','#00FF00','#0000FF','#FFFF00','#00FFFF','#FF00FF','#C0C0C0','#FFFFFF');
echo "count: ". $count . "<br>";

$break = 0;
$break_count = 0;
$break_limit = 1;

$reg_exUrl = "/(http|https|ftp|ftps|)+(\:|)+([\/\/]|)+[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
$html_ahref_attrs = 'target="_blank"';

while( $row = $sql->getFirstRow($results) )
	{	
		if($break == 0)
			{
				echo "booo<br>";
				if($count > sizeof($css_color))
					{
						$count = 0;
						
					}
					
				// Get single post data from Database ------------------------------------------------------------------------------------------------
				// Assign Database field data to variables--------------------------------------------------------------------------------------------
				$post_id = $row["posts_id"];
				$post_fb_id = $row["post_FB_id"];
				$post_msge_txt = $row["message"];
				$post_msge_txt = convert_string_to_HTML_link($post_msge_txt, $reg_exUrl, $html_ahref_attrs);
				$post_story_txt = $row["story"];
				$post_story_txt = convert_string_to_HTML_link($post_story_txt, $reg_exUrl, $html_ahref_attrs);
				$postStory_split = explode(" ", $postStory);
				$postStory_size = sizeof($postStory_split);
				$post_content_msg = $row["message"];
				$post_content_msg = convert_string_to_HTML_link($post_content_msg, $reg_exUrl, $html_ahref_attrs);
				$post_fb_likesCount = $row["likesCount"];
				$post_fb_sharesCount = $row["sharesCount"];
				$post_fb_commentsCount = $row["commentsCount"];
				$postCreated = $row["created"];
				$postCreated = $fb->time_fb_convert($postCreated,$fb_time_t,$fb_time_suffix);
				$postUpdated = $row["updated"];
				$postUpdated = $fb->time_fb_convert($postUpdated,$fb_time_t,$fb_time_suffix);
				$post_int_img_folder = $int_image_folder . $token . $post_fb_id;
				$post_ext_img_folder = $ext_image_folder . $token . $post_fb_id;
				$post_img_descrip = $row["picture_descrip"];
				$post_img_descrip = convert_string_to_HTML_link($post_img_descrip, $reg_exUrl, $html_ahref_attrs);
				$post_img_caption = $row["picture_caption"];
				$post_img_caption = convert_string_to_HTML_link($post_img_caption, $reg_exUrl, $html_ahref_attrs);
				$post_img_name = $row["picture_name"];
				$post_img_name = convert_string_to_HTML_link($post_img_name, $reg_exUrl, $html_ahref_attrs);
				$post_img_link = $row["picture_link"];
				echo '<p style="color:'.$css_color[$count].'">';
				echo "post_int_img_folder: ". $post_int_img_folder . "<br>";
				echo "post_ext_img_folder: ". $post_ext_img_folder . "<br>";
				echo "post_id: ". $post_id . "<br>";
				echo "post_fb_id: ". $post_fb_id . "<br>";
				echo "post_msge_txt: ". $post_msge_txt . "<br>";
				echo "post_story_txt: ". $post_story_txt . "<br>";
				echo "postStory_split[1]: ". var_dump($postStory_split) . "<br>";
				echo "postStory_size: ". $postStory_size . "<br>";
				echo "post_content_msg = ".$post_content_msg."<br>";
				echo "post_fb_likesCount = ".$post_fb_likesCount."<br>";
				echo "post_fb_sharesCount = ".$post_fb_sharesCount."<br>";
				echo "post_fb_commentsCount = ".$post_fb_commentsCount."<br>";
				echo "postCreated = ".$postCreated."<br>";
				echo "postUpdated = ".$postUpdated."<br>";
				echo "post_img_folder: ". $post_img_folder . "<br>";
				echo "post_img_descrip: ". $post_img_descrip . "<br>";
				echo "post_img_caption: ". $post_img_caption . "<br>";
				echo "post_img_name: ". $post_img_name . "<br>";
				echo "post_img_link: ". $post_img_link . "<br>";
				var_dump($post_fb_commentsCount);
				echo "<br>";
				
				$count+=1;

				// WORDPRESS UPDATE SENT TO CMS - START ===============================================================================================================
				// Execute and update wp from 0 to 1
				$query_update_sent_run = $query_update_wp.$post_id;
				$sql->getQuery($query_update_sent_run);
				// WORDPRESS UPDATE SENT TO CMS - END  ===============================================================================================================
				
				// WORD PRESS UPDATE SENT TO CMS - START ===============================================================================================================
				// Execute and update wp from 0 to 1
				//$query_update_sent_run = $query_update_drupal.$post_id;
				//$sql->getQuery($query_update_sent_run);
				// WORD PRESS UPDATE SENT TO CMS - END   ===============================================================================================================

				// BUILD POSTS HTML CONTENT - START +===============================================================================================================
				// Create Post HTML Title Header------------------------------------------------------------------------------------------------------
				//THIS IS NOT REQUIRED AS EACH POST IS CREATED AS A DRUPAL ARTICLE THAT HAS A TITLE
				$title = $fb->get_post_title($post_msge_txt,$post_story_txt,$post_title_default,$max_title_len);
				echo "title: ". $title . "<br>";
				$summary = $fb->get_post_title($post_msge_txt,$post_story_txt,"",$max_summary_len);
				// Create Post HTML Title Header------------------------------------------------------------------------------------------------------				
				
				// Create Post HTML Story ------------------------------------------------------------------------------------------------------------
				//echo "HTML Story: <br>";
				$HTML_story = "";
				if( ($post_story_txt != Null) or ($post_story_txt != "") )
					{	
						$HTML_story = $fb->build_simple_html_tag(Null,array("post-news-story"),$post_story_txt,'<p ','</p>');
					}
				echo "HTML_story: ". $HTML_story . "<br>";
				//echo $HTML_story . "<br>";
				// Create Post HTML Story ------------------------------------------------------------------------------------------------------------
				
				// Get Content HTML and Encapsulate Image HTML if exists -----------------------------------------------------------------------------
				$HTML_content_msg = "";
				if( ($post_content_msg != Null) or ($post_content_msg != "") )
					{
						$HTML_content_msg = $fb->build_simple_html_tag(Null,Null,$post_content_msg,'<p','</p>');
					}
				echo "HTML_content_msg".$HTML_content_msg."<br>";	
				
				// Get Description HTML and Encapsulate it into HTML if exists -----------------------------------------------------------------------------
				$HTML_description = "";
				if( ($HTML_description != Null) or ($HTML_description != "") )
					{
						$HTML_content_msg = $fb->build_post_description(Null,"description",$post_img_descrip);
					}
				echo "HTML_description".$HTML_description."<br>";
				
				// Get Image HTML --------------------------------------------------------------------------------------------------------------------
				// If no link is record in Facebook database, creating image will be ignored
				// List image files
				$HTML_img = "";
				$list_files = $fb->list_files_from_folder($post_int_img_folder);
				// Remove "." and ".." from list_files
				$list_img_files = $fb->list_img_from_folder($list_files,$img_ext);
				$img_size = array(100,100);
				$caption = " ";
				if( ($list_img_files[0] != Null) or (strlen($list_img_files[0]) != 0) )//NEED TO ADD REGEX INSTEAD OF (strlen($list_img_files[0]) != 0)
					{
						//$list_post_img_descrip = array($post_img_descrip);
						$list_post_img_caption = array($post_img_caption);
						$list_post_img_name = array($post_img_name);
						$list_post_img_link = array($post_img_link);
						$list_post_img_alt = array($list_img_files[0]);
						$size = getimagesize($post_int_img_folder.$token.$list_img_files[0]);
						$dimension = array($size[0],$size[1]);
						if( $size[0] > $img_max_size || $size[1] > $img_max_size )
							{
								$dimension = $fb->list_files_from_folder($post_int_img_folder);
							}
						echo "dimension: ".$dimension[0].":".$dimension[1]."<br>";
						$HTML_img = '<figure id="'.'post-news-fb-'.$post_id.'-img-1'.'" align="alignright" width="'.$dimension[0].'">';
						if($post_img_link != Null)
							{
								$HTML_img = $HTML_img.'<a href="'.$post_img_link.'" target="_blank">';
							}
						else
							{
								$HTML_img = $HTML_img.'<a href="'.$post_ext_img_folder.$token.$list_img_files[0].'" target="_blank">';
							}
							
						if( ($post_img_caption != Null) or (!empty($post_img_caption)) )
							{
								$caption = $post_img_caption;
							}
						else
							{
								$caption = $post_img_name;
							}

						$HTML_img = $HTML_img.'<img id="post-news-img-'.$post_id.'" class="size-full post-news-img" src="'.$post_ext_img_folder.$token.$list_img_files[0].'" alt="'.$post_img_name.'" width="'.$dimension[0].'" height="'.$dimension[1].'">';
						$HTML_img = $HTML_img.'</a><figcaption>'.$caption.'</figcaption></figure>';	
					}
				echo "HTML_img: " . $HTML_img . "<br>";
				// Get Image HTML --------------------------------------------------------------------------------------------------------------------
				
				// Get Content HTML and Encapsulate Image HTML if exists -----------------------------------------------------------------------------
				//echo "HTML Content: <br>";
				$HTML_msg = "";
				if( ($post_content_msg != Null) or ($post_content_msg != "") )
					{
						$HTML_msg = $fb->build_simple_html_tag(Null,Null,$post_content_msg,'<p','</p>');
					}
				echo "HTML_content_msg".$HTML_msg."<br>";
				$excerpt = $fb->get_post_title($post_msge_txt,$post_story_txt,"Read more....",32);
				$HTML_post_content = $HTML_img.$HTML_description.$HTML_story.$HTML_msg;
				$HTML_post_content = $fb->build_simple_html_tag(Null,array("post-news-content"),$HTML_post_content,'<div ','</div><!-- END content -->');
				$HTML_post_content = $HTML_post_content.$fb->build_simple_html_tag(Null,array("clearfix"),"" ,'<div ','</div>'); 
				echo "HTML_post_content ".$HTML_post_content."<br>";
				// Get Content HTML and Encapsulate Image HTML if exists -----------------------------------------------------------------------------


				// Get Comments HTML --------------------------------------------------------------------------------------------------------------------
				// If post comment count is greater than 0 or not null
				$HTML_comments  = "";
				$update = False;
				if($post_fb_commentsCount != 0) 
					{
						$fb_time_t = "T";
						$fb_time_suffix = "+";
						$query_comments = "SELECT * FROM ".$sqlDatabase.".".$sqlTbleComments." WHERE `postId` = ".$post_id;
						$results_comments = $sql->getQuery($query_comments);
						echo "query_comments = ".$query_comments."<br>";
						$comment_count = 1;

						while( $row_comments = $sql->getFirstRow($results_comments) )
							{
								$HTML_comments_msg = '';
										
								$comments_created = $row_comments["created"];
								$comments_msg = $row_comments["message"];
										
								// Get Comments Created---------------------------------------------------
								$comments_created = $fb->time_fb_convert($comments_created,$fb_time_t,$fb_time_suffix);
								$HTML_comments_p_time = $fb->build_simple_html_tag(Null,Null,$comments_created,'<br><span ','</span>');
								// Get Comments Created---------------------------------------------------
										
								// Get Comments Message---------------------------------------------------
								$HTML_comments_msg = $HTML_comments_msg.$fb->build_simple_html_tag(Null,Null,$comments_msg.$HTML_comments_p_time,'<p ','</p>');;
								$HTML_comments_msg = $fb->build_simple_html_tag(Null,array("post-news-update-content"),$HTML_comments_msg,'<div ','</div>'); 
								echo "HTML_comments_msg = ".$HTML_comments_msg."<br>";
								// Get Comments Message---------------------------------------------------

								//$HTML_comments  = $HTML_comments .$fb->build_post_comment($HTML_comments_p_title,$HTML_comments_p_time,$HTML_comments_msg,$wp_comments_id,$wp_comments_div_class);
								$HTML_comments  = $HTML_comments .$HTML_comments_msg;
								echo "HTML_all_comments = ".$HTML_comments ."<br>";
										
								$comment_count += 1;	
								$update = True;
							}
							
						if($update === True)
							{
								// Get Comments Title-----------------------------------------------------
								$HTML_comments_p_title = $fb->build_simple_html_tag(Null,array("post-news-update-title"),'Update: ','<div ','</div><!-- END update title -->');
								// Get Comments Title-----------------------------------------------------					
								
								$HTML_comments  = $HTML_comments_p_title.$HTML_comments ;
								$HTML_comments  = $fb->build_simple_html_tag(Null,array("post-news-update"),$HTML_comments ,'<div ','</div><!-- END update -->'); 
								$HTML_comments  = $HTML_comments.$fb->build_simple_html_tag(Null,array("clearfix"),"" ,'<div ','</div>'); 
								echo "HTML_all_comments = ".$HTML_comments ."<br>";
							}
					}
				// Get Comments HTML --------------------------------------------------------------------------------------------------------------------
				
				// Get Footer HTML --------------------------------------------------------------------------------------------------------------------
				$HTML_likes = "";
				$HTML_shares = "";
				$HTML_social_media = "";
				if($post_fb_likesCount != 0)
					{
						$HTML_likes_Label = $fb->build_simple_html_tag(Null,array("post-news-likes-label"),"Likes: " ,'<div ','</div><!-- END likes label -->');
						$HTML_likes_count = $fb->build_simple_html_tag(Null,array("triangle-border", "left", "post-news-likes-count"),$post_fb_likesCount ,'<div ','</div><!-- END likes count -->');
						$HTML_likes = $fb->build_simple_html_tag(Null,array("post-news-likes"),$HTML_likes_Label.$HTML_likes_count ,'<div ','</div><!-- END likes -->');
						echo "post_fb_likesCount = ".$post_fb_likesCount."<br>";
					}
				
				if($post_fb_sharesCount != 0)
					{
						$HTML_shares_Label = $fb->build_simple_html_tag(Null,array("post-news-shares-label"),"Shares: " ,'<div ','</div><!-- END shares label -->');
						$HTML_shares_count = $fb->build_simple_html_tag(Null,array("triangle-border", "left", "post-news-shares-count"),$post_fb_sharesCount ,'<div ','</div><!-- END shares count -->');
						$HTML_shares = $fb->build_simple_html_tag(Null,array("post-news-shares"),$HTML_shares_Label.$HTML_shares_count ,'<div ','</div><!-- END shares -->');
						echo "post_fb_sharesCount = ".$post_fb_sharesCount."<br>";
					}
				if( ($post_fb_sharesCount != 0) OR ($post_fb_likesCount != 0) )
					{
						$HTML_social_media = $fb->build_simple_html_tag(Null,array("post-social-media"),$HTML_likes.$HTML_shares ,'<div ','</div><!-- END social media -->');
					}
				$HTML_post_content = $HTML_post_content.$HTML_comments.$HTML_social_media;
				$HTML_post_content = $fb->build_simple_html_tag("post-news-fb-".$post_id,array("post-news"),$HTML_post_content,'<div ','</div><!-- END post -->');
				echo "HTML_post_content = ".$HTML_post_content."<br>";
				echo "</p><br><br><br>";
				// BUILD POSTS HTML CONTENT - END  ================================================================================================================

				// ADD WORD PRESS POST - START ===================================================================================================================
				$new_post = array(
					'post_title' => $title,//'News Post FB '.$post_id,
					'post_content' => $HTML_post_content,
					'post_status' => 'publish',
					'post_date' => $postCreated,
					'post_modified' => $postCreated,
					'post_author' => $user_ID,
					'post_type' => 'post',
					'post_category' => array(3),//3 = News
					'post_excerpt' => $excerpt
				);
				$post_id = wp_insert_post($new_post);				
				// Limits the amount of posts transferred
				// ADD WORD PRESS POST - END  ===================================================================================================================

				
				// RE-INITIALISE DB VARIABLES - START =============================================================================================================
				$post_id = $post_fb_id = $post_msge_txt = $post_story_txt = $post_content_msg = 
					$post_fb_likesCount = $post_fb_commentsCount = $post_fb_commentsCount = $postCreated = 
					$postUpdated = $post_int_img_folder = $post_ext_img_folder = $post_img_descrip = $post_img_caption = 
					$post_img_name = $post_img_link = $title = Null;
				$list_post_img_tag = array();
				$list_files = array();
				$list_img_files = array();
				// RE-INITIALISE DB VARIABLES - END  =============================================================================================================
				/**/
				
				// Limits the amount of posts transferred
				$break_count += 1;
				if($break_count == $break_limit)
					{
						$break  = 1;
					}
			}
	}
echo "END<BR>";

?>