<?php
/*
Enter your username and password into the array below

Example:

$userAccounts = array(
	array('user'=>bob','pass'=>'1234'),
	array('user'=>joe','pass'=>'5678'),
	);

Will allow bob access with the password 1234, and joe access with password 5678
*/


$userAccounts = array();




?>
<html>
	<head>
		<title>Restricted area</title>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
	</head>

	<body>

		<div class="container-fluid">

            <div class="row">

			    <div class="col-md-6 well well-large col-md-offset-3" style="margin-top:5%;">
			        
			        <h3><span class="glyphicon glyphicon-lock"></span> Secure Area</h3>
			        <br /><br />

			        <form method="post" name="login" class="form-horizontal" role="form" id="login">
						<div class="form-group ">
							<label class="col-sm-2&#x20;control-label">Username</label>
							<div class=" col-sm-10">
								<input name="username" type="text" placeholder="Enter&#x20;username" required="required" class="form-control" value="">
							</div>
						</div>	

						<div class="form-group ">
							<label class="col-sm-2&#x20;control-label">Password</label>
							<div class=" col-sm-10">
								<input name="password" type="password" required="required" placeholder="Password" class="form-control" value="">
							</div>
						</div>

						<div class="form-group "><div class=" col-sm-10 col-sm-offset-2">
							<button type="submit" name="button-submit" class="btn&#x20;btn-default" value="">
								Login
							</button>
						</div>

						</div>
					</form>        
			    </div>
			    
			</div>

        </div> <!-- /container -->


	</body>

</html>