<?php
namespace PhenLib;

abstract class JSON
{
	//sends wrapped message with message interface
	public static function encode_send( $data )
	{
		$msg =
		[
			"message" => NULL,
			"exception" => NULL
		];
		
		if( is_a( $data, "Exception" ) )
		{
			$msg['message'] = $data->getMessage();
			$msg['exception'] = 
			[
				"type" => get_class( $data ),
				"code" => $data->getCode(),
				"file" => $data->getFile(),
				"line" => $data->getLine(),
				"trace" => $data->getTrace()
			];
			if( $data instanceof \ErrorException )
				$msg['exception']['severity'] = $data->getSeverity();
		}
		else
			$msg['message'] = $data;

		JSON::encode_send_raw( $msg );
	}

	//sends raw message
	public static function encode_send_raw( $data )
	{
		header('Content-Type: application/json; charset=utf-8');
		$json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		header("Content-length: " . strlen( $json ) );
		echo $json;
	}
};
?>
