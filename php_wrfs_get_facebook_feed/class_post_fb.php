<?php
/*This class is used to create Facebook post based on the
MySQL winmalee facebook database field information*/
class postFb
{
	function __construct()
		{	
		}
		

	//This function returns the Text for the Facebook (FB) Post title
	//It attempts to use a pre-defined ($max_len) number of words from 
	//firstly the FB's title, then story else default
	function get_post_title($msge,$story,$default,$max_len)
		{
			//$returnTitle = "News Upadate";
			$returnTitle = $default;
			$split = explode(" ", $msge);
			if($msge == Null)
				{
					//echo "msge = NULL <br>";
					if($story == Null)
						{
							//echo "story = NULL <br>";
							//continue;
						}
					else
						{
							$split = explode(" ", $story);
							if(sizeof($split) > $max_len)
								{
									$returnTitle = "";
									for ($i = 0; $i < $max_len; $i++) 
										{
											$returnTitle = $returnTitle.$split[$i] . " ";
										}
									$returnTitle = $returnTitle."...";
								}
							else
								{
									$returnTitle = $story;
								}
						}
				}
			else
				{
					$split = explode(" ", $msge);
					if(sizeof($split) > $max_len)
						{
							$returnTitle = "";
							for ($i = 0; $i < $max_len; $i++) 
								{
									$returnTitle = $returnTitle.$split[$i] . " ";
								}
							$returnTitle = $returnTitle."...";
						}
					else
						{
							$returnTitle = $msge;
						}
					//echo "msge = NOT NULL <br>";
				}
			return $returnTitle;
		}
	
	//This function returns the HTML block for the FB Post's Title
	//$title is the text used for the title, generally retrieved from get_post_title()
	function build_post_title($div1_id,$div1_classArray,$P1_classArray,$P2_classArray,$title,$date)
		{
			//echo "build_post_title :: ". $div1_id."<br>";
			$open = '<p';
			$close = '</p>';
			$html = $this->build_simple_html_tag(Null,$P1_classArray,$title,$open,$close);
			$html = $html.$this->build_simple_html_tag(Null,$P2_classArray,'Posted '.$date,$open,$close);
			$open = '<div';
			$close = '</div>';
			$html = $this->build_simple_html_tag($div1_id,$div1_classArray,$html,$open,$close);
			//$html = $this->build_simple_html_tag($div1_id,$div1_classArray,$html,$open,$close);
			$html = '<section name="title">'.$html.'</section><!-- END post-title -->';
			return $html;
		}
	
	//This function returns the HTML block for the FB Post's Description
	//$description is the text used for the title, generally retrieved from get_post_title()
	function build_post_description($div1_id,$div1_classArray,$description)
		{
			$open = '<div';
			$close = '</div>';
			$html = $this->build_simple_html_tag($div1_id,$div1_classArray,$description,$open,$close);
			$html = $html.'<!-- END post-desciption -->';
			return $html;
		}		
		
	//This function returns the HTML block for the FB Post's Story
	//The funtion returns nothing ($returnText = "") if there is no content
	//the MySQL Story Field
	function build_post_story($div1_id,$div1_classArray,$div2_classArray,$story)
		{
			//echo "build_post_story :: story = ".$story."<br>";
			$html = '';
			if( ($story != Null) or ($story != "") )
				{
					$open = '<p';
					$close = '</p>';
					$html = $this->build_simple_html_tag(Null,$div2_classArray,$story,$open,$close);
					$open = '<div';
					$close = '</div>';
					$html = $this->build_simple_html_tag($div1_id,$div1_classArray,$html,$open,$close);
					$html = '<section name="story">'.$html.'</section><!-- END post-story -->';
				}
			
			return $html;
		}
		
