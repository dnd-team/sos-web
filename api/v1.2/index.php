<?php
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(); 

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
	$api_key = $headers['Auth'];
    // Verifying Authorization Header
    if (isset($api_key)) { 
        $db = new DbHandler();

        // get the api key

        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
		}else {
		// api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is missing";
        echoResponse(400, $response);
        $app->stop();
    } 
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('surname', 'name', 'password', 'password_retry', 'email'));

            $response = array();

            // reading post params
            $first_name_input = $app->request->post('surname');
            $name_input = $app->request->post('name');
            $password_input = $app->request->post('password');
            $password_retry_input = $app->request->post('password_retry');
            $email_input = $app->request->post('email');
     
            $first_name = test_input($first_name_input);
            $name = test_input($name_input);
            $password = test_input($password_input);
            $password_retry = test_input($password_retry_input);
            $email = test_input($email_input);
        
            if(!($password === $password_retry)){
                $response["error"] = true;
                $response["message"] = 'Passwords do not match.';
                echoResponse(200, $response);
                $app->stop();
            }
        
            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createUser($first_name, $name, $password, $email);

            $response["error"] = $res[0];
            $response["message"] = $res[1];
            // echo json response
            echoResponse(201, $response);
        });


/**
 * Verify account
 * url - /verify
 * method - POST
 * params - verfication_code,email
 */
$app->post('/verify', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('verification_code','email'));

            // reading post params
            $verification_code_input = $app->request()->post('verification_code');
            $email_input = $app->request()->post('email');
    
            $verification_code = test_input($verification_code_input);
            $email = test_input($email_input);

            $response = array();

            $db = new DbHandler();
            // get the user by email
            $res = $db->verifyAccount($verification_code,$email);

            if($res){
                $response["error"] = false;
                $response["message"] = "Account successfully verified.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. User could not be verified.";
                echoResponse(200, $response);
            }
        });

/**
 * Password reset
 * url - /reset-password
 * method - POST
 * params - email
 */
$app->post('/reset-password', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email'));

            // reading post params
            $email_input = $app->request()->post('email');
    
            $email = test_input($email_input);

            $response = array();

            $db = new DbHandler();
            // get the user by email
            $user = $db->getUserByEmail($email);

            if ($user != NULL) {
                $email = $user['email'];
                if($db->resetPassword($email)){
                    $response["error"] = false;
                    $response['message'] = "Reset email successfully sent.";
                } else{
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }

            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "Email does not exist";
            }


            echoResponse(200, $response);
        });


/**
 * Change password
 * url - /password
 * method - PUT
 * params - email, password, password_retry
 */
$app->put('/password', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('verification_code','password','password_retry'));

            // reading post params
            $verification_code_input = $app->request()->post('verification_code');
            $password_input = $app->request()->post('password');
            $password_retry_input = $app->request()->post('password_retry');
    
            $verification_code = test_input($verification_code_input);
            $password = test_input($password_input);
            $password_retry = test_input($password_retry_input);

            if(!($password === $password_retry)){
                $response["error"] = true;
                $response["message"] = 'Passwords do not match.';
                echoResponse(200, $response);
                $app->stop();
            }

            $response = array();

            $db = new DbHandler();
            // get the user by email
            $res = $db->updatePassword($verification_code,$password);

            if($res){
                $response["error"] = false;
                $response["message"] = "Password updated successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Problem while updating password.";
                echoResponse(200, $response);
            }

        });


/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email_input = $app->request()->post('email');
    		$password_input = $app->request()->post('password');
    
            $email = test_input($email_input);
            $password = test_input($password_input);

            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['surname'] = $user['surname'];
                    $response['name'] = $user['username'];
                    $response['email'] = $user['email'];
                    $response['token'] = $user['token'];
                    $response['apiKey'] = $user['api_key'];
                    $response['verified'] = $user["verified"];
                    $response['joined_school'] = $user["joined_school"];
                    $response['logged_in'] = $user["logged_in"];
					$response['schoolid'] = $user['schoolid'];
					$response['version'] = $user['version'];
					$response['role'] = $user['role'];
                    $response['created_at'] = $user['created_at'];
                    $token = $db->getSchoolToken($user['schoolid']);
                    $response['baseURL'] = SOS_URL."preview/p?token=".$token;
                    //!IMPORTANT PLEASE CHANGE TO SCHOOL URL
                    //$response['school_url'] = SOS_URL."preview/p?token=".$token;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoResponse(200, $response);
        });

/*
 * ------------------------ METHODS WITH AUTHENTICATION ------------------------
 */

/**
 * Check if user has selected school and other things
 * url - /update
 * method - GET
 */
$app->get('/update', 'authenticate', function() {

            global $user_id;

            $response = array();
            $db = new DbHandler();

            $res = $db->getUpdateInformation($user_id);

            if($res){
                $response["error"] = false;
                $response["update"] = $res; 
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Couldn't fetch update information.";
                echoResponse(200, $response);
            }

        });

/**
 * Logout user
 * url - /logout
 * method - POST
 * params - device_token
 */
$app->post('/logout', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('device_token'));

			$device_token_input = $app->request->post('device_token');
    
            $device_token = test_input($device_token_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->logoutUser($user_id,$device_token);

            if($res){
                $response["error"] = false;
                $response["message"] = "User successfully logged out.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Cannot logout user.";
                echoResponse(200, $response);
            }

});


/** EXTRA FOR PIMP YOUR SCHOOL AND OTHER SCHOOL COMPETITONS ***/

$app->get('/competitions', 'authenticate', function() use ($app) {
    
            global $user_id;
    
            $response = array();
            $db = new DbHandler(); 
    
            $data = $db->getCompetitions($user_id);
    
            if($data){
                $response["error"] = false; 
                $response["competitions"] = $data;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching competitions.";
                echoResponse(200, $response);
            }

        });

$app->get('/competition/:token', 'authenticate', function($competition_token_input) use ($app) {
    
            $competition_token = test_input($competition_token_input);

            global $user_id;
    
            $response = array();
            $db = new DbHandler();
    
            $data = $db->getCompetition($user_id,$competition_token_input);
    
            if($data){
                $response["error"] = false; 
                $response["competition"] = $data;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching competition details.";
                echoResponse(200, $response);
            }

        });


$app->post('/competition/voted', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('competition_token'));

			$competition_token_input = $app->request->post('competition_token');
    
            $competition_token = test_input($competition_token_input); 

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->votedForCompetition($user_id,$competition_token);

            if($res){
                $response["error"] = false;
                $response["message"] = "Successfully voted for competetion. One SOS point added.";
                $response["points"] = $db->getVotingPoints($user_id);
                echoResponse(201, $response);
            } else { 
                $response["error"] = true;   
                $response["message"] = "Error. Problem while voting. Please try again.";
                echoResponse(200, $response);
            }

});

