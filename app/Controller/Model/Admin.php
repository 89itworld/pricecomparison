<?php
/**
 * Contact Model
 *
 * Demonstrate Captcha validation via Behavior
 *
 * PHP version 5 and CakePHP version 2.0+
 *
 * @category Model
 * @author   Donovan du Plessis <donovan@binarytrooper.com>
 */
App::uses('AppModel', 'Model');
class Admin extends AppModel {
    var $name = 'Admin';
    /**
     * Extend model with Captcha Behavior
     *
     * @var array
     * @access public
     */
  
   /*
    * Validation rules for Admin login
    */ 
     public $validate = array(
        'username' => array(
             'rule1' => array(
                'rule'     =>'notEmpty',
                /*'required' => true,*/
                'message'  => 'This is required',
                 'last' => true,
            ),
            'rule2' => array(
                'rule'     =>'email',
                /*'required' => true,*/
                'message'  => 'Please enter valid email'
            )
        ),
		'password' => array(
             'rule1' => array(
                'rule'     =>'notEmpty',
                /*'required' => true,*/
                'message'  => 'This is required',
                 'last' => true,
            )
        )
    
    );

    function check_confirm_pass(){
       if($this->data['Admin']['new_password'] != $this->data['Admin']['confirm_password']){
               return false;
           }else{
               return true;
           }  
      }
       function check_old_pass(){
           //$this->loadModel('Admin');
           $count = $this->find('count',array('conditions'=>array('Admin.id'=>$this->data['Admin']['id'],'Admin.password'=>md5($this->data['Admin']['old_password']))));
            if($count>0){
                return true;
            }else{
                return false;
            }
       }
}
?>
