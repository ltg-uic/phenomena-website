<?php
namespace Phen;

class Debug extends \PhenLib\Displayable
{
	public function __construct()
	{
		parent::__construct();

		$get = var_export( $_GET, true );
		$post = var_export( $_POST, true );
		$session = ( isset( $_SESSION ) ) ? var_export( $_SESSION, true ) : "";
		$relrootpath = \PhenLib\PageController::getRelativeRootPath();
		$server = htmlentities( var_export( $_SERVER, true ) );
		$html = <<<EOHTML
<!-- DEBUG -->
<div style="font-family: monospace; white-space: pre; font-size: 8pt;">DEBUG:

GET:
{$get}

POST:
{$post}

SESSION:
{$session}

RELATIVE ROOT PATH:
{$relrootpath}

SERVER:
{$server}
</div>
EOHTML;

		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

}
?>
