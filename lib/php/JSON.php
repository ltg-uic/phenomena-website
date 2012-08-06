<?php
namespace PhenLib;

abstract class JSON
{
	public static function encode_send( $data )
	{
		header('Content-Type: application/json; charset=utf-8');
		$json = json_encode( $data );
		header("Content-length: " . strlen( $json ) );
		echo $json;
	}
}
?>
