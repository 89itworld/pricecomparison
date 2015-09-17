<?php

// $coles_woolworth_join=array('table'=>'woolworths_products','alias'=>'WoolworthsProduct','type'=>'inner','conditions'=>array('WoolworthsProduct.category_id=ColesProduct.category_id','WoolworthsProduct.subcategory_id=ColesProduct.subcategory_id','WoolworthsProduct.product_item_id=ColesProduct.product_item_id','WoolworthsProduct.size_type=ColesProduct.size_type','WoolworthsProduct.volume =ColesProduct.volume'));


/*'fields'=>array('`ColesProduct`.`id`', 'GROUP_CONCAT(`WoolworthProduct`.`id`) as id', 'GROUP_CONCAT(`ColesProduct`.`name`) as ColesProductname', '`ColesProduct`.`type`', '`ColesProduct`.`volume`','`ColesProduct`.`size_type`', '`ColesProduct`.`image`', 'GROUP_CONCAT(`ColesProduct`.`description`) as description', 'GROUP_CONCAT(`WoolworthProduct`.`name`) as WoolworthProduct_name', '`ColesPrice`.`id`', '`ColesPrice`.`coles_product_id`', 'GROUP_CONCAT(`ColesPrice`.`category`) as category', '`ColesPrice`.`subcategory`', '`ColesPrice`.`product_item`', '`ColesPrice`.`current_price`', '`ColesPrice`.`previous_price`', '(`ColesPrice`.`cup_price`)', '(`ColesPrice`.`stockcode`)', '(`ColesPrice`.`url`)', 'GROUP_CONCAT(`WoolworthsPrice`.`current_price`) as Woolworths_current_price', 'GROUP_CONCAT(`WoolworthsPrice`.`previous_price`) as WoolworthsPrice_previous_price'),*/     






  /*
            'fields'=> array('ColesProduct.id','ColesProduct.name','ColesProduct.type','ColesProduct.volume','ColesProduct.size_type','ColesProduct.image','ColesProduct.description','WoolworthsProduct.id','WoolworthsProduct.name','ColesPrice.id' , '`ColesPrice`.`coles_product_id`' ,  '`ColesPrice`.`category`' ,  '`ColesPrice`.`subcategory`' ,  '`ColesPrice`.`product_item`' ,  '`ColesPrice`.`current_price`' ,  '`ColesPrice`.`previous_price`' ,  '`ColesPrice`.`cup_price`' ,  '`ColesPrice`.`stockcode`' ,  '`ColesPrice`.`url`','WoolworthsPrice.current_price','WoolworthsPrice.previous_price' ),
            'order' => array(
                'ColesProduct.type' => 'Desc'
            ),
            */



//pr($result);
        //$this->set('products', $result);
        /*$final_products=array();
        foreach ($result as $key => $value) {
                 
            $percent= similar_text($value['ColesProduct']['name'], $value['WoolworthProduct']['name'], $percent)." ";
            echo $percent;
            pr($value);
            if($percent>=35){
                $final_products[]=$value;
            }
        }*/



