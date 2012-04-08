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
		
		$stmt->close();
	}

	public function getRedirect()
	{
		return \PhenLib\Controller::getLastPage();
	}
}
?>
