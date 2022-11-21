<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>TEST</title>
<link href="https://ip-rtk-uk.com/css/mail.css" rel="stylesheet">	
</head>
<style>
	.header{
		width:700px;
		padding:10px;
		background-color:#999999;
	}
	
	.header img{
		height:60px;
	}
	
	.maintext{
		width:700px;
		font-size:1.2em;
		margin-top:50px;
	}
	
	.button{
		background-color:black;
		color:white;
		padding:10px;
		width:150px;
		margin-left:275px;
		cursor:pointer;
	}
</style>	

<body>
	
	<div class="header">
		<img src="{{ $logo }}">
	</div>
	
	
	<div class="maintext">
	<p>Hi {{ $user[0]['fname'] }},</p>
	<p>You are receiving this email because we received a password reset request for your account.</p>
	<p>This password reset link will expire in 60 minutes.If you did not request a password reset, no further action is required.</p>	
	</div>
	
	<div class="maintext" style="text-align:center">
			<div class="button"><a-href={{ $link }}>Reset Password</a></div>
	</div>
	
	<div class="maintext">
		<p>If you’re having trouble clicking the "Reset Password" button, click the link below or copy and paste the URL below into your web browser:</p>
		<p>{{ $link }}</p>
	</div>
	
	<div class="maintext">
		<p>Regards,</p>
		<p>{{ $user[0]['dealers']['contact'] }}</p>
	</div>
	
	<div class="maintext">
		<p>{{ $user[0]['dealers']['title'] }}<br/>
		{{ $user[0]['dealers']['email'] }}<br/>
		{{ $user[0]['dealers']['tel'] }}</p>
	</div>
		
	
</body>
</html>