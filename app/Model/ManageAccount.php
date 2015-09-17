<?php
    class ManageAccount extends AppModel{
        var $name = 'ManageAccount';
        //public $hasOne='User';
       
        var $validate = array(
            'coles_email' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter coles email Address.'
                ), 'email' => array(
                    'rule' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/',
                    'message' => 'Please enter a valid Coles email address.'
                ),
            ),
            'coles_password' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter Coles Password.'
                )
            ),
            'woolworths_email' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter the email Address.'
                ), 'email' => array(
                    'rule' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/',
                    'message' => 'Please enter a valid Woolworths email address.'
                ),
            ),
            'woolworths_password' => array(
                'notempty' => array(
                    'rule' => 'notempty',
                    'message' => 'Please enter Woolworths Password.'
                )
            ),
           
        );

    }
?>