$app->post('/competition/vote/later', 'authenticate', function() use ($app) {
    
            verifyRequiredParams(array('competition_token'));

			$competition_token_input = $app->request->post('competition_token');
    
            $competition_token = test_input($competition_token_input); 

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->voteLaterForCompetition($user_id,$competition_token);

            if($res){
                $response["error"] = false;
                $response["message"] = "Will remind you on voting.";
                echoResponse(201, $response);
            } else { 
                $response["error"] = true;   
                $response["message"] = "Error. Problem while saving state. Please try again later.";
                echoResponse(200, $response);
            }

});
 
$app->get('/competition/points/', 'authenticate', function() use ($app) {

            global $user_id;
    
            $response = array();
            $db = new DbHandler();
    
            $data = $db->getVotingPoints($user_id);
    
            if($data || $data == 0){
                $response["error"] = false; 
                $response["points"] = $data;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching voting points.";
                echoResponse(200, $response);
            }

        });


/*** ***/


/**
 * Get school cities with federal states
 * url - /register/cities
 * method - GET
 */ 
$app->get('/register/cities', 'authenticate', function() use ($app) {

            global $user_id;
    
            $response = array();
            $db = new DbHandler();
    
            $data = $db->getFederalStatesWithCities($user_id);
    
            if($data){
                $response["error"] = false; 
                $response["federal_states"] = $data;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching cities.";
                echoResponse(200, $response);
            }

        });

/**
 * Get schools for city and federal state
 * url - /register/:state/:city/schools
 * method - GET
 */
$app->get('/register/:city/schools', 'authenticate', function($city_input) use ($app) {
    
            $city = test_input($city_input);

            global $user_id;
    
            $response = array(); 
            $db = new DbHandler();
    
            $data = $db->getSchoolSearchResults($user_id,$city);
    
            if($data){
                 $response["error"] = false;
                $response["schools"] = $data;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching schools.";
                echoResponse(200, $response);
            }

        });


/**
 * Select school for user
 * url - /register/school/verify
 * method - POST
 * params - school_name, school_password
 */
$app->post('/register/school/verify', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('school_token','school_password'));

			$school_token_input = $app->request->post('school_token');
			$school_password_input = $app->request->post('school_password');
    
            $school_token = test_input($school_token_input);
            $school_password = test_input($school_password_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->selectSchoolForUser($user_id,$school_token,$school_password);

            if($res == 2){
                $response["error"] = false;
                $response["message"] = "School selection saved successfully.";
                echoResponse(200, $response);
            } else if($res == 0) {
                $response["error"] = true;
                $response["message"] = "Error. Problem while saving school selection.";
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Wrong school password.";
                echoResponse(200, $response);
            }

        });


/**
 * Register new school
 * url - /register/school
 * method - POST
 * params - school_name, email, school_type, school_plz, school_city school_street, school_teacher
 */
$app->post('/register/school', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('school_name','email','teacher','school_plz'));

			$school_name_input = $app->request->post('school_name');
			$email_input = $app->request->post('email');
            $teacher_input = $app->request->post('teacher');
            $plz_input = $app->request->post('school_plz');
    
            $school_name = test_input($school_name_input);
            $email = test_input($email_input);
            $teacher = test_input($teacher_input);
            $plz = test_input($plz_input);
    
            $phone = $city = $street = "";
            if($app->request->post('phone') && !empty($app->request->post('phone'))){
                 $phone_input = $app->request->post('phone');
                 $phone = test_input($phone_input);
            }
            if($app->request->post('city') && !empty($app->request->post('city'))){
                 $city_input = $app->request->post('city');
                 $city = test_input($city_input);
            }
            if($app->request->post('street') && !empty($app->request->post('street'))){
                 $street_input = $app->request->post('street');
                 $street = test_input($street_input);
            }
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->registerSchool($user_id,$school_name,$email,$teacher,$plz,$phone,$city,$street);

            if($res){
                $response["error"] = false;
                $response["message"] = "School registered successfully.";
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while registering school.";
                echoResponse(200, $response);
            }

        });


/**
 * Add student to demo school
 * url - /register/school/demo
 * method - POST
 */
$app->post('/register/school/demo', 'authenticate', function() use ($app) {
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->selectDemoSchool($user_id);
    
            if($res){
                $response["error"] = false;
                $response["message"] = "School selection saved successfully.";
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while registering school.";
                echoResponse(200, $response);
            }

        });




/**
 * Get settings
 * url - /settings
 * method - GET
 */ 
$app->get('/account', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user homework
            $res = $db->getUserSettings($user_id);

            if($res){
                 $response["error"] = false;
                 $response["settings"] = $res;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching settings.";
                echoResponse(200, $response);
            }

        });


/**
 * Get settings
 * url - /settings
 * method - GET
 */
$app->get('/account', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user homework
            $res = $db->getUserSettings($user_id);

            if($res){
                 $response["error"] = false;
                 $response["settings"] = $res;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching settings.";
                echoResponse(200, $response);
            }

        });

/**
 * Get timetable with vp
 * url - /timetable/:daynumber
 * method - GET
 * params - daynumber
 */
$app->get('/timetable/:daynumber', 'authenticate', function($daynumber_input) use ($app) {
    
            $daynumber = test_input($daynumber_input);

            global $user_id;

            $response = array();
            $db = new DbHandler();

            $data = $db->getVP($user_id,$daynumber);


            if($data){
                $response["error"] = false;
                $response["vpURL"] = $data["vpURL"];
                $response["timetableURL"] = $data["timetableURL"];
                $response["teacher-timetableURL"] = $data["teacherURL"];
                $response["room-timetableURL"] = $data["roomURL"];
                $response["timetable"] = $data["timetable"];
                $response["additions"] = $data["additions"];
                $response["num_lessons"] = $data["num_lessons"];
                $response["created_at"] = $data["created_at"];
                $response["updated_at"] = $data["updated_at"];
                $response["isCurrentVP"] = $data["isCurrentVP"];
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error.";
                echoResponse(200, $response);
            }


        });


/**
 * Get timetable with vp
 * url - /timetable/:daynumber
 * method - GET
 * params - daynumber
 */
$app->get('/:school/vp/share/:daynumber', function($school_token_input,$daynumber_input) use ($app) {
    
            $school_token = test_input($school_token_input);
            $daynumber = test_input($daynumber_input);

            $response = array();
            $db = new DbHandler();

            $res = $db->shareVP($school_token,$daynumber);

            if($res){
                $response["error"] = false;
                $response["url"] = $res;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating link.";
                echoResponse(200, $response);
            }

        });

/**
 * Get timetable with vp
 * url - /timetable/:daynumber
 * method - GET
 * params - daynumber
 */
$app->get('/:school/timetable/share/:type', function($school_token_input,$type_input) use ($app) {
    
            $school_token = test_input($school_token_input);
            $type = test_input($type_input);

            $response = array();
            $db = new DbHandler();

            $res = $db->shareTimetable($school_token,$type);

            if($res){
                $response["error"] = false;
                $response["url"] = $res;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating link.";
                echoResponse(200, $response);
            }

        });


/**
 * Get timetable with vp for entire week
 * url - /timetable/week/
 * method - GET
 */
