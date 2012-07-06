<?php
namespace PhenLib;

abstract class JSON
{
	public static function encode_send( $data )
	{
		header('Content-Type: application/json; charset=utf-8');

		echo json_encode( $data );
	}
}
?>