	//This function returns an array of file names with extension in any given folder
	function list_files_from_folder($folder)
		{
			$returnArray = array();
			//echo "list_files_from_folder :: ". "1.";
			if ($handle = opendir($folder)) 
				{
					//echo "list_files_from_folder :: ". "Directory handle: $handle\n";
					//echo "list_files_from_folder :: ". "Entries:\n";
					// This is the correct way to loop over the directory.
					while (false !== ($entry = readdir($handle))) 
						{
							array_push($returnArray,$entry);
							//echo "list_files_from_folder :: ". "$entry<br>";
						}
					//var_dump($returnArray);
					//echo "<br>";
					//echo "<br>";
					closedir($handle);
				}
			return $returnArray;
		}

	//This function removes unwanted charactes in the standard timestamp retreived from FB post capture
	//It returns a string
	function time_fb_convert($timestamp,$t,$suffix)
		{
			$timestamp = str_replace($t," ",$timestamp);
			//$timestamp = str_replace($suffix,"",$timestamp);
			$timestamp = strstr($timestamp, $suffix, true);
			return $timestamp;
		}
	//This function works in conjunction with function list_files_from_folder(folder)
	function list_img_from_folder($arry,$ext)
		{
			$returnArray = array();
			foreach ($arry as $file) 
				{
					$fileExt = pathinfo($file, PATHINFO_EXTENSION);
					//echo "list_img_from_folder :: ". $fileExt . "<br>";
					foreach ($ext as $e)
						{
							if($fileExt == $e)
								{
									array_push($returnArray,$file);
								}
						}
				}
			return $returnArray;
		}
	
	//This function recalculates a value by multiplying a by a pre-defined fraction
	function resize_img($size,$fraction)
		{
			$newSize = $size * $fraction;
			return $newSize;
		}
	
	//This function recalculates a value by multiplying a by a pre-defined fraction
	function resize_img_size($width,$height,$max)
		{
			$fraction = 0;
			if($width > $height)
				{
					$fraction = $max / $width;
					//echo "(int)width: " . (int)$width . "<br>";
					//echo "fraction(width): " . $fraction . "<br>";
				}
			else
				{
					$fraction = $max / $height;
					//echo "(int)height: " . (int)$height . "<br>";
					//echo "fraction(height): " . $fraction . "<br>";
				}
			$newHeight = $this->resize_img($height,$fraction);
			$newWidth = $this->resize_img($width,$fraction);
			$returnArray = array($newWidth,$newHeight);
			return $returnArray;
		}
	 
	// This function has:
	// 		1. <img> tag
	// This function injects:
	// 		1. <div> Class
	// 		2. <img> Source
	// 		3. <img> Alternate Text
	// 		4. <img> Width and Hieght
	// This function calls the resize_img_size(), which recalculates the <img> width and hieght attributes
	// Returns as a String
	function build_individ_post_image($id,$classArray, $img_src,$alt,$max)
		{
			$html = '';
			list($width, $height, $type, $attr) = getimagesize($img_src);
			//echo "build_individ_post_image :: "." img_src: ".$img_src."<br>";
			//echo "build_individ_post_image :: "." width: ".$width."<br>";
			//echo "build_individ_post_image :: "." height: ".$height."<br>";
			//echo "build_individ_post_image :: "." type: ".$type."<br>";
			//Test and resize image if greater than max dimension allowed
			$img_size = $this->resize_img_size($width,$height,$max);
			//echo "build_individ_post_image :: "."img_size: ";
			$html = '<img';
			if( ($classArray != Null) or (sizeof($classArray) != 0) )
				{
					$html = $html.$this->build_class_string($classArray);
				}
			$html = $html.' src="'.(string)$img_src.'" alt="'.(string)$alt.'" width="'.(string)$img_size[0].'" height="'.(string)$img_size[1].'">';
			//echo "build_individ_post_image :: "."html: ".$html."<br>";
			return $html;
		}
		
