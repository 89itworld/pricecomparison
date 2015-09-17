<?php
/*
    * Users Controller
    * Date - 18/6/2014
    * Time 01:40 PM
    * This controller manages all the functionality related to users of the application.
*/
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class UsersController extends AppController
{
    public $uses = array("User");
    public $components = array('Email');

    // before filter function of Users Controller
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('shophead_login', "login", "shophead_forgot_password", "passwordreset");
    }

    /**
     * This function use for admin panel login
     */
    public function shophead_login()
    {
    	
		//echo AuthComponent::password("123456");
        $this->layout = "admin_login";
        /*if ($this->Auth->user()) {
            return $this->redirect($this->Auth->redirect());
        }*/

        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            $this->User->validator()->remove('email', 'unique');
            if ($this->User->validates()) {
                if ($this->Auth->login()) {
                    $this->redirect(array('controller' => 'admins', 'action' => 'dashboard', 'shophead' => true));
                } else {
                    $this->Session->write('flash', array('You Have entered wrong username or password.', 'failure'));
                    $this->redirect(array('controller' => 'users', 'action' => 'login', 'shophead' => true));
                }
            }
        }
    }

    /**
     * This function use for user panel login
     */
    public function login()
    {
        $this->layout = "user_layout";
       /* if ($this->Auth->user()) {
            return $this->redirect($this->Auth->redirect());
        }*/
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            $this->User->validator()->remove('email', 'unique');
            if ($this->User->validates()) {
                if ($this->Auth->login()) {
                    $this->redirect(array('controller' => 'employees', 'action' => 'dashboard', 'shophead' => false));
                } else {
                    $this->Session->write('flash', array(LOGIN_ERROR, 'failure'));
                    $this->redirect(array('controller' => 'users', 'action' => 'login', 'shophead' => false));
                }
            }
        }
    }

    /**
     * This function use for admin panel logout.
     */
    public function shophead_logout()
    {
        if ($this->Auth->logout()) {
            $this->Session->write('flash', array('You Have successfully logged out.', 'success'));
            $this->redirect(array('controller' => 'users', 'action' => 'login', 'shophead' => true));
        }
    }

    /**
     * This function use for admin panel logout.
     */
    public function logout()
    {
        if ($this->Auth->logout()) {
            $this->Session->write('flash', array('You Have successfully logged out.', 'success'));
            $this->redirect(array('controller' => 'users', 'action' => 'login', 'shophead' => false));
        }
    }


    /**
     * This function use for admin user Listing  in admin panel
     */
    function shophead_index()
    {
        $this->layout = 'admin_layout';
        $this->loadModel('Profile');
        $conditions = array('Profile.is_deleted' => 0);
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions,
            'order' => array(
                'Profile.modified' => 'DESC'
            )
        );
        $user_data = $this->paginate('Profile');
        $this->set('user_data', $user_data);
    }

   

    public  function shophead_change_password(){
        $this->layout = "admin_layout";
        if (!empty($this->request->data)) {
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $userid = $this->Auth->user('id');
                $pass = $this->Auth->password($this->request->data['User']['old_password']);
                $result = $this->User->find('first', array('conditions' => array('User.id' => $userid, 'User.password' => $pass)));
                if (!empty($result)) {
                    $com_pass = $this->request->data["User"]["password"];
                    if ($this->request->data['User']['password'] == $this->request->data['User']['confirm_pass']) {
                        $this->request->data['User']['id'] = $userid;
                        if ($this->User->save($this->request->data)) {
                            $user_email = $result["User"]["email"];
                           // $this->_send_forgot_pass_mail($user_email, $com_pass);
                            $this->Session->write('flash', array(EMP_CHANGE_PASSWORD, 'success'));
                            $this->redirect(array('controller' => 'admins', 'action' => 'dashboard'));
                        }
                    } else {

                        $this->Session->write('flash', array(EMP_NOT_PASSWORD_PASSWORD, 'failure'));
                        $this->redirect(array('controller' => 'users', 'action' => 'change_password'));
                    }
                } else {

                    $this->Session->write('flash', array(WRONG_PASSWORD, 'failure'));
                    $this->redirect(array('controller' => 'users', 'action' => 'change_password'));
                }
            }
        }
    }

    /**
     * This function use for forgot password for admin user
     */
    function shophead_forgot_password()
    {
        $this->layout = "user_login";
        if (!empty($this->request->data)) {
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $user_email = $this->request->data['User']['forgot_email'];
                $result = $this->User->find('first', array('conditions' => array('User.email' => $user_email)));
                if (!empty($result)) {
                	$ref_id = $this -> _randomstring(7);
					$email = $result['User']['email'];
					$lastid = $result['User']['id'];
					
					$dataarray['User']['id'] = $lastid;
					$dataarray['User']['activation_key'] = $ref_id;
					
					$this -> User -> save($dataarray);
					
                    $pass = rand(9999, 9999999);
                    $com_pass = "EMP" . $pass;
                    $this->request->data["User"]["password"] = $com_pass;
                    $this->request->data["User"]["id"] = $result["User"]["id"];
                    if ($this->User->save($this->request->data)) {
                        //$this->_send_forgot_pass_mail($this->request->data['User']['forgot_email'], $com_pass);
						$this -> _send_forgot_pass_mail($this->request->data['User']['forgot_email'], $ref_id, $lastid, "passwordreset");
                        $this->Session->write('flash', array(SEND_PASSWORD, 'success'));
                        $this->redirect(array('controller' => 'Users', 'action' => 'login',"admin"=>true));
                    }
                } else {
                    $this->Session->write('flash', array(WRONG_EMAIL, 'failure'));
                    $this->redirect(array('controller' => 'Users', 'action' => 'forgot_password',"admin"=>true));
                }
            }
        }
    }


	function passwordreset($lastid = null, $activate = null) {
		$this -> layout = 'user_login';
		
		//$this -> render(false);
		$data = $this -> request -> data;
		$db = $this -> User -> find('count', array('conditions' => array('User.id' => $lastid)));
		$this -> set('id', $lastid);
		 if (!empty($this->request->data)) {
			 $this->User->set($this->request->data);
			
           // if ($this->User->validates()) {
            	$this->User->validator()->remove('email', 'forgot_email','password','new_pass','confirm_pass');
			$data['User']['id'] = $this->request->data['User']['id'];
			//$data['User']['password'] = md5($this -> data['User']['password']);
			$data['User']['password'] = $this->request->data['User']['new_pass2'];
			$this -> User -> save($data);
				  $this->Session->write('flash', array(CHANGE_PASSWORD, 'success'));
				  $this->redirect(array('controller' => 'users', 'action' => 'login', 'shophead' => true));
			
		//}
		}
		 
		 
		 
	}


	function _send_forgot_pass_mail($email = null, $activate = null, $lastid, $passwordreset = null) {

		$this -> autoRender = false;
		$this -> Email -> delivery = MAIL_DELIVERY;

		$this -> Email -> smtpOptions = array('host' => SMTP_HOST, 'username' => SMTP_USERNAME, 'password' => SMTP_PWD, 'port' => SMTP_PORT);

		$this -> Email -> to = $email;
		$this -> Email -> cc = CC;
		$this -> Email -> subject = 'for varification';
		$from = EMAIL_NOTIFICATION;
		if (!is_null($from) && trim($from) != "") {
			$this -> Email -> from = $from;
		} else {
			$this -> Email -> from = false;
			$this -> Email -> fromName = false;
		}
		$this -> Email -> template = 'template';

		
			$data = "<a href=" . Router::url('/', true) . "Users/" . $passwordreset . "/" . $lastid . "/" . $activate . ">click here for " . $passwordreset . "</a>/";
		

		$this -> set("name", $data);

		$this -> Email -> replyTo = NOREPLY_EMAIL;
		$this -> Email -> sendAs = 'both';

		if ($this -> Email -> send()) {
			return "mail successfully delivered check for account activation";
		} else {
			return false;
		}
	}

	function _randomstring($length) {
		$key = '';
		$keys = array_merge(range(0, 9), range('a', 'z'));

		for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}

		return $key;
	}


}

?>