$app->get('/timetable/week/', 'authenticate', function() {

            global $user_id;

            $response = array();
            $db = new DbHandler();

            $data = $db->getVPForAllDays($user_id);

            if($data){
                $response["error"] = false;
                $response["content"] = $data;
                echoResponse(200, $response);
            }
            else {
                $response["error"] = true;
                $response["message"] = "Error.";
                echoResponse(200, $response);
            }
        });



/**
 * Check if user has logged in before
 * url - /logged_in
 * method - GET
 */
$app->get('/timetable/teachers/', 'authenticate', function() {

            global $user_id;

            $response = array();
            $db = new DbHandler();

            $res = $db->createTeacherPlans("LP100");

            if($res){
                $response["error"] = false;
                $response["message"] = $res;
                echoResponse(200, $response);
            }
            else {
                $response["error"] = true;
                $response["message"] = "Error.";
                echoResponse(200, $response);
            }
        });

/**
 * Get aushang json
 * url - /aushang
 * method - GET
 */
$app->get('/aushang', 'authenticate', function(){

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user problems
            $data = $db->getAushang($user_id);
        
            if($data){
                $response["error"] = false;
          	    $response["aushang"] = $data;
                echoResponse(200, $response);
            }
            else {
                $response["error"] = true;
                $response["message"] = "No results.";
                echoResponse(200, $response);
            }

        });


/**
 * Get aushang for user
 * url - /aushang/user
 * method - GET
 */
$app->get('/aushang/user', 'authenticate', function(){

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user problems
            $data = $db->getUserAushang($user_id);

            if($data){
                $response["error"] = false;
          	    $response["aushang"] = $data;
                echoResponse(200, $response);
            }
            else {
                $response["error"] = true;
                $response["message"] = "No results.";
                echoResponse(200, $response);
            }

        });



/**
 * Delete user aushang
 * url - /aushang/user/aushangid
 * method - DELETE
 */
$app->delete('/aushang/user/:id', 'authenticate', function($aushang_token_input) use($app){
        
            $aushang_token = test_input($aushang_token_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user problems
            $res = $db->deleteUserAushang($user_id,$aushang_token);

            if($res){
                $response["error"] = false;
          	    $response["message"] = "Aushang deleted successfully.";
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while deleting aushang.";
                echoResponse(200, $response);
            }
        });


/**
 * Create new aushang ad
 * url - /aushang/student/ad
 * method - POST
 * params - title, desc, action_type, action_url
 */
$app->post('/aushang/student/ad', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('title','description','action_type'));

			$title_input = $app->request->post('title');
			$desc_input = $app->request->post('description');
            $grades = "";
    
            $title = test_input($title_input);
            $desc = test_input($desc_input);

            if($app->request->post('grades') && !empty($app->request->post('grades'))){
                 $grades_input = $app->request->post('grades');
                 $grades = test_input($grades_input);
            }

            $action_url = "";

            if($app->request->post('action_url') && !empty($app->request->post('action_url'))){
                 $action_url_input = $app->request->post('action_url');
                 $action_url = test_input($action_url_input);
            }

            $action_type_input = $app->request->post('action_type');
            $action_type = test_input($action_type_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->createAd($user_id,$title,$desc,$grades,$action_type,$action_url);

            if($res){
                $response["error"] = false;
                $response["message"] = "Created student ad successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error.";
                echoResponse(200, $response);
            }

        });


/**
 * Share aushang ad
 * url - /aushang/share/:id
 * method - GET
 * params - aushang_id
 */
$app->get('/aushang/share/:id', 'authenticate', function($aushang_token_input) use($app) {
        
            $aushang_token = test_input($aushang_token_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            // get share url
            $res = $db->shareAushang($user_id,$aushang_token);

             if ($res) {
                $response["error"] = false;
                $response["url"] = $res;
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating link.";
                echoResponse(200, $response);
            }
        });


/**
 * Get questions
 * url - /questions
 * method - GET
 */ 
$app->get('/questions', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user homework
            $res = $db->getQuestions();

            if($res){
                 $response["error"] = false;
                 $response["questions"] = $res;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "No results.";
                echoResponse(200, $response);
            }

        });


/**
 * Ask Question
 */
$app->post('/questions', function() use ($app) {

			verifyRequiredParams(array('question','author'));

			$question_input = $app->request->post('question');
    
            $question = test_input($question_input);
    
            $author_input = $app->request->post('author');
    
            $author = test_input($author_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->saveQuestion($question,$author);
    
            if($res){
                $response["error"] = false;
                $response["saved"] = $res;
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while saving question.";
                echoResponse(200, $response);
            }

        });



/**
 * Get school url
 * url - /school/url
 * method - GET
 */
$app->get('/school/url', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all school grades
            $url = $db->getSchoolPreviewURLForUser($user_id);

            if($url){
                $response["error"] = false;
          	    $response["url"] = $url;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching school url.";
                echoResponse(200, $response);
            }

        });

/**
 * Get school website
 * url - /school/website
 * method - GET
 */
$app->get('/school/:token/website', function($school_token_input) {

            $school_token = test_input($school_token_input);
    
            $response = array();
            $db = new DbHandler();

            // fetching all school grades
            $url = $db->getSchoolURL($school_token);

            if($url){
                $response["error"] = false;
          	    $response["url"] = $url;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching school website.";
                echoResponse(200, $response);
            }

        });



/**
 * Get teachers for school
 * url - /school/teachers
 * method - GET
 */
$app->get('/school/teachers', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all school grades
            $teachers = $db->getTeachers($user_id);

            if($teachers){
                $response["error"] = false;
          	    $response["teachers"] = $teachers;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching teachers.";
                echoResponse(200, $response);
            }

        });


/**
 * Save Teacher selection
 * url - /school/teachers/save
 * method - POST
 * params - teacher_id
 */
$app->post('/school/teachers/save', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('teacher_id'));

			$teacher_id_input = $app->request->post('teacher_id');
    
            $teacher_id = test_input($teacher_id_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->saveTeacherSelection($user_id,$teacher_id);

            if($res){
                $response["error"] = false;
                $response["saved"] = $res;
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while saving teacher_id.";
                echoResponse(200, $response);
            }

        });

/**
 * Get grades for school
 * url - /school/grades
 * method - GET
 */
$app->get('/school/grades', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all school grades
            $grades = $db->getGrades($user_id);

            if($grades){
                $response["error"] = false;
          	    $response["grades"] = $grades;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching school grades.";
                echoResponse(200, $response);
            }

        });




/**
 * Get courses for grade of school
 * url - /school/:grade/courses
 * method - GET
 * params - grade
 */
