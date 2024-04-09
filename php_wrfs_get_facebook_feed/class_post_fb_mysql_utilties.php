<?php include("class_string_utilities.php");?>
<?php
/*This class is used to create retreive Facebook post 
and save the data into MySQL database*/

class postFbToMySQL
{
	function __construct()
		{	
		
			//$strgUtil = new stringUtil();
			//echo $strgUtil."<br>";
		}
		
	function buildQuery($colValArray,$fkArray,$table)
		{
		$query = "CREATE TABLE IF NOT EXISTS ".$table."(";
		//Get Value and data types
		for ($i = 0; $i < sizeof($colValArray); $i++) 
			{
				$query .= $colValArray[$i][0]." ".$colValArray[$i][1].", ";
			}
		if($fkArray != Null)
			{
				//Add Foriegn Keys
				$query_ConName = "CONSTRAINT ";
				$query_ConFK = " FOREIGN KEY (";
				$query_ConRef = ") REFERENCES ";
				$query_ConEnd = ") ON DELETE SET NULL ON UPDATE CASCADE,";
				foreach($fkArray as $p)
					{
						$query_ConName .= $p[0];//Name
						//echo "query_ConName: ". $query_ConName . "<br>";
						$query_ConFK .= $p[1];
						//echo "query_ConFK : ". $query_ConFK  . "<br>";
						$query_ConRef .= $p[3]."(".$p[2];
						//echo "query_ConRef: ". $query_ConRef . "<br>";
						$query .= $query_ConName.$query_ConFK.$query_ConRef.$query_ConEnd;
						//echo "query: ". $query . "<br>";
						//Reset vars
						$query_ConName = "CONSTRAINT ";
						$query_ConFK = " FOREIGN KEY (";
						$query_ConRef = ") REFERENCES ";
						$query_ConEnd = ") ON DELETE SET NULL ON UPDATE CASCADE,";/**/
					}
			}
		//echo "Trim::query: ". $query . "<br>";
		$query = rtrim($query,", ");
		//echo "Trim::query: ". $query . "<br>";
		$query .= ")";/**/
		//echo "Cap::query: ". $query . "<br>";
		return $query;
	}
	
