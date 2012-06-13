<?php
namespace Phen;

class LoginRecoverRegister extends \PhenLib\Displayable implements \PhenLib\Action
{
	public function __construct()
	{
		parent::__construct();

		$html = "";

		if( isset( $_SESSION[$this->id]['loginValid'] ) )
		{
			if( $_SESSION[$this->id]['loginValid'] )
				$html .= "<p><b>LOGIN VALID</b></p>";
			else
				$html .= "<p><b>LOGIN INVALID</b></p>";
			unset( $_SESSION[$this->id]['loginValid'] );
		}

		$html .= <<<EOHTML
<form action="LoginRecoverRegister" method="post">
	<div>
		Username: <input type="text" name="username" /><br />
		Password: <input type="password" name="password" /><br />
		<input type="submit" name="action_login" value="Login" /><br />
		<input type="submit" name="action_recover" value="Forgot?" /><br />
		<input type="submit" name="action_register" value="New?" />
	</div>
</form>
EOHTML;
		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

	public function execute()
	{
//TODO temp nothing
		return ;

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
		return NULL;
		return \PhenLib\Controller::getLastPage();
	}
}
?>
<?php
namespace Phen;

class Register extends \PhenLib\Displayable implements \PhenLib\Action
{
	public function __construct()
	{
		parent::__construct();

		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( <<<EOHTML
<h2>Register</h2>
<form action="register" method="post">
	<div>
		Username: <input type="text" name="username" /><br />
		Email: <input type="text" name="email" /><br />
		Password: <input type="password" name="password" /><br />
		<input type="submit" name="Submit" />
	</div>
</form>
EOHTML
) );
	}

	public function execute()
	{
//TODO - password minimum complexity
//TODO - validation
		$db = \PhenLib\Database::connect();

		$sql = "INSERT INTO `phen_website`.`users`
			(
				`user_id`,
				`user_login`,
				`user_password`,
				`user_email`
			)
			VALUES
			(
				NULL, ?, ?, ?
			)
			ON DUPLICATE KEY UPDATE
			`user_password` = ?, `user_email` = ?";

		$stmt = $db->prepare( $sql );

		$pass = \PhenLib\HashSSHA512::hash( $_POST['password'] );
		$stmt->bind_param( "sssss", $_POST['username'], $pass, $_POST['email'], $pass, $_POST['email'] );

		$stmt->execute();

		$id = $stmt->insert_id;
		
		$stmt->close();

		$this->setupUser( $id ); 
	}

	public function getRedirect()
	{
		return \PhenLib\Controller::getLastPage();
	}


	private function setupUser( $id )
	{
		$windows = 4;

		echo "Setting up user id: {$id}<br />";
		//get phenomenas from pod server, update db
		//right now we just get from db until a query to the server works
		$db = \PhenLib\Database::connect();

		$db->real_query( "SELECT * FROM `phenomenas`" );

		$res = $db->store_result();

		$xmpp = new \PhenLib\XMPPJAXL();
		while( $row = $res->fetch_assoc() )
			for( $x=0; $x<$windows; $x++ )
			{
				$xmpp_user = "{$row['phenomena_name']}_{$id}_{$x}";
				\PhenLib\XMPPServiceAdministration::addUser( $xmpp, $xmpp_user, $GLOBALS['xmppDomain'], \PhenLib\Password::generateRandom(), $added[$xmpp_user] );
			}
		$xmpp->execute();
		echo "users added:<br />\n";
		var_export( $added );
		echo $xmpp->getErrors();
		$res->free();
		exit();
	}
}
?>
