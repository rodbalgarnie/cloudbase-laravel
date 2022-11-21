@extends('layouts.app')
@section('content')

<?php if(isset($_GET['email'])){
	
	$url = explode('/',$_SERVER['REQUEST_URI']);
	$url = explode('?',$url[3]);
	$token = $url[0]; //$_GET['token'];
	$email = $_GET['email'];
	} else {
	$email = '';
	$token = '';
	}
	?>

	<div id="app">
		
		<b-container class="px-0" style="max-width:100%">
			<home :pwtoken="{{ json_encode($token) }}" :pwemail="{{ json_encode($email) }}" ></home>
		</b-container>
		
	</div>	 
	
    
@endsection   