$app->get('/school/:grade/courses', 'authenticate', function($grade_input) use ($app) {
    
            $grade = test_input($grade_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all courses for a specific grade
            $courses = $db->getCoursesForGrade($grade,$user_id);

            if($courses){
                $response["error"] = false;
          	    $response["courses"] = $courses;
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching courses.";
                echoResponse(200, $response);
            }
        });




/**
 * Save Users Selection of grade and courses
 * url - /school/:grade/courses/save
 * method - POST
 * params - grade, course_list
 */
$app->post('/school/:grade/courses/save', 'authenticate', function($grade_input) use ($app) {
    
            $grade = test_input($grade_input);

			verifyRequiredParams(array('course_list'));

			$course_list_input = $app->request->post('course_list');
    
            $course_list = test_input($course_list_input);
            //explode course_list by separation string ','
            $courseList = explode(',',$course_list);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->saveUserSelection($user_id,$grade,$courseList);

            if($res){
                $response["error"] = false;
                $response["saved"] = $res;
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while saving grade and courses.";
                echoResponse(200, $response);
            }

        });



$app->post('/courses/merge', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('course_id','grade','new_course_id'));

			$course_id_input = $app->request->post('course_id');
            //explode course_list by separation string ','
            $grade_input = $app->request->post('grade');
            $new_course_id_input = $app->request->post('new_course_id');
    
            $course_id = test_input($course_id_input);
            $grade = test_input($grade_input);
            $new_course_id = test_input($new_course_id_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->mergeCourseMembers($user_id,$grade,$course_id,$new_course_id);

            if($res){
                $response["error"] = false;
                $response["saved"] = $res;
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while saving grade and courses.";
                echoResponse(200, $response);
            }

        });


/**
 * Get courses for user
 * url - /courses
 * method - GET
 */
