<?php
namespace Phen;

class LoginRecoverRegister extends \PhenLib\Displayable implements \PhenLib\Action
{
	private $recovery_key;

	public function __construct( \SPLQueue $uq = NULL )
	{
		parent::__construct();

		//get recovery key from url, if present
		$this->recovery_key = NULL;
		if( $uq !== NULL && ! $uq->isEmpty() )
		{
			if( $uq->dequeue() !== "recover" )
				throw new Exception( "invalid argument" );
			$this->recovery_key = $uq->dequeue();
			return;
		}

		$recovery_result_popup = "";
		if( ( $recovery_result = \PhenLib\User::getRecoveryResult() ) !== NULL )
		{ 
			if( $recovery_result === TRUE )
				$msg = "Password Successfully Reset";
			else
				$msg = "There was an error resetting your password";
			$recovery_result_popup = <<<EOJAVASCRIPT
$(document).one('pageshow', function() {
	triggerDialogFeedback( "{$msg}" );
});
EOJAVASCRIPT;
			\PhenLib\User::clearRecoveryResult();
		}
		$html = <<<EOHTML
<script type="text/javascript">
<!--

triggerDialogFeedback = function( msg ) 
{
	var dialogFeedback = $( '#{$this->id}_dialog-feedback' );
	dialogFeedback.empty();
	dialogFeedback.append( $( '<span></span>' ).text( msg ) );
	dialogFeedback.popup( "open" );
}

$(document).one('pageinit', function() 
{
	var loginForm = $( "#{$this->id}_action_login" );
	loginForm.on('submit', function( e ) 
	{
		//prevent form submission
		e.preventDefault();
		e.stopPropagation();
		var username = $( '#{$this->id}_action_login input[name="username"]' ).prop( "value" );
		var password = $( '#{$this->id}_action_login input[name="password"]' ).prop( "value" );
		//send request
		$.ajax(
		{
			url: "LoginRecoverRegister",
			type: "POST",
			data: {'action_login': 'Login', 'username': username, 'password': password},
			datatype: "json",
			complete: function( jqXHR, status )
				{ 
					if( jqXHR.status === 200 ) 
					{	
						if( jqXHR.responseText === "true" )
						{
							$.mobile.changePage( "control-panel/" );
						}
						else
						{
							triggerDialogFeedback( "Login Error, Please Verify Your Username and Password" );
						}
					}
				}
		} ).fail( function()
			{
			triggerDialogFeedback( "Error Communicating With Server, Please Try Again" );	
		});
	});


	var registerForm = $( "#{$this->id}_action_register" );
	registerForm.on('submit', function( e ) 
	{
		//prevent form submission
		e.preventDefault();
		e.stopPropagation();
		var username = $( '#{$this->id}_action_register input[name="username"]' ).prop( "value" );
		var email = $( '#{$this->id}_action_register input[name="email"]' ).prop( "value" );
		var password = $( '#{$this->id}_action_register input[name="password"]' ).prop( "value" );
		//send request
		$.ajax(
		{
			url: "LoginRecoverRegister",
			type: "POST",
			data: {'action_register': 'Register', 'username': username, 'email': email, 'password': password},
			datatype: "json",
			complete: function( jqXHR, status )
				{ 
					if( jqXHR.status === 200 ) 
					{	
						if( jqXHR.responseText === "true" )
							triggerDialogFeedback("Your Registration Was Successful, Please Log In");
						else
							triggerDialogFeedback( "Registration Error, Please Try Again. If You Continue To Have Problems Please Contact the Phenomena Team" );
					}
				}
		} ).fail( function()
			{
			triggerDialogFeedback( "Error Communicating With Server, Please Try Again" );	
		});
	});
	
	var recoverForm = $( "#{$this->id}_action_recover" );
	recoverForm.on('submit', function( e ) 
	{
		//prevent form submission
		e.preventDefault();
		e.stopPropagation();
		var email = $( '#{$this->id}_action_recover input[name="email"]' ).prop( "value" );
		var password = $( '#{$this->id}_action_recover input[name="password"]' ).prop( "value" );
		var cpassword = $( '#{$this->id}_action_recover input[name="cpassword"]' ).prop( "value" );
		if( password !== cpassword ) {
			triggerDialogFeedback( "Passwords Do Not Match" );
			return;
		}
		//send request
		$.ajax(
		{
			url: "LoginRecoverRegister",
			type: "POST",
			data: {'action_recover': 'Recover', 'email': email, 'password': password },
			datatype: "json",
			complete: function( jqXHR, status )
				{ 
					if( jqXHR.status === 200 ) 
					{	
						if( jqXHR.responseText === "true" )
							triggerDialogFeedback("Password Recovery Email Sent, Please Check Your Email");
						else
							triggerDialogFeedback( "Password Recovery Error, Please Try Again. If You Continue To Have Problems Please Contact the Phenomena Team" );
					}
				}
		} ).fail( function()
			{
			triggerDialogFeedback( "Error Communicating With Server, Please Try Again" );	
		
		});
	});
	
});
{$recovery_result_popup}
-->
</script>
<form id="{$this->id}_action_login" action="LoginRecoverRegister" method="post">
	<div>
		<div class="ui-grid-a">
			<div class="ui-block-a" style="width: 30%;"><label for="{$this->id}_button-login-username" style="margin: 15px 0px;">Username</label></div>
			<div class="ui-block-b" style="width: 70%;"><input id="{$this->id}_button-login-username" type="text" name="username" required="true"/></div>
			<div class="ui-block-a" style="width: 30%;"><label for="{$this->id}_button-login-password" style="margin: 15px 0px;">Password</label></div>
			<div class="ui-block-b" style="width: 70%;"><input id="{$this->id}_button-login-password" type="password" name="password" required="true" /></div>
		</div>
		<input type="submit" name="action_login" value="Login" />
		<a href="#{$this->id}_dialog-recover" data-role="button" data-rel="popup" data-transition="slideup">Forgot?</a>
		<a href="#{$this->id}_dialog-register" data-role="button" data-rel="popup" data-transition="slideup">New?</a>
	</div>
</form>

<div data-role="popup" id="{$this->id}_dialog-recover" class="ui-content">
	<h2>Recover</h2>
	<form id="{$this->id}_action_recover" action="LoginRecoverRegister" method="post">
		<div>
			Email: <input type="text" name="email" /><br />
			New Password: <input type="password" name="password" /><br />
			Confirm New Password: <input type="password" name="cpassword" /><br />
			<input type="submit" name="action_recover" value="Recover" />
		</div>
	</form>
</div>

<div data-role="popup" id="{$this->id}_dialog-register" class="ui-content">
	<h2>Register</h2>
	<form id="{$this->id}_action_register" action="LoginRecoverRegister" method="post">
		<div>
			Username: <input type="text" name="username" /><br />
			Email: <input type="text" name="email" /><br />
			Password: <input type="password" name="password" /><br />
			<input type="submit" name="action_register" value="Register" />
		</div>
	</form>
</div>

<div data-role="popup" id="{$this->id}_dialog-feedback" class="ui-content">
	
</div>
EOHTML;
		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

	public function execute()
	{
		if( isset( $_POST['action_login'] ) )
		{
			if( isset( $_POST['username'] ) && isset( $_POST['password'] ) && \PhenLib\Authentication::doLogin( $_POST['username'], $_POST['password'] ) )
			{
				//json success and exit()
				\PhenLib\JSON::encode_send( TRUE );
				exit();
			}
			else
			{
				//json fail and exit()
				\PhenLib\JSON::encode_send( FALSE );
				exit();
			}
		}
		else if( isset( $_POST['action_recover'] ) )
		{
			if( isset( $_POST['email'] ) && isset( $_POST['password'] ) && \PhenLib\User::recoverInitialize( $_POST['email'], $_POST['password'] ) )
			{	
				//json success and exit()
				\PhenLib\JSON::encode_send( TRUE );
				exit();
			}
			else
			{	
				//json fail and exit()
				\PhenLib\JSON::encode_send( FALSE );
				exit();
			}		
		}
		else if( isset( $_POST['action_register'] ) )
		{
			if( isset( $_POST['username'] ) && isset( $_POST['password'] ) && isset( $_POST['email'] ) && \PhenLib\User::create( $_POST['username'], $_POST['password'], $_POST['email'] ) )
			{
				//json success and exit()
				\PhenLib\JSON::encode_send( TRUE );
				exit();
			}
			else
			{
				//json fail and exit()
				\PhenLib\JSON::encode_send( FALSE );
				exit();
			}
		}
		else if( $this->recovery_key != NULL )
		{
			\PhenLib\User::recoverFinalize( $this->recovery_key );
		}
		else
			throw new \Exception( "missing or invalid action" );
	}

	public function getRedirect()
	{
		if( $this->recovery_key != NULL )
			return "../../";
		//All actions handled by JSON
		return NULL;
	}
}
?>
