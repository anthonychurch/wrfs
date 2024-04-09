<?php
class stringUtil
{
	function __construct(){	}
	function outputStringFromArray($array,$quotes)//$quotes is a boolean
	{
		$strng="";
		for ($i = 0; $i <= sizeof($array)-1; $i++) 
		{
			echo "array[".$i."]: ".$array[$i]."<br>";
			if($quotes)
				{
					$strng .= '"'.$array[$i].'"' . ',';
				}
			else
				{
					$strng .= $array[$i] . ',';
				}	
		}
		$strng = rtrim($strng, ",");
		return $strng;
	}
	function getStartEndString($str,$findme,$offset)
	{
		$s = substr($str,$offset,strlen($str));
		$findmelen = strlen($findme);//Get length of $findme
		//Find the postion of the first occurance of $pos
		$start = strpos($s, $findme) + $offset;
		$end = $start + $findmelen;
		//Returns Array Start, End and Length
		return array($start,$end,$findmelen);
	}
	
}
?>