$app->get('/courses', 'authenticate', function(){

            $response = array();

            global $user_id;
            $db = new DbHandler();
            $response = array();

            // get courses for user
            $res = $db->getCourseList($user_id);

            if($res){
                $response["error"] = false;
                $response["courses"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching courses for user.";
                echoResponse(200, $response);
            }

        });

/**
 * Get courses for homework
 * url - /homework/selectedcourses/
 * method - GET
 */
$app->get('/homework/selectedcourses/', 'authenticate', function(){

            $response = array();

            global $user_id;
            $db = new DbHandler();
            $response = array();

            // get courses for user
            $res = $db->getCourseIdList($user_id);

            if($res){
                $response["error"] = false;
                $response["courses"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching courses for user.";
                echoResponse(200, $response);
            }

        });


/**    
 * Get tasks for course
 * url - /course/:id/tasks
 * method - GET     
 * params - course_id
 */
$app->get('/course/:id/tasks', 'authenticate', function($course_token_input) use($app) {
    
            $course_token = test_input($course_token_input);

            global $user_id; 
            $db = new DbHandler();
            $response = array(); 

            // get tasks for course
            $res = $db->getTasksForCourse($course_token, $user_id);
    
            $response["error"] = false;
            $response["tasks"] = $res;
            echoResponse(200, $response);

        });


/**       
 * Get tasks for course
 * url - /course/:id/tasks
 * method - GET     
 * params - course_id
 */
$app->get('/course/:id/details', 'authenticate', function($course_token_input) use($app) {
    
            $course_token = test_input($course_token_input);

            global $user_id; 
            $db = new DbHandler();
            $response = array(); 

            // get tasks for course
            $res = $db->getCourseDetails($user_id,$course_token);
    
            if($res){
                $response["error"] = false;
                $response["details"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching details for course.";
                echoResponse(200, $response);
            }

        });




/**
 * Get members for course
 * url - /course/:id/members
 * method - GET
 * params - course_id
 */
$app->get('/course/:id/members', 'authenticate', function($course_token_input) use($app) {
    
            $course_token = test_input($course_token_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            // get tasks for course
            $res = $db->getCourseMembers($course_token, $user_id);

            if($res && (count($res) > 0)){
                $response["error"] = false;
                $response["members"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching members for course.";
                echoResponse(200, $response);
            }


        });

/**
 * Delete Course Member
 * url - /course/:id/member
 * method - DELETE
 * params - member_id
 */
$app->delete('/course/:id/members/:memberid', 'authenticate', function($course_token_input,$member_token_input) use($app) {

            $course_token = test_input($course_token_input);
            $member_token = test_input($member_token_input);
    
            global $user_id;

            $response = array();
            $db = new DbHandler();

            $res = $db->removeCourseMember($user_id,$course_token,$member_token);

            if($res){
                $response["error"] = false;
          	    $response["message"] = "Member removed successfully.";
                echoResponse(200, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while removing course member.";
                echoResponse(200, $response);
            }

        });


/**
 * Get entries for course
 * url - /course/:id/entries
 * method - GET
 * params - course_id
 */
$app->get('/course/:id/entries', 'authenticate', function($course_token_input) use($app) {
    
            $course_token = test_input($course_token_input);

            global $user_id;
            $db = new DbHandler();
            $response = array(); 

            // get tasks for course
            $res = $db->getEntriesForCourse($course_token, $user_id);

            if($res || count($res) == 0){
                $response["error"] = false; 
                $response["entries"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["entries"] = "Error. Problem while fetching entries for course.";
                echoResponse(200, $response);
            }

        });


/**
 * Crete entry
 * url - /entry/save
 * method - POST
 * params - course_id, title, description
 */
$app->post('/entries/save', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('course_id','title', 'description'));
			$course_token_input = $app->request->post('course_id');
			$title_input = $app->request->post('title');
			$desc_input = $app->request->post('description');

            $course_token = test_input($course_token_input);
            $title = test_input($title_input);
            $desc = test_input($desc_input);
            
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->createEntry($user_id,$course_token,$title,$desc);

            if($res){
                 $response["error"] = false;
          	     $response["message"] = "Entry added successfully.";
                 echoResponse(201, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating entry.";
                echoResponse(200, $response);
            }

        });

 
/**
 * Update entry
 * url - /entries/:id
 * method - PUT
 * params - title, description
 */
$app->put('/entries/:id', 'authenticate', function($entry_token_input) use($app) {
    
            verifyRequiredParams(array('title', 'description')); 
    
            $entry_token = test_input($entry_token_input);
			$title_input = $app->request->put('title');
			$desc_input = $app->request->put('description');
    
            $title = test_input($title_input);
            $desc = test_input($desc_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            $res = $db->updateEntry($user_id, $entry_token, $title, $desc);

            if ($res) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Entry updated successfully.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Entry failed to update. Please try again!";
            }
            echoResponse(200, $response);

        });



/**
 * Delete entry
 * url - /entries/:id
 * method - DELETE
 * params - entry_id
 */
$app->delete('/entries/:id', 'authenticate', function($entry_token_input) use($app) {

            $entry_token = test_input($entry_token_input);
    
            global $user_id;

            $db = new DbHandler();
            $response = array();
            $res = $db->deleteEntry($user_id, $entry_token);

            if ($res) {
                // task deleted successfully
                $response["error"] = false;
                $response["message"] = "Entry deleted successfully.";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Entry failed to delete. Please try again!";
            }
            echoResponse(200, $response);

        });




/**
 * Get teacher tasks
 * url - /course/:id/teacher/tasks
 * method - GET
 * params - course_id
 */
$app->get('/course/:id/teacher/tasks', 'authenticate', function($course_token_input) use($app) {
    
            $course_token = test_input($course_token_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            // get teacher tasks
            $res = $db->getTasksForTeacher($course_token, $user_id);

            if($res){
                 $response["error"] = false;
                 $response["tasks"] = $res;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching tasks for teacher.";
                echoResponse(200, $response);
            }

        });

// /**
//  * Crete teacher task
//  * url - /tasks/save
//  * method - POST
//  * params - course_id, name, desc, expire_date
//  */
// $app->post('/tasks/:id/delete/cancel', 'authenticate', function(task_token_input) use ($app) {

//             $task_token = test_input($task_token_input);

//             global $user_id;
//             $response = array();
//             $db = new DbHandler();

//             $res = $db->cancelTaskDeletion($user_id,$task_token);

//             if($res){
//                  $response["error"] = false;
//           	     $response["canceled"] = $res;
//                  echoResponse(201, $response);
//             }
//             else{
//                 $response["error"] = true;
//                 $response["message"] = "Error. Problem while canceling task deletion.";
//                 echoResponse(200, $response);
//             }


//         });


/**
 * Crete teacher task
 * url - /tasks/save
 * method - POST
 * params - course_id, name, desc, expire_date
 */
$app->post('/tasks/save', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('course_id','name','expire_date'));
			$course_token_input = $app->request->post('course_id');
			$title_input = $app->request->post('name');
            $expire_date_input = $app->request->post('expire_date');
        
            $course_token = test_input($course_token_input);
            $title = test_input($title_input);   
            $expire_date = test_input($expire_date_input);
            $course_token = test_input($course_token_input);

            $file_token_list = array();
            if($app->request->post('file_token_list') && !empty($app->request->post('file_token_list'))){
                $file_token_str_input = $app->request->post('file_token_list');
                $file_token_str = test_input($file_token_str_input);
                $file_token_list = explode(',',$file_token_str);
            }

            $desc = "";
            if($app->request->post('description') && !empty($app->request->post('description'))){
                $desc_input = $app->request->post('description');
                $desc = test_input($desc_input);
            }
    
            $send_notification = false;
            if($app->request->post('send_notification') && !empty($app->request->post('send_notification'))){
                 $send_notification_input = $app->request->post('send_notification');
                 $send_notification = test_input($send_notification_input);
                 if($send_notification){
                     $send_notification = true;
                 }
            }

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->createTeacherTask($user_id,$course_token,$title,$desc,$expire_date,$file_token_list,$send_notification);

            if($res){
                 $response["error"] = false;
          	     $response["task_id"] = $res;
                 echoResponse(201, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating task.";
                echoResponse(200, $response);
            }


        });


/**
 * Update task
 * url - /tasks/:id
 * method - PUT
 * params - task_id, name, desc, expire_date
 */
$app->put('/tasks/:id', 'authenticate', function($task_token_input) use($app) {

            $task_token = test_input($task_token_input);
    
			verifyRequiredParams(array('name','description','expire_date'));

			$name_input = $app->request->put('name');
			$desc_input = $app->request->put('description');
            $expire_date_input = $app->request->put('expire_date');
    
            $name = test_input($name_input);
            $desc = test_input($desc_input);
            $expire_date = test_input($expire_date_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            $res = $db->updateTask($user_id, $task_token, $name,$desc,$expire_date);

            if ($res) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task updated successfully.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to update. Please try again!";
            }
            echoResponse(200, $response);

        });



/**
 * Delete task
 * url - /tasks/:id
 * method - DELETE
 * params - task_id
 */
$app->delete('/tasks/:id', 'authenticate', function($task_token_input) use($app) {
    
            $task_token = test_input($task_token_input);

            global $user_id;

            $db = new DbHandler();
            $response = array();
            $res = $db->deleteTask($user_id, $task_token);

            if ($res) {
                // task deleted successfully
                $response["error"] = false;
                $response["message"] = "Task deleted successfully.";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Task failed to delete. Please try again!";
            }
            echoResponse(200, $response);

        });

/**
 * Delete old tasks
 * url - /tasks/delte/:token
 * method - DELETE
 * params -
 */
$app->delete('/tasks/old/:token', function($token_input) use ($app) {
           $token = test_input($token_input);
           if($token == "2SdQFPptwJLEg2qj"){
               $response = array();
               $db = new DbHandler();

               $res = $db->deleteOldTasks(); 

               if($res){
                    $response["error"] = false;
                    $response["deleted"] = $res;
                    echoResponse(201, $response);
               }
               else{
                   $response["error"] = true;
                   $response["message"] = "Error. Problem while deleting old tasks.";
                   echoResponse(200, $response);
               }
           }



       });


/**
 * Get tasks files
 * url - /task/:id/files
 * method - GET
 * params - task_id
 */
$app->get('/task/:id/files', 'authenticate', function($task_token_input) use($app) {

            $task_token = test_input($task_token_input);
    
            global $user_id;
            $db = new DbHandler();
            $response = array();
            
            // fetching all files for task
            $res = $db->getFilesForTask($task_token, $user_id);
    
            if ($res) {
                $response["error"] = false;
                $response["files"] = $res;
                echoResponse(200, $response);
            } else { 
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching files for task.";
                echoResponse(200, $response);
            }

    }); 


/**** Events *** /
 
/**
 * Get events  
 * url - /events 
 * method - GET 
 */
$app->get('/events', 'authenticate', function() use($app) {

            $response = array();
            $db = new DbHandler();

            $date = "";
            if($app->request->get('date') && !empty($app->request->get('date'))){
                $date_input = $app->request->get('date');
                $date = test_input($date_input);
            }
            global $user_id;
    
            // fetching all events (otpional for date)
            $res = $db->getEvents($user_id,$date,false);

            if($res){
                 $response["error"] = false;
                 $response["events"] = $res;
                 if($res == "No events."){
                     $response["events"] = array();
                 }
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching events.";
                echoResponse(200, $response);
            }

        });


/**
 * Get 20 next events
 * url - /events/list
 * method - GET
 */
$app->get('/events/list/', 'authenticate', function() use($app) {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $limit = 20;
            if($app->request->get('limit') && !empty($app->request->get('limit'))){
                $limit_input = $app->request->get('limit');
                $limit = test_input($limit_input);
            }
            $date = date("Y-m-d");

            // fetching all events (otpional for date)
            $res = $db->getEvents($user_id,$date,$limit);

            if($res){
                 $response["error"] = false;
                 $response["events"] = $res;
                 if($res == "No events."){
                     $response["events"] = array();
                 }
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching events.";
                echoResponse(200, $response);
            }

        });

/**
 * Join Event
 * url - /event/join
 * method - POST
 * params - event_id
 */
$app->post('/event/:token/join', 'authenticate', function($event_token_input) use ($app){

            $event_token = test_input($event_token_input);
            
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->joinEvent($user_id,$event_token);
            if($res) {
                $response["error"] = false;
                $response["message"] = "Joined event successfully.";
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while joining event.";
                echoResponse(200, $response);
            }
        });



/*** Homeworks ***/

/**
 * Get homework
 * url - /homework
 * method - GET
 */
$app->get('/homework', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user homework
            $res = $db->getHomework($user_id,0);

            if($res){
                 $response["error"] = false;
                 $response["homework"] = $res;
                 if($res == "No homework."){
                     $response["homework"] = array();
                 }
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching homework.";
                echoResponse(200, $response);
            }

        });


/**
 * Get homework which is done
 * url - /homework/done
 * method - GET
 */
$app->get('/homework/done', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user homework
            $res = $db->getHomework($user_id,1);

            if($res){
                 $response["error"] = false;
                 $response["homework"] = $res;
                 if($res == "No homework."){
                     $response["homework"] = array();
                 }
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching homework.";
                echoResponse(200, $response);
            }

        });


/**
 * Create new homework
 * url - /homework/save
 * method - POST
 * params - title, desc, expire_date, first_reminder, second_reminder
 */
$app->post('/homework/save', 'authenticate', function() use ($app){
    
			verifyRequiredParams(array('title','expire_date'));

			$title_input = $app->request->post('title');
            $expire_date_input = $app->request->post('expire_date');
    
            $title = test_input($title_input);
            $expire_date = test_input($expire_date_input);

            $course = $first_reminder = $second_reminder = $desc = "";
            if($app->request->post('course') && !empty($app->request->post('course'))){
                $course_input = $app->request->post('course');
                $course = test_input($course_input);
            }
            if($app->request->post('first_reminder') && !empty($app->request->post('first_reminder'))){
                 $first_reminder_input = $app->request->post('first_reminder');
                 $first_reminder = test_input($first_reminder_input);
            }
            if($app->request->post('second_reminder') && !empty($app->request->post('second_reminder'))){
                $second_reminder_input = $app->request->post('second_reminder');
                $second_reminder = test_input($second_reminder_input);
            }
            if($app->request->post('description') && !empty($app->request->post('description'))){
                $desc_input = $app->request->post('description');
                $desc = test_input($desc_input);
            }
            
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->createHomework($user_id,$title,$desc,$course,$expire_date,$first_reminder,$second_reminder);
            if($res) {
                $response["error"] = false;
                $response["message"] = "Homework added successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating homework.";
                echoResponse(200, $response);
            }
        });

/**
 * Update homework
 * url - /homework/:id
 * method - PUT
 * params - title, desc, expire_date, first_reminder, second_reminder
 */
$app->put('/homework/:id', 'authenticate', function($homework_token_input) use($app) {
    
            $homework_token = test_input($homework_token_input);

			verifyRequiredParams(array('title','description','expire_date'));

			$title_input = $app->request->post('title');
            $desc_input = $app->request->post('description');
            $expire_date_input = $app->request->post('expire_date');

            $title = test_input($title_input);
            $desc = test_input($desc_input);
            $expire_date = test_input($expire_date_input);
    
            $course = $first_reminder = $second_reminder = "";
            if($app->request->post('course') && !empty($app->request->post('course'))){
                $course_input = $app->request->post('course');
                $course = test_input($course_input);
            }
            if($app->request->post('first_reminder') && !empty($app->request->post('first_reminder'))){
                $first_reminder_input = $app->request->post('first_reminder');
                $first_reminder = test_input($first_reminder_input);
            }

            if($app->request->post('second_reminder') && !empty($app->request->post('second_reminder'))){
                $second_reminder_input = $app->request->post('second_reminder');
                $second_reminder = test_input($second_reminder_input);
            }

            global $user_id;
            $db = new DbHandler();
            $response = array();

            $res = $db->updateHomework($user_id,$homework_token,$title,$desc,$course,$expire_date,$first_reminder,$second_reminder);

            if ($res) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Homework updated successfully.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Homework failed to update. Please try again!";
            }
            echoResponse(200, $response);

        });


$app->put('/homework/:id/done', 'authenticate', function($homework_token_input) use($app) {

            $homework_token = test_input($homework_token_input);
    
			verifyRequiredParams(array('done'));

            $done_input = $app->request->post('done');
    
            $done = test_input($done_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            $res = $db->setHomeworkDone($user_id,$homework_token,$done);

            if ($res) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Homework updated successfully.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Homework failed to update. Please try again!";
            }
            echoResponse(200, $response);

        });


/**
 * Delete homework
 * url - /homework/:id
 * method - DELETE
 * params - homework_id
 */
$app->delete('/homework/:id', 'authenticate', function($homework_token_input) use($app) {
    
            $homework_token = test_input($homework_token_input);

            global $user_id;

            $db = new DbHandler();
            $response = array();

            $res= $db->deleteHomework($user_id, $homework_token);

            if ($res) {
                // homework deleted successfully
                $response["error"] = false;
                $response["message"] = "Homework deleted successfully.";
            } else {
                // homework failed to delete
                $response["error"] = true;
                $response["message"] = "Homework failed to delete. Please try again!";
            }
            echoResponse(200, $response);

        });

/**
 * Share homework
 * url - /homework/share/:id
 * method - GET
 * params - homework_id
 */
$app->get('/homework/share/:id', 'authenticate', function($homework_token_input) use($app) {
    
            $homework_token = test_input($homework_token_input);

            global $user_id;
            $db = new DbHandler();
            $response = array();

            // get share url
            $res = $db->shareHomework($user_id,$homework_token);

             if ($res) {
                $response["error"] = false;
                $response["url"] = $res;
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating link.";
                echoResponse(200, $response);
            }
        });


/**
 * Share homework with selected users
 * url - /homework/share/:id
 * method - POST
 * params - member_list
 */
$app->post('/homework/share/:id', 'authenticate', function($homework_token_input) use ($app) {

            $homework_token = test_input($homework_token);
    
			verifyRequiredParams(array('member_list'));

			$member_list_input = $app->request->post('member_list');
    
            $member_list = test_input($member_list_input);
            //explode course_list by separation string ','
            $member_list = explode(',',$member_list);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->shareHomeworkWithMembers($user_id,$homework_token,$member_list);

            if($res){
                $response["error"] = false;
                $response["shared"] = $res;
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while sharing homework with members.";
                echoResponse(200, $response);
            }

        });

/**
 * Share homework with entire course
 * url - /homework/share/:id/course
 * method - POST
 * params - course 
 */
$app->post('/homework/share/:id/course', 'authenticate', function($homework_token_input) use ($app) {
    
            $homework_token = test_input($homework_token_input);

			verifyRequiredParams(array('course'));

			$course_abbreviation_input = $app->request->post('course');
    
            $course_abbreviation = test_input($course_abbreviation_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->shareHomeworkWithCourse($user_id,$homework_token,$course_abbreviation);

            if($res){
                $response["error"] = false;
                $response["shared"] = $res;
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while sharing homework with course.";
                echoResponse(200, $response);
            }

        });



/**
 * Accept homework invitation
 * url - /homework/share/:id/accept
 * method - POST
 * params -
 */
$app->post('/homework/share/:id/accept', 'authenticate', function($homework_token_input) use ($app) {
    
            $homework_token = test_input($homework_token_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->acceptHomeworkInvitation($user_id,$homework_token);

            if($res){
                $response["error"] = false;
                $response["message"] = "Invitation accepted successfully.";
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while accepting invitation.";
                echoResponse(200, $response);
            }

        });

/**
 * Decline homework invitation
 * url - /homework/share/:id/decline
 * method - POST
 * params -
 */
$app->post('/homework/share/:id/decline', 'authenticate', function($homework_token_input) use ($app) {

            $homework_token = test_input($homework_token_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->declineHomeworkInvitation($user_id,$homework_token);

            if($res){
                $response["error"] = false;
                $response["message"] = "Invitation declined successfully.";
                echoResponse(201, $response);
            } else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while declining invitation.";
                echoResponse(200, $response);
            }

        });

/*** Consultations ***/

/**
 * Get consultations
 * url - /consultations
 * method - GET
 */
$app->get('/consultations', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all consultations
            $res = $db->getConsultations($user_id);
            if($res){
                 $response["error"] = false;
                // $response["consultation"] = $res;
                 $response["consultation"] = array();
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching consultations.";
                echoResponse(200, $response);
            }

        });

/**
 * Get consultations for teacher
 * url - /consultations/teacher
 * method - GET
 */
$app->get('/consultations/teacher', 'authenticate', function() {

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all consultations
            $res = false;
            if($res){
                 $response["error"] = false;
                // $response["consultation"] = $res;
                 $response["consultation"] = array();
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching consultations.";
                echoResponse(200, $response);
            }

        });

/**
 * Get appointments for teacher
 * url - /consultation/:id
 * method - GET
 * params - teacher_id
 */
$app->get('/consultation/:id', 'authenticate', function($teacher_id_input) use($app) {

            $teacher_id = test_input($teacher_id_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            //Get appointments for teacher
            $res = $db->getConsultation($user_id,$teacher_id);

            if($res){
                 $response["error"] = false;
                 $response["appointments"] = $res;
                 echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching free appointments.";
                echoResponse(200, $response);
            }

        });

/**
 * Register new guest for consultation
 * url - /consultation/register/guest
 * method - POST
 * params - appointment_id, guest_name, guest_email
 */
$app->post('/consultation/register/guest', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('appointment_id','guest_name','guest_email'));

			$ct_id_input = $app->request->post('appointment_id');
            $guest_name_input = $app->request->post('guest_name');
            $guest_email_input = $app->request->post('guest_email');
    
            $ct_id = test_input($ct_id_input);
            $guest_name = test_input($guest_name_input);
            $guest_email = test_input($guest_email_input);

            $response = array();
            $db = new DbHandler();

            $res = $db->registerGuestForConsultation($guest_name,$guest_email,$ct_id);

            if($res) {
                $response["error"] = false;
                $response["message"] = "Guest registerd successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while registering guest.";
                echoResponse(200, $response);
            }
        });

/**
 * Register registered user for consultation
 * url - /consultation/register
 * method - POST
 * params - appointment_id
 */
$app->post('/consultation/register', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('appointment_id'));

			$ct_id_input = $app->request->post('appointment_id');
    
            $ct_id = test_input($ct_id_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->registerUserForConsultation($user_id,$ct_id);

            if($res) {
                $response["error"] = false;
                $response["message"] = "User registered successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching reports for user.";
                echoResponse(200, $response);
            }
        });


/* Bug Report */

/**
 * Get bug report
 * url - /bugreport
 * method - GET
 */
$app->get('/bugreport', 'authenticate', function(){

            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user problems
            $data = $db->getBugReport($user_id);

            if($data){
                $response["error"] = false;
          	    $response["reports"] = $data;
                echoResponse(200, $response);
            }
            else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching reports for user.";
                echoResponse(200, $response);
            }

        });
/**
 * Create new bug report
 * url - /bugreport
 * method - POST
 * params - title, desc, action_type, action_url
 */
$app->post('/bugreport/save', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('name','description','report_id'));

			$name_input = $app->request->post('name');
			$desc_input = $app->request->post('description');

            $report_id_input = $app->request->post('report_id');
            $link = "";
    
            $name = test_input($name_input);
            $desc = test_input($desc_input);
            $reportID = test_input($report_id_input);

            if($app->request->post('link') && !empty($app->request->post('link'))){
                $link_input = $app->request->post('link');
                $link = test_input($link_input);
            }

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->saveBugReport($user_id,$name,$desc,$reportID,$link);

            if($res){
                $response["error"] = false;
                $response["message"] = "Bugreport added successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while creating new bugreport.";
                echoResponse(200, $response);
            }

        });