	//Return Multi Diensional Array
	function getPagePostContent($jsonObj,$summaryArray)
		{
			$count = 0;
			$array;
			foreach ($jsonObj->data as $obj_data)
				{
					//echo "Start Gettng POST<br>";
					$getPosts_fromName = $obj_data->from->name;
					//if($getPosts_fromName == Null){$getPosts_fromName = '';}
					//echo "1.getPosts_fromName: " . $getPosts_fromName . "<br>";//FK
					$getPosts_fromFB_id = $obj_data->from->id;
					//if($getPosts_fromFB_id == Null){$getPosts_fromFB_id = '';}
					//echo "2.getPosts_fromFB_id: " . $getPosts_fromFB_id . "<br>";//FK
					$getPosts_story = $obj_data->story;
					$getPosts_story = str_replace('"','\"',$getPosts_story);
					//if($getPosts_fromFB_id == Null){$getPosts_fromFB_id = '';}
					//echo "3.getPosts_story: " . $getPosts_story . "<br>";
					$getPosts_postFB_id = $obj_data->id;
					//echo "4.getPosts_postFB_id: " . $getPosts_postFB_id . "<br>";
					//$getPosts_folder_id = $storeFBdata_folder;
					//echo "getPosts_folder_id: " . $getPosts_folder_id . "<br>";//FK
					//Need to add folder later
					$getPosts_message = $obj_data->message;
					$getPosts_message = str_replace('"','\"',$getPosts_message);
					$getPosts_message = str_replace("'","\'",$getPosts_message);
					//echo "6.getPosts_message: " . $getPosts_message . "<br>";
					$getPosts_picture = $obj_data->picture;
					//echo "7.getPosts_picture: " . $getPosts_picture . "<br>";
					$getPosts_pictureName = $obj_data->name;
					$getPosts_pictureName = str_replace('"','\"',$getPosts_pictureName);
					$getPosts_pictureName = str_replace("'","\'",$getPosts_pictureName);
					//echo "8.getPosts_pictureName: " . $getPosts_pictureName . "<br>";
					$getPosts_pictureLink = $obj_data->link;
					//echo "9.getPosts_pictureLink: " . $getPosts_pictureLink . "<br>";
					$getPosts_pictureCaption = $obj_data->caption;
					$getPosts_pictureCaption = str_replace("'","\'",$getPosts_pictureCaption);
					$getPosts_pictureCaption = str_replace('"','\"',$getPosts_pictureCaption);
					//echo "10.getPosts_pictureCaption: " . $getPosts_pictureCaption . "<br>";
					$getPosts_PictureDescription = str_replace('"','\"',$getPosts_PictureDescription);
					$getPosts_PictureDescription = str_replace("'","\'",$getPosts_PictureDescription);
					$getPosts_PictureDescription = $obj_data->description;
					//echo "11.getPosts_PictureDescription: " . $getPosts_PictureDescription . "<br>";
					$getPosts_createdTime = $obj_data->created_time;
					//echo "12.getPosts_createdTime: " . $getPosts_createdTime . "<br>";
					$getPosts_updatedTime = $obj_data->updated_time;
					//echo "13.getPosts_updatedTime: " . $getPosts_updatedTime . "<br>";
					$getPosts_Likes = $obj_data->likes;
					$getPosts_LikesCount = 0;
					if(!empty($getPosts_Likes))
						{
							$summary_link = $summaryArray[0] . $getPosts_postFB_id . '/' . $summaryArray[3] . $summaryArray[1];
							//echo "summary_link: " . $summary_link . "<br>";
							$json_summary = file_get_contents($summary_link);
							$obj_summary = json_decode($json_summary);
							$getPosts_LikesCount = $obj_summary->summary->total_count;
						}
					//echo "14.getLikesCount: " . $getLikesCount . "<br>";
					//$getPosts_Shares = $obj_data["shares"]["count"];
					$getPosts_Shares = $obj_data->shares->count;
					if(empty($getPosts_Shares))
						{
							$getPosts_Shares  = 0;
						}
					//echo "getPosts_Shares: " . $getPosts_Shares . "<br>";
					$getPosts_Comments = $obj_data->comments;
					$getPosts_CommentsCount = 0;
					if(!empty($getPosts_Comments ))
						{
							$summary_link = $summaryArray[0] . $getPosts_postFB_id . '/' . $summaryArray[2] . $summaryArray[1];
							//echo "summary_link: " . $summary_link . "<br>";
							$json_summary = file_get_contents($summary_link);
							$obj_summary = json_decode($json_summary);
							$getPosts_CommentsCount = $obj_summary->summary->total_count;
						}
					//echo "getPosts_CommentsCount: " . $getPosts_CommentsCount . "<br>";
					//echo "getPosts_LikesCount: " . $getPosts_LikesCount . "<br>";
					$linksArray = array("",//0.id
										$getPosts_fromName,//1.from Name (ID)
										$getPosts_fromFB_id,//2.from FB id (ID)
										$getPosts_story,//3.story 
										$getPosts_postFB_id,//4.post FB id
										"",//5.folder
										$getPosts_message,//6.Message
										$getPosts_picture,//7.picture
										$getPosts_pictureName,//8.picture name
										$getPosts_pictureLink,//9.picture link
										$getPosts_pictureCaption,//10.picture caption
										$getPosts_PictureDescription,//11.picture description
										$getPosts_createdTime,//12.created
										$getPosts_updatedTime,//13.updated
										$getPosts_LikesCount,//14.likes count
										$getPosts_Shares,//15.shares count
										$getPosts_CommentsCount,//16.comments count
										"",//17.paging id (ID)
										"",//18.last
										0 //19.fb node id count
										);
					$array[$count] = $linksArray;
					/*foreach ($array[$count] as $p)
						{
							echo "$p = ".$p."<br>";;
						}*/
					$count += 1;
					//if($count > 2){break;}
					//break;
					//echo "Start Gettng POST<br><br><br><br>";
				}
			return $array;
		}		

	/* This function retreives the following paging data:
	* 1. Current page link ($currentPage)
	* 2. Next page link ($getNextPage)
	* 3. Previous page link ($getPreviousPage)
	* 4. Creation time of last post of the Page being retrieved ($getPosts_createdTime)
	* 5. Integer to determine if post have been retrieved from the Page ($gotPost)
	* This function will return an array	
	*/
	
