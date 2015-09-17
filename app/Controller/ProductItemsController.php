<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class ProductItemsController extends AppController
{
    public $name = 'ProductItems';
    public $uses = array();
    public $components = array('Paginator');

    // before filter function of Users Controller
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
	
     /**
     * This function use for product category Listing  in admin panel
     */
    function shophead_index()
    {
        $this->layout = 'admin_layout';        
        $this->set('title','Product Items Management');
        App::Import("Model","ProductCategory");
        $this->ProductCategory=new ProductCategory();
        $categories_list = $this->ProductCategory->find('list', array('conditions' => array('ProductCategory.is_deleted' => 0), 'fields' => array('ProductCategory.id', 'ProductCategory.name')));
        $this->set('categories_list', $categories_list);
		$Category_join = array('table' => 'product_sub_categories', 'alias' => 'ProductSubCategory', 'type' => 'inner', 'conditions' => array('ProductItem.product_sub_category_id =ProductSubCategory.id'));		
        $conditions = array('ProductItem.is_deleted' => 0);
        if (!empty($this->request->data)) {
            
            if ($this->request->data['ProductItem']['product_sub_category_id'] != "") {
                $conditions['ProductItem.product_sub_category_id'] = $this->request->data['ProductItem']['product_sub_category_id'];
                $this->request->params['named']['ProductItem.product_sub_category_id'] = $this->request->data['ProductItem']['product_sub_category_id'];
            }
            if ($this->request->data['ProductItem']['product_category_id'] != "") {
                $conditions['ProductItem.product_category_id'] = $this->request->data['ProductItem']['product_category_id'];
                $this->request->params['named']['ProductItem.product_category_id'] = $this->request->data['ProductItem']['product_category_id'];
            }
        } else {
            if (isset($this->request->params['named']['ProductItem.product_category_id']) && $this->request->params['named']['ProductItem.product_category_id'] != "") {
                $conditions['ProductItem.product_category_id'] = $this->request->params['named']['ProductItem.product_category_id'];
                $this->request->data['ProductItem']['product_category_id'] = $this->request->params['named']['ProductItem.product_category_id'];
            }
            if (isset($this->request->params['named']['ProductItem.product_sub_category_id']) && $this->request->params['named']['ProductItem.product_sub_category_id'] != "") {
                 $conditions['ProductItem.product_sub_category_id'] = $this->request->params['named']['ProductItem.product_sub_category_id'];
                $this->request->data['ProductItem']['product_sub_category_id'] = $this->request->params['named']['ProductItem.product_sub_category_id'];
            }
        }
        
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'fields'=> array('ProductSubCategory.name as catname,ProductItem.id,ProductItem.name,ProductItem.sort_order,ProductItem.created,ProductItem.status,ProductItem.updated,ProductItem.product_sub_category_id'),
            'conditions' => $conditions,
            'order' => array(
                'ProductItem.sort_order' => 'Asc'
            ),
            'joins'=>array($Category_join)
        );
		
		
        $result = $this->paginate('ProductItem');
		//pr($result);
        $this->set('result', $result);
    }
	 /**
     * This function use for product category add  in admin panel
     */
	function shophead_add()
    {
        $this->layout = 'admin_layout';

		App::import('Model', 'ProductCategory');
		$this -> ProductCategory = new ProductCategory();
		
		$this -> set('categories', $this -> ProductCategory -> find('list', array('fields' => array('ProductCategory.id', 'ProductCategory.name'), 'conditions' => array('ProductCategory.status' => 1,'ProductCategory.is_deleted'=>0))));
		$categories_list = $this->ProductCategory->find('list', array('conditions' => array('ProductCategory.is_deleted' => 0), 'fields' => array('ProductCategory.id', 'ProductCategory.name')));
        $this->set('categories_list', $categories_list);
        if ($this->request->is('post')) {
            $this->ProductItem->set($this->request->data);
			 $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
            if ($this->ProductItem->validates()) {
                 //pr($this->request->data);die;
                if ($this->ProductItem->save($this->request->data)) {
                    $this->Session->write('flash', array(ADD_RECORD, 'success'));
                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            }
        }
    }
   
     /**
     *This function use for product category edit  in admin panel
     * @param string $category_id
     */
    function shophead_edit($category_id = "")
    {
        $this->layout = 'admin_layout';

		App::import('Model', 'ProductCategory');
		$this -> ProductCategory = new ProductCategory();
		
        $id = base64_decode($category_id);
        // pr($id);
        $this->loadModel('ProductSubCategory');
		$this -> set('categories', $this -> ProductCategory -> find('list', array('fields' => array('ProductCategory.id', 'ProductCategory.name'), 'conditions' => array('ProductCategory.status' => 1,'ProductCategory.is_deleted'=>0))));
        $this->set('subcategories',$this->ProductSubCategory->find('list',array('fields'=>array('ProductSubCategory.id','ProductSubCategory.name'),'conditions'=>array('ProductSubCategory.status'=>1,'ProductSubCategory.is_deleted'=>0))));
        $data = $this->ProductItem->find('first', array('conditions' => array('ProductItem.id' => $id)));
        if (!empty($data)) {
            $sub_categories_list = $this->ProductSubCategory->find('list', array('conditions' => array('ProductSubCategory.id' => $data['ProductItem']['product_sub_category_id']), 'fields' => array('ProductSubCategory.id', 'ProductSubCategory.name')));
                $this->set('sub_categories_list', $sub_categories_list);
            if (!empty($this->request->data)) {
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->ProductItem->set($this->request->data);               
                if ($this->request->data['ProductItem']['name'] == $data['ProductItem']['name']) {
                    unset($this->request->data['ProductItem']['name']);
                }
                if ($this->ProductSubCategory->validates()) {
                    if ($this->ProductItem->save($this->request->data)) {
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
                    }
                }
            }
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for product category delete  in admin panel
     * @param string $category_id
     */
    function shophead_deleted($item_id = "")
    {
        $id = base64_decode($item_id);
        $productitem_data = $this->ProductItem->find('first', array('conditions' => array('ProductItem.id' => $id)));
        if (!empty($productitem_data)) {
            $new_business_data = $productitem_data['ProductItem']['id'];
           if ($this->ProductItem->delete($new_business_data)) {
                $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for product items in-active  in admin panel
     * @param string $category_id
     */
      public function shophead_disabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $item_id = base64_decode($id);
            $this->ProductItem->id = $item_id;
            if ($this->ProductItem->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
        }
    }

    /**
     *  This function use for product items record active  in admin panel
     * @param null $id
     */
    public function shophead_enabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $item_id = base64_decode($id);
            $this->ProductItem->id = $item_id;
            if ($this->ProductItem->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'ProductItems', 'action' => 'index'));
        }
    }
	
}