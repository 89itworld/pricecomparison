<?php
    class ProductCategory extends AppModel{
        var $name = 'ProductCategory';
        public $hasMany = array('ProductSubCategory'=>array('conditions'=>array('ProductSubCategory.is_deleted'=>0)),'ProductItem'=>array('order'=>array('ProductItem.sort_order'=>'Asc')));        
        var $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter title.'
            ), 'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This  title has been already taken.'
            ), 'rule2' => array(
                'rule' => '/^[a-zA-Z&, ]*$/',
                'message' => 'Only letters and spaces and (&,) are allowed'
            )
        ),
        'sort_order'=>array(
        'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter sort order.'
            ),'rule2' => array(
                'rule' => '/^[0-9]{1,4}$/',
                'message' => 'Only numbers are allowed.'
            )
        )
       
    );


    }
?>