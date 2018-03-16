<?php
namespace rbwebdesigns\blogcms;

use rbwebdesigns\core\Sanitize;

class AccountController extends GenericController
{
    protected $model;
    
    public function __construct()
    {
        $this->model = BlogCMS::model('\rbwebdesigns\blogcms\model\AccountFactory');
    }
    
    public function route($params)
    {
        if(!USER_AUTHENTICATED) $this->thrownAccessDenied();
        
        switch($params[0])
        {
            case 'user':
                if(isset($params[1])) $user = $GLOBALS['modelUsers']->getUserById(Sanitize::int($params[1]));
                else return $this->throwNotFound();
                
                $this->view->setVar('user', $user);
                $this->view->setPageTitle($user['username'] . '\'s profile');
                $this->view->render('account/viewuser.tpl');
                break;
                
            case 'edit':
                if(isset($_POST['fld_submit_accchange'])) $this->updateAccountDetails();
                $this->view->setPageTitle('Edit Profile');
                $this->view->setVar('user', $GLOBALS['gobjUser']);
                $this->view->render('account/editdetails.tpl');
                break;
                
            case 'changepassword':
                if(isset($_POST['fld_submit_passwordchange'])) $this->updatePassword();
                $this->view->setPageTitle('Change Password');
                // $this->view->setVar();
                $this->view->render('account/changepassword.tpl');
                break;
            
            case 'changeprofilephoto':
                if(isset($_POST['fld_submit_uploadphoto'])) $this->uploadProfilePhoto();
                $this->view->setPageTitle('Upload profile photo');
                $this->view->render('account/uploadavatar.tpl');
                break;
                
            default:
                // profile main
                $this->view->setPageTitle('Your Account');
                $this->view->render('account/main.tpl');
                break;
        }
    }
    
    public function login(&$request, &$response)
    {
        if($request->method() == 'POST') return $this->runLogin($request, $response);

        $response->setTitle('Login required');
        $response->writeTemplate('account/login.tpl');
    }

    public function register(&$request, &$response)
    {
        if($request->method() == 'POST') return $this->runRegister($request, $response);

        $response->setTitle('Create a new account');
        $response->writeTemplate('account/register.tpl');
    }

    protected function runLogin(&$request, &$response)
    {
        $username = $request->getString('fld_username');
        $password = $request->getString('fld_password');

        if (strlen($username) == 0 || strlen($password) == 0) {
            redirect('/account/login', 'Please complete all fields', 'error');
        }

        if ($this->model->login($username, $password)) {
            redirect('/', 'Welcome back', 'success');
        }
        else {
            redirect('/account/login', 'No match found for username and password', 'error');
        }
    }

    protected function runRegister(&$request, &$response)
    {
        $details = [
            'firstname' => $request->getString('fld_name'),
            'surname' => $request->getString('fld_surname'),
            'email' => $request->getString('fld_email'),
            'emailConfirm' => $request->getString('fld_email_2'),
            'username' => $request->getString('fld_username'),
            'password' => $request->getString('fld_password'),
            'passwordConfirm' => $request->getString('fld_password_2')
        ];

        // Check all fields complete
        foreach ($details as $value) {
            if (strlen($value) == 0) {
                redirect('/account/login', 'Please complete all fields', 'error');
                break;
            }
        }

        // Validate
        if ($details['email'] != $details['emailConfirm'] || $details['password'] != $details['passwordConfirm']) {
            redirect('/account/register', 'Email or passwords did not match', 'error');
        }

        if ($this->model->register($details)) {
            redirect('/account/login', 'Account created', 'success');
        }
        else {
            redirect('/account/login', 'Unable to create account right now - please try again later', 'error');
        }
    }