/*public function index($limit=''){
            $this->set('pagetitle',"Price Comparison");
            $this->set('limit',$limit);
            $conditions=array('ColesPrice.subcategory'=>'Bread','ColesPrice.product_item'=>'Wholemeal');
            //$conditions = array('ColesProduct.is_deleted'=>0,'ColesProduct.status'=>1,'ColesPrice.status' => 1,'WoolworthProduct.is_deleted'=>0,'WoolworthProduct.status'=>1,'WoolworthPrice.status' => 1);
            if (!empty($this->request->data)) {
                
                if ($this->request->data['Homes']['keyword'] != "") {
                $cond = array();
                $complete_name = explode(" ", $this->request->data['Homes']['keyword']);
                $cond['ColesProduct.name LIKE'] = "%" . trim($this->request->data['Homes']['keyword']) . "%";
                 $conditions['OR'] = $cond;
                $this->request->params['named']['Homes.keyword'] = $this->request->data['Homes']['keyword'];
            }
            } else {
                    if (isset($this->request->params['named']['Homes.keyword']) && $this->request->params['named']['Homes.keyword']!= "") {
                $cond = array();
                $cond['ColesProduct.name LIKE'] = "%" . trim($this->request->params['named']['Homes.keyword']) . "%";
                $conditions['OR'] = $cond;
                $this->request->data['ColesProduct']['keyword'] = $this->request->params['named']['Homes.keyword'];
            }
        }
            
            //$this->Product->unbindModel(array('belongsTo' => array('ProductItem')));
            $productcategories_list=$this->ProductCategory->find('all',
array('conditions'=>array('ProductCategory.status'=>1,'ProductCategory.is_deleted'=>0),'fields'=>array('ProductCategory.id','ProductCategory.name'),'contains'=>array('ProductSubCategory.id'),'order'=>array('ProductCategory.sort_order'=>'Asc')));
            $this->set('productcategories_list',$productcategories_list);

       $ProductPrice_join = array('table' => 'woolworths_prices', 'alias' => 'WoolworthPrice', 'type' => 'inner', 'conditions' => array('WoolworthPrice.subcategory=ColesPrice.subcategory','WoolworthPrice.product_item=ColesPrice.product_item')); 
              
       $ColesProduct_join= array('table' => 'coles_products', 'alias' => 'ColesProduct', 'type' => 'inner', 'conditions' => array('ColesProduct.id=ColesPrice.coles_product_id')); 
        
        $this->paginate = array(
            'recursive' => 0,
            'limit' => $limit,            
            'conditions' => $conditions,            
            'fields'=> array('ColesProduct.id','ColesProduct.name','ColesProduct.type','ColesProduct.volume','ColesProduct.size_type','ColesProduct.image','ColesProduct.description','ColesPrice.id' , '`ColesPrice`.`coles_product_id`' ,  '`ColesPrice`.`category`' ,  '`ColesPrice`.`subcategory`' ,  '`ColesPrice`.`product_item`' ,  '`ColesPrice`.`current_price`' ,  '`ColesPrice`.`previous_price`' ,  '`ColesPrice`.`cup_price`' ,  '`ColesPrice`.`stockcode`' ,  '`ColesPrice`.`url`','WoolworthPrice.current_price','WoolworthPrice.previous_price' ),
            'order' => array(
                'ColesProduct.type' => 'Desc'
            ),            
            'joins'=>array($ProductPrice_join,$ColesProduct_join)
        );
         try {
        $result = $this->paginate('ColesPrice');
        $this->set('products', $result);
    } catch (NotFoundException $e) {
        $this->redirect(array('controller' => 'Homes', 'action' => 'pagenotfound'));
    }
    }*/
    
    
    /*public function index($limit=''){
            $this->set('pagetitle',"Price Comparison");
            $this->set('limit',$limit);
            $conditions = array('Product.is_deleted'=>0,'Product.status'=>1,'ProductPrice.status' => 1);
            if (!empty($this->request->data)) {
                if ($this->request->data['Homes']['keyword'] != "") {
                $cond = array();
                $complete_name = explode(" ", $this->request->data['Homes']['keyword']);
                $cond['Product.name LIKE'] = "%" . trim($this->request->data['Homes']['keyword']) . "%";
                 $conditions['OR'] = $cond;
                $this->request->params['named']['Homes.keyword'] = $this->request->data['Homes']['keyword'];
            }
            } else {
                    if (isset($this->request->params['named']['Homes.keyword']) && $this->request->params['named']['Homes.keyword']!= "") {
                $cond = array();
                $cond['Product.name LIKE'] = "%" . trim($this->request->params['named']['Homes.keyword']) . "%";
                $conditions['OR'] = $cond;
                $this->request->data['Product']['keyword'] = $this->request->params['named']['Homes.keyword'];
            }
        }
            
            //$this->Product->unbindModel(array('belongsTo' => array('ProductItem')));
            $productcategories_list=$this->ProductCategory->find('all',
array('conditions'=>array('ProductCategory.status'=>1,'ProductCategory.is_deleted'=>0),'fields'=>array('ProductCategory.id','ProductCategory.name'),'contains'=>array('ProductSubCategory.id'),'order'=>array('ProductCategory.sort_order'=>'Asc')));
            $this->set('productcategories_list',$productcategories_list);

       $ProductPrice_join = array('table' => 'product_prices', 'alias' => 'ProductPrice', 'type' => 'inner', 'conditions' => array('ProductPrice.product_id =Product.id'));        
        
        
        $this->paginate = array(
            'recursive' => 0,
            'limit' => $limit,            
            'conditions' => $conditions,
            'fields'=> array('Product.id,Product.product_category_id,Product.product_sub_category_id,Product.product_item_id,Product.name,Product.description,Product.volume,Product.size_type,Product.image,Product.type,ProductPrice.id,ProductPrice.coles_prices,ProductPrice.   coles_previousprice,ProductPrice.coles_discount,ProductPrice.coles_previousdiscount,ProductPrice.woolworths_prices,ProductPrice.woolworths_previousprice,ProductPrice.woolworths_discount,ProductPrice.woolworths_previousdiscount,ProductPrice.coles_currentunitprice,ProductPrice.woolworths_currentunitprice,ProductPrice.current_volume,ProductPrice.  currentsizetype,ProductPrice.coles_previousunitprice,ProductPrice.woolworths_previousunitprice,ProductPrice.previous_volume,ProductPrice.previoussizetype,ProductItem.id,ProductItem.name,ProductItem.description,ProductItem.created,ProductItem.status,ProductItem.updated,ProductItem.product_sub_category_id'),
            'order' => array(
                'Product.type' => 'Desc'
            ),
            'joins'=>array($ProductPrice_join)
        );
         try {
        $result = $this->paginate('Product');
        $this->set('products', $result);
    } catch (NotFoundException $e) {
        $this->redirect(array('controller' => 'Homes', 'action' => 'pagenotfound'));
    }
    }*/
   
   public function searching(){
        $this->layout = 'admin_layout';
        $this->set('title','Product Management');
        $conditions = array('Product.is_deleted' => 0);
        if (!empty($this->request->data)) {
            if ($this->request->data['Product']['status'] != "") {
                $conditions['Product.status'] = $this->request->data['Product']['status'];
                $this->request->params['named']['Product.status'] = $this->request->data['Product']['status'];
            }
            if ($this->request->data['Product']['keyword'] != "") {
                $cond = array();
                $complete_name = explode(" ", $this->request->data['Product']['keyword']);
                $cond['Product.name LIKE'] = "%" . trim($this->request->data['Product']['keyword']) . "%";
                 $cond['Product.created LIKE'] = "%" . trim($this->request->data['Product']['keyword']) . "%";
                $cond['ProductCategory.name LIKE'] = "%" . trim($this->request->data['Product']['keyword']) . "%";
                $cond['ProductSubCategory.name LIKE'] = "%" . trim($this->request->data['Product']['keyword']) . "%";
                 $conditions['OR'] = $cond;
                $this->request->params['named']['Product.keyword'] = $this->request->data['Product']['keyword'];
            }
            //$this->set('searching', 'searching');
        } else {
            if (isset($this->request->params['named']['Product.status']) && $this->request->params['named']['Product.status'] != "") {
                $conditions['Product.status'] = $this->request->params['named']['Product.status'];
                $this->request->data['Product']['status'] = $this->request->params['named']['Product.status'];
            }
            if (isset($this->request->params['named']['Product.keyword']) && $this->request->params['named']['Product.keyword'] != "") {
                $cond = array();
                $cond['Product.name LIKE'] = "%" . trim($this->request->params['named']['Product.keyword']) . "%";
                $cond['Product.created LIKE'] = "%" . trim($this->request->params['named']['Product.keyword']) . "%";
                $cond['ProductCategory.name LIKE'] = "%" . trim($this->request->params['named']['Product.keyword']) . "%";
                 $cond['ProductSubCategory.name LIKE'] = "%" . trim($this->request->data['Product']['keyword']) . "%";
                $conditions['OR'] = $cond;
                $this->request->data['Product']['keyword'] = $this->request->params['named']['Product.keyword'];
            }
        }
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions,
            'order' => array(
                'Product.updated' => 'Desc'
            )
        );
        $result = $this->paginate('Product');
        
        //pr($result);
        $this->set('Product_data', $result);
    }

?>