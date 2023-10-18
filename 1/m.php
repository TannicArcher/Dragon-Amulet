<?php

// карта, передается map=x123x456 и для картики img=1 bw=1|2|3, f=x123x456 место флага
$tmp = $QUERY_STRING;
if ( $tmp == '' )
		$tmp = $_SERVER["QUERY_STRING"];
$tmp = urldecode( $tmp );
parse_str( $tmp );

$loc = $l;

// >1650
function calctc( $loc )
{
		if ( $loc == "_begin" )
				$loc = "x1158x523";
		if ( $loc == "arena" )
				$loc = "x1086x501";
		if ( substr( $loc, 0, 4 ) == "c.1." )
				$loc = "x1429x168";
		if ( substr( $loc, 0, 4 ) == "c.2." )
				$loc = "x781x429";
		if ( substr( $loc, 0, 4 ) == "c.3." )
				$loc = "x1129x369";
		if ( substr( $loc, 0, 4 ) == "c.4." )
				$loc = "x2320x348";

		$tc = explode( "x", $loc );
		if ( $tc[2] > 1101 )
		{
				$tc[1] = round( ( $tc[1] - 20 ) / 6 );
				$tc[2] = round( ( $tc[2] - 1101 ) / 6 );
				$tc[3] = 2;
		}
		else
				if ( $tc[1] > 1650 )
				{
						$tc[1] = round( ( $tc[1] - 450 - 1200 ) / 15 );
						$tc[2] = round( $tc[2] / 15 );
						$tc[3] = 1;
				}
				else
				{
						$tc[1] = round( ( $tc[1] - 450 ) / 12 );
						$tc[2] = round( $tc[2] / 12 );
						$tc[3] = 0;
				}
				return $tc;
}

$tc = calctc( $l );
$tcf = calctc( $f );

if ( $img )
{ // выведем картинку
		if ( $bw == 1 )
				$t = "wbmp";
		else
				if ( $bw == 2 )
						$t = "jpg";
				else
						$t = "png";
		if ( $tc[3] == 2 )
				$img = "map3." . $t;
		else
				if ( $tc[3] )
						$img = "map2." . $t;
				else
						$img = "map1." . $t;
		$size = getimagesize( $img );
		if ( $bw == 1 )
				$im = imagecreatefromwbmp( $img );
		else
				if ( $bw == 2 )
						$im = imagecreatefromjpeg( $img );
				else
						$im = imagecreatefrompng( $img );
		if ( !$im )
				die( "err" );
		$col = imagecolorat( $im, $tc[1] + 1, $tc[2] + 1 );
		if ( $col == 0 || $col == "16777180" )
		{
				$cb = 1;
				$cc = imagecolorallocate( $im, 255, 255, 255 );
		}
		else
		{
				$cb = imagecolorallocate( $im, 255, 255, 255 );
				$cc = 1;
		}
		imagefilledrectangle( $im, $tc[1], $tc[2], $tc[1] + 2, $tc[2] + 2, $cb );
		imagesetpixel( $im, $tc[1] + 1, $tc[2] + 1, $cc );
		if ( $tcf[3] == $tc[3] && $f != $l )
		{ // флаг
				if ( $col == 0 || $col == "16777180" )
				{
						$cb = 1;
						$cc = imagecolorallocate( $im, 255, 255, 255 );
				}
				else
				{
						$cb = imagecolorallocate( $im, 255, 255, 255 );
						$cc = 1;
				}
				if ( $bw != 1 )
				{
						$cb = imagecolorallocate( $im, 255, 255, 0 );
						$cc = imagecolorallocate( $im, 0, 0, 0 );
				}
				imagefilledrectangle( $im, $tcf[1], $tcf[2], $tcf[1] + 2, $tcf[2] + 2, $cb );
				imagesetpixel( $im, $tcf[1] + 1, $tcf[2] + 1, $cc );
		}
		header( "Content-type: {$size['mime']}" ); //;charset=utf-8
		if ( $bw == 1 )
				imagewbmp( $im );
		else
				if ( $bw == 2 )
						imagejpeg( $im );
				else
						imagepng( $im );
		imagedestroy( $im );
		die( "" );
} //if $img

?>