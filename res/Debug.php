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
		$html = <<<EOHTML
<!-- DEBUG -->
<div style="font-family: monospace; white-space: pre; font-size: 8pt; border: solid 1px black; max-width: 500px; padding: 5px;">DEBUG:

GET:
{$get}

POST:
{$post}

SESSION:
{$session}
</div>
EOHTML;

		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

}
?>
