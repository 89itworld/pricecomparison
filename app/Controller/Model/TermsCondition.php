<?php
    class TermsCondition extends AppModel{
        var $name = 'TermsCondition';
        
        var $validate = array(
        'description' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter description.'
            ),
        )
    );
    }
?>