/**
 * Create new user feedback
 * url - /feedback
 * method - POST
 * params - title, message
 */
$app->post('/feedback/save', 'authenticate', function() use ($app) {

			verifyRequiredParams(array('name','message'));

			$name_input = $app->request->post('name');
			$message_input = $app->request->post('message');

            $name = test_input($name_input);
            $message = test_input($message_input);

            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->saveUserFeedback($user_id,$name,$message);

            if($res){
                $response["error"] = false;
                $response["message"] = "Feedback sent successfully.";
                echoResponse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Error. Problem while submitting new feedback.";
                echoResponse(200, $response);
            }

        });


/**
 * Get file
 * url - /file/:id
 * method - GET
 * params - file_id
 */
$app->get('/file/:id', 'authenticate', function($file_id_input) use ($app) {
    
            $file_id = test_input($file_id_input);

            global $user_id;

            $db = new DbHandler();

            $data = $db->getFile($user_id,$file_id);
            echo($data);
        });


/**
 * Update file
 * url - /files/:id
 * method - PUT
 * params - name
 */
$app->put('/files/:id', 'authenticate', function($file_id) use($app) {
    

			verifyRequiredParams(array('name'));
			$name_input = $app->request->put('name');

            $name = test_input($name_input);

            $db = new DbHandler();
            $response = array();

            $res = $db->updateFile($user_id, $file_id, $name);

            if ($res) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "File updated successfully.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "File failed to update. Please try again!";
            }
            echoResponse(200, $response);

        });

