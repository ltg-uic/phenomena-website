<?php
namespace Phen;

class LoginRecoverRegister extends \PhenLib\Displayable implements \PhenLib\Action
{
	public function __construct()
	{
		parent::__construct();

//TODO	- user feedback on recovery
//	- move processing to javascript section, post to execute, json response
//	- eliminate gimmicky getRecoveryResult
//	- should fix the popup not going away on load
		//process recovery key
		if( isset( $_GET['recovery_key'] ) )
		{
			\PhenLib\User::recoverFinalize( $_GET['recovery_key'] );
			header( "Location: ../" );
			exit();
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

			$recovery_result_popup =
<<<EOJAVASCRIPT
$(document).one('pagechange', function() {
	setTimeout( function(){ triggerDialogFeedback( "{$msg}" ) }, 500 );
});
EOJAVASCRIPT;
			\PhenLib\User::clearRecoveryResult();
		}
		$brr = \PhenLib\PageController::getBaseRelativePath();
		$html =
<<<EOHTML
<script type="text/javascript">
<!--

triggerDialogFeedback = function( msg ) 
{
	var dialogFeedback = $( '#{$this->id}dialog-feedback' );
	dialogFeedback.empty();
	dialogFeedback.append( $( '<span></span>' ).text( msg ) );
	dialogFeedback.popup( "open" );
}

$(document).one('pageinit', function() 
{
	var loginForm = $( "#{$this->id}form-login" );
	loginForm.validate(
	{
		submitHandler: function(form, validator) 
		{		
			//send request
			$.ajax(
			{
				url: "LoginRecoverRegister",
				type: "POST",
				data: loginForm.serialize(),
				dataType: "json",
			} ).done( function( responseJSON )
			{
				if( responseJSON.exception === null )
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
		}

	});
	
	//TODO register form focus on popup
	/*$( "#{$this->id}dialog-register" ).popup(
	{
		opened: function(event, ui) 
		{
			alert("hit");
			console.log("register popup opened"); 
			var username = $( '#{$this->id}form-register input[name="username"]' );
			console.log(username);
	 	}
	});*/

	var registerForm = $( "#{$this->id}form-register" );
	registerForm.validate(
	{
		submitHandler: function(form, validator) 
		{
			//send request
			$.ajax(
			{
				url: "LoginRecoverRegister",
				type: "POST",
				data: registerForm.serialize(),
				dataType: "json",
			} ).done( function( responseJSON )
			{
				if( responseJSON.exception !== null )
				{
					if( responseJSON.exception.type === "PhenLib\\\\RecaptchaException" ) 
					{
						Recaptcha.create( "{$GLOBALS['recaptchaPublicKey']}", 
						"{$this->id}register-recaptcha",
						{
							theme: "blackglass",
							callback: Recaptcha.focus_response_field,
							extra_challenge_params: "error=" + responseJSON.message
						});
					}			
					else 
					{
						var registerPopup = $( "#{$this->id}dialog-register" )
						registerPopup.bind(
						{
							popupafterclose: function() 
							{
								setTimeout( function() { triggerDialogFeedback( responseJSON.message ) }, 0 );
							}
						});
						registerPopup.popup( "close" );
					}
				}
				else if( responseJSON.message === true )
				{ 
					var registerPopup = $( "#{$this->id}dialog-register" )
					registerPopup.bind(
					{
						popupafterclose: function() 
						{
							setTimeout( 'triggerDialogFeedback( "Your Registration Was Successful, Please Log In" )', 0 );
						}
					});
					registerPopup.popup( "close" );
				}
			}).fail( function()
			{
				var registerPopup = $( "#{$this->id}dialog-register" );
				registerPopup.bind(
				{
					popupafterclose: function()
					{
						setTimeout( 'triggerDialogFeedback( "Error Communicating With Server, Please Try Again" )', 0 );
					}
				});
				registerPopup.popup( "close" );
			});
		}
	});
		
	var recoverForm = $( "#{$this->id}form-recover" );
	recoverForm.validate(
	{	
		submitHandler: function(form, validator) 
		{
			//validate
			var password = $( '#{$this->id}form-recover_input-password' ).prop( "value" );
			var cpassword = $( '#{$this->id}form-recover_input-cpassword' ).prop( "value" );
			if( password !== cpassword ) {
				triggerDialogFeedback( "Passwords Do Not Match" );
				return;
			}
			//send request
			$.ajax(
			{
				url: "LoginRecoverRegister",
				type: "POST",
				data: recoverForm.serialize(),
				dataType: "json",
			} ).done( function( responseJSON )
			{ 
				if( responseJSON.exception !== null )
				{
					if( responseJSON.exception.type === "PhenLib\\\\RecaptchaException" ) 
					{
						Recaptcha.create( "{$GLOBALS['recaptchaPublicKey']}",
						"{$this->id}recover-recaptcha",
						{
							theme: "blackglass",
							callback: Recaptcha.focus_response_field,
							extra_challenge_params: "error=" + responseJSON.message
						});
					}			
					else
					{
						var recoverPopup = $( "#{$this->id}dialog-recover" );
						recoverPopup.bind(
						{
							popupafterclose: function()
							{
								setTimeout( function() { triggerDialogFeedback( responseJSON.message ) },0 );
							}
						});
						$( "#{$this->id}dialog-recover" ).popup( "close" );
					}
				}
				else if( responseJSON.message === true  )
				{
					var recoverPopup = $( "#{$this->id}dialog-recover" );
					recoverPopup.bind(
					{
						popupafterclose: function()
						{
							setTimeout( 'triggerDialogFeedback( "Password Recovery Email Sent, Please Check Your Email" )',0 );
						}
					});
					recoverPopup.popup( "close" );
				}
			} ).fail( function()
			{
					var recoverPopup = $( "#{$this->id}dialog-recover" );
					recoverPopup.bind(
					{
						popupafterclose: function()
						{
							setTimeout( 'triggerDialogFeedback( "Error Communicating With Server, Please Try Again" )',0 );
						}
					});
					recoverPopup.popup( "close" );
			});
		}
	});
	var recoverPopup = $( "#{$this->id}dialog-recover" );
	recoverPopup.bind(
	{ 
		popupafteropen: function( event, ui ) 
		{ 
			Recaptcha.create( "{$GLOBALS['recaptchaPublicKey']}",
				"{$this->id}recover-recaptcha",
				{
					theme: "blackglass",
					callback: Recaptcha.focus_response_field
				}
			);
		} 
	});
	var registerPopup = $( "#{$this->id}dialog-register" );
	registerPopup.bind(
	{ 
		popupafteropen: function( event, ui ) 
		{ 
			Recaptcha.create( "{$GLOBALS['recaptchaPublicKey']}",
				"{$this->id}register-recaptcha",
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
<form id="{$this->id}form-login" action="LoginRecoverRegister" method="post" >
	<div style="min-width: 325px;">
		<label for="{$this->id}form-login_input-username" class="ui-hidden-accessible">Username</label>
		<input id="{$this->id}form-login_input-username" type="text" name="username" placeholder="Username" class="required" />
		<label for="{$this->id}form-login_input-password" class="ui-hidden-accessible">Password</label>
		<input id="{$this->id}form-login_input-password" type="password" name="password" placeholder="Password" class="required" />
		<input type="submit" name="action_login" value="Login" />
		<a href="#{$this->id}dialog-recover" data-role="button" data-rel="popup" data-transition="slideup">Forgot?</a>
		<a href="#{$this->id}dialog-register" data-role="button" data-rel="popup" data-transition="slideup">New?</a>
	</div>
</form>

<div data-role="popup" id="{$this->id}dialog-recover" class="ui-content">
	<h2>Recover</h2>
	<form id="{$this->id}form-recover" action="LoginRecoverRegister" method="post">
		<div>
			<label for="{$this->id}form-recover_input-email" class="ui-hidden-accessible">Email</label>
			<input id="{$this->id}form-recover_input-email" type="text" name="email" placeholder="Email" class="required email" />
			<label for="{$this->id}form-recover_input-password" class="ui-hidden-accessible">New Password</label>
			<input id="{$this->id}form-recover_input-password" type="password" name="password" placeholder="New Password" class="required" />
			<label for="{$this->id}form-recover_input-cpassword" class="ui-hidden-accessible">Confirm New Password</label>
			<input id="{$this->id}form-recover_input-cpassword" type="password" name="cpassword" placeholder="Confirm New Password" class="required" />
			<div id="{$this->id}recover-recaptcha" style="min-width: 318px; min-height: 129px;"></div>
			<input type="submit" name="action_recover" value="Recover" />
		</div>
	</form>
</div>

<div data-role="popup" id="{$this->id}dialog-register" class="ui-content">
	<h2>Register</h2>
	<form id="{$this->id}form-register" action="LoginRecoverRegister" method="post">
		<div>
			<label for="{$this->id}form-register_input-username" class="ui-hidden-accessible">Username</label>
			<input id="{$this->id}form-register_input-username" type="text" name="username" placeholder="Username" class="required" />
			<label for="{$this->id}form-register_input-email" class="ui-hidden-accessible">Email</label>
			<input id="{$this->id}form-register_input-email" type="text" name="email" placeholder="Email" class="required email" />
			<label for="{$this->id}form-register_input-password" class="ui-hidden-accessible">Password</label>
			<input id="{$this->id}form-register_input-password" type="password" name="password" placeholder="Password" class="required" />
			<div id="{$this->id}register-recaptcha" style="min-width: 318px; min-height: 129px;"></div>
			<input type="submit" name="action_register" value="Register" />
		</div>
	</form>
</div>

<div data-role="popup" id="{$this->id}dialog-feedback" class="ui-content"></div>
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
		else
			throw new \Exception( "missing or invalid action" );
	}

	public function getRedirect()
	{
		//All actions handled by JSON
		return NULL;
	}
}
?>
