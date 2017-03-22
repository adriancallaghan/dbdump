<?php
/*


Adrian Callaghan 21 Mar 2017 

Very lightweight database dumper that automatically locks access details provided by other PHP frameworks



****** How to use ******

Enter your usernames and passwords into the constructor in either plain text (insecure if read by someone else) or encypted



Example 1: allowing bob access with the password 1234 and joe access with password 5678 - no encryption 

	dbDump::Init(array(
		'users'	=> array(
			array('username'=>'bob','password'=>'1234'),
			array('username'=>'joe','password'=>'5678'),
		)));



Example 2: allowing bob access with the password 1234 and joe access with password 5678 - with encyption method and hashed passwords that must match the method result

	dbDump::Init(array(
		'users'	=> array(
			array('username'=>'bob','password'=>'81dc9bdb52d04dc20036dbd8313ed055'),
			array('username'=>'joe','password'=>'674f3c2c1a8a6f90461e8a66fb5550ba'),
		),
		'passwordEncryption'=>function($pass){
			return md5($pass);
		}
	));

*/




/******************

Constructor

*******************/
dbDump::Init(array(
	'users'	=> array(
		array('username'=>'bob','password'=>'81dc9bdb52d04dc20036dbd8313ed055'),
		array('username'=>'joe','password'=>'674f3c2c1a8a6f90461e8a66fb5550ba'),
	),
	'passwordEncryption'=>function($pass){
		return md5($pass);
	}
));







/******************

Class starts...

*******************/
final class dbDump{

	const APP_STATE = 'state';
	const APP_AUTH 	= 'MY_TOKEN';
	const APP_SALT  = 'MY_SALT';

	private $_users;
	private $_passwordEncryption;

	protected function destroySession(){
		$this->initSession();
		session_destroy();
		return $this;
	}

	protected function initSession(){
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		return $this;
	}

	protected function getSession($object = true){
		$this->initSession();
		return $object ? (object) $_SESSION : $_SESSION;
    }

   	protected function getSessionVar($var = ''){
   		return isset($this->session->{$var}) ? $this->session->{$var} : false;
    }

    protected function setSessionVar($key, $val){
    	$session 			= $this->session;
    	$session->{$key} 	= $val;
    	$this->session = (array) $session;
    }

    protected function setSession(array $values){
    	$this->initSession();
    	$_SESSION = $values;
    	return $this;
    }

	protected function getGet($object = true){
		return $object ? (object) $_GET : $_GET;
	}

	protected function getGetVar($var = ''){
   		return isset($this->post->{$var}) ? $this->post->{$var} : false;
    }

    protected function isPost(){
    	return empty($this->getPost(false)) ? false : true;
    }

	protected function getPost($object = true){
		return $object ? (object) $_POST : $_POST;
	}

	protected function getPostVar($var = ''){
   		return isset($this->post->{$var}) ? $this->post->{$var} : false;
    }

	protected function getState(){
		return $this->getSessionVar(self::APP_STATE);
	}

	protected function setState($state = ''){
		$this->setSessionVar(self::APP_STATE, $state);
		return $this;
	}

	protected function is_constant($token) {
	    return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING || $token == T_LNUMBER || $token == T_DNUMBER;
	}

	protected function strip($value) {
	    return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
	}