    private function updateAccountDetails()
    {        
        $details = array(
            "name" => Sanitize::string($_POST['fld_firstname']),
            "surname" => Sanitize::string($_POST['fld_surname']),
            "description" => Sanitize::string($_POST['fld_description']),
            "email" => Sanitize::email($_POST['fld_email']),
            "gender" => Sanitize::string($_POST['fld_gender']),
            "location" => Sanitize::string($_POST['fld_location']),
            "username" => Sanitize::string($_POST['fld_username'])
        );
        
        // Sanitize Date Input
        $in_dob_day = Sanitize::int($_POST['fld_dob_day']);
        $in_dob_month = Sanitize::int($_POST['fld_dob_month']);
        $in_dob_year = Sanitize::int($_POST['fld_dob_year']);
        
        // Check the date combination actually exists!
        if(checkdate($in_dob_month, $in_dob_day, $in_dob_year)) {
            // Convert to date
            $details['dob'] = date("Y-m-d", strtotime($in_dob_year."-".$in_dob_month."-".$in_dob_day));
        
        } else {
            setSystemMessage("Unable to set date of birth", "Error");
            redirect('/account/edit');
        }
        
        // Check that if the username has changed then if this one is avaliable
        $this->mdlUsers->updateDetails($details);
        
        setSystemMessage("Details updated", "Success");
        redirect('/account/edit');
    }
    
    private function updatePassword()
    {
        // Change Password
        $details = array(
            "current_password" => Sanitize::string($_POST['fld_current_password']),
            "new_password" => Sanitize::string($_POST['fld_new_password']),
            "new_password_rpt" => Sanitize::string($_POST['fld_new_password_rpt'])
        );
        
        // Update DB
        if($this->mdlUsers->updatePassword($details)) setSystemMessage("Password changed", "Success");
        else setSystemMessage("Failed to change password", "Error");
            
        redirect('/account/changepassword');
    }
        
    private function generateProfilePhotoName()
    {
        $filename = rand(10000,32000) . rand(10000,32000) . "." . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        
        if (file_exists(SERVER_AVATAR_FOLDER . "/" . $filename))
        {
            return $this->generateProfilePhotoName(); // run recursive until we've got a unique one?
        }
        
        return $filename;
    }
    
    private function uploadProfilePhoto()
    {
        // If file type is correct type and is less than 20kb
        /*
        ($_FILES["file"]["type"] == "image/gif")|| || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/png")
        RESTRICTED TO JPG
        */
        if (!($_FILES['avatar']['type'] == 'image/jpeg' && $_FILES['avatar']['size'] < 200000))
        {
            setSystemMessage('Unsuitable Photo - file must be a JPEG image under 20KB', 'Error');
            redirect('/account/uploadprofilephoto');
        }
        
        // file has upload error then return error
        if ($_FILES['avatar']['error'] > 0)
        {
            setSystemMessage('Unable to upload - ' . $_FILES['avatar']['error'], 'Error');
            redirect('/account/uploadprofilephoto');
        }
        
        // Make a new file name (to hopefully avoid duplicates)
        $_FILES['avatar']['name'] = $this->generateProfilePhotoName();
        
        move_uploaded_file($_FILES['avatar']['tmp_name'], SERVER_AVATAR_FOLDER . '/' . $_FILES['avatar']['name']);

        $this->db->runQuery('UPDATE ' . TBL_USERS . ' SET profile_picture = "'.$_FILES['avatar']["name"].'" WHERE id = "' . USER_ID . '"');
        
        // Make a thumbnail image
        $imagePath = SERVER_AVATAR_FOLDER . '/' . $_FILES["avatar"]["name"];
        $srcImage = imagecreatefromjpeg($imagePath);
        list($imageHeight,$imageWidth) = getimagesize($imagePath);

        $min = min(array($imageHeight,$imageWidth));

        if( $min == $imageWidth )
        {
            $startX = floor( ($imageHeight - $imageWidth) / 2 );
            $startY = 0;
        }
        else
        {
            $startX = 0;
            $startY = floor( ($imageWidth - $imageHeight) / 2 );
        }

        $destImage = imagecreatetruecolor( $min , $min );
        $destLoc = SERVER_AVATAR_FOLDER . '/thumbs/' . $_FILES["avatar"]["name"];
        imagecopy($destImage , $srcImage , 0 , 0 , $startX , $startY , $min , $min);
        imagejpeg($destImage,$destLoc);
        
        setSystemMessage('Upload Successful', 'Success');
        redirect('/account');
    }
}

