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
				throw new \Exception( "invalid argument" );
			$this->recovery_key = $uq->dequeue();
		}
	}

	public function generateOutput()
	{
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
		$brr = \PhenLib\PageController::getBaseRelativePath();
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
			dataType: "json",
		} ).done( function( responseJSON )
		{
			if( responseJSON.exception == null )
			{
				$.mobile.changePage( "{$brr}control-panel/" );
			}
			else
			{
				triggerDialogFeedback( responseJSON.message );
			}
		} ).fail( function()
		{
			triggerDialogFeedback( "Error Communicating With Server, Please Try Again" );	
		});
	});

	
	//TODO register form focus on popup
	/*$( "#{$this->id}_dialog-register" ).popup(
	{
		opened: function(event, ui) 
		{
			alert("hit");
			console.log("register popup opened"); 
			var username = $( '#{$this->id}_action_register input[name="username"]' );
			console.log(username);
	 	}
	});*/

	var registerForm = $( "#{$this->id}_action_register" );
	registerForm.on('submit', function( e ) 
	{
		//TODO disable duplicate submission
		//prevent form submission
		e.preventDefault();
		e.stopPropagation();
		var username = $( '#{$this->id}_action_register input[name="username"]' ).prop( "value" );
		var email = $( '#{$this->id}_action_register input[name="email"]' ).prop( "value" );
		var password = $( '#{$this->id}_action_register input[name="password"]' ).prop( "value" );
		var recaptcha_challenge_field = $( '#{$this->id}_action_register input[name="recaptcha_challenge_field"]' ).prop( "value" );
		var recaptcha_response_field = $( '#{$this->id}_action_register input[name="recaptcha_response_field"]' ).prop( "value" );
		//send request
		$.ajax(
		{
			url: "LoginRecoverRegister",
			type: "POST",
			data: {'action_register': 'Register', 'username': username, 'email': email, 'password': password, 'recaptcha_challenge_field': recaptcha_challenge_field, 'recaptcha_response_field': recaptcha_response_field},
			dataType: "json",
		} ).done( function( responseJSON )
		{
			if( responseJSON.exception !== null )
			{
				if( responseJSON.exception.type.localeCompare( "PhenLib\RecaptchaException" ) ) 
				{
					Recaptcha.reload();
					var recaptchaMsg = $('#recaptcha_instructions_image')
					recaptchaMsg.html( responseJSON.message );
					recaptchaMsg.css( 'color','#F00' );	
				}			
			}
			else if( responseJSON.message === true )
			{ 
				var registerPopup = $( "#{$this->id}_dialog-register" )
				registerPopup.bind(
				{
					popupafterclose: function() 
					{
						setTimeout( 'triggerDialogFeedback( "Your Registration Was Successful, Please Log In" )',0 );
					}
				});
				registerPopup.popup( "close" );
			}
			else
			{
				var registerPopup = $( "#{$this->id}_dialog-register" )
				registerPopup.bind(
				{
					popupafterclose: function() 
					{
						setTimeout( function() {  triggerDialogFeedback( responseJSON.message ) },0 );
					}
				});
				registerPopup.popup( "close" );
			}
		}).fail( function()
		{
			var registerPopup = $( "#{$this->id}_dialog-register" );
			registerPopup.bind(
			{
				popupafterclose: function()
				{
					setTimeout( 'triggerDialogFeedback( "Error Communicating With Server, Please Try Again" )',0 );
				}
			});
			registerPopup.popup( "close" );
		});
	});
	
	var recoverForm = $( "#{$this->id}_action_recover" );
	recoverForm.on( 'submit', function( e ) 
	{
		//prevent form submission
		e.preventDefault();
		e.stopPropagation();
		var email = $( '#{$this->id}_action_recover input[name="email"]' ).prop( "value" );
		var password = $( '#{$this->id}_action_recover input[name="password"]' ).prop( "value" );
		var cpassword = $( '#{$this->id}_action_recover input[name="cpassword"]' ).prop( "value" );
		var recaptcha_challenge_field = $( '#{$this->id}_action_recover input[name="recaptcha_challenge_field"]' ).prop( "value" );
		var recaptcha_response_field = $( '#{$this->id}_action_recover input[name="recaptcha_response_field"]' ).prop( "value" );
		if( password !== cpassword ) {
			triggerDialogFeedback( "Passwords Do Not Match" );
			return;
		}
		//send request
		$.ajax(
		{
			url: "LoginRecoverRegister",
			type: "POST",
			data: {'action_recover': 'Recover', 'email': email, 'password': password, 'recaptcha_challenge_field': recaptcha_challenge_field, 'recaptcha_response_field': recaptcha_response_field},
			dataType: "json",
		} ).done( function( responseJSON )
		{ 
			if( responseJSON.exception !== null )
			{
				if( responseJSON.exception.type.localeCompare( "PhenLib\RecaptchaException" ) ) 
				{
					Recaptcha.reload();
					var recaptchaMsg = $('#recaptcha_instructions_image')
					recaptchaMsg.html( responseJSON.message );
					recaptchaMsg.css( 'color','#F00' );	
				}			
			}
			else if( responseJSON.message === true  )
			{
				var recoverPopup = $( "#{$this->id}_dialog-recover" );
				recoverPopup.bind(
				{
					popupafterclose: function()
					{
						setTimeout( 'triggerDialogFeedback( "Password Recovery Email Sent, Please Check Your Email" )',0 );
					}
				});
				recoverPopup.popup( "close" );
			}
			else
			{
				var recoverPopup = $( "#{$this->id}_dialog-recover" );
				recoverPopup.bind(
				{
					popupafterclose: function()
					{
						setTimeout( function() { triggerDialogFeedback( responseJSON.message ) },0 );
					}
				});
				$( "#{$this->id}_dialog-recover" ).popup( "close" );
			}
		} ).fail( function()
		{
				var recoverPopup = $( "#{$this->id}_dialog-recover" );
				recoverPopup.bind(
				{
					popupafterclose: function()
					{
						setTimeout( 'triggerDialogFeedback( "Error Communicating With Server, Please Try Again" )',0 );
					}
				});
				recoverPopup.popup( "close" );
		});
	});
	var recoverPopup = $( "#{$this->id}_dialog-recover" );
	recoverPopup.bind(
	{ 
		popupafteropen: function( event, ui ) 
		{ 
			Recaptcha.create( "{$GLOBALS['recaptchaPublicKey']}",
				"recover_recaptcha",
				{
					theme: "blackglass",
					callback: Recaptcha.focus_response_field
		    		}
			);
		} 
	});
	var registerPopup = $( "#{$this->id}_dialog-register" );
	registerPopup.bind(
	{ 
		popupafteropen: function( event, ui ) 
		{ 
			Recaptcha.create( "{$GLOBALS['recaptchaPublicKey']}",
				"register_recaptcha",
				{
					theme: "blackglass",
					callback: Recaptcha.focus_response_field
		    		}
			);
		}
	});	
});
{$recovery_result_popup}
-->
</script>
<form id="{$this->id}_action_login" action="LoginRecoverRegister" method="post">
	<div>
		<div class="ui-grid-a">
			<div class="ui-block-a" style="width: 30%;"><label for="{$this->id}_button-login-username" style="margin: 15px 0px;">Username</label></div>
			<div class="ui-block-b" style="width: 70%;"><input id="{$this->id}_button-login-username" type="text" name="username" required="required"/></div>
			<div class="ui-block-a" style="width: 30%;"><label for="{$this->id}_button-login-password" style="margin: 15px 0px;">Password</label></div>
			<div class="ui-block-b" style="width: 70%;"><input id="{$this->id}_button-login-password" type="password" name="password" required="required" /></div>
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
			<div id="recover_recaptcha" style="height: 129px;"></div>
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
			<div id="register_recaptcha" style="height: 129px;"></div>
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
			try
			{
				if( isset( $_POST['username'] ) && isset( $_POST['password'] ) && \PhenLib\Authentication::doLogin( $_POST['username'], $_POST['password'] ) )
				{
					//json success and exit()
					\PhenLib\JSON::encode_send( TRUE );
					exit();
				}
				else
				{
					throw new \Exception("User Authentication Failed");
				}
			}
			catch( \Exception $e )
			{
				\PhenLib\JSON::encode_send( $e );
				exit();
			}
		}
		else if( isset( $_POST['action_recover'] ) )
		{
			try
			{
				//check recaptcha
				if( isset( $_POST["recaptcha_challenge_field"] ) & isset( $_POST["recaptcha_response_field"] ) )
				{
					$error = \PhenLib\reCAPTCHA::Validate(
						$GLOBALS['recaptchaPrivateKey'], $_SERVER["REMOTE_ADDR"],
						$_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
		
					//check recatchpa
					if ( !$error->is_valid ) 
					{ 
						throw new \PhenLib\RecaptchaException($error->error);
					}
				}
	
				if( isset( $_POST['email'] ) && isset( $_POST['password'] ) && \PhenLib\User::recoverInitialize( $_POST['email'], $_POST['password'] ) )
				{	
					//json success and exit()
					\PhenLib\JSON::encode_send( TRUE );
					exit();
				}
				else
				{	
					throw new \Exception("Password Recovery Failed");
				}
			}
			catch( \Exception $e )
			{
				\PhenLib\JSON::encode_send( $e );
				exit();
			}
		}
		else if( isset( $_POST['action_register'] ) )
		{
			try 
			{
				if( isset( $_POST["recaptcha_challenge_field"] ) & isset( $_POST["recaptcha_response_field"] ) )
				{
					$error = \PhenLib\reCAPTCHA::Validate( $GLOBALS['recaptchaPrivateKey'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
		
					//check recatchpa
					if ( !$error->is_valid ) 
					{ 
						throw new \PhenLib\RecaptchaException($error->error);
					}
				}	

				if( isset( $_POST['username'] ) && isset( $_POST['password'] ) && isset( $_POST['email'] ) && \PhenLib\User::create( $_POST['username'], $_POST['password'], $_POST['email'] ) )
				{
					//json success and exit()
					\PhenLib\JSON::encode_send( TRUE );
					exit();
				}
				else
				{
					throw new \Exception("New User Creation Failed");
				}
			}
			catch( \Exception $e )
			{
				\PhenLib\JSON::encode_send( $e );
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
