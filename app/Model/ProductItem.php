<?php
    class ProductItem extends AppModel{
        var $name = 'ProductItem';
        public $belongsTo  = array(        
        'ProductCategory'
        );
      
        var $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter title.'
            ), 'rule2' => array(
                'rule' => '/^[a-zA-Z-&, ]*$/',
                'message' => 'Only letters, spaces and (&,) are allowed'
            )
        ),
       
    );


    }
?>