	protected function getDefinitions($php){

		$defines 	= array();
		$state 		= 0;
		$key 		= '';
		$value 		= '';
		$tokens 	= token_get_all($php);
		$token 		= reset($tokens);
		while ($token) {
		//    dump($state, $token);
		    if (is_array($token)) {
		        if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
		            // do nothing
		        } else if ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
		            $state = 1;
		        } else if ($state == 2 && $this->is_constant($token[0])) {
		            $key = $token[1];
		            $state = 3;
		        } else if ($state == 4 && $this->is_constant($token[0])) {
		            $value = $token[1];
		            $state = 5;
		        }
		    } else {
		        $symbol = trim($token);
		        if ($symbol == '(' && $state == 1) {
		            $state = 2;
		        } else if ($symbol == ',' && $state == 3) {
		            $state = 4;
		        } else if ($symbol == ')' && $state == 5) {
		            $defines[$this->strip($key)] = $this->strip($value);
		            $state = 0;
		        }
		    }
		    $token = next($tokens);
		}

		return $defines;

	}

	protected function generateDbForm($fields = null, $forceDisplay = false){

		if ($fields===null && !$forceDisplay){
			return;
		}

		$form = "<form method='post' name='dbForm' class='form-horizontal col-vert-20' role='form'>";

		$form.= "<div class='form-group'><label class='col-sm-2&#x20;control-label'>Host</label><div class='col-sm-10'><input name='host' type='text' placeholder='Enter host name' required='required' class='form-control' value='".(isset($fields->host) ? $fields->host : '')."'></div></div>";

		$form.= "<div class='form-group'><label class='col-sm-2&#x20;control-label'>Table</label><div class='col-sm-10'><input name='table' type='text' placeholder='Enter table name' required='required' class='form-control' value='".(isset($fields->table) ? $fields->table : '')."'></div></div>";

		$form.= "<div class='form-group'><label class='col-sm-2&#x20;control-label'>User</label><div class='col-sm-10'><input name='username' type='text' placeholder='Enter user name' required='required' class='form-control' value='".(isset($fields->username) ? $fields->username : '')."'></div></div>";

		$form.= "<div class='form-group'><label class='col-sm-2&#x20;control-label'>Password</label><div class='col-sm-10'><input name='password' type='text' placeholder='Enter password' required='required' class='form-control' value='".(isset($fields->password) ? $fields->password : '')."'></div></div>";

		$form.= "<div class='form-group'><div class='col-sm-12'><button type='submit' name='button-submit' class='btn&#x20;btn-success btn-lg pull-right' value=''>
		<span class='glyphicon glyphicon-download'></span>&nbsp;Download</button></div></div>";



		

		$form.= "</form>";


		return $form;
	}

	protected function getPlatforms(){

		$out 			= '<ul class="list-group">';
		$wordpressConf	= 'wp-config.php';
		$wordpressLogo  = '<img src="https://s.w.org/about/images/logos/wordpress-logo-32-blue.png" alt="WordPress logo" longdesc="WordPress Logo Stacked">';
		$c5Conf			= 'config/site.php';
		$c5Logo  		= '<img src="https://www.concrete5.org/files/3613/5517/8150/concrete5_Wordmark_200x37.png" alt="C5 logo" longdesc="C5 Logo Stacked">';
		$mysqlLogo      = '<img src="https://www.mysql.com/common/logos/logo-mysql-170x115.png" alt="mysql logo" longdesc="Mysql Logo stacked">';
		$error          = '<div class="pull-right text-warning"><span class="glyphicon glyphicon-warning-sign status"></span></div>';
		$notFound       = '<div class="pull-right text-danger"><span class="glyphicon glyphicon-remove status"></span></div>';
		$found          = '<div class="pull-right text-success"><span class="glyphicon glyphicon-ok status"></span></div>';


		$out.= '<li class="list-group-item">';
		if (file_exists($wordpressConf)){

			if (($fp = fopen($wordpressConf, "r"))!==false){
				$params = $this->getDefinitions(stream_get_contents($fp));
	      		$out.= $found.$wordpressLogo.$this->generateDbForm((object) array(
	      			'host'		=> isset($params['DB_HOST']) 		? $params['DB_HOST'] 		: '',
	      			'table'		=> isset($params['DB_NAME']) 		? $params['DB_NAME'] 	: '',
	      			'username'	=> isset($params['DB_USER']) 		? $params['DB_USER'] 	: '',
	      			'password' 	=> isset($params['DB_PASSWORD']) 	? $params['DB_PASSWORD'] 	: '',
	      		));
	      		fclose($fp);
	      	} else {
	      		$out.= $error.$wordpressLogo;
	      	}
		} else {
			$out.= $notFound.$wordpressLogo;
		}
		$out.= '</li>';


		$out.= '<li class="list-group-item">';
		if (file_exists($c5Conf)){

			if (($fp = fopen($c5Conf, "r"))!==false){
				$params = $this->getDefinitions(stream_get_contents($fp));
	      		$out.= $found.$c5Logo.$this->generateDbForm((object) array(
	      			'host'		=> isset($params['DB_SERVER']) 		? $params['DB_SERVER'] 		: '',
	      			'table'		=> isset($params['DB_DATABASE']) 	? $params['DB_DATABASE'] 	: '',
	      			'username'	=> isset($params['DB_USERNAME']) 	? $params['DB_USERNAME'] 	: '',
	      			'password' 	=> isset($params['DB_PASSWORD']) 	? $params['DB_PASSWORD'] 	: '',
	      		));
	      		fclose($fp);
	      	} else {
	      		$out.= $error.$c5Logo;
	      	}
		} else {
			$out.= $notFound.$c5Logo;
		}
		$out.= '</li>';


		$out.= '<li class="list-group-item">';
		$out.= $found.$mysqlLogo.$this->generateDbForm(null, true);
		$out.= '</li>';


		return $out.'</ul>';
	}

	protected function getHeader(){
		return '<html><head><title>Restricted area</title><link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"><script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script><script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script><META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW"><style>.col-vert-20{margin-top:20px;}.col-vert-100{margin-top:100px;}.status{font-size:30px;}</style></head><body><div class="container-fluid"><div class="row">';
	}

	protected function getFooter(){
		$footer = '</div></div>';

		if ($this->authentication) {

			$footer .= '<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				  <div class="modal-dialog modal-sm" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				        <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-lock"></span>&nbsp;Log out request.</h4>
				      </div>
				      <div class="modal-body">
				        Are you sure you wish to logout '.$this->authentication->username.'?
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				        <a type="button" class="btn btn-primary" href="?logout=true">Confirm</a>
				      </div>
				    </div>
				  </div>
				</div>
				</body></html>';
		}

		return $footer;
	}

	protected function loginForm($inErrorState = false){
		return '<div class="col-md-6 well well-large col-md-offset-3 col-vert-100"><h3><span class="glyphicon glyphicon-lock"></span> '.($inErrorState ? 'Access Denied' : 'Secure Area' ).'</h3><form method="post" name="login" class="form-horizontal col-vert-20" role="form"><div class="form-group '.($inErrorState ? 'has-error' : '').'"><label class="col-sm-2&#x20;control-label">Username</label><div class=" col-sm-10"><input name="username" type="text" placeholder="Enter&#x20;username" required="required" class="form-control" value="">'.($inErrorState ? '<ul class="help-block"><li>Invalid username &amp; password combination</li></ul>' : '').'</div></div><div class="form-group '.($inErrorState ? 'has-error' : '').'"><label class="col-sm-2&#x20;control-label">Password</label><div class=" col-sm-10"><input name="password" type="password" required="required" placeholder="Password" class="form-control" value="">'.($inErrorState ? '<ul class="help-block"><li>Invalid username &amp; password combination</li></ul>' : '').'</div></div><div class="form-group "><div class=" col-sm-10 col-sm-offset-2"><button type="submit" name="button-submit" class="btn&#x20;btn-default" value="">Login</button></div></div></form></div>';
	}

	protected function getDownloadOptions($inErrorState = false){ 
		return '<div class="col-md-6 col-md-offset-3 col-vert-100"><div class="panel panel-primary"><div class="panel-heading"><span class="glyphicon glyphicon-wrench"></span>&nbsp;Export options<button class="pull-right btn btn-danger btn-xs" data-toggle="modal" data-target="#confirmationModal">'.$this->authentication->username.'&nbsp;<span class="glyphicon glyphicon-remove-circle"></span></button></div>'.$this->platforms.'</div></div>'; 
	}

	protected function setUsers(array $users = null){
        if ($users==null){
            $users = array();
        }
        foreach($users AS $key=>$user){
        	$users[$key] = (object) $user;
        }
        $this->_users = (object) $users;
        return $this;
    }
    
    protected function getUsers(){
                
        if (!isset($this->_users)){
            $this->setUsers();
        }
        return $this->_users;
    }

	protected function setPasswordEncryption($function = null){
        if ($function==null){
            $function = function($val){return $val;};
        }
        $this->_passwordEncryption = $function;
        return $this;
    }
    
    protected function getPasswordEncryption(){
                
        if (!isset($this->_passwordEncryption)){
            $this->setPasswordEncryption();
        }
        return $this->_passwordEncryption;
    }

    protected function PasswordEncrypt($pass = ''){

    	$encryptor 	= $this->getPasswordEncryption();
    	return call_user_func($encryptor, $pass);
 
    }

    protected function generateUserToken(\StdClass $user){
    	return md5((isset($user->username) ? $user->username : uniqid()).self::APP_SALT.(isset($user->password) ? $user->password : uniqid()));
    }

    protected function setAuthentication(\StdClass $user){
    	$this->setSessionVar(self::APP_AUTH, $this->generateUserToken($user));
    	return $this;
    }

    protected function getAuthentication(){
    	$token 	= $this->getSessionVar(self::APP_AUTH);
    	foreach($this->users AS $validUser){
    		$validToken = $this->generateUserToken($validUser);
    		if ($token==$validToken){
    			return $validUser;
    		}
    	}
    	
    }

    protected function authenticateUser(\StdClass $user){

		foreach($this->users AS $validUser){

			if ($user->username==$validUser->username && $this->passwordEncrypt($user->password)==$validUser->password){
				$this->authentication 	= $validUser;
				$this->state 			= 'options';
				return $this->authentication;
			}
		}

    }

	public static function Init(array $options = array()){

		static $inst 	= null;
        if ($inst 		=== null) {
            $inst 		= new dbDump($options);
        }

        if (isset($inst->get->logout)){
        	$inst->destroySession();
        	header('Location: '.$_SERVER['PHP_SELF']);
        	die;
        }

        if (!$inst->authentication){
        	$inst->state = null;
        }

		switch($inst->state){
			case 'options':

				//var_dump($inst->authentication);
				if ($inst->isPost()){

					$hostname 	= $inst->getPostVar('host');
					$table 		= $inst->getPostVar('table');
					$username 	= $inst->getPostVar('username');
					$password 	= $inst->getPostVar('password');
					$command 	= "mysqldump --add-drop-table --host=$hostname --user=$username --password=$password $table";
					 
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename='.basename($table."_".date("Y-m-d_H-i-s").".sql"));
					system($command);
					exit();
				} else {
					echo $inst->header;					
					echo $inst->downloadOptions;
					echo $inst->footer;
				}
			break;
			default:

				if ($inst->isPost()){
					if ($inst->authenticateUser($inst->post)){
						$inst->state = 'options';
						header("Refresh:0");
						die;
					} else {
						echo $inst->header;
						echo $inst->loginForm(true);
						echo $inst->footer;
					}

				} else {
					echo $inst->header;
					echo $inst->loginForm();
					echo $inst->footer;
				}
			break;
		}

	}
    
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (method_exists($this, $method)) {
            $this->$method($value);
            return $this;
        }
                
        throw new \Exception('"'.$name.'" is an invalid property of '.__CLASS__.' assignment failed!');
        
    }

    public function __get($name)
    {
        
        $method = 'get' . $name;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        throw new \Exception('Invalid '.__CLASS__.' property '.$name);
    }
    
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }  

    private function __construct(array $options = null)
    {     
    	// env vars
    	set_time_limit(20);

        if (is_array($options)) {
            $this->setOptions($options);
        }

    }

	private function __clone(){}
}



			    
			
