<?php
namespace Phen;

class Login extends \PhenLib\Displayable implements \PhenLib\Action
{
	public function __construct()
	{
		parent::__construct();

		$html = "<h2>Login</h2>";


		if( isset( $_SESSION[$this->id]['loginValid'] ) )
		{
			if( $_SESSION[$this->id]['loginValid'] )
				$html .= "<p><b>LOGIN VALID</b></p>";
			else
				$html .= "<p><b>LOGIN INVALID</b></p>";
			unset( $_SESSION[$this->id]['loginValid'] );
		}

		$html .= <<<EOHTML
<form action="login" method="post">
	<div>
		Username: <input type="text" name="username" /><br />
		Password: <input type="password" name="password" /><br />
		<input type="submit" name="Login" />
	</div>
</form>
EOHTML;
		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

	public function execute()
	{
		\PhenLib\Session::start();
		$db = \PhenLib\Database::connect();

		$sql = "SELECT `user_password`
			FROM `users`
			WHERE `user_login` = ?";

		$stmt = $db->prepare( $sql );
		$stmt->bind_param( "s", $_POST['username'] );
		$stmt->execute();
		$stmt->bind_result( $stored_hash );
		$stmt->fetch();
		$stmt->close();

		$_SESSION[$_POST['id']]['loginValid'] = \PhenLib\HashSSHA512::verify( $_POST['password'], $stored_hash );
	}

	public function getRedirect()
	{
		return \PhenLib\Controller::getLastPage();
	}
}
?>