/**
 * Delete file
 * url - /files/:id
 * method - DELETE
 * params - file_id
 */
$app->delete('/files/:id', 'authenticate', function($file_id_input) use($app) {

            $file_id = test_input($file_id_input);
    
            global $user_id;

            $db = new DbHandler();
            $response = array();
            $res = $db->deleteFile($user_id, $file_id);

            if ($res) {
                // task deleted successfully
                $response["error"] = false;
                $response["message"] = "File deleted successfully.";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "File failed to delete. Please try again!";
            }
            echoResponse(200, $response);

        });



$app->post("/files/upload",'authenticate', function () use ($app) {

            global $user_id;
            $db = new DbHandler();

            $res = $db->uploadFile($user_id);

//            if($res){
//                $response["error"] = false;
//                $response["file_token"] = $res;
//                echoResponse(201, $res);
//            } else {
//                $response["error"] = true;
//                $response["message"] = "Error. Problem while uploading files.";
//
//            }
            echoResponse(200, $res);

    });


// Preview

/**
 * Get aushang preview
 * url - /preview/aushang/:token
 * method - GET
 */
$app->get('/preview/aushang/:token', function($aushang_token_input) use($app){

            $aushang_token = test_input($aushang_token_input);
    
            $response = array();

            $db = new DbHandler();
            $response = array();

            // get courses for user
            $res = $db->getAushangForPreview($aushang_token);

            if($res){
                $response["error"] = false;
                $response["aushang"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching aushang for preview.";
                echoResponse(200, $response);
            }

        });

/**
 * Get homework preview
 * url - /preview/homework/:token
 * method - GET
 */
$app->get('/preview/homework/:token', function($homework_token_input) use($app){

            $homework_token = test_input($homework_token_input);
    
            $response = array();

            $db = new DbHandler();
            $response = array();

            // get courses for user
            $res = $db->getHomeworkForPreview($homework_token);

            if($res){
                $response["error"] = false;
                $response["homework"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching homework for preview.";
                echoResponse(200, $response);
            }

        });

/**
 * Get vp preview
 * url - /preview/school/:token/vp/:daynumber
 * method - GET
 */
$app->get('/preview/school/:token/vp/:daynumber', function($school_token_input,$daynumber_input) use($app){

            $school_token = test_input($school_token_input);
    
            $daynumber = test_input($daynumber_input);
    
            $response = array();

            $db = new DbHandler();
            $response = array();

            // get courses for user
            $res = $db->getVPForPreview($school_token,$daynumber);

            if($res){
                $response["error"] = false;
                $response["vp"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching vp for preview.";
                echoResponse(200, $response);
            }

        });




/**
 * Register device for push notifications
 * url - /notifications/register
 * method - POST
 * params - email
 */
$app->post('/notifications/register', 'authenticate', function() use ($app) {
        
			verifyRequiredParams(array('device_token'));
			$token_input = $app->request->post('device_token');
            $token = test_input($token_input);
    
            global $user_id;
            $response = array();
            $db = new DbHandler();

            $res = $db->registerDeviceForNotifications($user_id,$token);

            if($res){
                 $response["error"] = false;
          	     $response["message"] = "Device added successfully.";
                 echoResponse(201, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while adding device.";
                echoResponse(200, $response);
            }

        });

/**
 * Send notification for homework
 * url - /notifications/homework
 * method - POST
 * params -
 */
$app->get('/notification/homework/:token', function($token_input) use ($app) {
            $token = test_input($token_input);
            if($token == "qV3QnERxrG7g8kbs"){
                $response = array();
                $db = new DbHandler();

                $res = $db->sendHomeworkNotification();

                if($res){
                     $response["error"] = false;
                     $response["message"] = $res;
                     echoResponse(201, $response);
                }
                else{
                    $response["error"] = true;
                    $response["message"] = "Error. Problem while sending notification.";
                    echoResponse(200, $response);
                }
            }



        });


/** 
 * Send notification with custom content
 * url - /notifications/student/:token
 * method - POST
 * params -
 */ 
$app->post('/notification/student/:token', 'authenticate', function($token_input) use ($app) {
        
            $token = test_input($token_input);
            if($token == "qV3QnERxrG7g8kbs"){
                verifyRequiredParams(array('title','body'));
			    $title_input = $app->request->post('title');
                $title = test_input($title_input);
                
                $body_input = $app->request->post('body');
                $body = test_input($body_input);
                
                $grade = "";
                if($app->request->post('grade') && !empty($app->request->post('grade'))){
                    $grade_input = $app->request->post('grade');
                    $grade = test_input($grade_input);
                }
                 
                global $user_id;
                $response = array(); 
                $db = new DbHandler(); 

                $res = $db->sendStudentNotification($user_id,$title,$body,$grade);

                if($res){
                     $response["error"] = false;
                     $response["message"] = $res;
                     echoResponse(201, $response);
                }
                else{
                    $response["error"] = true;
                    $response["message"] = "Error. Problem while sending notification.";
                    echoResponse(200, $response);
                }
            }

        });

/** 
 * Send notification with custom content
 * url - /notifications/teachers/:token 
 * method - POST
 * params -
 */ 
$app->post('/notification/teachers/:token', 'authenticate', function($token_input) use ($app) {
        
            $token = test_input($token_input);
            if($token == "Ncg8BZnuqSAmkNTY"){
                verifyRequiredParams(array('title','body','url'));
			    $title_input = $app->request->post('title');
                $title = test_input($title_input);
                
                $body_input = $app->request->post('body');
                $body = test_input($body_input);
                 
                $url = $app->request->post('url');  
                
//                $ios_url = $app->request->post('ios_url');                
                
                global $user_id; 
                $response = array(); 
                $db = new DbHandler(); 

                $res = $db->sendTeacherNotifications($user_id,$title,$body,$url,"");

                if($res){
                     $response["error"] = false;
                     $response["message"] = $res;
                     echoResponse(201, $response);
                }
                else{
                    $response["error"] = true;
                    $response["message"] = "Error. Problem while sending notification.";
                    echoResponse(200, $response);
                }
            }

        });


/**
 * Send notification for timetable
 * url - /notifications/timetable
 * method - POST
 * params -
 */
$app->get('/notification/timetable/:token', function($token_input) use ($app) {
            $token = test_input($token_input);
    
            if($token == "qV3QnERxrG7g8kbs"){
                $response = array();
                $db = new DbHandler();

                $res = $db->sendTimetableNotification();

                if($res){
                     $response["error"] = false;
                     $response["message"] = $res;
                     echoResponse(201, $response);
                }
                else{
                    $response["error"] = true;
                    $response["message"] = "Error. Problem while sending notification.";
                    echoResponse(200, $response);
                }
            }



        });


/*****  SOS Viewer System *******/

/**
 * Get url (for SOS viewer system)
 * method - GET
 */
$app->get('/viewer/url','authenticate', function() use($app){
    
            $response = array();

            $db = new DbHandler();
            $response = array();

            global $user_id;
    
            $res = 0;
            if($user_id == 1082){
                $res = $db->getViewerDashboardURL($user_id);
            } else if($user_id == 1081){
                $daynumber = "";
                if($app->request->get('daynumber') && !empty($app->request->get('daynumber'))){
                    $daynumber_input = $app->request->get('daynumber');
                    $daynumber = test_input($daynumber_input);
                }
                $res = $db->getViewerVPURL($user_id,$daynumber);
            } else if($user_id == 1142){
                $daynumber = "";
                if($app->request->get('daynumber') && !empty($app->request->get('daynumber'))){
                    $daynumber_input = $app->request->get('daynumber');
                    $daynumber = test_input($daynumber_input);
                }
                $res = $db->getViewerTeacherVPURL($user_id,$daynumber);
            }

            if($res){
                $response["error"] = false;
                $response["url"] = $res;
                echoResponse(200, $response);
            }
            else{
                $response["error"] = true;
                $response["message"] = "Error. Problem while fetching vp url for SOS viewer.";
                echoResponse(200, $response);
            }

        });

        

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid.';
        echoResponse(200, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

//    echo json_encode($response);
    $json  = json_encode( $response, JSON_UNESCAPED_UNICODE ); //or json_encode($o);
    $error = json_last_error();
    echo $json;
   // var_dump($json, $error === JSON_ERROR_UTF8);

}

$app->run();
?>
