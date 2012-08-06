<?php
namespace PhenLib;

abstract class XML
{
	public static function send( $xml )
	{
		header('Content-Type: application/xml; charset=utf-8');
		header("Content-length: " . strlen( $xml ) );
		echo $xml;
	}
}
?>
