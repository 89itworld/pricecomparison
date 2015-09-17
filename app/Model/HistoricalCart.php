<?php
    class HistoricalCart extends AppModel{
        var $name = 'HistoricalCart';
        public $belongsTo  = array(       
        'WoolworthsProduct' => array(
            'className' => 'WoolworthsProduct',
            'foreignKey' => 'woolworths_product_id'
        ),
        'ColesProduct' => array(
            'className' => 'ColesProduct',
            'foreignKey' => 'coles_product_id'
        ),
        'WoolworthsPrice' => array(
            'className' => 'WoolworthsPrice',
            'foreignKey' => 'woolworths_price_id'
        ),
        'ColesPrice' => array(
            'className' => 'ColesPrice',
            'foreignKey' => 'coles_price_id'
        )
    );
        
    }
?>