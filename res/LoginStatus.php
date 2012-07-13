<?php
namespace Phen;

class LoginStatus extends \PhenLib\Displayable implements \PhenLib\Action 
{
	public function __construct()
	{
		parent::__construct();

		$user = \PhenLib\Authentication::getAuthenticatedUser();
		$rootPath = \PhenLib\PageController::getBaseRelativePath();
		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;
	
		$html = <<<EOHTML

<script type="text/javascript">
<!--	

$(document).one('pageinit', function() {

	var logoutButton = $("#{$this->id}_action_logout");
	logoutButton.on('click', function() {
		//send logout request
                $.ajax(
                {
                        url: "LoginStatus",
                        type: "POST",
                        data: {'action_logout': 'Logout'},
                        datatype: "json",
                        complete: function( jqXHR, status ) 
				{
                        		if( jqXHR.status === 200 )
                                		$.mobile.changePage( "{$rootPath}" );
				}
                });
	});
});

-->
</script>

<span>Logged In As {$user}</span>
<a data-role="button" id="{$this->id}_action_logout" data-mini="true">Logout</a>

EOHTML;

		//replaces root container
		$root->parentNode->replaceChild( \PhenLib\Template::HTMLtoDOM( $html ), $root );
	}

	public function execute() 
	{
		if( isset( $_POST['action_logout'] ) )
		{
			\PhenLib\Authentication::doLogout();
			exit();	
		} 
		else 
		{
			throw new \Exception( "missing or invalid action" );	
		}

	}

	public function getRedirect() {
		return NULL;

	}
}
?>
