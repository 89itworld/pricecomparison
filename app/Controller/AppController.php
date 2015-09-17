<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package        app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    public $components = array(
        'Session',
        'RequestHandler',
        'Auth'
    );
    public $helpers = array('Html', 'Session', 'Js', 'Mysession');

    public function beforeFilter()
    {
        $this->Auth->loginError = 'Invalid username or password , Please try again.';
        $this->Auth->authError = 'You are not authorised to access that location !';

        if (isset($this->params['shophead'])) {

            $this->Auth->loginRedirect = array('controller' => 'Admins', 'action' => 'dashboard', 'shophead' => true);
            $this->Auth->logoutRedirect = array('controller' => 'Users', 'action' => 'login', 'shophead' => true);
            $this->Auth->redirect = array('controller' => 'Users', 'action' => 'login', 'shophead' => true);
            $user_scope = array('User.user_type' => 1, 'User.status' => 1, 'User.is_deleted' => 0);

        } else {
            $this->Auth->loginRedirect = array('controller' => 'employees', 'action' => 'dashboard', 'shophead' => false);
            $this->Auth->logoutRedirect = array('controller' => 'users', 'action' => 'login', 'shophead' => false);
            $this->Auth->redirect = array('controller' => 'users', 'action' => 'login', 'shophead' => false);
            //$user_scope = array('User.user_type' => array(2,3), 'User.status' => 1, 'User.is_deleted' => 0);
            $user_scope = array('User.user_type' => 2, 'User.status' => 1, 'User.is_deleted' => 0);

        }
		
        $this->Auth->allow('shophead_login', "login","shophead_forgot_password");
        $this->Auth->authenticate = array(
            AuthComponent::ALL => array(
                'userModel' => 'User',
                'fields' => array(
                    'username' => 'email',
                    'password' => 'password'
                ),
                'scope' => $user_scope,
            ), 'Form'
        );
        if (isset($this->request->params['shophead']) && ($this->request->params['prefix'] == 'shophead')) {
            $this->layout = 'admin_login';
            //$this->check_permission();
        }
    }

    public function check_permission()
    {
        $this->loadModel('AdminMethod');
        $method_result = $this->AdminMethod->find('first', array("conditions" => array('controller' => $this->request->params['controller'], 'action' => $this->request->params['action'], 'status' => 1)));
        $method_id = $method_result['AdminMethod']['id'];
        if (!empty($method_result)) {
            if ($method_result['AdminMethod']['is_allow'] == 0) {
                $this->loadModel('AdminPermission');
                if ($this->Auth->User()) {
                    $result = $this->AdminPermission->find('first', array("conditions" => array('AdminPermission.admin_method_id' => $method_id, 'AdminPermission.user_type' => $this->Auth->User('user_type_id'))));
                    if (empty($result)) {
                        $this->Session->write('flash', array(UNAUTHORIZED, 'failure'));
                        $this->redirect(array('controller' => 'Admins', 'action' => 'dashboard', 'shophead' => true));
                    }

                } else {
                    $this->Session->write('flash', array(UNAUTHORIZED, 'failure'));
                    $this->redirect(array('controller' => 'Admins', 'action' => 'dashboard', 'shophead' => true));
                }
            }
        } else {
            $this->Session->write('flash', array(UNAUTHORIZED, 'failure'));
            $this->redirect(array('controller' => 'Admins', 'action' => 'dashboard', 'shophead' => true));
        }
    }


    function check_session($user_type_id)
    {
        $type = $this->Session->read('Auth.User.user_type');
        if ($type == $user_type_id) {
            return true;
        } else {
            return false;
        }
    }

   
}
