<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class WoolworthsController extends AppController
{
    public $name = 'Woolworths';
    public $uses = array("Product","ProductCategory","ProductSubCategory","ProductPrice","Postcode","ProductDiscount","ProductItem","WoolworthsProduct","WoolworthsPrice");
    public $components = array('Paginator','FileWrite');

    // before filter function of Products Controller
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    
    
    function shophead_index(){
        $this->layout = 'admin_layout';
        $this->set('title','Woolworth Management');
        $conditions=array("WoolworthsProduct.is_deleted"=>0);
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions,
            'order' => array(
                'WoolworthsProduct.updated' => 'Desc'
            )
        );
        $result = $this->paginate('WoolworthsProduct');
        //pr($result);
        $this->set('Product_data', $result);		
    }
    
     /**
     * This function use for product Listing  in admin panel
     */
    function shophead_index_back()
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
        $data = $this->WoolworthsProduct->find('first', array('conditions' => array('WoolworthsProduct.id' => $id)));
        $productpricelist=$this->WoolworthsPrice->find('all',array('conditions'=>array('WoolworthsPrice.woolworths_product_id'=>$id)));
        $this->set('productpricelist', $productpricelist);        
        if (!empty($data)) {
            $sub_categories_list = $this->ProductSubCategory->find('list', array('conditions' => array('ProductSubCategory.id' => $data['WoolworthsProduct']['subcategory_id']), 'fields' => array('ProductSubCategory.id', 'ProductSubCategory.name')));
                $this->set('sub_categories_list', $sub_categories_list);
                
            $item_list = $this->ProductItem->find('list', array('conditions' => array('ProductItem.id' => $data['WoolworthsProduct']['product_item_id']), 'fields' => array('ProductItem.id', 'ProductItem.name')));
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
            $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
        }
    }
    function shophead_view($product_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($product_id);
        $categories_list = $this->ProductCategory->find('list', array('conditions' => array('ProductCategory.is_deleted' => 0), 'fields' => array('ProductCategory.id', 'ProductCategory.name')));
        $this->set('categories_list', $categories_list);
        $productpricelist=$this->WoolworthsPrice->find('all',array('conditions'=>array('WoolworthsPrice.woolworths_product_id'=>$id)));
        $this->set('productpricelist', $productpricelist);
        $data = $this->WoolworthsProduct->find('first', array('conditions' => array('WoolworthsProduct.id' => $id)));
        $this->set('data', $data);
        if (empty($data)) {
            $this->redirect(array('controller' => 'WoolworthsProducts', 'action' => 'index'));
        }
    }
    
     /**
     * This function use for product delete  in admin panel
     * @param string $product_id
     */
    function shophead_deleted($product_id = "")
    {
        $id = base64_decode($product_id);
        $categroy_data = $this->WoolworthsProduct->find('first', array('conditions' => array('WoolworthsProduct.id' => $id)));
        $new_product_data=array();
        if (!empty($categroy_data)) {
            $new_product_data['WoolworthsProduct']['is_deleted'] = 1;
            $new_product_data['WoolworthsProduct']['id'] = $categroy_data['WoolworthsProduct']['id'];
           if ($this->WoolworthsProduct->save($new_product_data)) {
                $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
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
            $this->WoolworthsProduct->id = $product_id;
            if ($this->WoolworthsProduct->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
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
            $this->WoolworthsProduct->id = $product_id;
            if ($this->WoolworthsProduct->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Woolworths', 'action' => 'index'));
        }
    }
        
        
        
      
    
    
    
}