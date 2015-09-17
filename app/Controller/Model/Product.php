<?php
    class Product extends AppModel{
       var $name = 'Product';
       public $hasMany =array('ProductPrice');
       public $belongsTo  = array(
        'ProductCategory' => array(
            'className' => 'ProductCategory',
            'foreignKey' => 'product_category_id'
        ),
        'ProductSubCategory' => array(
            'className' => 'ProductSubCategory',
            'foreignKey' => 'product_sub_category_id'
        ),
        'ProductItem' => array(
            'className' => 'ProductItem',
            'foreignKey' => 'product_item_id'
        )
        
       
    );

       var $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter title.'
            ), 'rule2' => array(
                'rule' => '/^[a-zA-Z ]*$/',
                'message' => 'Only letters and spaces are allowed'
            )
        ),
        'size' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter the volume.'
            ), 'rule2' => array(
                'rule' => '/^[0-9]{10}$/',
                'message' => 'Please enter a valid volume.'
            )
        ),
        'volume' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter the volume.'
            ), 'rule2' => array(
                'rule' => '/^[0-9]{1,10}$/',
                'message' => 'Please enter a valid volume.'
            )
        ),
        'product_category_id' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please select the category.'
            )
        ),
        'size_type' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please select the size type.'
            )
        ),
       "image" => array(
            "check_format" => array(
                "rule" => array("validate_image"),
                "message" => "Image upload Error , Please upload proper image format ONLY.",
                "last" => true
            ),
            "check_size" => array(
                "rule" => array("check_size"),
                "message" => "Image size is more than specified size.",

            ),
        ),
    );
      function validate_image($data)
    {

        try {
            $file_name = $data["image"]["name"];
            //pr($data);
            if (!empty($file_name)) {
                $tempFile = new File($file_name);
                $ext = $tempFile->ext();
                $ext = strtolower($ext);
                $types = array("gif", "jpg", "jpeg", "png", "pjpeg", "x-png", "tiff", "x-tiff");
                $val = in_array($ext, $types, true);
                //pr($val);
                if ($val) {
                    //pr($data);
                    //die;
                    return true;
                }
                return false;
            }
            return true;
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    //<p> function validate_file() ends here</p>

    function check_size($data)
    {
        try {
            $file_name = $data["image"]["name"];
            if (!empty ($file_name)) {
                $size = $data["image"]["size"];
                if ($size <= 1048576) { //1MB
                    return true;
                }
                return false;
            }
            return true;
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

   function ProductInnerJoinProductPrices($data){
        $query=$this->query("SELECT * FROM `product_prices` INNER JOIN products ON product_prices.`product_id` = products.id WHERE products.product_category_id =".$data['product_category_id']." AND products.product_sub_category_id =".$data['product_sub_category_id']." AND products.product_item_id=".$data['product_item_id']." AND products.name = '".$data['name']."' AND products.is_deleted=0 AND product_prices.postcode =".$data['postcode']."");
       return $query;      
   }

    }
?>