	// This function has:
	// 		1. <a> tag
	//		2. <img> tag
	//		3. <span> (caption) tag
	// This function takes a predefined HTML image tag and span (caption) tag, encapsulates it within a link <a> tag
	// Returns as a String
	// Sample: <a href="http://www.google.com"><img class="post-image" src="images/fire001.jpg" alt="Fire" height="145" width="220"><span>caption</span></a>
	function build_individ_post_link($link,$caption,$HTML_img)
			{
				$returnTxt = '<a href="'.$link.'">'.$HTML_img;//.'<span>'.$caption.'</span></a>';
				// Test if caption is not Null or has no String characters
				if( ($caption != Null) or (strlen($caption) != 0) )
					{
						$returnTxt = $returnTxt.'<span>'.$caption.'</span>';
					}
				$returnTxt = $returnTxt.'</a>';
				return $returnTxt;
			}

	// This function has:
	// 		1. 
	//		2. 
	//		3. 
	// This function 
	// Returns as a Array		
	function list_individ_images($img_arry,$div1_classArray,$img_src_folder,$alt_arry,$max,$link_arry,$caption_arry,$token)
		{
			//echo "sizeof($img_arry): ". sizeof($img_arry) . "<br>";
			$returnArry = array();
			$count = 0;
			if(sizeof($img_arry) != 0)
				{
					//echo "list_individ_images() :: ". "boooooo<br>";
					foreach ($img_arry as $file)
						{
							//echo "list_individ_images() :: ". "file: " . $file . "<br>";
							$img_src = $img_src_folder.$token.$file;
							//echo "list_individ_images() :: ". "img_src: " . $img_src . "<br>";
							$html = $this->build_individ_post_image(Null,$div1_classArray, $img_src,$alt_arry[$count],$max);
							//echo "list_individ_images() :: ". "returnText: " . $returnText . "<br>";
							// Encapsulate <img> tag within a <a> tag
							if($link_arry[$count] != Null)
								{
									$html = $this->build_individ_post_link($link_arry[$count],$caption_arry[$count],$html);
								}
							//echo "list_individ_images :: html = ".$html."<br>";
							array_push($returnArry,$html);
							$count = $count + 1;
						}
				}
			//echo "list_individ_images() :: ". "returnArry: " . $returnArry[0] . "<br>";
			return $returnArry;
		}

	// This function has:
	// 		1. 
	//		2. 
	//		3. 
	// This function 
	// Returns as a Array
	// <div class="float-right post-image-content"><a href="http://www.google.com">
	// <img class="post-image" src="images/fire001.jpg" alt="Fire" height="145" width="220"><span>caption</span></a>
	// <p class="post-title-4 post-image-txt">Description text content</p>
	// </div>
	function build_post_image($HTML_arry,$div1_classArray,$div2_classArray,$descrip_arry)
		{
			$returnArry = array();
			$count = 1;
			if(sizeof($HTML_arry) != 0)
				{
					foreach ($HTML_arry as $file)
						{
							// Add description 
							if( (sizeof($descrip_arry) != 0) and (sizeof($descrip_arry) == sizeof($HTML_arry)) )
								{
									//$html = '<div class="'.$div1_class.'">';
									$open = '<p';
									$close = '</p>';
									$html = $this->build_simple_html_tag(Null,$div2_classArray,$descrip_arry[$count],$open,$close);
									// Encapsulate wiht <div>
									$open = '<div';
									$close = '</div>';
									$html = $this->build_simple_html_tag(Null,$div1_classArray,$file,$open,$close);
									array_push($returnArry,$html);
									$count = $count + 1;
								}
							else
								{
									$open = '<div';
									$close = '</div>';
									$html = $this->build_simple_html_tag(Null,$div1_classArray,$file,$open,$close);
									array_push($returnArry,$html);
									$count = $count + 1;
								}
						}
				}
			return $returnArry;
		}
		
