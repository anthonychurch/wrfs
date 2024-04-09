<?php
class folderUtilities
	{
		function __construct(){	}
		
		function create_folder($fullPath,$permission,$recursive)
			{
				$success = false;
				if (!file_exists($fullPath)) 
						{
							//if (!mkdir($fullPath, 0777, true)) 
							if (!mkdir($fullPath, $permission, $recursive))
								{
									die('Failed to create folders...');
								}
							else
								{
									$success = true;
								}
						}
					else
						{
							echo $fullPath." already exists<br>";
							$success = true;
						}
				return $success;
			}

		function image_save_to_folder($imageArray,$fullPath,$newName,$ext,$token)
			{
				$count = 0;
				$success = $this->create_folder($fullPath,0777,true);
				if (file_exists($fullPath))
					{
						foreach($imageArray as $i)
							{
								//TODO - END
								echo "imageArray[0]: ".$imageArray[0]."<br>";
								echo "i: ".$i."<br>";
								//if(file_exists($i))
								//	{
										//$ext = pathinfo($i, PATHINFO_EXTENSION);
										$img = str_replace("https://","",$i);
										$img = str_replace("http://","",$img);
										echo "img: ".$img."<br>";
										//$fp = $fullPath.$token.basename($fullPath).".".$ext;
										$fp = $fullPath.$token.$newName.".".$ext;
										echo "fp: ".$fp."<br>";
										//$this->image_save_from_url($i,$fullPath);
										$img = $this->saveImage($img,$fp);
										echo "img: ".$img."<br>";
								//	}
								//$count += 1;
							}
					}
				else{echo $fullPath." does not exist<br>";}/**/
			}
		
		function image_save_from_url($img,$imgPath)
			{
				if($imgPath!="" && $imgPath)
					{
						$imgPath = $imgPath."/".basename($img);
					}
				$ch = curl_init ($img);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
				curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
				$rawdata=curl_exec($ch);
				curl_close ($ch);
				if(file_exists($imgPath))
					{
						unlink($imgPath);
					}
				$fp = fopen($imgPath,'x');
				fwrite($fp, $rawdata);
				fclose($fp);
			}
			
		function saveImage($url, $savePath) 
			{

				$ch = curl_init($url);
				$fp = fopen($savePath, 'wb');

				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);

				$result = curl_exec($ch);

				fclose($fp);
				curl_close($ch);

				return $result;

			}
			
		function check_file_names($file,$filesArray,$dir,$token)
			{
				$success = '';
				foreach($filesArray as $value)
					{
						//echo "value: $value <br>";
						if(($value != "..") or ($value != "."))
							{
								preg_match("/^".$file."$/", $value, $matches);
								
								if($matches[0] == $file)
									{
										echo "matches: $matches[0] <br>";
									}
								else
									{
										preg_match("/".$file."/", $value, $matches);
										//echo "matches: $matches[0] <br>";
										//echo "file: $file <br>";
										if($matches[0] == $file)
											{
												//echo "renaming file <br>";
												$success = rename( $dir.$token.$value, $dir.$token.$file );
												//echo rename( $value, $file );
											}
									}
							}
					}
				return $success;
			}
		
		//$ignoreDots = true or false
		function get_files_in_folder($folder,$ignoreDots,$ignoreDir)
			{
				$files = scandir($folder);
				$returnArray = [];
				
				if($ignoreDots)
					{
						foreach($files as $f)
							{
								if( ($f != "..") or ($f != ".") )
									{
										if($ignoreDir)
											{
												$info = new SplFileInfo($f);
												$isFile = $info->getExtension();
												if( ($isFile != Null) or ($isFile != "") )
													{	
														array_push($returnArray, $f);
													}
											}
										else
											{
												array_push($returnArray, $f);
											}
									}
							}
					}
				else
					{
						if($ignoreDir)
							{
								foreach($files as $f)
									{
										$info = new SplFileInfo($f);
										$isFile = $info->getExtension();
										if( ($isFile != Null) or ($isFile != "") )
											{
												array_push($returnArray, $f);
											}
									}
							}
						else
							{
								$returnArray = $files;
							}
					}
				return $returnArray;
			}
	}
?>




