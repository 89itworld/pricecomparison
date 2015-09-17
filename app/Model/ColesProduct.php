<?php
    class ColesProduct extends AppModel{
        var $name = 'ColesProduct';
        public $belongsTo  = array(
        'ProductCategory' => array(
            'className' => 'ProductCategory',
            'foreignKey' => 'category_id'
        ),
        'ProductSubCategory' => array(
            'className' => 'ProductSubCategory',
            'foreignKey' => 'subcategory_id'
        ),
        'ProductItem' => array(
            'className' => 'ProductItem',
            'foreignKey' => 'product_item_id'
        )
        
       
    );
    }
?>