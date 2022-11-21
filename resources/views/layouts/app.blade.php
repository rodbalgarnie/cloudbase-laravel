<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AWS Cloudbase</title>
   <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@300;500&display=swap" rel="stylesheet">
   
	
	<link href="{{ asset(mix('css/custom.css'),true) }}" rel="stylesheet">
	
	
	<meta name="robots" content="noindex" />   
	
</head>
<body>
		
<div id="app" > 
	<div class="loginpage">
		
	<b-navbar type="dark" variant="dark" style="position:absolute;left:0;width:100%">
    <b-navbar-brand href="/">
    <img src="images/cloudbaselogo.png" style="height:65px;padding:5px;">  
    </b-navbar-brand>
	</b-navbar>	

    <b-container>

    <b-row class="vh-100" align-v="center">
        
       <b-col class="col-12 col-sm-10 col-lg-8 offset-sm-1 offset-lg-2">

          <b-card
            class="mb-2 loginbox"
            >
            <b-row>
                <b-col class="col-12 text-center">
                    <h3>Please use ip-rtk-uk.com domain</h3>
                </b-col>
            </b-row>    
        </b-card>  

      </b-col>
   </b-row> 


<div class="fluid-container footer">
    <p class="text-center">Nick Abbey Digital Agriculture Ltd &copy 2022</p>
</div>

</b-container>


</div>
	</div>
	
<!--
        <main class="py-0">
            @yield('content') 
			
        </main>
-->

</body>
</html>