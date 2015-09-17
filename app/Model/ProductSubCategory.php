<?php
    class ProductSubCategory extends AppModel{
        var $name = 'ProductSubCategory';
        //public $hasMany = array('ProductItem');
      
        var $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter title.'
            ), 'rule2' => array(
                'rule' => '/^[a-zA-Z&, ]*$/',
                'message' => 'Only letters and spaces and (&,) are allowed'
            )
        ),
       
    );


    }
?>