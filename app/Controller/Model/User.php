<?php
    class User extends AppModel{
        var $name = 'User';
       
       /* public $belongsTo  = array(
            'UserType' => array(
                'className' => 'UserType',
                'foreignKey' => 'user_type_id'
            )
        );*/
        var $validate = array(
        'firstname' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the first name.'
                ), 'rule2' => array(
                    'rule' => '/^[A-Za-z]+$/',
                    'message' => 'Please enter a valid first name.'
                ),
            ),
            'lastname' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the last name.'
                ), 'rule2' => array(
                    'rule' => '/^[A-Za-z]+$/',
                    'message' => 'Please enter a valid last name.'
                ),
            ),
            'suburb' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please select Suburb.'
                )
            ),
            'security_answer' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter Security Answer.'
                )
            ),
            'state' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please select State.'
                )
            ),
            'postcode' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the Postcode.'
                ), 'rule2' => array(
                    'rule' => '/^[0-9]+$/',
                    'message' => 'Please enter a valid postcode.'
                ),
            ),
            'contact_no' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the Mobile number.'
                ), 'rule2' => array(
                    'rule' => '/^[0-9]{10,15}+$/',
                    'message' => 'Please enter a valid mobile number. (Minimum 10 and Maximum 15 digits required)'
                ),
            ),
            'address' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the address.'
                )
            ),
            'score' => array(
                'rule2' => array(
                    'rule' => '/^[100]+$/',
                    'message' => 'Address is not verified.'
                )
            ),
            'email' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the email Address.'
                ), 'unique' => array(
                    'rule' => 'isUnique',
                    'message' => 'This email Address has already been taken.'
                ), 'email' => array(
                    'rule' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/',
                    'message' => 'Please enter a valid email address.'
                ),
            ),
            
			'confirmemail'=>array(
					'notempety'=>array(
					'rule'=>'notempty',
					'message'=>'Please Enter email Address.'
				),  'email'=> array(
					'rule'=>'/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/',
					'message'=>'Please Enter a valid email address.'),
			
				    'identicalFieldValues' => array (
                    'rule' => array('identicalFieldValues', 'email'),
                    'message' =>  "The Email does not match.")
            
				),
			
			
            'password' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the Password.'
                ) ,
                'minlength'=>array(
                    'rule' => array('minLength','6'),
                    'message' => 'Password must be atleast 6 characters long.',
                ),
                'maxlength'=>array(
                    'rule' => array('maxLength','15'),
                    'message' => 'Password must be atmost 15 characters long.',
                )
            ),
            'new_pass' =>array(
                'notempty'=>array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the new password.'
                ),
                'maxlength'=>array(
                    'rule' => array('maxLength','15'),
                    'message' => 'Password must be atmost 15 characters long.',
                ),
                'minlength'=>array(
                    'rule' => array('minLength','6'),
                    'message' => 'Password must be atleast 6 characters long.',
                )
            ),
            'confirm_pass' =>array(
                'notempty'=>array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the confirm Password.'
                ),
                'maxlength'=>array(
                    'rule' => array('maxLength','15'),
                    'message' => 'Password must be atmost 15 characters long.',
                ),
                'minlength'=>array(
                    'rule' => array('minLength','6'),
                    'message' => 'Password must be atleast 6 characters long.',
                ),
                'identicalFieldValues' => array (
                    'rule' => array('identicalFieldValues', 'password'),
                    'message' =>  "The confirm password does not match the password."
                )
            ) ,
            'old_password' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the Old Password.'
                )
            ),
            'forgot_email' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the email Address.'
                ), 'email' => array(
                    'rule' => 'email',
                    'message' => 'Please enter a valid email address.'
                ),
            ),
        );
        function identicalFieldValues($field = array(), $compare_field = null){
            foreach ($field as $key => $value) {
                $v1 = $value;
                $v2 = $this->data[$this->name][$compare_field];
                if ($v1 !== $v2) {
                    return FALSE;
                }
                else {
                    continue;
                }
            }
            return TRUE;
        }
        public function beforeSave($options = array()){
                
            if (isset($this->data[$this->name]['password'])) {
                                //echo "string";
                $this->data[$this->name]['password'] = AuthComponent::password($this->data[$this->name]['password']);
                
            }
                if(isset($this->data[$this->name]['forgotpassword'])){
                    $this->data[$this->name]['password'] = '';
                    unset($this->data[$this->name]['forgotpassword']);
                }

            return true;
        }
    }
?>