	// This function has:
	// 		1. 
	//		2. 
	//		3. 
	// This function 
	// Returns as a String
	function build_post_content($HTML_story,$HTML_img_arry,$HTML_comments,$HTML_msg,$id,$classArray)
		{
			$open = '<div';
			$close = '</div>';
			$html = '';
			/*
			echo "build_post_content :: html = ".$html."<br>";
			echo "build_post_content :: HTML_story = ".$HTML_story."<br>";
			echo "build_post_content :: HTML_comments = ".$HTML_comments."<br>";
			echo "build_post_content :: id = ".$id."<br>";
			echo "build_post_content :: classArray = ".$classArray."<br>";
			echo "build_post_content :: HTML_img_arry[0] = ".$HTML_img_arry[0]."<br>";
			echo "build_post_content :: HTML_msg = ".$HTML_msg."<br>";
			*/
			if( (sizeof($HTML_story) != Null) and (sizeof($HTML_story) != 0) )
				{
					// Only add one image
					$html = $html.$HTML_story;
				}
			
			if( (sizeof($HTML_img_arry) != Null) and (sizeof($HTML_img_arry) != 0) )
				{
					// Only add one image
					$html = $html.$HTML_img_arry[0];
				}
			$html = $html.$HTML_msg;
			echo "build_post_content :: html = ".$html."<br>";
			if( ($HTML_comments != Null) or ($HTML_comments != "") )
				{
					$html = $html.$HTML_comments;
				}
			$html = $this->build_simple_html_tag($id,$classArray,$html,$open,$close);
			echo "build_post_content :: html = ".$html."<br>";
			$html = '<section name="content">'.$html.'</section><!-- END post-content -->';
			return $html;
		}	
	
	function build_post_footer_fb_like_squ($img_src,$div1_classArray,$alt,$txt,$wdth,$hght)	
		{
			$html = '<span ';
			if( ($div1_classArray != Null) or (sizeof($div1_classArray) != 0) )
				{
					$html = $html.$this->build_class_string($div1_classArray);
				}
			$html = $html.'><img src="'.$img_src.'" alt="'.$alt.'" width="'.$wdth.'" height="'.$hght.'">'.$txt.'</span>';
			return $html;
		}
		
	function build_post_footer($HTML_footer_fb_likes,$HTML_footer_fb_shares,$HTML_footer_update,$id,$classArray)	
		{	
			$open = '<div';
			$close = '</div>';
			//echo "build_post_footer :: HTML_footer_update = ".$HTML_footer_update."<br>";
			$html = $HTML_footer_fb_likes.$HTML_footer_fb_shares.$HTML_footer_update;
			$html = $this->build_simple_html_tag($id,$classArray,$html,$open,$close);
			$html = '<section name="footer">'.$html.'</section><!-- END post-footer -->';
			return $html;
		}
	
	function build_simple_html_tag($id,$classArray,$txt,$openTag,$closeTag)
		{
			$html = $openTag;
			if( ($id != Null) or ($id != "") )
				{
					$html = $html.' id="'.$id.'" ';
				}
			if( ($classArray != Null) or (sizeof($classArray) != 0) )
				{
					$html = $html.' '.$this->build_class_string($classArray);
				}
			/*if( ($html != Null) or ($html != "") )
				{
					$html = $html.'>'.$txt.$closeTag;
				}
			else
				{
					$html = $html.$closeTag;
				}*/
			$html = $html.'>'.$txt.$closeTag;
			return $html;
		}
	
	function build_post_comment($HTML_title,$HTML_date,$HTML_msg,$id,$classArray)
		{
			$html = '<div';
			$close = $HTML_title.$HTML_date.$HTML_msg.'</div><!-- END post-add-content -->';
			$html = $this->build_simple_html_tag($id,$classArray,$txt,$html,$close);
			return $html;
		}
		
	function build_class_string($classArray)
		{
			$html = $html.' class="';
			foreach($classArray as $c)
				{
					$html = $html.$c.' ';
				}
			// Rmove last white space
			$html = substr($html, 0, -1); 
			$html = $html.'"';
			return $html;
		}/**/
}
?>