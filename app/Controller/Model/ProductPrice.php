<?php
    class ProductPrice extends AppModel{
        var $name = 'ProductPrice';
         
       var $validate = array(
        'coles_prices' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter the coles price.'
            ), 'rule2' => array(
                'rule' => array("coles_price"),
                'message' => 'Please enter valid coles price in format 0.00'                
            )
        )
    );  
    function coles_price($data){
        try{
            if (preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $data['coles_prices'])){                
                return true;
            }
            else {                
                return false;
            }
        }catch(Exception $ex){
            echo $ex->getMessage();
        }        
    }
    }
?>



        