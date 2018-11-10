<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Dominik Scherm <dominik@dnddev.com>
 */
class DbHandler {

    private $conn;

    function __construct(){ 
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */ 

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($first_name, $name, $password, $email) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)){
            // Generating API key
            $api_key = $this->generateApiKey();

            //Generate Password Hash
            $password_hash = PassHash::hash($password);

            $version = "sos.v1.210";

            $u = trim($email);
            $ver = sha1(time());

            $created = date("Y-m-d H:i:s");
            $role = "STUDENT";
            $token = $this->unique_id(10);

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO `LCUser` (`token`,`username`,`first_name`, `password`, `email`, `ver_code`, `api_key`, `hasLoggedIn`,`schoolid`,`role`,`created_at`, `version`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            
            $schoolid = "LP100";
            $hasLoggedIn = 0;

            $stmt->bind_param("sssssssissss", $token, $name, $first_name, $password_hash, $email, $ver, $api_key, $hasLoggedIn, $schoolid,$role, $created, $version);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                 if(!$this->sendVerificationEmail($first_name,$u, $ver)){
                    $response[0] = true;
                    $response[1] = "Problem while sending verification email. Couldn't create account.";
                    return $response;

                }
                $response[0] = false;
                $response[1] = "Account created successfully.";
                return $response;
            } else {
                // Failed to create user
                $response[0] = true;
                $response[1] = "Problem while creating user account.";
                return $response;
            }
        } else {
            // User with same email already existed in the db
            $response[0] = true;
            $response[1] = "User account already exists.";
            return $response;
        }

    }

    private function sendVerificationEmail($name,$email, $ver)
    {
         require(__DIR__ .'/php-mailer/class.phpmailer.php');
         require(__DIR__ .'/php-mailer/class.smtp.php');


         $e = sha1($email); // For verification purposes
         $to = trim($email);

         $subject = "Bitte verifiziere deinen SOS Account";
         $content =
        "Hallo $name,<br>vielen Dank für die Registrierung.<br><br>
        Dein Username ist: $email<br><br>Bitte bestätige deinen Account:";

         $link = SOS_URL."register/accountverify.php?v=$ver&e=$e";

         $greeting = "Danke!<br>

        <br>Sollten noch weitere Fragen bestehen, wende Dich bitte an support@schoolos.de<br>
        <br>
        Mit freundlichen Grüßen<br><br>

        Dein SOS Team <br>
        schoolos.de";

        $message = $this->getEmailFromTemplate("Bitte verifiziere Deinen neuen SOS Account",$content,$link,"Account verifizieren",$greeting);


        $mail = new PHPMailer;

        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'ssl';
        $mail->Host = '';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'sos@schoolos.de';                 // SMTP username
        $mail->Password = '';
        $mail->Port = 465;                                     // TCP port to connect to

        $mail->setFrom('sos@web.schoolos.de', 'School Organising System');
        $mail->addAddress($to);               // Name is optional
        $mail->addReplyTo('sos@schoolos.de', 'School Organising System');


        $mail->isHTML(true); // Set email format to HTML
        $mail->CharSet = 'UTF-8';

        $mail->Subject = $subject;
        $mail->Body    = $message;
        $altBody = $content.$link.$greeting;
        $mail->AltBody = $altBody;

        if(!$mail->send()) {
            return false;
        } else {
           return true;
        }


     }
    
    public function selectDemoSchool($user_id){
        $stmt = $this->conn->prepare("UPDATE `LCUser` SET `hasLoggedIn` = ?,`joinedSchool` = ?,`schoolid` = ?,`role`= ? WHERE `id` = ?");
        $loggedIn = 0; 
        $joinedSchool = 1;
        $role = "STUDENT";
        $schoolid = "FM254";
        $stmt->bind_param("iissi", $loggedIn, $joinedSchool, $schoolid, $role, $user_id);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result){
            return TRUE;

        } else {
            return FALSE;
        }
    }
    
     public function selectSchoolForUser($user_id,$school_token,$school_code){
         
            $res = 0;
         
         	$userInfo = $this->getUserInfo($user_id);
		    $schoolid = $userInfo["schoolid"];
         
            $this->deleteUserCourses($user_id,$schoolid);
            $this->deleteUserHomework($user_id,$schoolid);
         
            $schoolid = mb_substr($school_code, 0, 5);
            $role_id = mb_substr($school_code, 5, 6);
            if($school_code == "LP10005" || $school_code == "LP10001"){
                $schoolid = mb_substr($school_code, 0, 5);
                $role_id = mb_substr($school_code, 6, 7);
            }
          
            $stmt = $this->conn->prepare("SELECT COUNT(schoolid) AS theCount,token,teacher_code,student_code
            FROM Schools WHERE schoolid=?");

            $stmt->bind_param("s", $schoolid);

            $stmt->execute();

            $stmt->bind_result($theCount,$token,$teacher_code,$student_code);

            $stmt->store_result();

            $stmt->fetch();

            $role = "";

            if($theCount == 0) {
                 $stmt->close();
                 $res = 0; 
                 return $res;
            } else { 
                if($token == $school_token){
                    if($role_id == $student_code){
                        $role = "STUDENT";
                        $stmt = $this->conn->prepare("UPDATE `LCUser` SET `hasLoggedIn` = ?,`joinedSchool` = ?,`schoolid` = ?,`role`= ? WHERE `id` = ?");
                        $loggedIn = 0; 
                        $joinedSchool = 1;
                        $stmt->bind_param("iissi", $loggedIn, $joinedSchool, $schoolid, $role, $user_id);
                        $result = $stmt->execute();
                        $stmt->close();

                        // Check for successful insertion
                        if ($result){
                            $res = 2; 
                            return $res;
                            
                        } else {
                            $res = 0; 
                            return $res;
                        }

                    } else{
                        $res = 1; 
                        return $res;
                    }
                }
                else{
                    $res = 1; 
                    return $res;
                }
            }
    }
    
     public function getFederalStatesWithCities($user_id){
     
            $res = array();
          
            $stmt = $this->conn->prepare("SELECT fs.name, c.name, c.token FROM FederalStates fs, Cities c WHERE fs.name = c.federal_state");
        
            $result = $stmt->execute();

            /* Store the result (to get properties) */
            $stmt->store_result();

             /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($federal_state,$city,$token);
 
         
            if (result) {
                 while ($stmt->fetch()){
                    if(empty($res[$federal_state])){
                       $res[$federal_state] = array(); 
                    }
                    $tmp = array();
                    $tmp["id"] = $token;
                    $tmp["name"] = $city;
                    $res[$federal_state][] = $tmp;
               }
            } else {
                $stmt->close();
                return FALSE;
            }

           return $res;
    
      }
    

    
      public function getSchoolSearchResults($user_id,$city_token){ 
                    
                    $res = array();
                    $city_id = $this->getCityId($city_token);
          
                    $stmt = $this->conn->prepare("SELECT s.token,s.school_name FROM Schools s,`City_Schools` cs WHERE s.id = cs.school_id AND cs.city_id = ?");
 
                    $stmt->bind_param("i",$city_id);
 
                    $result = $stmt->execute();

                    /* Store the result (to get properties) */
                    $stmt->store_result();

                   /* Bind the result to variables */ 
                   $stmt->bind_result($token,$school_name);

                    if ($result) {
                         while ($stmt->fetch()){
                            $tmp = array();
                            $tmp["id"] = $token;
                            $tmp["name"] = $school_name;
                            array_push($res,$tmp);
                       }
                    } else {
                        $stmt->close();
                        return FALSE;
                    }

                   /* free results */
                   $stmt->free_result();

                   /* close statement */
                   $stmt->close();

                   return $res;
            
        }
    
        public function registerSchool($user_id,$school_name,$email,$teacher,$plz,$phone,$city,$street){
            $userInfo = $this->getUserInfo($user_id);
            $user_email = $userInfo["email"];
            $author = $userInfo["username"];

            if(!$this->sendSchoolRegistrationForm("sos@schoolos.de",$user_email,$author,$school_name,$email,$teacher,$plz,$phone,$city,$street)){
                return false;
            }
            return true;
        }
    
        private function sendSchoolRegistrationForm($email,$user_email,$user_name,$school_name,$school_email,$school_teacher,$school_plz,$school_phone,$school_city,$school_street){
                $date = date("Y-m-d H:i:s");
                
                $to = trim($email);

                $subject = "[SOS] Eine neue Schule wurde registriert";

                $headers = "From: SOS <school@sos.com> Content-Type: text/plain";

                $msg =
                    "Neue Schule wurde registriert:

                    Name der Schule: $school_name

                    Ansprechpartner: $school_teacher

                    E-Mail des Ansprechpartners der Schule: $school_email

                    Addresse der Schule: $school_plz, $school_street $school_city
                    
                    Telefonnummer der Schule: $school_phone

                    Erstellt von $user_name ($user_email) am $date

                    --
                    Bitte so schnell wie möglich hinzufügen und User informieren!

                    SOS
                    web.schoolos.de";

                return mail($to, $subject, $msg, $headers);
        }

    
        public function getCityId($token) {
            $stmt = $this->conn->prepare("SELECT id FROM Cities WHERE token = ?");
            $stmt->bind_param("s", $token);
            if ($stmt->execute()) {
                $stmt->bind_result($id);
                $stmt->fetch();
                $stmt->close();
                return $id;
            } else {
                return NULL;
            }
        }


    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password FROM LCUser WHERE email = ?");
		$username = $email;

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();


			//PassHash::check_password($password_hash, $password)
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from LCUser WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT username,first_name, token, email,verified, api_key, hasLoggedIn, joinedSchool, schoolid, role, created_at, version FROM LCUser WHERE email = ?");
		$username = $email;
        $stmt->bind_param("s", $email);
        if ($this->isUserExists($email)){
            if ($stmt->execute()) {
                // $user = $stmt->get_result()->fetch_assoc();  
                $stmt->bind_result($username,$firstName,$token, $email, $verified, $api_key, $logged_in, $joined_school, $schoolid, $role, $created_at, $version);
                $stmt->fetch();
                $user = array();
                $user["username"] = $username;
                $user["surname"] = $firstName;
                $user["email"] = $email;
                $user["token"] = $token;
                $user["version"] = $version;
				$user["role"] = $role;
                $user["api_key"] = $api_key;
                $user["verified"] = $verified;
                $user["joined_school"] = $joined_school;
                $user["logged_in"] = $logged_in;
                $user["schoolid"] = $schoolid;
                $user["created_at"] = $created_at;
                $stmt->close();
                return $user;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    public function getSchoolPreviewURLForUser($user_id){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        $token = $this->getSchoolToken($schoolid);
        $url = SOS_URL."preview/p?token=".$token;
        
        return $url;
    }
    
    public function getSchoolURL($school_token){
        $school_url = "";

        $stmt = $this->conn->prepare("SELECT school_url FROM Schools WHERE random_token = ?");

        $stmt->bind_param("s",$school_token);

        $result = $stmt->execute();

		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($url);

        while ($stmt->fetch()) {
            $school_url = $url;
        }

	    /* free results */
	    $stmt->free_result();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            return $school_url;
        } else {
            return FALSE;
        }

    }

    public function getSchoolToken($schoolid){
        $school_token = "";

        $stmt = $this->conn->prepare("SELECT random_token FROM Schools WHERE schoolid = ?");

        $stmt->bind_param("s",$schoolid);

        $result = $stmt->execute();

		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($token);

        while ($stmt->fetch()) {
            $school_token = $token;
        }

	    /* free results */
	    $stmt->free_result();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            return $school_token;
        } else {
            return FALSE;
        }

    }
    /**
     * Fetching user info by user_id
     * @param String $user_id User id
     */
     private function getUserInfo($user_id) {
        $stmt = $this->conn->prepare("SELECT username,first_name,email,schoolid,role,grade,version,teacher_id FROM LCUser WHERE id = ?");
		$username = $email;
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($username,$surname,$email,$schoolid,$role, $grade, $version,$teacher_id);
            $stmt->fetch();
            $user = array();
            $user["username"] = $username;
            $user["surname"] = $surname;
            $user["email"] = $email;
            $user["schoolid"] = $schoolid;
			$user["role"] = $role;
            $user["grade"] = $grade;
            $user["version"] = $version;
            $user["teacher_id"] = $teacher_id;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM LCUser WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }

    }
    
     public function logoutUser($user_id,$device_token){
        //Delete Device token from user
        $stmt = $this->conn->prepare("SELECT d.id FROM Devices d, User_Devices ud WHERE d.device_token = ? AND d.id = ud.device_id AND ud.user_id = ?");

        $stmt->bind_param("si", $device_token,$user_id);

        $stmt->execute();

        $stmt->store_result();

        $num_of_rows = $stmt->num_rows;

        $stmt->bind_result($device_id);

        $stmt->fetch();

        if($num_of_rows == 0) {
             $stmt->close();
             return TRUE;
        } else {

            $stmt = $this->conn->prepare("DELETE FROM `User_Devices` WHERE user_id = ? AND device_id = ?");
            $stmt->bind_param("ii",$user_id, $device_id);
            $result = $stmt->execute();

            if($result){
                $stmt = $this->conn->prepare("DELETE FROM Devices WHERE id = ?");
                $stmt->bind_param("i", $device_id);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            } else{
                $stmt->close();
                return FALSE;
            }

        }
     }

    
     public function verifyAccount($verification_code,$email){
            $stmt = $this->conn->prepare("SELECT username FROM `LCUser` WHERE ver_code=? AND SHA1(email)=? AND verified=0");

            $stmt->bind_param("ss", $verification_code,$email);

            $stmt->execute();

            $stmt->bind_result($username);

            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                if(isset($username) && !empty($username))
                {

                    $stmt = $this->conn->prepare("UPDATE `LCUser` SET `verified`= 1 WHERE `ver_code` = ? LIMIT 1");
                    $stmt->bind_param("s", $verification_code);
                    $result = $stmt->execute();
                    $stmt->close();

                    // Check for successful insertion
                    if ($result){
                        return $result;
                    } else {
                        return FALSE;
                    }


                } else {
                    $stmt->close();
                    return FALSE;
                }
            } else{
                $stmt->close();
                return FALSE;
            }


        }


    /**
     * Resets a user's status to unverified and sends them an email
     *
     * @return mixed    TRUE on success and a message on failure
     */
    public function resetPassword($email){
            $stmt = $this->conn->prepare("SELECT ver_code FROM `LCUser` WHERE email = ?");
        
            $stmt->bind_param("s",$email);

            if ($stmt->execute()){
                /* Store the result (to get properties) */
                $stmt->bind_result($ver);

                $stmt->fetch();

                if($this->sendResetEmail($email,$ver))
                {
                    $res = TRUE;
                }
                else{
                    $res = NULL;
                }

                 /* free results */
                $stmt->free_result();

                /* close statement */
                $stmt->close();
                return $res;
            } else{
                return NULL;
            }

        
            
        

    }
    

    public function updatePassword($verification_code,$password){

            require_once("PassHash.php");
            $password_hash = PassHash::hash($password);

            $stmt = $this->conn->prepare("UPDATE `LCUser` SET password=? WHERE ver_code=? LIMIT 1");
            $stmt->bind_param("ss", $password_hash,$verification_code);

            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result){
//                $this->sendPasswordChangedEmail($verification_code);
                return $result;
            } else {
                return FALSE;
            }


        }

    private function sendResetEmail($email, $ver){
                 require(__DIR__ .'/php-mailer/class.phpmailer.php');
                 require(__DIR__ .'/php-mailer/class.smtp.php');

                 $e = sha1($email); // For verification purposes
                 $to = trim($email);

                 $subject = "Zurücksetzen Deines SOS Passworts";
                 $content =
                "Hallo,<br>wir haben gehört Du hast dein Passwort vergessen?<br>Kein Problem, klicke unten auf den Link und leg einfach ein Neues fest.<br>";

                 $link = SOS_URL."register/resetpassword?&e=$ver";

                 $greeting = "Danke!<br>

                <br>Sollten noch weitere Fragen bestehen, wende Dich bitte an support@schoolos.de<br>
                <br><br>
                Mit freundlichen Grüßen<br><br>

                Dein SOS Team <br>
                schoolos.de";

                $message = $this->getEmailFromTemplate("Zurücksetzen Deines SOS Passworts",$content,$link,"Passwort zurücksetzen",$greeting);

                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->SMTPAuth = true; // authentication enabled
                $mail->SMTPSecure = 'ssl';
                $mail->Host = '';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'sos@schoolos.de';                 // SMTP username
                $mail->Password = '';
                $mail->Port = 465;                                     // TCP port to connect to

                $mail->setFrom('sos@web.schoolos.de', 'School Organising System');
                $mail->addAddress($to);               // Name is optional
                $mail->addReplyTo('sos@schoolos.de', 'School Organising System');


                $mail->isHTML(true); // Set email format to HTML
                $mail->CharSet = 'UTF-8';

                $mail->Subject = $subject;
                $mail->Body    = $message;
                $altBody = $content.$link.$greeting;
                $mail->AltBody = $altBody;

                if(!$mail->send()) {
                    return false;
                } else {
                   return true;
                }

            }

    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT id from LCUser WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT id FROM LCUser WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }
    
     public function getUpdateInformation($user_id){
        $update = array();
        $userInfo = $this->getUserInfo($user_id);

        //!Wichtig check if user has done this before!!

        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT hasLoggedIn,joinedSchool,hasVoted,sos_points,voteLater FROM `LCUser` WHERE id = ?");

        //!!Schoolid Problem !!
		$stmt->bind_param("i",$user_id);

        $stmt->execute();

        /* Store the result (to get properties) */
	    $stmt->store_result();

         /* Get the number of rows */
	   $num_of_rows = $stmt->num_rows;


	   /* Bind the result to variables */
	   $stmt->bind_result($logged_in,$joined_school,$hasVoted,$sos_numb_points,$vote_later);
	   $stmt->fetch();
 
       $update["school_url"] = $this->getSchoolPreviewURLForUser($user_id);
       $update["logged_in"] = $logged_in;
       $update["joined_school"] = $joined_school;
       $update["version"] = SOS_VERSION;
       $update["has_voted"] = false; 
       $update["school_has_voting"] = false;
       $update["voting_token"] = "bWD6qQvTyeTeXBFC";
       $update["sos_points"] = $sos_numb_points;

        if($schoolid == "LP100"){
            $update["school_has_voting"] = false;
            $update["has_voted"] = ($hasVoted) ? true : false; 
            $update["voting_token"] = "bWD6qQvTyeTeXBFC";
            $update["sos_points"] = $sos_numb_points;
            
            if($vote_later == 0 || $vote_later >= 3){
               
                if($vote_later >= 3){
                   if(!$this->resetVoteLater($user_id)){
                        return FALSE;
                    } 
                }
                
            } else{
                $this->voteLaterForCompetition($user_id);
                $update["has_voted"] = true;
            }
            
        }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $update;

	}

    
    /************************
        
            PIMP YOUR SCHOOL - NEW COMPETITION PART
        
    *************************/
    
    public function resetVoteLater($user_id) {
        $stmt = $this->conn->prepare("UPDATE `LCUser` SET voteLater = 0 WHERE `id` = ?");
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result){
            return $result;
        } else {
            // task failed to create
            return FALSE;
        }
    }
    
    public function getCompetitions($user_id){
        
         $userInfo = $this->getUserInfo($user_id);
         $schoolid = $userInfo["schoolid"];
        
         if($schoolid == "LP100"){
            $competitions = array();
             return $competitions;
         }
        
         
         
    }
    
    public function getCompetition($user_id,$competition_token){
        
        if($competition_token == "bWD6qQvTyeTeXBFC"){
            $competition = array();
            $competition["name"] = "Pimp Your School";
            $competition["voting_image_url"] = "";
            $competition["voting_interval"] = "daily";
            $competition["voting_description"] = "Hi Leute, wir sind Dominik, Jan und Denis, eure SOS Entwickler. Wenn euch unsere App gefällt, wäre jetzt die perfekte Gelegenheit uns zu unterstützen - natürlich kostenlos! Bitte votet für uns beim PIMP YOUR SCHOOL Wettbewerb. Als Dankeschön spendieren wir euch SOS in euren Lieblingsfarben, machen eine mega geile SOS-Party am Leibniz und verlosen 250€ unter allen Supportern!";
            $competition["action_url"] = "";
            $competition["action_button_text"] = "Jetzt voten für SOS";
            $competition["cancel_button_text"] = "Später voten";
            $competition["help_url"] = "";
            return $competition;
        } else{
            return FALSE;
        }
        
    }
    
    public function votedForCompetition($user_id,$competition_token){
        
        if($competition_token == "bWD6qQvTyeTeXBFC"){
            
             $hasVoted = false;
            
             $stmt = $this->conn->prepare("SELECT hasVoted FROM `LCUser` WHERE id = ?");

              //!!Schoolid Problem !!
              $stmt->bind_param("i",$user_id);

              $stmt->execute();

              /* Store the result (to get properties) */
              $stmt->store_result();

              /* Bind the result to variables */
              $stmt->bind_result($hasVoted);
              $stmt->fetch();

              $has_voted = $hasVoted;

              $stmt->free_result();
            
              if(!$has_voted){
                    $stmt = $this->conn->prepare("UPDATE `LCUser` SET sos_points = sos_points + 1, hasVoted = 1 WHERE `id` = ?");
                    $stmt->bind_param("i", $user_id);
                    $result = $stmt->execute();

                    if ($result){
                        $stmt->close();
                        return $result;
                    } else {
                        $stmt->close();
                        return FALSE;
                    }
              }
           
        } else{
            return FALSE;
        }
    }
    
    public function voteLaterForCompetition($user_id){
        $stmt = $this->conn->prepare("UPDATE `LCUser` SET voteLater = voteLater + 1 WHERE `id` = ?");
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result){
            return $result;
        } else {
            // task failed to create
            return FALSE;
        }
    }
    
    public function getVotingPoints($user_id){
        
         $points = 0;
        
         $userInfo = $this->getUserInfo($user_id);

         //!Wichtig check if user has done this before!!

         $schoolid = $userInfo["schoolid"];

         $stmt = $this->conn->prepare("SELECT sos_points FROM `LCUser` WHERE id = ?");

         //!!Schoolid Problem !!
		 $stmt->bind_param("i",$user_id);

         $stmt->execute();

         /* Store the result (to get properties) */
	     $stmt->store_result();

         /* Get the number of rows */
	     $num_of_rows = $stmt->num_rows;


          /* Bind the result to variables */
          $stmt->bind_result($sos_numb_points);
          $stmt->fetch();

          $points = $sos_numb_points;

           /* free results */
          $stmt->free_result();

           /* close statement */
           $stmt->close();

          return $points;

    }
    
    
    /******************************/
    
    
    public function updateJoinedSchool($user_id,$joined_school){
        
        if(!$joined_school){
            $this->updateLoggedIn($user_id,0);
        }
        
        $stmt = $this->conn->prepare("UPDATE `LCUser` SET `joinedSchool`= ? WHERE `id` = ?");
        $stmt->bind_param("ii", $joined_school, $user_id);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result){
            return $result;
        } else {
            // task failed to create
            return FALSE;
        }
    }

    public function getLoggedIn($user_id){
        $stmt = $this->conn->prepare("SELECT hasLoggedIn FROM LCUser WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($has_logged_in);
            $stmt->fetch();
            $stmt->close();
            return $has_logged_in;
        } else {
            return NULL;
        }
    }

    public function updateLoggedIn($user_id,$done){

        $stmt = $this->conn->prepare("UPDATE `LCUser` SET `hasLoggedIn`= ? WHERE `id` = ?");
        $done = intval($done);
        $stmt->bind_param("ii", $done, $user_id);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result){
            return $result;
        } else {
            // task failed to create
            return FALSE;
        }

    }

    public function getTeachers($user_id){
        $userInfo = $this->getUserInfo($user_id);

        //!Wichtig check if user has done this before!!

        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT teacher_id FROM `TeacherUser` WHERE schoolid = ? ORDER BY teacher_id ASC");

        //!!Schoolid Problem !!
		$stmt->bind_param("s",$schoolid);

        $stmt->execute();

        /* Store the result (to get properties) */
	    $stmt->store_result();

         /* Get the number of rows */
	   $num_of_rows = $stmt->num_rows;


	   /* Bind the result to variables */
	   $stmt->bind_result($teacher);
	   $teachers = array();
	   while ($stmt->fetch()){
			array_push($teachers,$teacher);
	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

		return $teachers;

	}

	public function getGrades($user_id){
        $userInfo = $this->getUserInfo($user_id);

        //!Wichtig check if user has done this before!!

        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT grade FROM `Grades` WHERE schoolid = ? ORDER BY grade_level,grade ASC");

        //!!Schoolid Problem !!
		$stmt->bind_param("s",$schoolid);

        $stmt->execute();

        /* Store the result (to get properties) */
	    $stmt->store_result();

         /* Get the number of rows */
	   $num_of_rows = $stmt->num_rows;


	   /* Bind the result to variables */
	   $stmt->bind_result($grade);
	   $grades = array();
	   while ($stmt->fetch()){
           if($grade != "7" && $grade != "8" && $grade != "9" && $grade != "10" && $grade != "Alle"){
               array_push($grades,$grade);
           }
	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

		return $grades;

	}



    public function getCoursesForGrade($grade,$user_id){
        $userInfo = $this->getUserInfo($user_id);

        //!Wichtig check if user has done this before!! 
        $schoolid = $userInfo["schoolid"];


        $stmt = $this->conn->prepare("SELECT token,course_id,teacher_id,course_name FROM `LCCourses` WHERE schoolid = ? AND (grade LIKE '%$grade%' OR grade = ? OR grade = ?) AND visible = 1 ORDER BY LCASE(course_id) ASC");

        //!!Schoolid Problem !!

        $grade_level = substr($grade, 0, -1);
        $all = "Alle";
		$stmt->bind_param("sss",$schoolid,$grade_level,$all);

        $stmt->execute();

        /* Store the result (to get properties) */
	    $stmt->store_result();

         /* Get the number of rows */
	   $num_of_rows = $stmt->num_rows;


	   /* Bind the result to variables */
	   $stmt->bind_result($id,$courseID,$teacher,$courseName);
	   $courses = array();
	   while ($stmt->fetch()){
            $course = array();
            $courseName = $courseName; 
            $course["id"] = $id;
            $course["name"] = $courseID;
            $course["description"] = $courseName;
            $course["teacher"] = $teacher;
            $course["category"] = $courseName;
            if(strpos($courseName," ") !== false){
                $category = explode(" ", $courseName);
                if(is_numeric($category[1])){
                    $course["category"] = $category[0];
                }

            }

			array_push($courses,$course);
	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

		return $courses;

	}

    //Fetching timetable + vp
    public function getVP($user_id,$daynumber){

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $version = $userInfo["version"];
        $schoolid = $userInfo["schoolid"];

        $grade = "";
        if($grade = "STUDENT"){
            $grade = $userInfo["grade"];
        }

        $teacher_id = "";
        if($role == "TEACHER" || $role == "PRINCIPAL"){
            $teacher_id = $userInfo["teacher_id"];
        }

		$stmt = $this->conn->prepare("SELECT jsonVP,vpURL,created_at, updated_at FROM `VPs` WHERE daynumber = ? AND schoolid = ?");

		$stmt->bind_param("is",$daynumber, $schoolid);

        $stmt->execute(); 
			 /* Store the result (to get properties) */
	   $stmt->store_result();

	   /* Get the number of rows */
	   $num_of_rows = $stmt->num_rows;

	   /* Bind the result to variables */
	   $stmt->bind_result($vpData,$vpURL,$created_at,$changed);
	   $vp = array();
       $timetableURL;
	   while ($stmt->fetch()){

                $data = "";
                if($role == "STUDENT"){
                    $data = $this->parseVP($vpData, $daynumber, $role, $grade, $user_id, $schoolid);
                }
                else if($role == "TEACHER" || $role == "PRINCIPAL"){
                    $data = $this->parseVP($vpData, $daynumber, $role, $teacher_id, $user_id, $schoolid);
                }

                $vp = $data["data"];
                $timetableURL = $data["url"];

	   }

        $res = array();
        $res["timetableURL"] = $this->getTimetableURL($schoolid,"student");
        $res["teacherURL"] = $this->getTimetableURL($schoolid,"teacher");
        $res["roomURL"] = $this->getTimetableURL($schoolid,"room");
        $res["vpURL"] = $this->getVPURL($schoolid,$daynumber,true);  
        $res["timetable"] = $vp["timetable"];
        $res["additions"] = $vp["additions"];
        $res["num_lessons"] = $vp["num_lessons"];
        $res["created_at"] = $created_at;
        $res["updated_at"] = $changed;
        $isCurrentVP = $this->isCurrentVP($changed);
        $res["isCurrentVP"] = $isCurrentVP;
	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

		return $res;

    }
 
  
    public function getVPURL($schoolid,$daynumber,$forMobile){
        $url = "";
        $token = $this->getSchoolToken($schoolid);
        
        $daynumber_extension = "";
        if($daynumber != ""){
            $daynumber_extension = "&daynumber=".$daynumber;
        }
        if($forMobile){
             $url = SOS_URL."preview/plan-m?type=vp&s=".$token.$daynumber_extension;
        } else{
             $url = SOS_URL."preview/plan?type=vp&s=".$token.$daynumber_extension;
        }

       return $url;

    }

     public function getTimetableURL($schoolid,$type){
        $url = ""; $type = strtolower($type);
        if($type == "teacher"){
            $stmt = $this->conn->prepare("SELECT timetable_teacher_url FROM Schools WHERE schoolid = ?");
        } else if($type == "student"){
           $stmt = $this->conn->prepare("SELECT timetable_student_url FROM Schools WHERE schoolid = ?");
        }  else if($type == "room"){
            $stmt = $this->conn->prepare("SELECT timetable_room_url FROM Schools WHERE schoolid = ?");
        }

        $stmt->bind_param("s",$schoolid);

        $result = $stmt->execute(); /* Store the result (to get properties) */

        $stmt->store_result(); /* Bind the result to variables */

        $stmt->bind_result($timetable_url);

        while ($stmt->fetch()) {
            $url = $timetable_url;
        }

        /* free results */

        $stmt->free_result();

        $stmt->close(); // Check for successful insertion

        if ($result){
            return $url;
        } else {
            return FALSE;
        }
    }




    public function shareTimetable($school_token,$type){
        $url = "";
        if($type == "teacher"){
            $stmt = $this->conn->prepare("SELECT timetable_teacher_url FROM Schools WHERE random_token = ?");
        } else if($type == "student"){
           $stmt = $this->conn->prepare("SELECT timetable_student_url FROM Schools WHERE random_token = ?");
        }  else if($type == "room"){
            $stmt = $this->conn->prepare("SELECT timetable_room_url FROM Schools WHERE random_token = ?");
        }

        $stmt->bind_param("s",$school_token);

        $result = $stmt->execute(); /* Store the result (to get properties) */

        $stmt->store_result(); /* Bind the result to variables */

        $stmt->bind_result($timetable_url);

        while ($stmt->fetch()) {
            $url = $timetable_url;
        }

        /* free results */

        $stmt->free_result();

        $stmt->close(); // Check for successful insertion

        if ($result){
            return $url;
        } else {
            return FALSE;
        }
    }


    //Share VP
     public function shareVP($school_token,$daynumber){
        $url = "";
        $stmt = $this->conn->prepare("SELECT random_token FROM Schools WHERE random_token = ?");
        $stmt->bind_param("s",$school_token);
        $result = $stmt->execute();

		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($token);

        while ($stmt->fetch()) {
             $url = SOS_URL."preview/plan?type=vp&s=".$token."&daynumber=".$daynumber;
           
        }

	    /* free results */
	    $stmt->free_result();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            return $url;
        } else {
            return FALSE;
        }
    }



    public function getVPForAllDays($user_id){

        $timetable = array();

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];

		$stmt = $this->conn->prepare("SELECT daynumber,vp_day,jsonVP,vpURL,created_at,updated_at FROM `VPs` WHERE schoolid = ?");

		$stmt->bind_param("s",$schoolid);

        $stmt->execute();
        /* Store the result (to get properties) */
	    $stmt->store_result();

	   /* Get the number of rows */
	   $num_of_rows = $stmt->num_rows;

	   /* Bind the result to variables */
	   $stmt->bind_result($daynumber,$vpDay,$vpData,$vpURL,$created_at,$changed);

       $timetableURL;
	   while ($stmt->fetch()){
           
				$data = $this->parseVP($vpData, $daynumber, $grade, $user_id, $schoolid);
                $vp = $data["data"];
                $timetableURL = $data["url"];

                $tmp = array();
                $tmp["date"] = $vpDay;
                $tmp["timetableURL"] = $timetableURL;
                $tmp["vpURL"] = $this->getVPURL($user_id,$daynumber,true);
                $tmp["timetable"] = $vp["timetable"];
                $tmp["additions"] = $vp["additions"];
                $tmp["num_lessons"] = $vp["num_lessons"];
                $tmp["created_at"] = $created_at;
                $tmp["updated_at"] = $update_at;

                $timetable[$daynumber] = $tmp;

	   }
	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

		return $timetable;

    }

    public function getTimetable($user_id,$week,$daynumber,$isForVP,$schoolid){
        $week = strtolower($week);
        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];

		$stmt = $this->conn->prepare("SELECT jsonTimetable_$week,timetableURL, created_at, updated_at FROM `TimetableStudents` WHERE grade = ? AND schoolid = ?");

		$stmt->bind_param("ss",$grade,$schoolid);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($jsonData,$jsonURL,$created_at,$changed);
		$timetable;

		while ($stmt->fetch()) {
			$timetable = $this->parseTimetable($jsonData,$daynumber, $isForVP);
//			else
//			{
//				$timetable = array();
//				$timetable["created_at"] = $created_at;
//				$timetable["updated_at"] = $changed;
//
//				if($version == "sos.v1.100" || $version == "sos.v1.101" || $version == "sos.v1.110"){
//					$timetable["URL"] = $jsonData;
//				} else if($version == "sos.v1.200" || $version == "sos.v1.201" || $version == "sos.v1.210"){
//					$timetable["jsonContent"] = $this->parseTimetable($jsonData,$daynumber, $isForVP);
//				}
//			}

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();
        $res = array();
        $res["data"] = $timetable;
        $res["url"] = $jsonURL;

		return $res;

    }

    public function getTeacherTimetable($user_id,$week,$daynumber,$isForVP){

        $week = strtolower($week);
        $userInfo = $this->getUserInfo($user_id);

        $teacher_id = $userInfo["teacher_id"];
        $schoolid = $userInfo["schoolid"];

		$stmt = $this->conn->prepare("SELECT jsonTimetable_$week,created_at,updated_at FROM `TimetableTeacher` WHERE teacher = ? AND schoolid = ?");

		$stmt->bind_param("ss",$teacher_id,$schoolid);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($jsonData,$created_at,$changed);
		$timetable;

		while ($stmt->fetch()) {
			if($isForVP){
				$timetable = $this->parseTimetable($jsonData,$daynumber, $isForVP);
			}
//			else
//			{
//				$timetable = array();
//				$timetable["created_at"] = $created_at;
//				$timetable["updated_at"] = $changed;
//
//				if($version == "sos.v1.100" || $version == "sos.v1.101" || $version == "sos.v1.110"){
//					$timetable["URL"] = $jsonData;
//				} else if($version == "sos.v1.200" || $version == "sos.v1.201" || $version == "sos.v1.210"){
//					$timetable["jsonContent"] = $this->parseTimetable($jsonData,$daynumber, $isForVP);
//				}
//			}

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();
        $res = array();
        $res["data"] = $timetable;
        $res["url"] = $jsonURL;

		return $res;

    }

    private function checkURL($url){
            if(strpos($url, "http") !== false){
                   return $url;
            } else{
                return "http://".$url;
            }
    }

    public function getQuestions(){
        $questions = array();

        $stmt = $this->conn->prepare("SELECT title,description,author,random_token,updated_at,created_at FROM `Questions` ORDER BY created_at DESC");

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($title,$description,$author,$random_token,$updated,$created);

        $url = "";
		while ($stmt->fetch()) {
                $question = array();
                $url = SOS_URL."questions/q?q=".$random_token;
                $question["url"] = $url;
                $question["title"] = $title;
                $question["author"] = $author;  
                $question["description"] = $description;
                $question["updated_at"] = $updated;
                $question["created_at"] = $created;

                array_push($questions, $question);

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $questions;
    }
    
    public function saveQuestion($question,$author){
        $random_token = $this->unique_id(10);
        $random_token = (string)$random_token;
        $date = date("Y-m-d H:i:s");
        $created = $date;

        $token = $this->unique_id(16);

        $stmt = $this->conn->prepare("INSERT INTO `Questions`(`token`,`title`, `description`, `random_token`, `author`, `created_at`) VALUES (?,?,?,?,?,?)");

        $stmt->bind_param("ssssss", $token, $question, $question, $random_token, $author, $created);
        $result = $stmt->execute();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            return $result;
        } else {
            // aushang failed to create
            return FALSE;
        }
    }
    
    public function getAushang($user_id){

        $aushang = array();

        $userInfo = $this->getUserInfo($user_id);

        $user_grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];


        $stmt = $this->conn->prepare("SELECT token, title,text,image,action_type,action_url,grades,category,updated_at,created_at,random_token FROM `Aushang` WHERE schoolid = ? AND verified = 1 ORDER BY keep_at_top DESC, created_at DESC");

		$stmt->bind_param("s",$schoolid);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$title, $text, $image, $type,$link,$grades,$category,$updated,$created,$token);
		$timetable;

        $url = "";
		while ($stmt->fetch()) {
                $match = true;
                if(isset($grades) && !empty($grades)){
                    $match = false;
                    $grades = preg_replace('/\.$/', '', $grades);
                    $grades = explode(',', $grades);
                    foreach($grades as $grade){
                        if($grade == $user_grade){
                            $match = true;
                        }
                    }
                }

                if($match){

                    $aushangData = array();
                    $url = SOS_URL."preview/p?type=aushang&token=".$token."&s=0";

                    $aushangData["id"] = $id;
                    $aushangData["title"] = $title;
                    $aushangData["description"] = $text;
                    if($image == "-"){
                        $aushangData["image"] = "Kein Bild verfügbar";
                    }
                    else{
                        $aushangData["image"] = $image;
                    }
                    $aushangData["category"] = $category;
                    $aushangData["action_type"] = $type;
                    $aushangData["action_url"] = $link;
                    $aushangData["share_url"] = $url;
                    $aushangData["updated_at"] = $updated;
                    $aushangData["created_at"] = $created;

                    array_push($aushang, $aushangData);
                }

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $aushang;


    }

    public function getUserAushang($user_id){

        $aushang = array();

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];


        $stmt = $this->conn->prepare("SELECT a.token,a.verified,a.title,a.text,a.image,a.action_type,a.action_url,a.category,a.updated_at,a.created_at,a.random_token FROM `Aushang` as a,`User_Aushang` as ua WHERE ua.schoolid = ? AND ua.member_id = ? AND ua.aushang_id = a.id ORDER BY created_at DESC");

		$stmt->bind_param("si",$schoolid,$user_id);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$ver,$title, $text, $image, $type,$link,$category,$updated,$created,$token);
		$timetable;

        $url = "";
		while ($stmt->fetch()) {
				$aushangData = array();
                $url = SOS_URL."preview/p?type=aushang&token=".$token."&s=0";

                $aushangData["id"] = $id;
                $aushangData["verified"] = $ver;
                $aushangData["title"] = $title;
                $aushangData["description"] = $text;
                if($image == "-"){
                    $aushangData["image"] = "Kein Bild verfügbar";
                }
                else{
                    $aushangData["image"] = $image;
                }
                $aushangData["category"] = $category;
                $aushangData["action_type"] = $type;
                $aushangData["action_url"] = $link;
                $aushangData["share_url"] = $url;
                $aushangData["updated_at"] = $updated;
                $aushangData["created_at"] = $created;

                array_push($aushang, $aushangData);

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $aushang;


    }

    public function getAushangId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM Aushang WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }

    public function deleteUserAushang($user_id,$aushang_token){
        $result;

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $schoolid = $userInfo["schoolid"];

        $aushang_id = $this->getAushangId($aushang_token);

        $stmt = $this->conn->prepare("SELECT `id` FROM `User_Aushang` WHERE `schoolid` = ? AND `member_id` = ? AND `aushang_id`= ?");

        $stmt->bind_param("sii",$schoolid,$user_id,$aushang_id);

        $stmt->execute();
        $stmt->store_result();

        /* Get the number of rows */
        $num_of_rows = $stmt->num_rows;
        if($num_of_rows > 0){

            $stmt->free_result();

//            $stmt = $this->conn->prepare("SELECT file_id FROM `Task_Files` WHERE task_id = ? AND schoolid = ?");
//            $stmt->bind_param("is",$task_id,$schoolid);
//            $stmt->execute();
//            $stmt->store_result();
//            $num_of_rows = $stmt->num_rows;
//            $stmt->bind_result($file_id);
//
//            if($num_of_rows > 0){
//                while ($stmt->fetch()) {
//                    $this->deleteFile($user_id,$file_id);
//                }
//            }
//
//            $stmt->free_result();

            $stmt = $this->conn->prepare("DELETE FROM `User_Aushang` WHERE schoolid = ? AND member_id = ? AND aushang_id = ?");
            $stmt->bind_param("sii", $schoolid, $user_id, $aushang_id);
            $result = $stmt->execute();

            if($result){
                $stmt = $this->conn->prepare("DELETE FROM Aushang WHERE schoolid = ? AND id = ?");
                $stmt->bind_param("si", $schoolid, $aushang_id);
                $result = $stmt->execute();
            } else{
                 $stmt->close();
                 return FALSE;
            }

       }
       else{
            $stmt->close();
            return FALSE;
        }


       /* close statement */
       $stmt->close();

       return $result;
    }

    public function createAd($user_id,$title,$desc,$grades,$action_type,$action_url){

        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        if($action_type == "3"){
            $action_url = $this->checkURL($action_url);
        }
        $random_ad_token = $this->unique_id(10);
        $random_ad_token = (string)$random_ad_token;
        $date = date("Y-m-d H:i:s");
        $ver = 0;
        $ver = intval($ver);
        $actionType = intval($action_type);
        $created = $date;

        $token = $this->unique_id(16);

        $stmt = $this->conn->prepare("INSERT INTO `Aushang`(`token`,`verified`, `schoolid`, `title`, `text`, `action_type`, `action_url`, `grades`, `created_at`,`random_token`) VALUES (?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param("sisssissss", $token, $ver, $schoolid, $title, $desc, $action_type, $action_url, $grades, $created, $random_ad_token);
        $result = $stmt->execute();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            $new_aushang_id = $this->conn->insert_id;
            $res = $this->createUserAushang($schoolid, $user_id, $new_aushang_id);
            if ($res) {
				return $new_aushang_id;
            } else {
                // aushang failed to create
                return FALSE;
            }
        } else {
            // aushang failed to create
            return FALSE;
        }


    }

	public function createUserAushang($schoolid, $user_id, $aushang_id) {
        $stmt = $this->conn->prepare("INSERT INTO User_Aushang (schoolid, aushang_id, member_id) values(?, ?, ?)");
        $stmt->bind_param("sii", $schoolid,$aushang_id, $user_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

    public function createAushangFeed(){
//        header("Content-Type: application/rss+xml; charset=UTF-8");
//
//        Convert elements into xml feed
//        $xml = new SimpleXMLElement('<rss/>');
//        $xml->addAttribute("version", "2.0");
//        $channel = $xml->addChild("channel");
//
//        $channel->addChild("title", "Your feed title");
//        $channel->addChild("link", "Your website's uri");
//        $channel->addChild("description", "Describe your feed");
//        $channel->addChild("language", "en-us");
//
//        foreach ($aushang as $entry) {
//            $item = $channel->addChild("item");
//
//            $item->addChild("title", $entry['title']);
//            $item->addChild("link", $entry['link']);
//            $item->addChild("text", $entry['text']);
//            $item->addChild("image", $entry['image']);
//        }
//        return($xml->asXML());
//
    }

    private function unique_id($l) {
            return substr(md5(uniqid(mt_rand(), true)), 0, $l);
    }

    public function shareAushang($user_id,$aushang_token){

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $version = $userInfo["version"];
        $schoolid = $userInfo["schoolid"];

        $aushang_id = $this->getAushangId($aushang_token);

        $stmt = $this->conn->prepare("SELECT random_token FROM Aushang WHERE id = ? AND schoolid = ? LIMIT 1");
        $stmt->bind_param("is", $aushang_id, $schoolid);
        $result = $stmt->execute();

		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($token);

        while ($stmt->fetch()) {
             $url = SOS_URL."preview/p?type=aushang&token=".$token."&s=0";
        }

	    /* free results */
	    $stmt->free_result();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            return $url;
        } else {
            return FALSE;
        }

    }

    public function saveTeacherSelection($user_id, $teacher_id) {
        $stmt = $this->conn->prepare("UPDATE LCUser SET teacher_id = ?,hasLoggedIn = 1 WHERE id = ?");
        $stmt->bind_param("ss", $teacher_id, $user_id);
        $res = $stmt->execute();
        $stmt->close();

        return $res;

    }

    
    public function saveUserSelection($user_id, $grade, $course_tokens) {
		
		$this->updateGrade($user_id, $grade,1);
		
		$rows = 0; $ids = array();
        
		$tokens = array_unique($course_tokens);
		
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];  
		
		$stmt = $this->conn->prepare("SELECT id FROM `Course_Members` WHERE `schoolid` = ? AND `member_id` = ?");

		$stmt->bind_param("si", $schoolid, $user_id);
 
		$stmt->execute();
		
		$stmt->store_result();

        $rows = $stmt->affected_rows;
		 
		$stmt->bind_result($id);

        while ($stmt->fetch()){
            array_push($ids,$id);

        }
    
		$stmt->close(); 
        
        $i = 0;
		$numb =  $rows - count($tokens);
		if($numb > 0){
			$stmt = $this->conn->prepare("DELETE FROM Course_Members WHERE schoolid = ? AND member_id = ? LIMIT ?");
			$stmt->bind_param("sii", $schoolid, $user_id,$numb);
			$result = $stmt->execute();

			if(false === $result){
				$stmt->close();
				return FALSE;
			} 
            $i = $numb;
            $stmt->close(); 
		}
        
		foreach($tokens as $course_token){
			if($rows > 0){
					$course_id = $this->getCourseId($course_token);
                    if ($course_id != NULL) {
						$stmt = $this->conn->prepare("UPDATE Course_Members SET `course_id`= ? WHERE `id` = ?");
                        $stmt->bind_param("ii", $course_id, $ids[$i]);
                        $result = $stmt->execute();
                        if (false === $result) {
                            die('execute() failed: ' . htmlspecialchars($stmt->error));
                            return FALSE;
                        }
                        $stmt->close(); 
					} else{
                       $stmt = $this->conn->prepare("DELETE FROM Course_Members WHERE id = ?");
                       $stmt->bind_param("i", $ids[$i]);
                       $result = $stmt->execute();

                        if(false === $result){
                            $stmt->close();
                            return FALSE;
                        }
                        $stmt->close();  
                    }
                    $rows = $rows - 1;
                    $i++;
					
					
			} else{
					$course_id = $this->getCourseId($course_token);
                    if ($course_id != NULL) {
                        $stmt = $this->conn->prepare("INSERT INTO Course_Members(schoolid,course_id, member_id) values(?, ?, ?)");
                        $stmt->bind_param("sii", $schoolid,$course_id, $user_id);
                        $result = $stmt->execute();
                        if (false === $result) {
                            die('execute() failed: ' . htmlspecialchars($stmt->error));
                            return FALSE;
                        }
                        $stmt->close(); 
                    } 
            }

			}
			
			return TRUE;

    }
    

    public function getCourseId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM LCCourses WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id; 
        } else {
            return NULL;
        }
    }
    
    public function deleteUserHomework($user_id,$schoolid){
        
        $res = true;

        $stmt = $this->conn->prepare("SELECT id FROM `User_Homework` WHERE `schoolid` = ? AND `user_id` = ?");

		$stmt->bind_param("si",$schoolid,$user_id);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($id);

        /* Get the number of rows */
		$num_of_rows = $stmt->num_rows;
        if($num_of_rows > 0){
            $stmt->fetch();
            $res = $this->deleteHomework($user_id,$id);
        }
        else{
            $stmt->free_result();
            $stmt->close();
            return FALSE;
        }
	    /* close statement */
        $stmt->free_result();
	    $stmt->close();

	    return $res;
	}

	public function deleteUserCourses($user_id,$schoolid){

        $this->updateGrade($user_id, "",0);

        $stmt = $this->conn->prepare("DELETE FROM Course_Members WHERE schoolid = ? AND member_id = ?");
        $stmt->bind_param("si", $schoolid, $user_id);
        $res = $stmt->execute();

	    /* close statement */
	    $stmt->close();

	    return $res;
	}


    public function mergeCourseMembers($user_id, $grade, $course_id, $new_course_id) {
        $members = array();
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        //COULD BE FASTER -> VERY SLOW
        $stmt = $this->conn->prepare("SELECT member_id FROM Course_Members cm,LCCourses lc WHERE lc.schoolid = ? AND lc.course_id = ? AND lc.grade = ? AND lc.id = cm.course_id");
        $stmt->bind_param("sss", $schoolid, $course_id,$grade);
        $stmt->execute();
        $stmt->store_result();

        $num_affected_rows = $stmt->affected_rows;

        $stmt->bind_result($member_id);

        while ($stmt->fetch()){
            array_push($members,$member_id);

        }

        foreach($members as $member){
            $stmt = $this->conn->prepare("INSERT INTO Course_Members(schoolid,course_id, member_id) values(?, ?, ?)");
            $stmt->bind_param("sii", $schoolid,$new_course_id, $member);
            $result = $stmt->execute();
        }

        $stmt = $this->conn->prepare("SELECT id FROM LCCourses lc WHERE lc.schoolid = ? AND lc.course_id = ? AND lc.grade = ? LIMIT 1");
        $stmt->bind_param("sss", $schoolid, $course_id,$grade);
        $stmt->execute();
        $stmt->store_result();

        $old_course_id = "";
        $stmt->bind_result($id);

        while ($stmt->fetch()){
           $old_course_id = $id;

        }

        foreach($members as $member){
			$stmt = $this->conn->prepare("DELETE FROM Course_Members WHERE schoolid = ? AND member_id = ? AND course_id = ?");
			$stmt->bind_param("sii", $schoolid, $member, $old_course_id);
			$stmt->execute();

		}

        $stmt->close();
        return true;


    }


    private function getCoursesForUser($user_id, $schoolid){
        
        $courses;
        
        $stmt = $this->conn->prepare("SELECT course_id FROM `Course_Members` WHERE schoolid = ? AND member_id = ?");

		$stmt->bind_param("si",$schoolid,$user_id);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($courseID);
        $courseIDs = array();

		while ($stmt->fetch()) {
                array_push($courseIDs, $courseID);

	    }

	    /* free results */
	    $stmt->free_result();
        $ids = array();
        $courseNames = array();
        $courseDescriptions = array();

        foreach($courseIDs as $courseID){
            $stmt = $this->conn->prepare("SELECT token,course_id,course_name FROM `LCCourses` WHERE schoolid = ? AND id = ?");

            $stmt->bind_param("si",$schoolid,$courseID);

            $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();


            /* Bind the result to variables */
            $stmt->bind_result($id,$courseID,$description);

            while ($stmt->fetch()) {
                array_push($ids, $id);
                array_push($courseNames, $courseID);
                array_push($courseDescriptions, $description);

            }

            /* free results */
            $stmt->free_result();
        }
        
        $courses["names"] = $courseNames;
        $courses["descriptions"] = $courseDescriptions;
        $courses["ids"] = $ids;

	   /* close statement */
	   $stmt->close();
        
       return $courses;

    }
    
    private function countCoursesForUser($user_id, $schoolid){

        $stmt = $this->conn->prepare("SELECT id FROM `Course_Members` WHERE schoolid = ? AND member_id = ?");

		$stmt->bind_param("si",$schoolid,$user_id);
		$stmt->execute();
	   	$stmt->store_result();
        /* Get the number of rows */
        $num_of_rows = $stmt->num_rows;
        $stmt->close();
        return $num_of_rows;

    }

    private function updateGrade($user_id, $grade,$logged_in){
        
        $stmt = $this->conn->prepare("UPDATE LCUser SET grade = ?,hasLoggedIn = ? WHERE id = ?");
        $stmt->bind_param("sis", $grade,$logged_in, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    private function getLessonIconURL($courseID){
        $courseID = strtolower($courseID);
        $lesson_icons = array("Bio","Chemie","Deutsch","DS","Englisch","French","Geo","Geschichte","Informatik","Kunst","Mathe","MuK","Musik","Physik","Politik","Psychologie","Recht","Sport","Spanisch","WAT");
        $lesson_names = array("bi","ch","de","ds","en","fr","ek","ge","if","ku","ma","sub","mu","ph","pb","psy","rl","sp","sn","wat");

        $image_name;
        foreach($lesson_names as $key => $lesson){
            if(strpos($courseID,$lesson) !== false){
                $image_name = $lesson_icons[$key];
            }
        }

        $image_url = SOS_URL . "images/course_icons/" . $image_name . ".png";
        return $image_url;
    }

    public function getCourseList($user_id){
        $courses = array();
        $userInfo = $this->getUserInfo($user_id);

        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        if($role != "TEACHER" && $role != "PRINCIPAL"){

            $stmt = $this->conn->prepare("SELECT c.token,c.course_id,c.teacher_id,c.course_name FROM LCCourses c, Course_Members m WHERE c.schoolid = ? AND m.course_id = c.id AND m.member_id = ? ORDER BY grade ASC, course_id ASC");

            $stmt->bind_param("si",$schoolid,$user_id);

            $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($id,$courseID,$teacher,$courseName);

            while ($stmt->fetch()) {
                    $courseID = $courseID;

                    $course = array();
                    $course["id"] = $id;
                    $course["name"] = $courseID;
                    $course["description"] = $courseName;
                    $course["teacher"] = $teacher;
                    $course["hasTasks"] = $this->countTasksForCourse($id);
                    $course["image_url"] = $this->getLessonIconURL($courseID);
                    array_push($courses, $course);

           }

           /* free results */
           $stmt->free_result();

           /* close statement */
           $stmt->close();

           return $courses;

        } else{
            $teacher_id = $userInfo["teacher_id"];
            return $this->getTeacherCoursesList($teacher_id,$schoolid);
        }
    }
    
    
     public function getCourseDetails($user_id,$course_token){
        $course = array();
        $userInfo = $this->getUserInfo($user_id);

        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        $stmt = $this->conn->prepare("SELECT c.course_id,c.teacher_id,c.course_name FROM LCCourses c WHERE c.schoolid = ? AND c.token = ?");

        $stmt->bind_param("ss",$schoolid,$course_token);

        $stmt->execute();
        /* Store the result (to get properties) */
        $stmt->store_result();

        /* Get the number of rows */
        $num_of_rows = $stmt->num_rows;

        /* Bind the result to variables */
        $stmt->bind_result($courseID,$teacher,$courseName);

        while ($stmt->fetch()) {
                $course["id"] = $course_token;
                $course["name"] = $courseID;
                $course["description"] = $courseName;
                $course["teacher"] = $teacher;
                $course["hasTasks"] = $this->countTasksForCourse($course_token);
                $course["image_url"] = $this->getLessonIconURL($courseID);

       }

       /* free results */
       $stmt->free_result();

       /* close statement */
       $stmt->close();

       return $course;

    }
    
    
    
     public function getCourseIdList($user_id){
        $courses = array();
        $userInfo = $this->getUserInfo($user_id);

        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        if($role != "TEACHER" && $role != "PRINCIPAL"){

            $stmt = $this->conn->prepare("SELECT c.course_id FROM LCCourses c, Course_Members m WHERE c.schoolid = ? AND m.course_id = c.id AND m.member_id = ? ORDER BY grade ASC, course_id ASC");

            $stmt->bind_param("si",$schoolid,$user_id);

            $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($courseID);

            while ($stmt->fetch()) {
                    array_push($courses, $courseID);

           }

           /* free results */
           $stmt->free_result();

           /* close statement */
           $stmt->close();

           return $courses;

        } else{
            $teacher_id = $userInfo["teacher_id"];
            return $this->getTeacherCoursesIdList($teacher_id,$schoolid);
        }
    }
    
     public function getTeacherCoursesIdList($teacher_id,$schoolid){

        $courses = array();

        $stmt = $this->conn->prepare("SELECT c.course_id FROM LCCourses c WHERE c.teacher_id = ? AND c.schoolid = ? ORDER BY course_id ASC");

        $stmt->bind_param("ss",$teacher_id,$schoolid);
 
		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($courseID);
        
		while ($stmt->fetch()) {
                array_push($courses, $courseID);


	   }
	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

       return $courses;
    }
    
     private function countTasksForCourse($course_token){
        $course_id = $this->getCourseId($course_token);
        $stmt = $this->conn->prepare("SELECT id from Course_Tasks WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getTeacherCoursesList($teacher_id,$schoolid){

        $courses = array();

        $stmt = $this->conn->prepare("SELECT c.token,c.course_id,c.course_name,c.grade FROM LCCourses c WHERE (c.teacher_id REGEXP '^".$teacher_id."' OR c.teacher_id REGEXP '[[:space:]]".$teacher_id."') AND c.schoolid = ? ORDER BY course_id ASC");

        $stmt->bind_param("s",$schoolid);
 
		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$courseID,$courseName,$grade);
        
		while ($stmt->fetch()) {
                $course = array();
                $courseID = $courseID;

                $course["id"] = $id;
                $course["name"] = $courseID;
                $course["teacher"] = $grade;
                $course["description"] = $courseID."_".$grade;
                $course["hasTasks"] = $this->countTasksForCourse($id);
                $course["image_url"] = $this->getLessonIconURL($courseID);
                array_push($courses, $course);
	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

       return $courses;
    }

    public function getTasksForCourse($course_token, $user_id){
        $tasks = array();

        $course_id = $this->getCourseId($course_token);

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT t.token,t.name,t.description,t.updated_at,t.expire_date FROM LCTasks t, Course_Tasks ct WHERE t.schoolid = ? AND ct.task_id = t.id AND ct.course_id = ? ORDER BY t.created_at DESC");

		$stmt->bind_param("si",$schoolid,$course_id);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$taskName,$taskDesc,$taskUpdated,$expire);
        
		while ($stmt->fetch()) {
                $task = array();
                $task["type"] = "task"; 
                $task["id"] = $id;
                $task["name"] = $taskName;
                $task["description"] = $taskDesc;
                $task["hasFiles"] = $this->countFilesForTask($id);
                $task["updated_at"] = $taskUpdated;
                $task["expire_date"] = $expire;
                array_push($tasks, $task);
	   }
        
       if($role != "TEACHER" && $role != "PRINCIPAL"){
                $homework = $this->getHomeworkForCourse($user_id,$course_id);

                $content = array_merge($tasks, $homework);
            
       }
        
	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

       return $content;

    }

    public function getHomeworkForCourse($user_id,$course_id){
        $homework = array();

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];
 
        $stmt = $this->conn->prepare("SELECT h.token,h.title,h.desc,h.expire_date,h.done,h.updated_at,h.created_at FROM Homework h, User_Homework uh,Course_Homework ch WHERE h.schoolid = ? AND uh.homework_id = h.id AND uh.user_id = ? AND ch.homework_id = h.id AND ch.course_id = ? AND uh.accepted = 1 ORDER BY created_at DESC");

		$stmt->bind_param("sii",$schoolid,$user_id,$course_id);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$title, $desc, $expire,$done,$updated,$created);

        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    $tmp = array();
                    $tmp["type"] = "homework";
                    $tmp["id"] = $id;
                    $tmp["name"] = $title;
                    $tmp["description"] = $desc;
                    $tmp["done"] = $done;
                    $tmp["updated_at"] = $updated;
                    $tmp["expire_date"] = $expire;
                    array_push($homework, $tmp);
	           }
            }
        }
        else{
            $stmt->close();
            return array();
        }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

      return $homework;

    }

    public function getTaskId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM LCTasks WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }

    private function countFilesForTask($task_token){
        $task_id = $this->getTaskId($task_token);
        $stmt = $this->conn->prepare("SELECT id from Task_Files WHERE task_id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getEntriesForCourse($course_token, $user_id){
        $course_id = $this->getCourseId($course_token);
        $entries = array();

        $userInfo = $this->getUserInfo($user_id);

        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT e.token,e.title,e.desc,e.updated_at,e.created_at FROM `CourseEntries` e, `Course_Entries` ce WHERE e.schoolid = ? AND ce.entry_id = e.id AND ce.course_id = ?");
        
		$stmt->bind_param("si",$schoolid,$course_id);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$title,$desc,$updated,$created);
        if ($num_rows >= 0) {
            while ($stmt->fetch()) {
                    $entry = array();
                    $entry["id"] = $id;
                    $entry["title"] = $title;
                    $entry["description"] = $desc;
                    $entry["updated_at"] = $updated;
                    $entry["created_at"] = $created;
                    array_push($entries, $entry);
           }
            /* free results */
	       $stmt->free_result();

           /* close statement */
           $stmt->close();
           return $entries;
        } else{
            $stmt->close();
            return array();
        }

    }

    public function createEntry($user_id,$course_token,$title,$desc){
    
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){

            $course_id = $this->getCourseId($course_token);    

            $token = $this->unique_id(16);

            $stmt = $this->conn->prepare("INSERT INTO `CourseEntries`(`schoolid`,`token`,`title`,`desc`, `created_at`) VALUES (?,?,?,?,?)");
            $date = date("Y-m-d H:i:s");

            $created = $date;
            $stmt->bind_param("sssss", $schoolid, $token, $title, $desc, $created);
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                $new_entry_id = $this->conn->insert_id;
                $res = $this->createCourseEntry($schoolid, $course_id, $new_entry_id);
                if ($res) {
                    return $new_entry_id;

                } else {
                    // task failed to create
                    return FALSE;
                }
            } else {
                // task failed to create
                return FALSE;
            }
        } else{
            return FALSE;
        }
	}

	public function createCourseEntry($schoolid, $course_id, $new_entry_id) {
        $stmt = $this->conn->prepare("INSERT INTO Course_Entries (schoolid, course_id, entry_id) values(?, ?, ?)");
        $stmt->bind_param("sii", $schoolid,$course_id, $new_entry_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

    public function getEntryId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM CourseEntries WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }

    public function updateEntry($user_id, $entry_token, $title, $description){
        $userInfo = $this->getUserInfo($user_id);
        $role = $userInfo["role"];
        if($role == "TEACHER" || $role == "PRINCIPAL"){
        
            $entry_id = $this->getEntryId($entry_token);

            $stmt = $this->conn->prepare("UPDATE `CourseEntries` SET `title`=?,`desc`=? WHERE id = ?");

            $stmt->bind_param("ssi", $title, $description, $entry_id);
            $result = $stmt->execute();
            // Check for successful insertion
            if (!$result) {
                return FALSE;
            }

            /* close statement */
            $stmt->close();

            return TRUE;
        } else{
            return FALSE;
        }
	}

    public function deleteEntry($user_id,$entry_token){
        $result;

        $entry_id = $this->getEntryId($entry_token);

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $schoolid = $userInfo["schoolid"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){
            $stmt = $this->conn->prepare("DELETE FROM Course_Entries WHERE schoolid = ? AND entry_id = ?");
            $stmt->bind_param("si", $schoolid, $entry_id);
            $stmt->execute();

            $stmt = $this->conn->prepare("DELETE FROM CourseEntries WHERE schoolid = ? AND id = ?");
            $stmt->bind_param("si", $schoolid, $entry_id);
            $result = $stmt->execute();

           /* close statement */
           $stmt->close();

            return $result;
        } else{
            return FALSE;
        }
    }

    public function getCourseMembersForSharing($course_token, $user_id){
        $members = array();

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];
        
        $course_id = $this->getCourseIdForStudent($course_token,$grade);
        
        $stmt = $this->conn->prepare("SELECT DISTINCT u.id,u.username,u.first_name FROM LCUser u,Course_Members cm,LCCourses lc WHERE  cm.course_id = ? AND cm.member_id = u.id AND cm.schoolid = ?");

		$stmt->bind_param("is",$course_id,$schoolid);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$username,$first_name);

		while ($stmt->fetch()) {

                $member = array();
                $member["member_id"] = $id;
                $member["username"] = $username;
                $member["first_name"] = $first_name;
                array_push($members, $member);

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $members;

    }
    
    public function getCourseMembers($course_token, $user_id){
        $members = array();

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $role = $userInfo["role"];
        $schoolid = $userInfo["schoolid"];
        
        $course_id = "";
        if($forHomeworkSharing){
            $course_id = $this->getCourseIdForStudent($course_token,$grade);
        } else{
            $course_id = $this->getCourseId($course_token);
        }
        
        $stmt = $this->conn->prepare("SELECT DISTINCT u.token,u.username,u.first_name,u.email FROM LCUser u,Course_Members cm,LCCourses lc WHERE  cm.course_id = ? AND cm.member_id = u.id AND cm.schoolid = ?");

		$stmt->bind_param("is",$course_id,$schoolid);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($token,$username,$first_name,$email);

		while ($stmt->fetch()) {
            
                $member = array();
                $member["id"] = $token;
                $member["username"] = str_repeat("*",strlen($username));
                $member["first_name"] = $first_name;
                $member["email"] = $email;
                
            
                if($role == "TEACHER" || $role == "PRINCIPAL"){
                     $member["username"] = $username;
                } 
            
                array_push($members, $member);

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $members;

    }
	
    public function removeCourseMember($user_id,$course_token,$member_token){
        $result;
        $course_id = $this->getCourseId($course_token);
		
		$member_id = $this->getMemberId($member_token);

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $schoolid = $userInfo["schoolid"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){

            $stmt = $this->conn->prepare("DELETE FROM Course_Members WHERE schoolid = ? AND member_id = ? AND course_id = ?");
            $stmt->bind_param("sii", $schoolid, $member_id, $course_id);
            $result = $stmt->execute();

            if($result){
                return TRUE;
            } else{
                return FALSE;
            }

        } else{
            return FALSE;
        }
    }
   

	public function deleteTask($user_id,$task_token){
        $result;

        $task_id = $this->getTaskId($task_token);

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $schoolid = $userInfo["schoolid"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){
            $stmt = $this->conn->prepare("SELECT `id` FROM `Task_Owner` WHERE `schoolid` = ? AND `user_id` = ? AND `task_id`= ?");

            $stmt->bind_param("sii",$schoolid,$user_id,$task_id);

            $stmt->execute();
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;
            if($num_of_rows > 0){

                $stmt->free_result();

                $stmt = $this->conn->prepare("SELECT file_id FROM `Task_Files` WHERE task_id = ? AND schoolid = ?");
                $stmt->bind_param("is",$task_id,$schoolid);
                $stmt->execute();
                $stmt->store_result();
                $num_of_rows = $stmt->num_rows;
                $stmt->bind_result($file_id);

                if($num_of_rows > 0){
                    while ($stmt->fetch()) {
                        $this->deleteFile($user_id,$file_id);
                    }
                }

                $stmt->free_result();

                $stmt = $this->conn->prepare("DELETE FROM Task_Owner WHERE schoolid = ? AND user_id = ? AND task_id = ?");
                $stmt->bind_param("sii", $schoolid, $user_id, $task_id);
                $stmt->execute();

                $stmt = $this->conn->prepare("DELETE FROM Course_Tasks WHERE schoolid = ? AND task_id = ?");
                $stmt->bind_param("si", $schoolid, $task_id);
                $stmt->execute();
                
                $stmt = $this->conn->prepare("DELETE FROM LCTasks WHERE schoolid = ? AND id = ?");
                $stmt->bind_param("si", $schoolid, $task_id);
                $result = $stmt->execute();
           }
           else{

                $stmt->close();
                return FALSE;
            }


           /* close statement */
           $stmt->close();

            return $result;
        } else{
            return FALSE;
        } 

    }

   public function deleteOldTasks(){
       $ids = array();
       $stmt = $this->conn->prepare("SELECT `id`,`expire_date` FROM `LCTasks` as t");
       
       $stmt->execute();
       $stmt->store_result();

       /* Get the number of rows */
       $num_of_rows = $stmt->num_rows;
       $stmt->bind_result($id,$expire);
       if($num_of_rows > 0){
           while($stmt->fetch()){
               
                date_default_timezone_set('Europe/Berlin'); 
                $expire_date = date("Y-d-m", strtotime($expire));
                $today = date('Y-m-d',strtotime("-14 days"));
                if($expire_date <= $today){
                    array_push($ids, $id);
                }

           }
           
       } 

       $stmt->free_result();
       foreach($ids as $task_id){
               $stmt = $this->conn->prepare("SELECT file_id FROM `Task_Files` WHERE task_id = ?");
               $stmt->bind_param("i",$task_id);
               $stmt->execute();
               $stmt->store_result();
               $num_of_rows = $stmt->num_rows;
               $stmt->bind_result($file_id);

               if($num_of_rows > 0){
                   while ($stmt->fetch()) {
                        if(!$this->deleteFileById($file_id)){
                            continue;
                        }
                   }
               }

               $stmt->free_result();
                $stmt = $this->conn->prepare("DELETE FROM Task_Owner WHERE task_id = ?");
                $stmt->bind_param("i", $task_id);
                $stmt->execute();

                $stmt = $this->conn->prepare("DELETE FROM Course_Tasks WHERE task_id = ?");
                $stmt->bind_param("i", $task_id);
                $stmt->execute();
                
                $stmt = $this->conn->prepare("DELETE FROM LCTasks WHERE id = ?");
                $stmt->bind_param("i", $task_id);
                $result = $stmt->execute();
       }
   
       $stmt->close();
       return TRUE;
      

   }

    // public function cancelTaskDeletion($user_id,$task_token){
    //     $userInfo = $this->getUserInfo($user_id);
    //     $schoolid = $userInfo["schoolid"];
    //     $role = $userInfo["role"];

    //     if($role == "TEACHER" || $role == "PRINCIPAL"){

    //         $task_id = $this->getTaskId($task_token);

    //         $stmt = $this->conn->prepare("UPDATE LCTasks SET in_delete_queue = 0,updated_at = ? WHERE id = ?");
    //         $date = date("Y-m-d H:i:s");

    //         $task_id = intval($task_id);
    //         $stmt->bind_param("is", $task_id,$date);
    //         $result = $stmt->execute();

    //         // Check for successful insertion
    //         if (!$result) {
    //             return FALSE;
    //         }

    //         /* close statement */
    //         $stmt->close();

    //         return TRUE;
    //     } else{
    //         return FALSE;
    //     }
	// }

	public function updateTask($user_id, $task_token, $name, $desc, $expire_date){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){

            $task_id = $this->getTaskId($task_token);

            $stmt = $this->conn->prepare("UPDATE LCTasks SET name = ?,description = ?,updated_at = ?, expire_date = ? WHERE id = ?");
            $date = date("Y-m-d H:i:s");
            $updated = $date;

            $task_id = intval($task_id);
            $stmt->bind_param("ssssi", $name, $desc, $updated, $expire_date, $task_id);
            $result = $stmt->execute();

            // Check for successful insertion
            if (!$result) {
                return FALSE;
            }

            /* close statement */
            $stmt->close();

            return TRUE;
        } else{
            return FALSE;
        }
	}

	public function getTasksForTeacher($course_token, $user_id){
		$tasks = array();

        $course_id = $this->getCourseId($course_id);

        $userInfo = $this->getUserInfo($user_id);

        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT t.token,t.name,t.description,t.updated_at,t.expire_date FROM LCTasks t, Course_Tasks ct, Task_Owner tw WHERE ct.schoolid = ? AND ct.task_id = t.id AND ct.course_id = ? AND tw.task_id = t.id AND tw.user_id = ?");

		$user_id = intval($user_id);
		$stmt->bind_param("sii",$schoolid,$course_id,$user_id);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$taskName,$taskDesc,$updated,$expire);

		while ($stmt->fetch()) {

                $task = array();
                $task["id"] = $id;
                $task["name"] = $taskName;
                $task["description"] = $taskDesc;

                $updated_date = strtotime($updated);
                $expire_date = strtotime($epxire);

//                date_default_timezone_set('Europe/Berlin');
//                $from = strtotime($expire);
//                $today = time();
//                $difference = $today - $from;
//                $diff = floor($difference / 86400);
//                if($diff >= 14 && $diff < 20){
////                    $task["delete_queue"] = true;
////                    $days = 20-$diff;
////                    $task["delete_message"] = "Auftrag wird in ".$days." Tagen gelöscht";
//                }

                $task["updated_at"] = $updated;
                $task["expire_date"] = $expire;
                array_push($tasks, $task);

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();


        return $tasks;
	}

	public function createTeacherTask($user_id,$course_token,$name,$desc,$expire_date,$file_token_list,$send_notification){

		$userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){

            $course_id = $this->getCourseId($course_token);

            $token = $this->unique_id(16);

            $stmt = $this->conn->prepare("INSERT INTO `LCTasks`(`token`,`schoolid`,`name`, `description`, `created_at`,`expire_date`) VALUES (?,?,?,?,?,?)");
            $date = date("Y-m-d H:i:s");
            $created = $date;
            $stmt->bind_param("ssssss", $token, $schoolid, $name, $desc, $created, $expire_date);
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                $new_task_id = $this->conn->insert_id;
                $res = $this->createCourseTask($schoolid, $course_id, $new_task_id);
                if ($res) {
                    $res = $this->createOwnerTask($schoolid, $user_id, $new_task_id);
                    if ($res) {
                        if($send_notification){
                            $notif = array();
                            $course_name = $this->getCourseName($course_token);
                            $notif["title"] = "Neuer Auftrag für deinen Kurs '".$course_name."' verfügbar";
                            $notif["body"] = $name." Zu erledigen bis zum ".$expire_date;
                            $this->sendTaskNotification($notif,$course_id);
                        }
                        if(count($file_token_list) > 0){
                            foreach($file_token_list as $file_token){
                                $res = $this->createTaskFile($schoolid, $new_task_id, $file_token);
                                if(!$res){
                                    return FALSE;
                                }
                            }

                        }
                        return $new_task_id;

                    } else {
                        // task failed to create
                        return FALSE;
                    }
                } else {
                    // task failed to create
                    return FALSE;
                }
            } else {
                // task failed to create
                return FALSE;
            }
        } else{
            return FALSE;
        }
	}
    
    public function sendStudentNotification($user_id,$title,$body,$grade){
       
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        if($grade == "" || !$grade){
            $stmt = $this->conn->prepare("SELECT lc.id FROM LCUser lc WHERE lc.verified = 1 AND lc.role = ? AND schoolid = ?");

            $role = "STUDENT";
            $stmt->bind_param("ss", $role,$schoolid);

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($user_id);
            if($result){
                if($num_of_rows > 0){
                    while ($stmt->fetch()) {
                        $device_tokens = $this->getDeviceTokensForUser($user_id);
                        $this->sendMessage($device_tokens,$title,$body,"",'FF003A',"","","","");
                   }
                }
            } else{
                    $stmt->close();
                    return FALSE;
            }
        } else{
           $stmt = $this->conn->prepare("SELECT lc.id FROM LCUser lc WHERE lc.verified = 1 AND lc.role = ? AND grade = ? AND schoolid = ?");

            $role = "STUDENT";
            $stmt->bind_param("sss", $role,$grade,$schoolid);

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($user);
            if($result){
                if($num_of_rows > 0){
                    while ($stmt->fetch()) {
                        $device_tokens = $this->getDeviceTokensForUser($user);
                        $this->sendMessage($device_tokens,$title,$body,"",'FF003A',"","","","");
                   }
                }
            } else{
                    $stmt->close();
                    return FALSE;
            }  
        }
        

        /* free results */
	   $stmt->free_result();

       $stmt->close();
       return TRUE;
    }

    public function sendTeacherNotifications($user_id,$title,$body,$url,$ios_resource_url){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        $stmt = $this->conn->prepare("SELECT lc.id FROM LCUser lc WHERE lc.verified = 1 AND lc.role = ? OR lc.role = ? AND schoolid = ?");

        $role = "TEACHER";
        $other_role = "PRINCIPAL";
        $stmt->bind_param("sss", $role,$other_role,$schoolid);

        $result = $stmt->execute();
        /* Store the result (to get properties) */
        $stmt->store_result();

        /* Get the number of rows */
        $num_of_rows = $stmt->num_rows;
        
        $ios_content = array();
        $random_token = $this->unique_id(2);
        $ios_content["sosnotif$random_token"] = $ios_resource_url;
        $ios_attachments = $ios_content;

        $random_token = $this->unique_id(2);
        $action_buttons = array();
        $action_buttons["id"] = "sosnotif$random_token";
        $action_buttons["text"] = "Jetzt teilnehmen!";
        
        $random_token = $this->unique_id(2);
        $web_action_buttons = array();
        $web_action_buttons["id"] = "sosnotif$random_token";
        $web_action_buttons["text"] = "Jetzt teilnehmen an der Umfrage!";
        $web_acton_buttons["url"] = $url;


        /* Bind the result to variables */
        $stmt->bind_result($user);
        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    $device_tokens = $this->getDeviceTokensForUser($user);
                    $this->sendMessage($device_tokens,$title,$body,"",'FF003A',$url,$action_buttons,$web_acton_buttons,$ios_attachments);
                }
            }
        } else{
                $stmt->close();
                return FALSE;
        }
    
        /* free results */
	   $stmt->free_result();

       $stmt->close();
       return TRUE;
    }

     public function sendTaskNotification($notif,$course_id){

        $stmt = $this->conn->prepare("SELECT lc.id FROM LCUser lc,Course_Members cm WHERE cm.course_id = ? AND lc.id = cm.member_id AND lc.hasLoggedIn = 1");

        $stmt->bind_param("i", $course_id);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($user_id);
        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    $device_tokens = $this->getDeviceTokensForUser($user_id);
                    $this->sendMessage($device_tokens,$notif["title"],$notif["body"],"",'FF003A',"","","","");

	           }
            }
        } else{
                $stmt->close();
                return FALSE;
        }

        /* free results */
	   $stmt->free_result();

       $stmt->close();
       return TRUE;

     }

    private function getCourseName($token) {
        $stmt = $this->conn->prepare("SELECT course_name FROM LCCourses WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($name);
            $stmt->fetch();
            $stmt->close();
            return $name;
        } else {
            return NULL;
        }
    }

	public function createCourseTask($schoolid, $course_id, $task_id) {
        $stmt = $this->conn->prepare("INSERT INTO Course_Tasks (schoolid, course_id, task_id) values(?, ?, ?)");
        $stmt->bind_param("sii", $schoolid,$course_id, $task_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

	public function createOwnerTask($schoolid, $user_id, $task_id) {
        $stmt = $this->conn->prepare("INSERT INTO Task_Owner (schoolid, user_id, task_id) values(?, ?, ?)");
        $stmt->bind_param("sii", $schoolid,$user_id, $task_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

    public function createTaskFile($schoolid, $task_id, $file_token) {
        $stmt = $this->conn->prepare("SELECT `id` FROM `LCFiles` WHERE `schoolid` = ? AND `token` = ? LIMIT 1");

		$stmt->bind_param("ss",$schoolid,$file_token);

		$res = $stmt->execute();
        $stmt->store_result();
		$num_of_rows = $stmt->num_rows;

        $stmt->bind_result($file_id);

        if($res){
            if($num_of_rows > 0){
                 while ($stmt->fetch()) {
                    $stmt = $this->conn->prepare("INSERT INTO Task_Files (schoolid, file_id, task_id) values(?, ?, ?)");
                    $stmt->bind_param("sii", $schoolid,$file_id, $task_id);
                    $result = $stmt->execute();

                    if (false === $result) {
                        die('execute() failed: ' . htmlspecialchars($stmt->error));
                    }
                    $stmt->close();
                    return $result;
                 }
            } else{
                return FALSE;
            }
        } else{
            return FALSE;
        }
    }

    // Files

    public function getFilesForTask($task_token, $user_id){
        $files = array();

        $task_id = $this->getTaskId($task_token);

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT f.token,f.file_name,f.folder_token,f.user_file_name,f.uploaded FROM LCFiles f, Task_Files tf WHERE f.schoolid = ? AND tf.file_id = f.id AND tf.task_id = ?");
        
		$stmt->bind_param("si",$schoolid,$task_id);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($token,$fileName,$folderToken,$userFileName,$uploaded);
        
		while ($stmt->fetch()) {

                $fileNameURL = str_replace(" ","%20",$fileName);

                $storeFolder = 'uploads';
                $targetPath = SOS_URL . $storeFolder . "/" . $folderToken . "/" . $fileNameURL;

//                $path = realpath(__DIR__ . '/../../../../SOS-dev/') . "/". $storeFolder . "/" . $folderToken . "/" . $fileName;
                $path = realpath(__DIR__ . '/../..') . "/". $storeFolder . "/" . $folderToken . "/" . $fileName;
                $size = filesize($path);

                $file = array();
                $file["id"] = $token;
                $file["token"] = $token;
                $file["name"] = $userFileName;
                $file["data_type"] = substr($fileName,strpos($fileName,".") +1);
                $file["url"] = $targetPath;
                $file["size"] = (string)$this->format_size($size);
                $file["uploaded"] = $uploaded;
                array_push($files, $file);

	   }
        


	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();


        return $files;


    }

    public function getFileId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM LCFiles WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }

    public function getFile($user_id, $file_token){

        $file_id = $this->getFileId($file_token);

        $file = $this->getFileByID($user_id,$file_id);
        $file = SOS_URL.'upload/'.$file;
            if (strpos($file, 'pdf') !== false) {
                header("Content-type:application/pdf");
                // It will be called downloaded.pdf
                header("Content-Disposition:attachment;filename='$file'");
                // The PDF source is in original.pdf
                readfile($file);
            }
            else if (strpos($file, 'jpg') !== false) {
                 header('Content-Type: image/jpg');

                 readfile($file);
            }
            else if (strpos($file, 'png') !== false) {

                header('Content-Type: image/png');
                //passthru("cat $file");
                readfile($file);
            }
            else if (strpos($file, 'tiff') !== false) {
                header('Content-Type: image/tiff');
                readfile($file);
            }
            else if (strpos($file, 'txt') !== false) {
                 $fh = fopen($file, 'r');

                $pageText = fread($fh, 25000);

                echo nl2br($pageText);
            }
            else {
                return 'Achtung Dateiformat wird nicht unterstützt!';
            }

    }
    
    public function getFileByID($user_id,$file_token){

        $file_id = $this->getFileId($file_token);

        $userInfo = $this->getUserInfo($user_id);

        $fileName = "";
        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];


        $stmt = $this->conn->prepare("SELECT file_name FROM LCFiles WHERE id = ? AND schoolid = ?");

		$stmt->bind_param("is",$file_id,$schoolid);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($name);

        if($num_of_rows > 0){
            while ($stmt->fetch()) {
                $fileName = $name;
	       }
        }
        else{
            $fileName = "Fehler. Keine Datei gefuden für ID.";
        }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

       return $fileName;

    }

    public function deleteFile($user_id,$file_id){
        $result;

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $schoolid = $userInfo["schoolid"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){

            $stmt = $this->conn->prepare("SELECT `folder_token` FROM `LCFiles` WHERE schoolid = ? AND id = ?");

            $stmt->bind_param("si",$schoolid,$file_id);

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($folder_token);

            $stmt->fetch();

            if($result){
                if($num_of_rows == 1){

                        $store_folder = '/uploads';
                        $dir = realpath(__DIR__ . '/../..') . "/". $store_folder . "/" . $folder_token;
                        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                        $files = new RecursiveIteratorIterator($it,
                                     RecursiveIteratorIterator::CHILD_FIRST);
                        foreach($files as $file) {
                            if ($file->isDir()){
                                rmdir($file->getRealPath());
                            } else {
                                unlink($file->getRealPath());
                            }
                        }
                        rmdir($dir);

                        $stmt->free_result();

                        $stmt = $this->conn->prepare("DELETE FROM Task_Files WHERE schoolid = ? AND file_id = ?");
                        $stmt->bind_param("si", $schoolid, $file_id);
                        $stmt->execute();

                        $stmt = $this->conn->prepare("DELETE FROM LCFiles WHERE schoolid = ? AND id = ?");
                        $stmt->bind_param("si", $schoolid, $file_id);
                        $result = $stmt->execute();

                } else{
                    $stmt->close();
                    return FALSE;
                }
           }
           else{
                return FALSE;
            }

           /* close statement */
           $stmt->close();
            return $result;
        } else{
            return FALSE;
        }

    }

    public function deleteFileById($file_id){
        $result;

        $stmt = $this->conn->prepare("SELECT `folder_token` FROM `LCFiles` WHERE id = ?");

        $stmt->bind_param("i",$file_id);

        $result = $stmt->execute();
        /* Store the result (to get properties) */
        $stmt->store_result();

        /* Get the number of rows */
        $num_of_rows = $stmt->num_rows;

        /* Bind the result to variables */
        $stmt->bind_result($folder_token);

        $stmt->fetch();

        if($result){
            if($num_of_rows == 1){

                    $store_folder = '/uploads';
                    $dir = realpath(__DIR__ . '/../..') . "/". $store_folder . "/" . $folder_token;
                    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it,
                                    RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($files as $file) {
                        if ($file->isDir()){
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    rmdir($dir);

                    $stmt->free_result();

                    $stmt = $this->conn->prepare("DELETE FROM Task_Files WHERE file_id = ?");
                    $stmt->bind_param("i", $file_id);
                    $stmt->execute();

                    $stmt = $this->conn->prepare("DELETE FROM LCFiles WHERE id = ?");
                    $stmt->bind_param("i", $file_id);
                    $result = $stmt->execute();

            } else{
                $stmt->close();
                return FALSE;
            }
        }
        else{
            return FALSE;
        }

        /* close statement */
        $stmt->close();
        return $result;

    }

    public function updateFile($user_id, $file_token, $name){
        
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];

        if($role == "TEACHER" || $role == "PRINCIPAL"){
            
            $file_id = $this->getFileId($file_token);

            $stmt = $this->conn->prepare("UPDATE LCFiles SET user_file_name = ? WHERE id = ?");
            $date = date("Y-m-d H:i:s");
            $updated = $date;

            $stmt->bind_param("si", $name, $file_id);
            $result = $stmt->execute();

            // Check for successful insertion
            if (!$result) {
                return FALSE;
            }

            /* close statement */
            $stmt->close();

            return TRUE;
            
        } else{
            return FALSE;
        }
	}

    public function uploadFile($user_id){
        $response = array();

        if (!empty($_FILES)){

            $userInfo = $this->getUserInfo($user_id);
            $schoolid = $userInfo["schoolid"];
            $role = $userInfo["role"];

            if($role == "TEACHER" || $role == "PRINCIPAL"){

                $tempFile = $_FILES['file']['tmp_name'];
                $fileName = $_FILES['file']['name'];
                $file_parts = pathinfo($fileName);

                switch($file_parts['extension'])
                {
                    case "jpg":
                        break;

                    case "png":
                        break;

                    case "rtf":
                        break;

                    case "pdf":
                        break;

                    case "jpeg":
                        break;

                    case "txt":
                        break;

                    case "tiff":
                        break;

                    case "JPG":
                        break;

                    case "PNG":
                        break;

                    case "RTF":
                        break;

                    case "PDF":
                        break;

                    case "JPEG":
                        break;

                    case "TXT":
                        break;

                    case "TIFF":
                        break;

                    case "php":
                        $response["error"] = true;
                        $response["message"] = "Error. File extension is not valid.";
                        return $response;

                    case "exe":
                        $response["error"] = true;
                        $response["message"] = "Error. File extension is not valid.";
                        return $response;

                    case NULL: // Handle no file extension
                        break;

                    default: 
                        $response["error"] = true;
                        $response["message"] = "Error. File extension is not valid.";
                        return $response;
                }

                $storeFolder = '/uploads';

                $folderToken = $this->unique_id(14);
                $targetPath = realpath(__DIR__ . '/../..') . "/". $storeFolder . "/" . $folderToken . "/";

                if (!file_exists($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }

                $arr = explode(".", $fileName, 2);
                $user_file_name = $arr[0];
                $fileName = $user_file_name;

                $fileName = $this->clear_string($fileName);

                $fileName = $fileName.".".$file_parts['extension'];

                $targetFile =  $targetPath.$fileName;

                move_uploaded_file($tempFile,$targetFile);

                    $datetime = date("Y-m-d H:i:s");

                    $token = $this->unique_id(16);

                    $stmt = $this->conn->prepare("INSERT INTO LCFiles (schoolid, token, file_name, folder_token,
                    user_file_name, uploaded) VALUES (?,?,?,?,?,?)");


                    $stmt->bind_param("ssssss", $schoolid, $token, $fileName, $folderToken, $user_file_name, $datetime);

                    $result = $stmt->execute();
                    $stmt->close();

                    // Check for successful insertion
                    if ($result){
                        $new_file_id = $this->conn->insert_id;


                        $response["error"] = false;
                        $response["file_token"] = $token;

                    } else {
                        // task failed to create
                        $response["error"] = true;
                        $response["message"] = "Error. Problem while creating database entry for file.";
                        $response["error-description"] = $_FILES['file']['error'];
                    }
            } else{
                $response["error"] = true;
                $response["message"] = "No permissions.";
            }


        } else {
            $response["error"] = true;
            $response["message"] = "Error. No files found.";

        }

        return $response;

    }

    function clear_string($str, $how = '-'){
        $search = array("ä", "ö", "ü", "ß", "Ä", "Ö",
                        "Ü", "&", "é", "á", "ó",
                        " :)", " :D", " :-)", " :P",
                        " :O", " ;D", " ;)", " ^^",
                        " :|", " :-/", ":)", ":D",
                        ":-)", ":P", ":O", ";D", ";)",
                        "^^", ":|", ":-/", "(", ")", "[", "]",
                        "<", ">", "!", "\"", "§", "$", "%", "&",
                        "/", "(", ")", "=", "?", "`", "´", "*", "'",
                        "_", ":", ";", "²", "³", "{", "}",
                        "\\", "~", "#", "+", ",",
                        "=", ":", "=)");
        $replace = array("ae", "oe", "ue", "ss", "Ae", "Oe",
                         "Ue", "und", "e", "a", "o", "", "",
                         "", "", "", "", "", "", "", "", "",
                         "", "", "", "", "", "", "", "", "",
                         "", "", "", "", "", "", "", "", "",
                         "", "", "", "", "", "", "", "", "",
                         "", "", "", "", "", "", "", "", "",
                         "", "", "", "", "", "", "", "", "");
        $str = str_replace($search, $replace, $str);
        $str = strtolower(preg_replace("/[^a-zA-Z0-9]+/", trim($how), $str));
        return $str;
    }

    private function format_size($size) {
      $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
      if ($size == 0) { return('n/a'); } else {
      return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
    }

     /************ Event Part *************/

    public function getEvents($user_id,$date,$limit){ 
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        $role = $userInfo["role"];
        $user_token = $userInfo["token"]; 
        
        $isStudent = true;
        $user_grade = $teacher_id = "";
        if($role == "STUDENT"){
            $user_grade = $userInfo["grade"];
        } else{
            $isStudent = false;
            $teacher_id = $userInfo["teacher_id"];
        }
        $user_level = "7";
        
        $events = array();
        
        if(empty($date) || !isset($date)){
             $date = date("Y-m-d");
        }
       
        $stmt = $this->conn->prepare("SELECT e.token,e.title,e.description,e.start_date,e.end_date,e.location,e.has_registration,e.registration_start,e.registration_end,e.form_link,e.registration_required,e.allowed_users,e.important,e.image_url,e.thumbnail_url,e.updated_at,e.created_at FROM Events e WHERE e.schoolid = ? AND DATE(e.start_date) >= ? ORDER BY start_date DESC");
        
        $stmt->bind_param("si",$schoolid,$date);
        $result = $stmt->execute();

        $stmt->store_result();
        $num_of_rows = $stmt->num_rows;
        $stmt->bind_result($token,$title,$description,$start_date,$end_date,$location,$has_registration,$registration_start,$registration_end,$form_link,$registration_required,$allowed_users,$important,$image_url,$thumbnail_url,$updated_at,$created_at);

        if($result){
            if($num_of_rows > 0){
                while($stmt->fetch()){
                    $selected = true;
                    if($allowed_users != NULL){
                        $filter = json_encode($allowed_users);
                        switch($filter["condition"]){
                            case "only_for_teachers":{
                                if($isStudent){
                                    $selected = false;
                                }
                                break;
                            }
                            case "only_for_students":{
                                if(!$isStudent){
                                    $selected = false;
                                }
                                break;
                            }
                            case "for_selected_levels":{
                                $selected = false;
                                if(isset($filter["content"]) && $isStudent){
                                    foreach($filter["content"] as $level){
                                        if($user_level == $level){
                                            $selected = true;
                                        }
                                    }
                                }
                                break;

                            }
                            case "for_selected_grades":{
                                $selected = false;
                                if(isset($filter["content"]) && $isStudent){
                                    foreach($filter["content"] as $grade){
                                        if($user_grade == $grade){
                                            $selected = true;
                                        }
                                    }
                                }
                                break;
                            }
                            case "for_selected_teachers":{
                                $selected = false;
                                if(isset($filter["content"]) && !$isStudent){
                                    foreach($filter["content"] as $teacher){
                                        if($teacher_id == $teacher){
                                            $selected = true;
                                        }
                                    }
                                }
                                break;
                            }
                            case "for_selected_users":{
                                $selected = false;
                                if(isset($filter["content"])){
                                    foreach($filter["content"] as $user){
                                        if($user_token == $user){
                                            $selected = true;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }

                    if($selected){
                          $event = array();
                          $event["token"] = $token;
                          $event["title"] = $title;
                          $event["description"] = $description;
                          $date = array();
                          $date["start_date"] = $start_date; 
                          $date["end_date"] = $end_date;
                          $event["date"] = $date;
                          $event["location"] = $location;
                          
                          if($has_registration){
                              $registration = array();
                              $registration["registration_start"] = $registration_start;
                              $registration["registration_end"] = $registration_end;
                              $registration["form_link"] = $form_link;
                              $event["registration"] = $registration;
                          }

                          $event["thumbnail_url"] = $thumbnail_url;
                          $event["updated_at"] = $update_at;
                          $event["created_at"] = $created_at;

                          array_push($events,$event);

                    }
                }
            }
        }

    }


    /************ Homework Part *************/

    public function getHomework($user_id,$done){
        $homework = array();

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];



        $stmt = $this->conn->prepare("SELECT h.token,h.title,h.desc,h.course,h.expire_date,h.first_reminder,h.second_reminder,h.done,h.updated_at,h.created_at,uh.accepted FROM Homework h, User_Homework uh WHERE h.schoolid = ? AND uh.homework_id = h.id AND uh.user_id = ? AND h.done = ? ORDER BY created_at DESC");

		$stmt->bind_param("sii",$schoolid,$user_id,$done);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$title, $desc, $course, $expire, $firstReminder,$secondReminder,$done,$updated,$created,$accepted);

        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    $tmp = array();
                    $tmp["id"] = $id;
                    $tmp["accepted"] = $accepted;
                    $tmp["title"] = $title;
                    $tmp["description"] = $desc;
                    $tmp["done"] = $done;
                    $tmp["course"] = $course;
                    $tmp["expire_date"] = $expire;
                    $tmp["first_reminder"] = $firstReminder;
                    $tmp["second_reminder"] = $secondReminder;
                    $tmp["updated_at"] = $updated;
                    $tmp["created_at"] = $created;
                    array_push($homework, $tmp);

	           }
            }
            else{
                $homework = "No homework.";
            }
        }
        else{
            $stmt->close();
            return FALSE;
        }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $homework;

    }



    public function getHomeworkId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM Homework WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->store_result();
            $num_of_rows = $stmt->num_rows;
            $stmt->bind_result($id);
            if($num_of_rows > 0){
                $stmt->fetch();
                $stmt->close();
                return $id; 
            } else{
                $stmt->close();
                return "hallo";
            }
            
        } else {
            return "hallo";
        }
    }

    public function deleteHomework($user_id,$homework_token){
        $result;

        $homework_id = $this->getHomeworkId($homework_token);

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT id,isOwner FROM `User_Homework` WHERE `schoolid` = ? AND `user_id` = ? AND `homework_id`= ?");

		$stmt->bind_param("sii",$schoolid,$user_id,$homework_id);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($id,$isOwner);

        /* Get the number of rows */
		$num_of_rows = $stmt->num_rows;
        if($num_of_rows > 0){
            $stmt->fetch();

            $stmt = $this->conn->prepare("DELETE FROM User_Homework WHERE schoolid = ? AND user_id = ? AND homework_id = ?");
            $stmt->bind_param("sii", $schoolid, $user_id, $homework_id);
            $result = $stmt->execute();

            if($isOwner){
                $stmt = $this->conn->prepare("DELETE FROM Homework WHERE schoolid = ? AND id = ?");
                $stmt->bind_param("si", $schoolid, $homework_id);
                $result = $stmt->execute();
            }
	   }
       else{
           $stmt->free_result();
            $stmt->close();
            return FALSE;
        }
	   /* close statement */
       $stmt->free_result();
	   $stmt->close();

        return $result;

    }

    public function setHomeworkDone($user_id,$homework_token,$done){
        $homework_id = $this->getHomeworkId($homework_token);

        $stmt = $this->conn->prepare("UPDATE `Homework` SET `done`= ? WHERE `id` = ?");
        $date = date("Y-m-d H:i:s");
        $updated = $date;
        $stmt->bind_param("ii", $done, $homework_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result){
            return $result;
        } else {
            // homework failed to update
            return FALSE;
        }

    }

    public function updateHomework($user_id,$homework_token,$title,$desc,$course,$expire_date,$first_reminder,$second_reminder){
        $homework_id = $this->getHomeworkId($homework_token);
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("UPDATE `Homework` SET `title`=?,`desc`=?,`course`=?,`expire_date`=?,`first_reminder`=?,`second_reminder`=? WHERE `id` = ?");
        $date = date("Y-m-d H:i:s");
        $updated = $date;
        $stmt->bind_param("ssssssi", $title,$desc,$course,$expire_date,$first_reminder,$second_reminder,$homework_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result){
            if($course != ""){
                $stmt = $this->conn->prepare("DELETE FROM Course_Homework WHERE schoolid = ? AND homework_id = ?");
                $stmt->bind_param("si", $schoolid, $homework_id);
                $stmt->execute();
                $num_affected_rows = $stmt->affected_rows;

                $role = $userInfo["role"]; $grade = "";
                if($role == "STUDENT"){
                    $grade = $userInfo["grade"];
                } else{
                    $grade = $userInfo["teacher_id"];
                }
                $res = $this->createCourseHomework($schoolid, $role, $grade, $course, $homework_id);
                if ($res) {
                     return TRUE;
                } else {
                    // homework failed to create
                    return FALSE;
                }

            } else{
                return $result;
            }
        } else {
            // homework failed to update
            return FALSE;
        }

    }


    public function
		createHomework($user_id,$title,$desc,$course,$expire_date,$first_reminder,$second_reminder){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        $random_token = $this->unique_id(16);
        $token = $this->unique_id(16);

        $stmt = $this->conn->prepare("INSERT INTO `Homework`(`token`,`schoolid`,`title`, `desc`,`course`, `expire_date`, `first_reminder`, `second_reminder`,`done`,`random_token`,`created_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $date = date("Y-m-d H:i:s");
        $created = $date;
        $done = 0;

        $title = $title;
        $desc = $desc;

        $stmt->bind_param("ssssssssiss", $token, $schoolid, $title, $desc, $course, $expire_date, $first_reminder, $second_reminder, $done,$random_token, $created);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result) {
            $new_homework_id = $this->conn->insert_id;
            $res = $this->createUserHomework($schoolid, $user_id, $new_homework_id);
            if ($res) {
                // homework created successfully
                if($course != ""){
                    $role = $userInfo["role"]; $grade = "";
                    if($role == "STUDENT"){
                        $grade = $userInfo["grade"];
                    } else{
                        $grade = $userInfo["teacher_id"];
                    }
                    $res = $this->createCourseHomework($schoolid, $role, $grade, $course, $new_homework_id);
                     if ($res) {
                         return $new_homework_id;
                     } else {
                        // homework failed to create
                        return FALSE;
                    }
                } else{
                    return $new_homework_id;
                }
            } else {
                // homework failed to create
                return FALSE;
            }
        } else {
            // homework failed to create
            return FALSE;
        }

    }

    public function createUserHomework($schoolid, $user_id, $homework_id) {
        $accepted = $isOwner = 1;
        $stmt = $this->conn->prepare("INSERT INTO User_Homework (schoolid, user_id, homework_id, accepted, isOwner) values(?, ?, ?, ?, ?)");
        $stmt->bind_param("siiii", $schoolid,$user_id, $homework_id,$accepted,$isOwner);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

    public function createCourseHomework($schoolid, $role, $grade, $course, $homework_id) {

        $id = "";
        if($role == "STUDENT"){
            $id = $this->getCourseIdForStudent($course,$grade);
        } else{
            $id = $this->getCourseIdForTeacher($course,$grade);
        }

        if($id != ""){
           $stmt = $this->conn->prepare("INSERT INTO Course_Homework (schoolid, course_id, homework_id) values(?, ?, ?)");
            $stmt->bind_param("sii", $schoolid,$id, $homework_id);
            $result = $stmt->execute();

            if (false === $result) {
                die('execute() failed: ' . htmlspecialchars($stmt->error));
            }
            $stmt->close();
            return $result;
        } else{
            return FALSE;
        }

    }

    private function getCourseIdForStudent($name,$grade) {
        $stmt = $this->conn->prepare("SELECT id FROM LCCourses WHERE course_id = ? AND grade = ?");
        $stmt->bind_param("ss", $name, $grade);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }

    private function getCourseIdForTeacher($name,$teacher) {
        $stmt = $this->conn->prepare("SELECT id FROM LCCourses WHERE course_id = ? AND teacher_id = ?");
        $stmt->bind_param("ss", $name, $teacher);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }

    public function shareHomework($user_id,$homework_token){
        $homework_id = $this->getHomeworkId($homework_token);

        $userInfo = $this->getUserInfo($user_id);

        $grade = $userInfo["grade"];
        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("SELECT token FROM Homework WHERE id = ? AND schoolid = ? LIMIT 1");
        $stmt->bind_param("is", $homework_id, $schoolid);
        $result = $stmt->execute();

		/* Store the result (to get properties) */
	   	$stmt->store_result();

		/* Bind the result to variables */
		$stmt->bind_result($token);

        while ($stmt->fetch()) {
             $url = SOS_URL."preview/p?type=homework&token=".$token."&s=0";
        }

	    /* free results */
	    $stmt->free_result();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            return $url;
        } else {
            return FALSE;
        }

    }

    public function shareHomeworkWithMembers($user_id,$homework_token,$member_list){
        $homework_id = $this->getHomeworkId($homework_token);

        $userInfo = $this->getUserInfo($user_id);
        $name = $userInfo["username"];
        $surname = $userInfo["surname"];

        $schoolid = $userInfo["schoolid"];

        //Send Push Notification

        foreach($member_list as $member_token){
            $member_id = $this->getMemberId($member_token);

            $stmt = $this->conn->prepare("INSERT INTO `User_Homework` (`schoolid`,`user_id`,`homework_id`,`accepted`,`isOwner`) VALUES (?,?,?,?,?)");

            $accepted = $isOwner = 0;
            $stmt->bind_param("siii", $schoolid,$member_id,$homework_id, $accepted, $isOwner);

            $result = $stmt->execute();

            // Check for successful insertion
            if ($result){
              $user_name = $surname.$name." hat eine Aufgabe mit dir geteilt";
              $this->sendHomeworkInvitationNotification($user_name);
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function shareHomeworkWithCourse($user_id,$homework_token,$course_abbreviation){
                  
        $homework_id = $this->getHomeworkId($homework_token);
         
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        
        $name = $userInfo["username"];
        $surname = $userInfo["surname"];
        
        $members = $this->getCourseMembersForSharing($course_abbreviation, $user_id);
        foreach($members as $member){
            if($member["member_id"] != $user_id){
                $stmt = $this->conn->prepare("INSERT INTO `User_Homework` (`schoolid`,`user_id`,`homework_id`,`accepted`,`isOwner`) VALUES (?,?,?,?,?)");

                $accepted = $isOwner = 0;
                $member_id = $member["member_id"];
                $stmt->bind_param("siiii", $schoolid,$member_id,$homework_id, $accepted,$isOwner);

                $result = $stmt->execute();

                // Check for successful insertion 
                if ($result){
                  $title = $surname." ".$name." hat eine Aufgabe mit dir geteilt.";
                  $this->sendHomeworkInvitationNotification($title, $member_id);
                }
            }
        }
        $stmt->close();
        return TRUE;
    }

    public function sendHomeworkInvitationNotification($notif_body,$user_id){

        $device_tokens = $this->getDeviceTokensForUser($user_id);
        if(!$this->sendMessage($device_tokens,"",$notif_body,"",'FF003A',"","","","")){
            return FALSE;
        }

       return TRUE;

     }

    private function getMemberId($token) {
        $stmt = $this->conn->prepare("SELECT id FROM LCUser WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            return $id;
        } else {
            return NULL;
        }
    }


    public function acceptHomeworkInvitation($user_id,$homework_token){
        $homework_id = $this->getHomeworkId($homework_token);
        if($homework_id != 0){
                $userInfo = $this->getUserInfo($user_id);

                $grade = $userInfo["grade"];
                $schoolid = $userInfo["schoolid"];

                $stmt = $this->conn->prepare("SELECT h.token,h.title,h.desc,h.course,h.expire_date,h.first_reminder,h.second_reminder,h.done,h.updated_at,h.created_at FROM Homework h WHERE h.schoolid = ? AND h.id = ? ORDER BY created_at DESC");

                $stmt->bind_param("si",$schoolid,$homework_id);

                $result = $stmt->execute();
                /* Store the result (to get properties) */
                $stmt->store_result();

                /* Get the number of rows */
                $num_of_rows = $stmt->num_rows;

                /* Bind the result to variables */
                $stmt->bind_result($id,$title, $desc, $course, $expire, $firstReminder,$secondReminder,$done,$updated,$created);

                if($result){
                    if($num_of_rows > 0){
                        while ($stmt->fetch()) { 
                            $random_token = $this->unique_id(16);
                            $token = $this->unique_id(16);

                            $stmt = $this->conn->prepare("INSERT INTO `Homework`(`token`,`schoolid`,`title`, `desc`,`course`, `expire_date`, `first_reminder`, `second_reminder`,`done`,`random_token`,`created_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                            $date = date("Y-m-d H:i:s");
                            $created = $date;
                            $stmt->bind_param("ssssssssiss", $token, $schoolid, $title, $desc, $course, $expire, $firstReminder, $secondReminder, $done,$random_token, $created);
                            $result = $stmt->execute();
                            $stmt->close();

                            // Check for successful insertion
                            if ($result) {
                                $new_homework_id = $this->conn->insert_id;
                                $stmt = $this->conn->prepare("UPDATE `User_Homework` SET `accepted`= 1,`homework_id`= ?,`isOwner`=1 WHERE `homework_id` = ? AND `user_id` = ?");
                                $stmt->bind_param("iii", $new_homework_id, $homework_id,$user_id);
                                $res = $stmt->execute();
                                $stmt->close();
                                if ($res) {
                                    // homework created successfully
                                    if($course != ""){
                                        $role = $userInfo["role"]; $grade = "";
                                        if($role == "STUDENT"){
                                            $grade = $userInfo["grade"];
                                        } else{
                                            $grade = $userInfo["teacher_id"];
                                        }
                                        $res = $this->createCourseHomework($schoolid, $role, $grade, $course, $new_homework_id);
                                         if ($res) {
                                             return $new_homework_id;
                                         } else {
                                            // homework failed to create
                                            return FALSE;
                                        }
                                    } else{
                                        return $new_homework_id;
                                    }
                                } else {
                                    // homework failed to create
                                    return FALSE;
                                }
                            } else {
                                // homework failed to create
                                return FALSE;
                            }

                       }
                    }
                }
                else{
                    $stmt->close();
                    return FALSE;
                }

               /* free results */
               $stmt->free_result();

               /* close statement */
               $stmt->close();
        } else{
            return FALSE;
        }
    }

    public function declineHomeworkInvitation($user_id,$homework_token){
        $homework_id = $this->getHomeworkId($homework_token);

        $userInfo = $this->getUserInfo($user_id);

        $schoolid = $userInfo["schoolid"];

        $stmt = $this->conn->prepare("DELETE FROM User_Homework WHERE user_id = ? AND homework_id = ?");
        $stmt->bind_param("ii", $user_id, $homework_id);
        $result = $stmt->execute();
        $stmt->close();

        // Check for successful insertion
        if ($result){
            return $result;
        } else {
            return FALSE;
        }
    }

    /************ Consultations ***************/

    public function getConsultations($user_id){
            $consultations = array();

            $userInfo = $this->getUserInfo($user_id);

            $grade = $userInfo["grade"];
            $schoolid = $userInfo["schoolid"];

            $stmt = $this->conn->prepare("SELECT t.id,t.name,t.surname,t.lessons,tc.full FROM `TeacherUser` as t, `Teacher_Consultations` as tc WHERE t.id = tc.teacher_id AND t.schoolid = ?");

            $stmt->bind_param("s",$schoolid);

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($id,$name, $surname, $lessons, $full);

            if($result){
                if($num_of_rows > 0){
                    while ($stmt->fetch()) {

                        $tmp = array();
                        $tmp["id"] = $id;
                        $tmp["name"] = $name;
                        $tmp["surname"] = $surname;
                        $tmp["lessons"] = $lessons;
                        $tmp["full"] = $full;
                        array_push($consultations, $tmp);

                   }
                }
                else{
                    $consultations = array();
                }
            }
            else{
                $stmt->close();
                return FALSE;
            }

           /* free results */
           $stmt->free_result();

           /* close statement */
           $stmt->close();

            return $consultations;
    }

    public function getConsultation($user_id,$teacher_id){

            $appointments = array();

            $userInfo = $this->getUserInfo($user_id);

            $grade = $userInfo["grade"];
            $schoolid = $userInfo["schoolid"];


            $stmt = $this->conn->prepare("SELECT c.id,c.time FROM `Consultations` c, `Teacher_Consultations` tc WHERE c.teacher_id = tc.teacher_id AND c.ver = 0 AND c.schoolid=? AND c.teacher_id = ?");

            $stmt->bind_param("si",$schoolid,$teacher_id);

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($id,$time);

            if($result){
                if($num_of_rows > 0){
                    while ($stmt->fetch()) {
                        $tmp = array();
                        $tmp["id"] = $id;
                        $tmp["time"] = $time;
                        array_push($appointments, $tmp);
                   }
                }
                else{
                    $appointments = array();
                }
            }
            else{
                $stmt->close();
                return FALSE;
            }

           /* free results */
           $stmt->free_result();

           /* close statement */
           $stmt->close();

            return $appointments;

        }

    public function registerGuestForConsultation($guest_name,$guest_email,$ct_id){
            if(!$this->sendGuestVerificationEmail($guest_email,$guest_name,$ct_id)) {
                    return false;
            } else{
                return true;
            }
        }

    public function registerUserForConsultation($user_id,$ct_id){
            $userInfo = $this->getUserInfo($user_id);

            $name = $userInfo["username"];
            $surname = $userInfo["surname"];
            $email = $userInfo["email"];

            $user_name = $surname.$name;
            $res = $this->registerGuestForConsultation($user_name,$email,$ct_id);
            return $res;
        }


    private function getEmailFromTemplate($title,$content,$link,$link_text,$greeting){
                 if(file_exists(__DIR__ ."/email.html")){
                    $message = file_get_contents(__DIR__ .'/email.html');
                    $parts_to_mod = array("EMAIL-TITLE", "EMAIL-CONTENT","BTN-LINK","BTN-TEXT","OTHER-CONTENT");
                    $replace_with = array($title, $content,$link,$link_text,$greeting);
                    for($i=0; $i<count($parts_to_mod); $i++){
                        $message = str_replace($parts_to_mod[$i], $replace_with[$i], $message);
                    }
                } else{
                    $message = "Es ist ein Fehler aufgetreten.<br/>Bitte wende dich an unseren Support unter support@schoolos.de. Vielen Dank!";
                    /* this likely won't ever be called, but it's good to have error handling */
                }
                return $message;
        }

    private function sendGuestVerificationEmail($email,$name, $ver){
                require(__DIR__ .'/php-mailer/class.phpmailer.php');
                require(__DIR__ .'/php-mailer/class.smtp.php');

                $to = trim($email);
                $subject = "Bitte bestätigen Sie Ihre Anmeldung zum Elternsprechtag.";
                $content =
        "Sehr geehrte/r $name,<br>vielen Dank für die Anmeldung zum Elternsprechtag am Leibniz-Gymnasium Potsdam.<br><br>

        Bitte bestätigen Sie Ihre Anmeldung:";

                $link = SOS_URL."register/consultations?s=$ver&e=$email&n=$name";
                $greeting = " Vielen Dank!<br>

        Sollten noch weitere Fragen bestehen, wenden Sie sich bitte an support@schoolos.de<br>
        <br>
        Mit freundlichen Grüßen<br><br>

        Ihr SOS Team <br>
        schoolos.de";

                $message = $this->getEmailFromTemplate("Anmeldung zum Elternsprechtag",$content,$link,"Jetzt bestätigen",$greeting);

                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->SMTPAuth = true; // authentication enabled
                $mail->SMTPSecure = 'ssl';
                $mail->Host = '';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'sos@schoolos.de';                 // SMTP username
                $mail->Password = '';
                $mail->Port = 465;                                     // TCP port to connect to

                $mail->setFrom('sos@web.schoolos.de', 'School Organising System');
                $mail->addAddress($email,$name);               // Name is optional
                $mail->addReplyTo('sos@schoolos.de', 'School Organising System');


                $mail->isHTML(true); // Set email format to HTML
                $mail->CharSet = 'UTF-8';

                $mail->Subject = 'Here is the subject';
                $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                $mail->Subject = $subject;
                $mail->Body    = $message;
                $altBody = $content.$link.$greeting;
                $mail->AltBody = $altBody;

                if(!$mail->send()) {
                    return false;
                } else {
                    return true;
                }
        }


    //Bug Report

    public function getBugReport($user_id){
            $bugs = array();

            $userInfo = $this->getUserInfo($user_id);

            $email = $userInfo["email"];
            $schoolid = $userInfo["schoolid"];

            $stmt = $this->conn->prepare("SELECT t.token,t.name,t.desc,t.done,t.created_at FROM `BugReports` as t WHERE t.schoolid = ? AND author = ?");

            $stmt->bind_param("ss",$schoolid, $email);

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($id,$name,$desc,$done,$created);

            if($result){
                if($num_of_rows > 0){
                    while ($stmt->fetch()) {

                        $tmp = array();
                        $tmp["id"] = $id;
                        $tmp["name"] = $name;
                        $tmp["description"] = $desc;
                        $tmp["done"] = $done;
                        $tmp["created_at"] = $created;
                        array_push($bugs, $tmp);

                   }
                }
                else{
                    $bugs = array();
                }
            }
            else{
                $stmt->close();
                return FALSE;
            }

           /* free results */
           $stmt->free_result();

           /* close statement */
           $stmt->close();

           return $bugs;
    }

    public function saveBugReport($user_id,$name,$desc,$report_id,$link){
            $userInfo = $this->getUserInfo($user_id);
            $email = $userInfo["email"];
            $author = $userInfo["username"];
            $token = $this->unique_id(16);
            $schoolid = $userInfo["schoolid"];

            $stmt = $this->conn->prepare("INSERT INTO `BugReports` (`token`,`schoolid`, `name`, `desc`, `link`, `report_id`, `done`, `author`, `created_at`) VALUES (?,?,?,?,?,?,?,?,?)");
            $date = date("Y-m-d H:i:s");
            $created = $date;
            $done = 0;
            $stmt->bind_param("ssssssiss", $token, $schoolid, $name, $desc, $link, $report_id, $done, $email, $created);

            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                $res = $this->sendBugReport("",$name,$desc,$link,$report_id,$author,$date);
                return $res;
            } else {
                // homework failed to create
                return FALSE;
            }
    }


    private function sendBugReport($email,$name,$desc,$link,$report_id,$author,$date){
                $to = trim($email);

                $subject = "[SOS] New bug";

                $headers = "From: <> Content-Type: text/plain";

                $msg =
                    "New bug found:

                    Name: $name

                    Description: $desc

                    Link: $link

                    Report ID: $report_id

                    Created by $author at $date

                    --
                    Please fix it as soon as possible!

                    SOS
                    web.schoolos.de";

                return mail($to, $subject, $msg, $headers);
    }


     public function saveUserFeedback($user_id,$name,$message){
            $userInfo = $this->getUserInfo($user_id);
            $author = $userInfo["email"];
            $schoolid = $userInfo["schoolid"];
            $date = date("Y-m-d H:i:s"); 

            $res = $this->sendUserFeedback("",$name,$message,$author,$date);
            return $res;
    } 

    private function sendUserFeedback($email,$name,$message,$author,$date){
                $to = trim($email);

                $subject = "[SOS] Neues User Feedback";

                $headers = "From: <> Content-Type: text/plain";

                $msg =
                    "Neues User Feedback:

                    Titel: $name
 
                    Nachricht: $message

                    Eingesendet von $author am $date

                    --
                    Vielen Dank !

                    SOS
                    web.schoolos.de";

               return mail($to, $subject, $msg, $headers);
    }


    //Settings
    public function getUserSettings($user_id){
            $userInfo = $this->getUserInfo($user_id);

            $version = SOS_VERSION;

            $settings["email"] = $userInfo["email"];
            $settings["first_name"] = $userInfo["surname"];
            $settings["name"] = $userInfo["username"];
            $settings["schoolid"] = $userInfo["schoolid"];
            $settings["grade"] = $userInfo["grade"];

            $role = "Administrator";

            if($userInfo["role"] == "STUDENT"){
                $role = "Schüler";
                $count_courses = $this->countCoursesForUser($user_id,$userInfo["schoolid"]);
                $settings["count_courses"] = $count_courses;
            } else if($userInfo["role"] == "TEACHER" || $userInfo["role"] == "PRINCIPAL"){
                $role = "Lehrer";
                $settings["teacher_id"] = $userInfo["teacher_id"];
            }
            $settings["role"] = $role;
            $settings["version"] = $version;


            return $settings;
    }

    //Preview

     public function getAushangForPreview($aushang_token){
        $aushang = array();

        $stmt = $this->conn->prepare("SELECT id,verified, title,text,image,action_type,action_url,category,updated_at,created_at,random_token FROM `Aushang` WHERE random_token = ?");

		$stmt->bind_param("s",$aushang_token);

		$stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$ver,$title, $text, $image, $type,$link,$category,$updated,$created,$token);
		$timetable;

        $url = "";
		while ($stmt->fetch()) {
                $aushang["id"] = $id;
                $aushang["verified"] = $ver;
                $aushang["title"] = $title;
                $aushang["description"] = $text;
                if($image == "-"){
                    $aushang["image"] = "Kein Bild verfügbar";
                }
                else{
                    $aushang["image"] = $image;
                }
                $aushang["category"] = $category;
                $aushang["action_type"] = $type;
                $aushang["action_url"] = $link;
                $aushang["updated_at"] = $updated;
                $aushang["created_at"] = $created;
	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $aushang;

     }

     public function getHomeworkForPreview($homework_token){
        $homework = array();

        $stmt = $this->conn->prepare("SELECT h.id,h.title,h.desc,h.expire_date,h.first_reminder,h.second_reminder,h.done,h.updated_at,h.created_at FROM Homework h WHERE token = ?");

		$stmt->bind_param("s",$homework_token);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($id,$title, $desc, $expire, $firstReminder,$secondReminder,$done,$updated,$created);

        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    $homework["id"] = $id;
                    $homework["title"] = $title;
                    $homework["description"] = $desc;
                    $homework["done"] = $done;
                    $homework["expire_date"] = $expire;
                    $homework["first_reminder"] = $firstReminder;
                    $homework["second_reminder"] = $secondReminder;
                    $homework["updated_at"] = $updated;
                    $homework["created_at"] = $created;

	           }
            }
            else{
                $homework = "No homework.";
            }
        }
        else{
            $stmt->close();
            return FALSE;
        }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

        return $homework;

     }

     public function getVPForPreview($school_token,$daynumber){
        $jsonVP;

        $stmt = $this->conn->prepare("SELECT vp.jsonVP FROM `VPs` vp,Schools s WHERE vp.daynumber=? AND vp.schoolid=s.schoolid AND s.random_token=?");

		$stmt->bind_param("is",$daynumber,$school_token);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($json_vp);
        if($result){
            $stmt->fetch();
            $jsonVP = $json_vp;
            $jsonVP = json_decode($jsonVP, true);
        }
        else{
            $stmt->close();
            return FALSE;
        }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

      return $jsonVP; 

     }
    
    public function getViewerDashboardURL($user_id){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];
        
        $url = SOS_URL."viewer/dashboard/index.html";
        
        return $url;

    }


     public function getViewerVPURL($user_id,$daynumber){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        $url = "";
        $token = $this->getSchoolToken($schoolid);
        if(empty($daynumber)){
            $daynumber = $this->getDayNumber(true);
        }
        $daynumber_extension = "&daynumber=".$daynumber;
        $url = SOS_URL."viewer/vpviewer/?type=vp&s=".$token.$daynumber_extension;
        
        return $url;

    }
    
    public function getViewerTeacherVPURL($user_id,$daynumber){
        $userInfo = $this->getUserInfo($user_id);
        $schoolid = $userInfo["schoolid"];

        $url = "";
        $token = $this->getSchoolToken($schoolid);
        if(empty($daynumber)){
            $daynumber = $this->getDayNumber(true);
        }
        $daynumber_extension = "&daynumber=".$daynumber;
        $url = SOS_URL."viewer/teacherviewer/?type=vp&s=".$token.$daynumber_extension;
        
        return $url;

    }




     public function sendHomeworkNotification(){

            $stmt = $this->conn->prepare("SELECT h.id,h.title,h.desc,h.expire_date FROM Homework h WHERE done = 0 AND (h.expire_date BETWEEN DATE_ADD(NOW(), INTERVAL 1 DAY) AND DATE_ADD(NOW(), INTERVAL 86432 SECOND) OR h.expire_date BETWEEN DATE_SUB(NOW(), INTERVAL 32 SECOND) AND NOW())");

            $result = $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            /* Bind the result to variables */
            $stmt->bind_result($id,$title, $desc, $expire_date);

            if($result){
                if($num_of_rows > 0){
                    while ($stmt->fetch()) {

                        $device_tokens = $this->getDeviceTokensForUserHomework($id);

                        $today = date("Y-m-d");
                        $day = date_format(date_create($expire_date), 'Y-m-d');
                        $notif_title = 'Deine Aufgabe "'.$title.'" ist in Kürze fällig!';
                        if($today == $day){
                             $notif_title = 'Deine Aufgabe "'.$title.'" ist fällig!';
                        }

                        $this->sendMessage($device_tokens,$notif_title,$desc,"","536DFE","","","","");
                   }
                }
                else{
                    return FALSE;
                }
            }
            else{
                $stmt->close();
                return FALSE;
            }

           /* free results */
           $stmt->free_result();

           /* close statement */
           $stmt->close();

           return TRUE;
     }

     private function getDeviceTokensForUserHomework($homework_id){

        $device_tokens = array();

        $stmt = $this->conn->prepare("SELECT d.device_token FROM Devices d, User_Devices ud, User_Homework uh, LCUser lc WHERE uh.homework_id = ? AND uh.user_id = ud.user_id AND ud.device_id = d.id AND lc.id = uh.user_id");

		$stmt->bind_param("i",$homework_id);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($device_token);

        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    array_push($device_tokens, $device_token);
	           }
            }
        }

        /* free results */
	   $stmt->free_result();

       $stmt->close();
       return $device_tokens;
     }

     private function getDeviceTokensForUser($user_id){

        $device_tokens = array();

        $stmt = $this->conn->prepare("SELECT d.device_token FROM Devices d, User_Devices ud WHERE ud.user_id = ? AND ud.device_id = d.id");

		$stmt->bind_param("i",$user_id);

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($device_token);

        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    array_push($device_tokens, $device_token);
	           }
            }
        }

        /* free results */
	   $stmt->free_result();

       $stmt->close();
       return $device_tokens;
     }

     private function getDayNumber($forToday){
        $n = date("w");
        //console.log(n);

        if($n == 5){
            $n = ($forToday) ? 5 : 1;
        }
        else if($n == 6){
            $n = ($forToday) ? 1 : 1;
        }
        else if($n == 0){
            $n = ($forToday) ? 1 : 1;
        } else{
            if(!$forToday){
               $n++;
            }
        }


        return $n;
    }


     public function sendTimetableNotification(){

        $stmt = $this->conn->prepare("SELECT id FROM LCUser WHERE hasLoggedIn = 1 AND role = 'STUDENT'");

		$result = $stmt->execute();
		/* Store the result (to get properties) */
	   	$stmt->store_result();

	   	/* Get the number of rows */
		$num_of_rows = $stmt->num_rows;

		/* Bind the result to variables */
		$stmt->bind_result($user_id);
        if($result){
            if($num_of_rows > 0){
                while ($stmt->fetch()) {
                    $device_tokens = $this->getDeviceTokensForUser($user_id);
                    $daynumber = $this->getDayNumber(false);
                    $notification = $this->getVPForNotification($user_id,$daynumber);
                    if($notification["title"] != "Keine Änderungen morgen"){
                        $this->sendMessage($device_tokens,$notification["title"],$notification["body"],$notification["subtitle"],'FF003A',"","","","");
                    }

	           }
            }
        } else{
                $stmt->close();
                return FALSE;
        }

        /* free results */
	   $stmt->free_result();

       $stmt->close();
       return TRUE;

     }

     private function getVPForNotification($user_id,$daynumber){

        $userInfo = $this->getUserInfo($user_id);

        $role = $userInfo["role"];
        $version = $userInfo["version"];
        $schoolid = $userInfo["schoolid"];

        $grade = "";
        if($grade = "STUDENT"){
            $grade = $userInfo["grade"];
        }

        $teacher_id = "";
        if($role == "TEACHER" || $role == "PRINCIPAL"){
            $teacher_id = $userInfo["teacher_id"];
        }

		$stmt = $this->conn->prepare("SELECT jsonVP FROM `VPs` WHERE daynumber = ? AND schoolid = ?");

		$stmt->bind_param("is",$daynumber, $schoolid);

        $stmt->execute();
			 /* Store the result (to get properties) */
	    $stmt->store_result();

	    /* Get the number of rows */
	    $num_of_rows = $stmt->num_rows;

	    /* Bind the result to variables */
	    $stmt->bind_result($vpData);
	    $notif = "";
	    while ($stmt->fetch()){

			if($version == "sos.v1.200" || $version == "sos.v1.201" || $version == "sos.v1.210"){

                if($role == "STUDENT"){
                    $notif = $this->parseVPForNotification($vpData, $daynumber, $role, $grade, $user_id, $schoolid);
                }
                else if($role == "TEACHER" || $role == "PRINCIPAL"){
                    $notif = $this->parseVPForNotification($vpData, $daynumber, $role, $teacher_id, $user_id, $schoolid);
                }
			}

	   }

	   /* free results */
	   $stmt->free_result();

	   /* close statement */
	   $stmt->close();

		return $notif;

    }

     private function parseVPForNotification($vpData, $day, $role, $grade, $user_id, $schoolid){

        $notif_content = array();
        $are_holidays = $this->areHolidays($schoolid);

        $json = json_decode($vpData, true);
        $week = $json["additions"]["week"];

        if(!$are_holidays){
            $courses = $this->getCoursesForUser($user_id, $schoolid);
            $courseNames = $courses["names"];
            $courseIds = $courses["ids"];

            $grade = strval($grade);
            $grades = strtolower($grade);
            $testgrades = array_map('strtolower', array(strtolower($grade)));
            $dayn = intval($day);

            if (
                $dayn != null
                && isset($dayn) && is_integer($dayn)
                && ($dayn<=7) && ($dayn>=1)
               ) {

                // Legal input
                http_response_code(200);

                $response_body = array();

                if($role == "STUDENT"){
                    if ($grades==null) {

                        // All data
                        foreach ($json["omission"] as $key_grade=>$value_omissions) {
                            if($key_grade != "N"){
                                foreach ($value_omissions as $omission) {
                                    array_push($response_body, $omission);
                                }
                            }
                        }
                    } else {
                        foreach ($testgrades as $grade) {

                            if (in_array($grade, array("5","6","7","8","9","10","11","12"))) {
                                // All data for whole years, e.g. "8", "6" etc.
                                if ($grade==5||$grade==6) {foreach ($json["omission"]["5-6"] as $possiblematch) {
                                    if (preg_replace('/[^0-9]/','',$possiblematch["grade"]) == $grade) {array_push($response_body, $possiblematch);}}}
                                else {foreach($json["omission"][$grade] as $match) {array_push($response_body, $match);}}
                            } else {
                                // Specific grade, e.g. "5a", "8c", "10e" etc.
                                $parentgrade = preg_replace('/[^0-9]/','',$grade);
                                if ($parentgrade==5 || $parentgrade==6) {$parentgrade="5-6";}
                                foreach ($json["omission"][$parentgrade] as $possiblematches) {
                                    if ((preg_match('/^'.$parentgrade.'\b/', $possiblematches["grade"], $matches)) || (preg_match('/\b'.$parentgrade.'\b/', $possiblematches["grade"], $matches)) || (preg_match('/^'.$grade.'/', $possiblematches["grade"], $matches)) || (preg_match('/\b'.$grade.'/', $possiblematches["grade"], $matches))){
                                           array_push($response_body, $possiblematches);
                                    }
                                }   
                            }
                        }
                    }
                } else {
                    foreach ($json["omission"] as $key_grade=>$value_omissions){
                        if($key_grade != "N"){
                            if (preg_match('/^'.$grade.'/', $omission["initials"], $matches) || preg_match('/\b'.$grade.'/', $omission["initials"], $matches)){
                                        $omission["day"] = $day;
                                         if($this->containsWordFromList($omission["initials"],["statt","für","anstatt"]) && !preg_match('/^'.$grade.'/', $omission["initials"], $matches)){
                                            $omission["description"] = $omission["initials"];
                                        }
                                        $omission["initials"] = $omission["grade"];
                                        unset($omission["grade"]);
                                        array_push($response_body, $omission);
                            }
                        }
                     }
                }

                //Parsen des Stundenplans
                //1. Step: VP und Stundenplan verbinden

                $res = $response_body;

                usort($res, function($a, $b) {
                    return strcmp($a["lesson"], $b["lesson"]);
                });

                usort($res, function($a, $b) {
                    return strcmp($a["lesson_type"], $b["lesson_type"]);
                });
                //2. Sortieren
                usort($res, function($a, $b) {
                    return strcmp($a["hour"], $b["hour"]);
                });

                //3. Chechen ob Kurs vom Schüler ausgewählt ist und 4. doppelte Einträge raus streichen
                if($role != "TEACHER" && $role != "PRINCIPAL"){
                    foreach ($res as $key => $lesson)
                    {
                      $lessons = explode(" ", $res[$key]["lesson"]);
                      $lesson_to_check = $res[$key]["lesson"];
                      if(!is_numeric($lessons[1])){
                            $lesson_to_check = $lessons[0];
                      }
                      if(in_array($lesson_to_check, $courseNames) || $lesson_to_check == "-"){
                            $res[$key]["course_id"] = $courseIds[array_search($lesson_to_check,$courseNames)];
                      }
                      else{
                            unset($res[$key]);
                      }

                    }
                    $res = array_values($res);
                }

                $body = "";

                foreach ($res as $key => $lesson)
                {
                    if($res[$key]["lesson_type"] == "block"){
                        $body .= $res[$key]["hour"].". Block: ".$res[$key]["lesson"].' '.$res[$key]["description"];
                    } else if($res[$key]["lesson_type"] == "first_half"){
                        $lesson_number = 2 * $res[$key]["hour"] - 1;
                        $body .= $lesson_number.". Stunde: ".$res[$key]["lesson"].' '.$res[$key]["description"];
                    } else if($res[$key]["lesson_type"] == "second_half"){
                        $lesson_number = 2 * $res[$key]["hour"];
                        $body .= $lesson_number.". Stunde: ".$res[$key]["lesson"].' '.$res[$key]["description"];
                    }

                    $body .= ". ";
                }

                $res = array_values($res);

                $count = count($res);
                if ($count>0) {
                    $notif_content["title"] = "Du hast morgen ".$count.' Änderungen im Studenplan';
                    if($count == 1){
                        $notif_content["title"] = "Du hast morgen 1 Änderung im Studenplan";
                    }
                    $notif_content["body"] = $body;
                    $notif_content["subtitle"] = "Plan aktualisert am ".$json["last_updated"];
                } else {
                    // No results
                    $notif_content["title"] = "Keine Änderungen morgen";
                    $notif_content["body"] = "Du hast morgen keine Änderung im Stundenplan.";
                    $notif_content["subtitle"] = "Plan aktualisert am ".$json["last_updated"];
                }


            }
        }

        return $notif_content;
	}

     private function sendMessage($player_id,$title,$desc,$subtitle,$accentColor,$action_url,$action_buttons,$web_action_buttons,$ios_attachments){
        $headings = array(
			"en" => $title,
            "de" => $title
 			);

		$content = array(
			"en" => $desc,
            "de" => $desc
			);

         $subtitles = array(
			"en" => $subtitle,
            "de" => $subtitle
			);

		$fields = array(
			'app_id' => "",
			'include_player_ids' => $player_id,
			'data' => array(),
			'contents' => $content,
            'headings' => $headings,
            'ios_sound' => 'sos_notification.mp3',
            'android_sound' => 'sos_notification_sound',
            'chrome_web_icon' => '',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 'Increase',
            'subtitle' => $subtitles,
            'android_led_color' => 'FFFF5900',
            'android_accent_color' => 'FF'.$accentColor,
            'url' => $action_url,
            'ios_attachments' => $ios_attachments,
            'buttons' => array($action_buttons),
            'web_buttons' => array($web_action_buttons)
            
		);

		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
												   'Authorization: Basic'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

	}

     public function registerDeviceForNotifications($user_id,$device_token){

        $stmt = $this->conn->prepare("SELECT COUNT(device_token) AS theCount FROM Devices d, User_Devices ud WHERE d.device_token = ? AND d.id = ud.device_id AND ud.user_id = ?");

        $stmt->bind_param("si", $device_token,$user_id);

        $stmt->execute();

        $stmt->bind_result($theCount);

        $stmt->store_result();

        $stmt->fetch();

        if($theCount > 0) {
             $stmt->close();
             return TRUE;
        } else {

            $userInfo = $this->getUserInfo($user_id);
            $schoolid = $userInfo["schoolid"];

            $token = $this->unique_id(16);

            $created = date("Y-m-d H:i:s");

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO `Devices` (`schoolid`,`token`,`device_token`, `created_at`) values(?, ?,?,?)");

            $stmt->bind_param("ssss", $schoolid,$token,$device_token, $created);

            $result = $stmt->execute();

            // Check for successful insertion
            if ($result){
                $new_device_id = $this->conn->insert_id;
                $res = $this->createUserDevice($schoolid, $user_id, $new_device_id);
                if(!$res){
                     $stmt->close();
                    return FALSE;
                }
                $stmt->close();
                return TRUE;
            } else {
                 $stmt->close();
                return FALSE;
            }
        }
     }

    public function createUserDevice($schoolid, $user_id, $device_id) {
        $stmt = $this->conn->prepare("INSERT INTO User_Devices (schoolid, user_id, device_id) values(?, ?, ?)");
        $stmt->bind_param("sii", $schoolid,$user_id, $device_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }


    private function areHolidays($schoolid){

        $are_holidays = FALSE;
        $stmt = $this->conn->prepare("SELECT areHolidays FROM `Schools` WHERE schoolid = ?");

        $stmt->bind_param("s",$schoolid);

        $result = $stmt->execute();
        /* Store the result (to get properties) */
        $stmt->store_result();

        /* Get the number of rows */
        $num_of_rows = $stmt->num_rows;

        /* Bind the result to variables */
        $stmt->bind_result($areHolidays);

        if($result){
            $stmt->fetch();
            $are_holidays = $areHolidays;
        }

        /* free results */
	    $stmt->free_result();

       /* close statement */
       $stmt->close();
       return $are_holidays;
    }


    //Parser functions
	private function parseVP($vpData, $day, $role, $grade, $user_id, $schoolid){

        $are_holidays = $this->areHolidays($schoolid);

        $json = json_decode($vpData, true);
        $week = $json["additions"]["week"];

        $data = "";

        if($role == "STUDENT"){
            $data = $this->getTimetable($user_id, $week, $day, true,$schoolid);
        } else if($role == "TEACHER" || $role == "PRINCIPAL"){
            $data = $this->getTeacherTimetable($user_id, $week, $day, true);
        }
        $timetableData = $data["data"];
        $timetableURL = $data["url"];

        if(!$are_holidays){
            $courses = $this->getCoursesForUser($user_id, $schoolid);
            $courseNames = $courses["names"];
            $courseIds = $courses["ids"];
            $courseDescriptions = $courses["descriptions"];

            $grade = strval($grade);
            $grades = strtolower($grade);
            $testgrades = array_map('strtolower', array(strtolower($grade)));
            $dayn = intval($day);

            if (
                $dayn != null
                && isset($dayn) && is_integer($dayn)
                && ($dayn<=7) && ($dayn>=1)
               ) {

                // Legal input
                http_response_code(200);

                $response_body = array();
                
                if($role == "STUDENT"){
                    if ($grades==null) {

                        // All data
                        foreach ($json["omission"] as $key_grade=>$value_omissions) {
                            if($key_grade != "N"){
                                foreach ($value_omissions as $omission) {
                                    array_push($response_body, $omission);
                                }
                            }
                            
                        }
                    } else {
                        foreach ($testgrades as $grade) {

                            if (in_array($grade, array("5","6","7","8","9","10","11","12"))) {
                                // All data for whole years, e.g. "8", "6" etc.
                                if ($grade==5||$grade==6) {foreach ($json["omission"]["5-6"] as $possiblematch) {
                                    if (preg_replace('/[^0-9]/','',$possiblematch["grade"]) == $grade) {array_push($response_body, $possiblematch);}}}
                                else {foreach($json["omission"][$grade] as $match) {array_push($response_body, $match);}}
                            } else {
                                // Specific grade, e.g. "5a", "8c", "10e" etc.
                                $parentgrade = preg_replace('/[^0-9]/','',$grade);
                                if ($parentgrade==5 || $parentgrade==6) {$parentgrade="5-6";}
                                foreach ($json["omission"][$parentgrade] as $possiblematches) {
                                    if ((preg_match('/^'.$parentgrade.'\b/', $possiblematches["grade"], $matches)) || (preg_match('/\b'.$parentgrade.'\b/', $possiblematches["grade"], $matches)) || (preg_match('/^'.$grade.'/', $possiblematches["grade"], $matches)) || (preg_match('/\b'.$grade.'/', $possiblematches["grade"], $matches))){
                                           array_push($response_body, $possiblematches);
                                    }
                                }   
                        }
                    }
                }
                } else {
                    foreach ($json["omission"] as $key_grade=>$value_omissions){
                        if($key_grade != "N"){
                            foreach ($value_omissions as $omission){
                                  if (preg_match('/^'.$grade.'/', $omission["initials"], $matches) || preg_match('/\b'.$grade.'/', $omission["initials"], $matches)){
                                        $omission["day"] = $day;
                                        if($this->containsWordFromList($omission["initials"],["statt","für","anstatt"]) && !preg_match('/^'.$grade.'/', $omission["initials"], $matches)){
                                            $omission["initials"] = $omission["initials"];
                                            $omission["grade"] = $omission["grade"];
                                            array_push($response_body, $omission);
                                        } else{
                                            $omission["initials"] = $omission["grade"];
                                            array_push($response_body, $omission);
                                            unset($omission["grade"]);
                                        }
                                }
                            }
                        }
                     }
                }
            
                //1. Sortieren
                usort($timetableData, function($a, $b) {
                    return strcmp($a["lesson_type"], $b["lesson_type"]);
                });
                
                usort($timetableData, function($a, $b) {
                    return strcmp($a["hour"], $b["hour"]);
                });
                
       
                
                $res = array();
                foreach ($timetableData as $key => $lesson){
                    $to_add = true;
                    foreach($response_body as $key_vp => $entry){
                        if ((preg_match('/\bstatt\b/', $entry["lesson"], $matches) && (!preg_match('/^'.$lesson["lesson"].'\b/', $entry["lesson"], $matches) && preg_match('/\b'.$lesson["lesson"].'/', $entry["lesson"], $matches))) || ((preg_match('/^'.$lesson["lesson"].'/', $entry["lesson"], $matches) || preg_match('/\b'.$lesson["lesson"].'/', $entry["lesson"], $matches)) && !preg_match('/\bstatt\b/', $entry["lesson"], $matches))){
                            if(($lesson["hour"] == $entry["hour"]) && (($entry["lesson_type"] == $lesson["lesson_type"]) || ($entry["lesson_type"] == "block" && $lesson["lesson_type"] != "block") || ($entry["lesson_type"] != "block" && $lesson["lesson_type"] == "block"))){
                                array_push($res,$entry);
                                unset($response_body[$key_vp]);
                                $to_add = false;
                            }
                              
                        }
                    }
                    if($to_add){
                        if(isset($res[$key-1])){
                            if($res[$key-1]["hour"] == $lesson["hour"] && ($res[$key-1]["lesson_type"] == "block" || $lesson["lesson_type"] == "block") && ($res[$key-1]["lesson_type"] != $lesson["lesson_type"]) && isset($res[$key-1]["ograde"])){
                                
                            } else{
                                array_push($res,$lesson); 
                            }
                        } else{
                            array_push($res,$lesson); 
                        }
                       
                    }
                    
                        
                }
               
                
                if(count($response_body) > 0){
                    
                    usort($response_body, function($a, $b) {
                        return strcmp($a["lesson_type"], $b["lesson_type"]);
                    });

                    usort($response_body, function($a, $b) {
                        return strcmp($a["hour"], $b["hour"]);
                    });
                    
                    if(count($res) > 0){
                        foreach ($res as $key => $lesson){
                            foreach($response_body as $key_vp => $entry){
                                if($entry["hour"] <= $lesson["hour"]){
                                    array_splice($res, $key, 0, array($response_body[$key_vp]));
                                    unset($response_body[$key_vp]);
                                } else{
                                    array_splice($res, $key+1, 0, array($response_body[$key_vp]));
                                    unset($response_body[$key_vp]);
                                }
                            }
                        }
                    } else{
                        $res = $response_body;
                    }
                        
                }
                
                
                //3. Chechen ob Kurs vom Schüler ausgewählt ist
                if($role != "TEACHER" && $role != "PRINCIPAL"){
                    foreach ($res as $key => $lesson)
                    {
                      $lessons = explode(" ", $res[$key]["lesson"]);
                      $lesson_to_check = $res[$key]["lesson"];
                      if(!is_numeric($lessons[1])){
                            $lesson_to_check = $lessons[0];
                      }
                      if(in_array($lesson_to_check, $courseNames)){
                            $res[$key]["course_id"] = $courseIds[array_search($lesson_to_check,$courseNames)];
                      }
                      else if($lesson_to_check == "Alle"){
                          $res[$key]["course_id"] = "-";
                      }
                      else{
                            unset($res[$key]);
                      }
                    }
                    $res = array_values($res);
                } 
                //4. Events berücksichtigen
                
                $event = "";
                if($role != "TEACHER" && $role != "PRINCIPAL"){
                    foreach ($res as $key => $lesson){
                        if($lesson["hour"] == 0){
                            $event = $lesson["description"]." mit Kurs ".$lesson["lesson"]." bei ".$lesson["initials"];  
                            unset($res[$key]);
                        }
                    }
                } else{
                    foreach ($res as $key => $lesson){
                        if($lesson["hour"] == 0){
                            $event = $lesson["description"]." mit Kurs ".$lesson["lesson"];      
                            unset($res[$key]);
                        }
                    }
                }
           
                $res = array_values($res);
                // 5. Einträge checken
                usort($res, function($a, $b) {
                    return strcmp($a["lesson_type"], $b["lesson_type"]);
                });
                usort($res, function($a, $b) {
                    return strcmp($a["hour"], $b["hour"]); 
                });
                foreach ($res as $key => $lesson){
                        if($key != 0){
                            if($res[$key-1]["hour"] == $res[$key]["hour"]){
                                if(!array_key_exists("ograde", $res[$key-1]) && !array_key_exists("ograde", $res[$key])){
                                    break;
                                }
                                if(!array_key_exists("ograde", $res[$key-1]) || !array_key_exists("ograde", $res[$key])){
                                    $index_ograde = ($key-1);
                                    $other_index = $key;
                                    if(array_key_exists("ograde", $lesson)){
                                        $index_ograde = $key;
                                        $other_index = ($key-1);
                                    }
                                   
                                    if($this->containsWordFromList($res[$index_ograde]["description"],["Klausur","Examen","Prüfung","KA","Klassenarbeit","Abitur","Abi","ABI"])){
                                            unset($res[$other_index]);
                                        } else if($this->containsWordFromList($res[$index_ograde]["description"],["Exkursion","Ausflug","Auslandsreise","Klassenfahrt","Kursfahrt"])){
                                            unset($res[$other_index]);
                                        } else if($this->containsWordFromList($res[$index_ograde]["description"],["für alle"," alle","Event","Beratung"])){
                                            unset($res[$other_index]);
                                        }
                                  
                                    
                                }
                                
                        }
                    }
                }
                
                
                $res = array_values($res);
                
                //6. Aufräumen, unnötige Paramater entfernen
                foreach ($res as $key => $lesson)
                {

                    $res[$key]["time"] = $this->getTime($res[$key]["hour"],$res[$key]["lesson_type"]);
                    
                    $res[$key]["period"] = (string)$res[$key]["hour"];
                    if(array_key_exists("hour", $res[$key])){
                                unset($res[$key]["hour"]);
                    }

                    if(!array_key_exists("lesson_type", $res[$key])){
                       $res[$key]["lesson_type"] = "block";
                    }
                    
                    if($res[$key]["lesson_type"] == "first_half"){
                        $res[$key]["duration"] = "45";
                    } else if($res[$key]["lesson_type"] == "second_half"){
                        $res[$key]["duration"] = "45";
                    } else{
                        $res[$key]["duration"] = "90";
                    }
                
                    $color = "#FF5900";
                    
                    $desc = $res[$key]["description"]; 
        
                    if($role != "TEACHER" && $role != "PRINCIPAL"){
                        $i = array_search($res[$key]["lesson"],$courseNames);
                        $res[$key]["description"] = $courseDescriptions[$i];
                        if($i == NULL){
                            $res[$key]["description"] = $res[$key]["lesson"];
                        }
                        
                    } else{
                        $res[$key]["description"] = $res[$key]["lesson"];
                    }
                    
                    
                    $teacher = $res[$key]["initials"];
                    $grade = $res[$key]["grade"];

                    if(array_key_exists("ograde", $res[$key])){
                            unset($res[$key]["ograde"]);

                    }
                    if(array_key_exists("grade", $res[$key])){
                                unset($res[$key]["grade"]);

                        }
                    if(array_key_exists("day", $res[$key])){
                                unset($res[$key]["day"]);

                        }
                    if(array_key_exists("oid", $res[$key])){
                                unset($res[$key]["oid"]);

                        }
                    
                     $res[$key]["headline"] = $res[$key]["lesson"].' '.$desc;
                    if($this->containsWordFromList($desc,["Ausfall"])){
                         //Ausfall
                       $color = "#FF0826";
                    } else if($this->containsWordFromList($desc,["Auftrag","Auftrag für zuhause"])){
                        //Auftrag
                        $color = "#f1c40f";
                    } else if($this->containsWordFromList($desc,["Exkursion","Ausflug","Auslandsreise","Klassenfahrt","Kursfahrt"])){
                        //Ausflug
                        $color = "#bdc3c7";
                    } else if($this->containsWordFromList($teacher,["statt","für","anstatt"])){
                        //Lehrerwechsel
                        $color = "#17B3F0";
                        if($role == "TEACHER" || $role == "PRINCIPAL"){
                            $desc_teacher = substr($teacher, 0, 3);
                            if(!$this->containsWordFromList($desc_teacher,[$grade])){
                                $color = "#FF0826";
                                $res[$key]["headline"] = "Ausfall: ".$grade.' '.$res[$key]["lesson"].' '.$teacher;
                                $res[$key]["description"] = "Entfällt für mich:";
                            }
                        }
                    } else if($this->containsWordFromList($res[$key]["lesson"],["statt","anstatt"])){
                        //Stundenwechsel
                        $color = "#17B3F0";
                        if($role == "TEACHER" || $role == "PRINCIPAL"){
                           $res[$key]["headline"] = "Vertreten: ".$res[$key]["initials"].' '.$res[$key]["lesson"].' in Raum '.$desc;
                        }
                    } else if($this->containsWordFromList($desc,["Raumwechsel","Raumänderung","statt","anstatt"])){
                        //Raumwechsel
                        $color = "#433BCA";
                    } else if($this->containsWordFromList($desc,["für alle"," alle","Event","Beratung"])){
                        //Events für alle
                        $color = "#2ecc71";
                    } else if($this->containsWordFromList($desc,["Klausur","Examen","Prüfung","KA","Klassenarbeit","Abitur","Abi","ABI"])){
                        //Events für alle
                        $color = "#9e92c0";   
                    } else{
                        if($role == "TEACHER" || $role == "PRINCIPAL"){
                           $res[$key]["headline"] = $res[$key]["initials"].' '.$res[$key]["lesson"].' in Raum '.$desc;
                        } else{
                            $res[$key]["headline"] = $res[$key]["lesson"].' in Raum '.$desc;
                        }
                    
                    }
                    
                    $res[$key]["color"] = $color;
                    
                    

                }

                $res = array_values($res);

                 //6. Freiblöcke hinzufügen
                $n = 0;

                foreach($res as $key => $lesson){
                    if($key != 0){
                        $diff = $res[$key+$n]["period"] - $res[($key-1)+$n]["period"];
                        $m = 0;
                        if($diff != 0){
                            if($res[$key-1+$n]["lesson_type"] == "first_half"){
                                $period = intval($res[$key-1+$n]["period"]);
                                $time = $this->getTime($period,"second_half");
                                $lesson = $this->getLesson($period,45,$time,"second_half","stunde");
                                array_splice($res,$key+$n,0,array($lesson));
                                $m++;
                            } else if($res[$key+$n]["lesson_type"] == "second_half"){
                                $period = intval($res[$key+$m+$n]["period"]);
                                $time = $this->getTime($period,"first_half");
                                $lesson = $this->getLesson($period,45,$time,"first_half","stunde");
                                array_splice($res,$key+$m+$n,0,array($lesson));
                            }
                            if($diff > 1){
                                for($i = 1; $i <= ($diff - 1); $i++){
                                    $period = intval($res[$key-1+$n]["period"]) + $i;
                                    $time = $this->getTime($period,"block");
                                    $lesson = $this->getLesson($period,90,$time,"block","block");
                                    array_splice($res,($key-1)+$i+$m+$n,0,array($lesson));
                                }
                            }
                        }
                    } else if($key == 0){
                         if($res[0]["lesson_type"] == "second_half"){
                                $period = intval($res[0]["period"]);
                                $time = $this->getTime($period,"first_half");
                                $lesson = $this->getLesson($period,45,$time,"first_half","stunde");
                                array_splice($res,0,0,array($lesson));
                                $n++;
                        }
                        if($res[0]["period"] > 1){
                            $diff = $res[0]["period"];
                            for($i = 1; $i <= ($diff - 1); $i++){
                                $period = intval($res[$key-1]["period"]) + $i;
                                $time = $this->getTime($period,"block");
                                $lesson = $this->getLesson($period,90,$time,"block","block");
                                array_splice($res,($i-1),0,array($lesson));
                                $n++;
                            } 
                        }
                    }

                }          
                $count = count($res);
                if ($count>0) {
                    $response = array(
                        "timetable" => $res, // array_unique cant be used to remove doubles!
                        "additions" => $json["additions"], 
                        "num_lessons" => $count
                    );
                    
                   
                } else {
                    // No results 
                    $response = array(
                        "timetable" => array(array("initials"=>"","lesson"=>"Kein Unterrichtsblock für den User abrufbar.","description"=>"Keine relevanten Informationen.","hour"=>"","headline"=>"Kein Unterricht.","time"=>"","color"=>"#ff151d","period"=>"-","duration"=>"-")),
                        "additions" => $json["additions"],
                        "num_omission" => $count,
                        "last_updated" => $json["last_updated"]
                    );
                }
                $res = array();
                $res["data"] = $response;
                $res["url"] = $timetableURL;
                return($res);

            } 
            else
            {
                // Error
                http_response_code(201); // Decide for proper status code
                return(array(
                    "omission" => array(array("grade"=>"","initials"=>"","ograde"=>"","lesson"=>"Fehler","description"=>"Bei der Anfrage ist ein Fehler aufgetreten","hour"=>"","oid"=>0)),
                    "Error" => array("type"=>"illegal parameters", "description"=>"The given parameters are not valid", "code"=>1)
                ));
            }
        }
        else{
            $res["data"] = array(
					"timetable" => array(array("initials"=>"","lesson"=>"Ferien","description"=>"Das Team von SOS wünscht dir erholsame Ferien!","hour"=>"","headline"=>"Ferien","time"=>"","color"=>"#1daafc","period"=>"-","duration"=>"-","course_id"=>"-")),
					"additions" => $json["additions"],
					"num_omission" => 0,
					"last_updated" => $json["last_updated"]
				);
            $res["url"] = $timetableURL;
			return($res);
        }

	}
  
    private function isCurrentVP($updated){
            if(strtotime($updated) < strtotime('-3 days')) {
                return false;
            } else{
               return true; 
            }
            

    }
    
    function in_array_r($needle, $haystack, $strict = true) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
    }   
    
	private function parseTimetable($jsonData,$daynumber, $isForVP){

			// Legal input
			http_response_code(200);

            $weekdaydb = array("monday","tuesday","wednesday","thursday","friday","monday","monday");

			$json = json_decode($jsonData, true);

//			$json = $vpData;
			// Collect proper information
			$response_body = array();

			// All data
				foreach ($json["lessons"] as $key_grade=>$value_lessons) {
					foreach ($value_lessons as $lesson) {
						array_push($response_body, $lesson);
					}
				}


                foreach ($response_body as $key => $lesson)
                {
                    if(!($response_body[$key]["day"] == $weekdaydb[$daynumber-1])){
                         unset($response_body[$key]);
                    }

                }

			if($isForVP){
				return($response_body);
			}

	}

    private function containsWordFromList($str, $wordList){
        foreach($wordList as $word){
            if(strpos($str,$word) !== false){
                return true;
            }
        }
        return false;
    }

    private function getLesson($period,$duration,$time,$lesson_type,$type){
        $lesson = array(
					"initials" => "",
                    "lesson" => "Frei".$type,
                    "description" => "Frei",
                    "duration" => "$duration",
                    "lesson_type" => "$lesson_type",
                    "headline" => "Frei".$type,
                    "period" => "$period",
                    "time"=> "$time",
                    "color"=> "#00bfa5"
        ); 
        return $lesson;
    }

    private function getTime($hour,$lesson_type){
        $dt = new DateTime;
        $dt->setTime(6, 30);
        $minutes = $hour * 90 + ($hour >= 4 ? (($hour-2) * 30 + 20) : (($hour-2) * 30 + 35));
        if($hour <= 3){
            $minutes = $hour * 90 + ($hour >= 2 ? ($hour-1) * 30 : 0);
        }
        if($hour == 5){
            $minutes = $hour * 90 + (($hour-2) * 30);
        }
        if($lesson_type == "second_half"){
            $minutes += 45;
        }
        $dt->add(new DateInterval('PT'.$minutes.'M'));
        $time = $dt->format('H:i');
        return $time;

    }


}

?>
