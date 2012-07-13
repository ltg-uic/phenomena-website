<?php
namespace Phen;

class Debug extends \PhenLib\Displayable
{
	public function generateOutput()
	{
		$get = htmlentities( print_r( $_GET, true ) );
		$post = htmlentities( print_r( $_POST, true ) );
		$session = htmlentities( ( isset( $_SESSION ) ) ? print_r( $_SESSION, true ) : "" );
		$pc = (new \ReflectionClass( '\PhenLib\PageController' ))->getStaticProperties();
		$pc['resourceQueue'] = "{Object Omitted}";
		$pc['rootResource'] = "{Object Omitted}";
		$pc = htmlentities( print_r( $pc, true ) );
		$server = htmlentities( print_r( $_SERVER, true ) );
		$html = <<<EOHTML
<!-- DEBUG -->
<div style="font-family: monospace; white-space: pre; font-size: 8pt;">DEBUG:

GET:
{$get}

POST:
{$post}

SESSION:
{$session}

PAGE CONTROLLER:
{$pc}

SERVER:
{$server}
</div>
EOHTML;

		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

}
?>