	function get_single_fb_paging_links($link)
		{	
			//echo "postFbToMySQL :: get_single_fb_paging_links<br>";
			$getLastPostsCreatedTime = "";
			$currentPage = $link;
			$gotPosts = 0;
			// Get JSON of the Facebook post page
			$json_object = file_get_contents($link);
			$obj = json_decode($json_object);
			// Get current pages last post created time
			foreach ($obj->data as $obj_data)
				{
					$getLastPostsCreatedTime = $obj_data->created_time;
				}
			$cd = new DateTime($getLastPostsCreatedTime);
			// Get Next and Previous Paging links
			$getNextPage = $obj->paging->next;
			$getPreviousPage = $obj->paging->previous;
			return array("",$currentPage,$getNextPage,$getPreviousPage,$getLastPostsCreatedTime,$gotPosts);
		}

	/* This function cycles through the FB json page links to get:
	* 1. Next Paging Link
	* 2. Previous Paging Link
	* 3. The creation time ($latestPageTime) of the last Post on a given page of Post. 
	* 		This will be used a check to ensure that Post previously retrieved are not retrieved again.
	* The parameters of this function are:
	* 1. $startPage = the main constant FB graph page that contains the current Posts
	* 2. $lastPage = last page that has in the Paging Table of the MySQL database
	* 3. $lastPageTime = last page that has in the Paging Table of the MySQL database Posts creation time
	* This function will return an array
	*/
	
	function get_all_fb_paging_links($startPage,$lastPage,$lastPageTime)
		{	
			//Init var
			$pageArray = array();
			
			//Get the time of the start page (master)
			$getPagingData = $this->get_single_fb_paging_links($startPage);//retrieves current page, next page, previous page, last posts created time and adds a false setting for the field lastPosts
			$cd = strtotime($getPagingData[4]);//strtotime($lastPageTime);
			$ld = strtotime($lastPageTime); 
			echo "getPagingData[0] = " . $getPagingData[0]."<br>";
			echo "getPagingData[1] = " . $getPagingData[1]."<br>";
			echo "getPagingData[2] = " . $getPagingData[2]."<br>";
			echo "getPagingData[3] = " . $getPagingData[3]."<br>";
			echo "getPagingData[4] = " . $getPagingData[4]."<br>";
			
			//Init $getCurrentPage var. This grabs the next page from the start page
			$getCurrentPage = $getPagingData[2];
			echo "getCurrentPage = " . $getCurrentPage."<br>";
			
			// Loop through and retrieve all next FB pages
			// as long as they do not match latestPage or is Null
			while($getCurrentPage != Null)
				{
					
					
					// Compare the current paging data against the last paging data entered into SQL database
					// If a matach break the loop
					echo "Comparing the Current Time of the paging link to the last paging entry:<br>";
					echo "Current page = ".$getCurrentPage."<br>";
					echo "Last page = ".$lastPage."<br>";
					echo "Current time = ".$cd."  /  ".$getPagingData[4]."<br>";
					echo "Last time = ".$ld."  /  ".$lastPageTime."<br>";
					if( ($getCurrentPage == $lastPage) or ($ld >= $cd) )
						{
							//echo "ld: ".(string)$ld."<br>";
							echo "Stopping Loop!!!!<br>";
							break;
						}
					else
						{
							// Else Gets single page feeds paging data as array
							echo "Current Paging Link will be added to Database!!!!<br>";
							$getPagingData = $this->get_single_fb_paging_links($getCurrentPage);//retrieves current page, next page, previous page, last posts created time and adds a false setting for the field lastPosts
							
							
							//Get the paging data and current time of previous page
							$getPagingData = $this->get_single_fb_paging_links($getCurrentPage);
							$cd = strtotime($getPagingData[4]);
							
							// Add to output array
							array_push($pageArray,$getPagingData);
							
							// Get the previous page and make Current page for the next loop
							$getCurrentPage = $getPagingData[2];//Next Paging Link
						}
					echo "<br>";
				}
			return $pageArray;
		}

	}
?>