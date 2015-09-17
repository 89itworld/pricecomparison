<?php
    class Postcode extends AppModel{
        var $name = 'Postcode';
        
        var $validate = array(
        'postcode' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter postcode.'
            ), 'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This postcode has been already taken.'
            ), 'rule2' => array(
                'rule' => '/^[0-9]*$/',
                'message' => 'Only numbers are allowed'
            )
        )
    );
    }
?>