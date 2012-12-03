<?php

//Params
$defaultMessage = "Sent from QuickMail";
$defaultRecipient="recipientemail@host.com";
$defaultSender = "youremail@host.com";
$defaultSenderName = "Your Name";

//SMTP server identifiers
$username = "smtpusername";
$password = "smtppassword";
$host = "ssl://smtp.yourhost.com";
$port = 465;

$redirectURL = "http://pagetoloadaftersendingemail.com";

//Main
if( ( isset( $_POST[ 'input' ] ) && !empty( $_POST[ 'input' ] ) )  || ( isset( $_GET[ 'input' ] ) && !empty( $_GET[ 'input' ] ) ) )
{
	require( "phpmailer/class.phpmailer.php" );
	
	if ( isset( $_POST['input'] ) )
	{
		$params = parseArgs( $_POST['input'] );
	}
	else
	{
		$params = parseArgs( utf8_decode( $_GET['input'] ) );
	}
		
	$recipientName = ""; 

	$bOK = true;
	
	foreach( $params as $key => $value )
	{
		switch( (string)$key )
		{
			case '0':
				$subject = $value;
				break;
			case 'r':
				$recipients = $value;
				break;
			case 'm':
				$message = $value;
				break;
			default :
				$content = error();
				$bOK = false;
				continue 2;
		}
	}
	
	if ( $bOK )
	{
		$recipients = ($recipients) ? explode( "," , $recipients ) : array( $defaultRecipient );
		$message = ($message) ? $message : $defaultMessage;
		$sender = ($sender) ? $sender : $defaultSender;
		$senderName = ($senderName) ? $senderName : $defaultSenderName;
		
		$mail = new PHPMailer();
		$mail->IsSMTP(); // send via SMTP
		$mail->Mailer = "smtp";  
		$mail->Host = $host;  
		$mail->Port = $port;  
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->Username = $username; // SMTP username
		$mail->Password = $password; // SMTP password

		$mail->From = $sender;
		$mail->FromName = $defaultSenderName;
		
		foreach ( $recipients as $recipient )
			$mail->AddAddress( trim( $recipient) );

		//$mail->AddAddress();
		$mail->AddReplyTo( $sender , $senderName );
		$mail->WordWrap = 50; // set word wrap

		$mail->IsHTML(true); // send as HTML
		$mail->Subject = stripslashes( $subject );
		$mail->Body = stripslashes( $message ); //HTML Body

		if( !$mail->Send() ){
			$content = error( $mail->ErrorInfo );
		}else{
			@header('Location:'.$redirectURL.'');
		}
	}
}

//Functions
function parseArgs( $cmdLine )
{
	$args = explode( " " , $cmdLine );
	$res = array();	
	for( $i = 0 ; $i < count( $args ) ; $i++ )
	{
		$str = $args[ $i ];
		if ( strlen( $str ) > 2 && substr( $str , 0 , 2 ) == '--' )
		{	
			$key = substr( $str , 2 );
			
			if( isset( $args[ $i + 1 ] ) && substr( $args[ $i + 1 ] , 0 ,2) != "--" )
			{
				$res[ $key ] = $args[ $i + 1 ];
				$i++;
			}
			else
			{
				$res[ $key ] = "";
			}
		}
		else
		{
			$last_index = array_keys( $res );
			if( $last_index ) 
			{
				$last_index = $last_index[ count( $last_index) - 1  ];
				$res[ $last_index ] .= " " .$str;
			}
			else
			{
				$res[ ] = $str;
			}
		}
	}
	return $res;
}

function error( $errorInfo = null )
{
		if ( $errorInfo )
		{
			return '<p class="error">' . $errorInfo . '</p>';
		}
		else
		{
			return '<p class="error">Syntax is : [subject] [--r recipient1[,recipient2,...]] [--m message]</p>';
		}
}

?>
<html>
	<head>
		<title>Quick Mail</title>
		<style type="text/css">
			#container{
				width : 600px;
				margin-right : auto;
				margin-left : auto;
				position:absolute;
				top:50%;
				left:50%;
				margin-left:-300px;
				margin-top : -200px;
			}		
			body {
				font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
				font-size: 16px; 
				border: 0px;
				margin: 20px;
				padding: 0px;
				background-color: white;
				color: #303030;
			}
			h1 {
				text-align : center;
				font-family: 'HelveticaNeue-UltraLight', 'Helvetica Neue UltraLight', 'Helvetica Neue', Arial, Helvetica, sans-serif;
				font-weight: 100;
				font-size: 40px; 
				margin : 0;
				margin-bottom: 10px;
			}
			input { 
				font-family: 'HelveticaNeue-UltraLight', 'Helvetica Neue UltraLight', 'Helvetica Neue', Arial, Helvetica, sans-serif;
				font-weight: 100;
				font-size: 18px; 
				letter-spacing: 1px;
				border: 3px solid #dddddd;
				margin: 0px 0px 0px 0px;
				padding: 2px;
				width : 600px;
			} 
			p, td, li, pre  {
				font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
				line-height: 1.2em;
				font-size : 15px;
				border: 0px;
				padding: 0px; 
				margin : 15px;
				font-weight: 200;
			}
      p {
        margin-left : 20px;
      }
      a {
        color:#303030;
        text-decoration:none;
        border-bottom: 1px dotted #303030;
      }
      .error {
        font-weight:100;
        color: #ffaaa8;
        font-size:18px;
      }
      .notif {
        font-weight:100;
        color: #7ba7ff;
        font-size:18px;
      }
		</style>
	</head>
	<body onload="document.getElementById('input').focus()">
		<div id="container">
			<h1>QuickMail</h1>
			<?php if($content) echo $content; ?>
			<form method="post" id="form">
				<input id="input" name="input"/>
			</form>
		</div>
	</body>
</html>
