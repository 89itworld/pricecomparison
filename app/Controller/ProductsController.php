<?php
/**
 * User: Ankur Chauhan
 * Date: 19/06/14
 * Time: 11:30 AM
 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class ProductsController extends AppController
{
    public $name = 'Products';
    public $uses = array("Product","ProductCategory","ProductSubCategory","ProductPrice","Postcode","ProductDiscount","ProductItem");
    public $components = array('Paginator','FileWrite');

    // before filter function of Products Controller
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
	
     /**
     * This function use for product Listing  in admin panel
     */
    function shophead_index()
    {
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
	 /**
     * This function use for product add  in admin panel
     */
	function shophead_add()
    {
        $this->layout = 'admin_layout';
		$categories_list = $this->ProductCategory->find('list', array('conditions' => array('ProductCategory.is_deleted' => 0), 'fields' => array('ProductCategory.id', 'ProductCategory.name')));
        $this->set('categories_list', $categories_list);
        $postcode = $this->Postcode->find('list',array('fields'=>array('id','postcode'),'conditions'=>array('Postcode.is_active'=>1,'Postcode.is_deleted'=>0)));        
        $this->set('postcode',$postcode);
        if ($this->request->is('post')) {
            $this->Product->set($this->request->data);
			 $file = $this->request->data['Product']['image'];
            $image_name = $file['name'];
			 $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
             
            if ($this->Product->validates()) {
                $productprice=array();            
                foreach($this->request->data['ProductPrice'] as $prodkey=> $prodprice){                                   
                 if(!in_array($prodprice['postcode'],$productprice)){
                     array_push($productprice,$prodprice['postcode']);
                     $finalproductprice[]=$prodprice;                    
                 }
                }
                
                $validate_product_name=$this->_checkduplicateproducts($this->request->data['ProductPrice']);                  
                foreach ($validate_product_name as $validkey => $productvalue) {
                    if(empty($productvalue)){
                        unset($validate_product_name[$validkey]);
                    }
                }
            if(count($validate_product_name)==0){
                $this->request->data['Product']['image'] = NULL;                
                if ($this->Product->save($this->request->data)) {
                    $product_id = $this->Product->id;                    
                    if(count($this->request->data['ProductPrice'])>0){
                        $counting = count($this->request->data['ProductPrice']);
                        foreach ($finalproductprice as $key => $value) { 
                            $this->ProductPrice->create();
                            $priceproduct['ProductPrice']['product_id']=$product_id;
                            $priceproduct['ProductPrice']['product_category_id']=$this->request->data['Product']['product_category_id'];
                            $priceproduct['ProductPrice']['coles_prices']=$value['coles_prices'];
                            $priceproduct['ProductPrice']['coles_discount']=$value['coles_discount'];
                            $priceproduct['ProductPrice']['coles_previousprice']=$value['coles_previousprice'];
                            $priceproduct['ProductPrice']['coles_previousdiscount']=$value['coles_previousdiscount'];
                            $priceproduct['ProductPrice']['woolworths_prices']=$value['woolworths_prices'];
                            $priceproduct['ProductPrice']['woolworths_previousprice']=$value['woolworths_previousprice'];
                            $priceproduct['ProductPrice']['woolworths_discount']=$value['woolworths_discount'];
                            $priceproduct['ProductPrice']['woolworths_previousdiscount']=$value['woolworths_previousdiscount'];
                            $priceproduct['ProductPrice']['postcode']=$value['postcode'];                            
                            $priceproduct['ProductPrice']['created']=date('Y-m-d H:i:s');
                            $this->ProductPrice->save($priceproduct);
                        }
                    }
                 if (!empty($image_name)) {
                            $this->_write_product_image($file, $product_id);
                        }
                 $this->Session->write('flash', array(ADD_RECORD, 'success'));
                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            }else{                
                $this->Session->write('flash', array('Product duplicacy', 'failure'));
                $this->redirect(array('controller' => 'Products', 'action' => 'add'));
            }       
                
            	
            }
        }
    }

     /**
     * This function use for product image upload
     * @param string $file
     * @param string $prd_id
     */
    private function _write_product_image($file = '', $prd_id = '')
    {
        $val = rand(999, 99999999);
        $image_name = "PRO" . $val . $prd_id . ".png";
        //$this->upload_image($file, $image_name);
        $this->request->data['Product']['image'] = $image_name;
        $this->request->data['Product']['id'] = $prd_id;
        $this->Product->save($this->request->data, false);
       /* if (!empty($file)) {
            $this->FileWrite->file_write_path = PRODUCT_IMAGE_PATH;
            $this->FileWrite->_write_file($file, $image_name);
        }*/
		include ("SimpleImage.php");
         $image = new SimpleImage();
	    if (!empty($file)) {
		$image -> load($file['tmp_name']);
        $image -> save(PRODUCT_IMAGE_PATH . $image_name);
		$image -> resizeToWidth(150, 150);
	    $image -> save(PRODUCT_IMAGE_THUMB_PATH . $image_name);
    	}
    }
	 
   
     /**
     *This function use for product edit  in admin panel
     * @param string $product_id
     */
    function shophead_edit($product_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($product_id);
        $categories_list = $this->ProductCategory->find('list', array('conditions' => array('ProductCategory.is_deleted' => 0), 'fields' => array('ProductCategory.id', 'ProductCategory.name')));        
        $this->set('categories_list', $categories_list);
        $data = $this->Product->find('first', array('conditions' => array('Product.id' => $id)));
		$productpricelist=$this->ProductPrice->find('all',array('conditions'=>array('ProductPrice.product_id'=>$id)));
        $this->set('productpricelist', $productpricelist);        
        if (!empty($data)) {
        	$sub_categories_list = $this->ProductSubCategory->find('list', array('conditions' => array('ProductSubCategory.id' => $data['Product']['product_sub_category_id']), 'fields' => array('ProductSubCategory.id', 'ProductSubCategory.name')));
        		$this->set('sub_categories_list', $sub_categories_list);
                
            $item_list = $this->ProductItem->find('list', array('conditions' => array('ProductItem.id' => $data['Product']['product_item_id']), 'fields' => array('ProductItem.id', 'ProductItem.name')));
                $this->set('item_list', $item_list);    
            if (!empty($this->request->data)) {
                $file = $this->request->data['Product']['image'];
                $exist_img = $file['name'];
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->Product->set($this->request->data);
                if ($this->request->data['Product']['name'] == $data['Product']['name']) {
                    unset($this->request->data['Product']['name']);
                }
				if (empty($exist_img)) {
                    unset($this->request->data['Product']['image']);
                }
                if ($this->Product->validates()) {
                    
                $productprice=array();
                foreach($this->request->data['ProductPrice'] as $prodkey=> $prodprice){                                   
                 if(!in_array($prodprice['postcode'],$productprice)){
                     array_push($productprice,$prodprice['postcode']);
                     $finalproductprice[]=$prodprice;                    
                 }
                }
                    
                    if (!empty($exist_img)) {
                        $this->request->data['Product']['image'] = "";
                    }else{
                    $this->request->data['Product']['image']=$this->request->data['Product']['old_image'];	
                    }					
                    if ($this->Product->save($this->request->data['Product'],false)) {
                    	$prd_id = $this->request->data['Product']['id'];
                        if(count($this->request->data['ProductPrice'])>0){
                        $counting = count($this->request->data['ProductPrice']);
                         $this->ProductPrice->deleteAll(array('ProductPrice.product_id'=>$prd_id));   
                        foreach ($finalproductprice as $key => $value) {
                            $this->ProductPrice->create();                           
                           $priceproduct['ProductPrice']['product_id']=$prd_id;
                            $priceproduct['ProductPrice']['product_category_id']=$this->request->data['Product']['product_category_id'];
                            $priceproduct['ProductPrice']['coles_prices']=$value['coles_prices'];
                            $priceproduct['ProductPrice']['coles_discount']=$value['coles_discount'];
                            $priceproduct['ProductPrice']['coles_previousprice']=$value['coles_previousprice'];
                            $priceproduct['ProductPrice']['coles_previousdiscount']=$value['coles_previousdiscount'];
                            $priceproduct['ProductPrice']['woolworths_prices']=$value['woolworths_prices'];
                            $priceproduct['ProductPrice']['woolworths_previousprice']=$value['woolworths_previousprice'];
                            $priceproduct['ProductPrice']['woolworths_discount']=$value['woolworths_discount'];
                            $priceproduct['ProductPrice']['woolworths_previousdiscount']=$value['woolworths_previousdiscount'];
                            $priceproduct['ProductPrice']['postcode']=$value['postcode'];                            
                            $priceproduct['ProductPrice']['created']=date('Y-m-d H:i:s');
                            //$this->ProductPrice->id=$value['id'];
                            $this->ProductPrice->save($priceproduct);
                        }
                    }
                    	if (!empty($exist_img)) {
                            if (!empty($this->request->data['Product']['old_image'])) {
                                $path = WWW_ROOT . DS . PRODUCT_IMAGE_PATH . $this->request->data['Product']['old_image'];
                                $this->FileWrite->delete_file($path);
                            }
                            $this->_write_product_image($file, $prd_id);
                        }
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'Products', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'Products', 'action' => 'index'));
                    }
                }
            }
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'Products', 'action' => 'index'));
        }
    }
    function shophead_view($product_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($product_id);
        $categories_list = $this->ProductCategory->find('list', array('conditions' => array('ProductCategory.is_deleted' => 0), 'fields' => array('ProductCategory.id', 'ProductCategory.name')));
        $this->set('categories_list', $categories_list);
        $productpricelist=$this->ProductPrice->find('all',array('conditions'=>array('ProductPrice.product_id'=>$id)));
        $this->set('productpricelist', $productpricelist);   
        $postcode = $this->Postcode->find('list',array('fields'=>array('id','postcode'),'conditions'=>array('Postcode.is_active'=>1,'Postcode.is_deleted'=>0)));        
        $this->set('postcode',$postcode);     
        $data = $this->Product->find('first', array('conditions' => array('Product.id' => $id)));
		$this->set('data', $data);
        if (!empty($data)) {
        	$sub_categories_list = $this->ProductSubCategory->find('list', array('conditions' => array('ProductSubCategory.id' => $data['Product']['product_sub_category_id']), 'fields' => array('ProductSubCategory.id', 'ProductSubCategory.name')));
        		$this->set('sub_categories_list', $sub_categories_list);
				
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'Products', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for product delete  in admin panel
     * @param string $product_id
     */
    function shophead_deleted($product_id = "")
    {
        $id = base64_decode($product_id);
        $categroy_data = $this->Product->find('first', array('conditions' => array('Product.id' => $id)));
        $new_product_data=array();
        if (!empty($categroy_data)) {
            $new_product_data['Product']['is_deleted'] = 1;
            $new_product_data['Product']['id'] = $categroy_data['Product']['id'];
           if ($this->Product->save($new_product_data)) {
                $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Products', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for product in-active  in admin panel
     * @param string $product_id
     */
    public function shophead_disabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $product_id = base64_decode($id);
            $this->Product->id = $product_id;
            if ($this->Product->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Products', 'action' => 'index'));
        }
    }

    /**
     *  This function use for product record active  in admin panel
     * @param null $id
     */
    public function shophead_enabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $product_id = base64_decode($id);
            $this->Product->id = $product_id;
            if ($this->Product->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Products', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Products', 'action' => 'index'));
        }
    }
	
		function shophead_ajaxgetItems($product_sub_category_id=null) {
		$this -> layout = false;
		$this -> render(false);

		App::import('Model', 'ProductItem');
		$this -> ProductItem = new ProductItem();

		$b_d = $this -> ProductItem -> find('list', array("fields" => array("ProductItem.id", "ProductItem.name"), "conditions" => array("ProductItem.product_sub_category_id" => $product_sub_category_id, "ProductItem.status" => 1, "ProductItem.is_deleted" => 0)));

		

		$opt = '<option value="">----Select Product Items----</option>';
		foreach ($b_d as $k => $v) {
			$opt .= "<option value='" . $k . "'>" . ucwords($v) . "</option>";
		}
		echo $opt;
	}
        
        function shophead_ajaxgetCategory($product_category_id=null) {
        $this -> layout = false;
        $this -> render(false);

        App::import('Model', 'ProductSubCategory');
        $this -> ProductSubCategory = new ProductSubCategory();

        $b_d = $this -> ProductSubCategory -> find('list', array("fields" => array("ProductSubCategory.id", "ProductSubCategory.name"), "conditions" => array("ProductSubCategory.product_category_id" => $product_category_id, "ProductSubCategory.status" => 1, "ProductSubCategory.is_deleted" => 0)));

        

        $opt = '<option value="">----Select Product Sub Category----</option>';
        foreach ($b_d as $k => $v) {
            $opt .= "<option value='" . $k . "'>" . ucwords($v) . "</option>";
        }
        echo $opt;
    }
        
    function shophead_addprices(){         
        $this -> layout = false;
        $this->autoRender = false;
        $key = $this->request->data['key'];
        $this->set('key',$key);
        $this -> render("/Elements/addprices");
    }
	
    function _checkduplicateproducts($data){
        $this -> layout = false;
        $this->autoRender = false;
        $validate=array();
                foreach ($data as $arraykey => $arrvalue) {
                $prdata['product_category_id']=$this->request->data['Product']['product_category_id'];
                $prdata['product_sub_category_id']=$this->request->data['Product']['product_sub_category_id'];
                $prdata['product_item_id']=$this->request->data['Product']['product_item_id'];
                $prdata['name']=$this->request->data['Product']['name'];
                $prdata['postcode']=$arrvalue['postcode'];
                $validate[]=$this->Product->ProductInnerJoinProductPrices($prdata);
                }
        return $validate;                
    }
	
}