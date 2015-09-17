<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class ProductSubCategoriesController extends AppController
{
    public $name = 'ProductSubCategories';
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
        $this->set('title','Product Sub Category Management');
		$Category_join = array('table' => 'product_categories', 'alias' => 'ProductCategory', 'type' => 'inner', 'conditions' => array('ProductSubCategory.product_category_id =ProductCategory.id'));
		
        $conditions = array('ProductSubCategory.is_deleted' => 0);
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'fields'=> array('ProductCategory.name as catname,ProductSubCategory.id,ProductSubCategory.name,ProductSubCategory.description,ProductSubCategory.created,ProductSubCategory.status,ProductSubCategory.updated,ProductSubCategory.product_category_id'),
            'conditions' => $conditions,
            'order' => array(
                'ProductSubCategory.updated' => 'Desc'
            ),
            'joins'=>array($Category_join)
        );

        $result = $this->paginate('ProductSubCategory');
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
		
        if ($this->request->is('post')) {
            $this->ProductSubCategory->set($this->request->data);
			 $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
            if ($this->ProductSubCategory->validates()) {
                if ($this->ProductSubCategory->save($this->request->data)) {
                    $this->Session->write('flash', array(ADD_RECORD, 'success'));

                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
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
		$this -> set('categories', $this -> ProductCategory -> find('list', array('fields' => array('ProductCategory.id', 'ProductCategory.name'), 'conditions' => array('ProductCategory.status' => 1))));
        $data = $this->ProductSubCategory->find('first', array('conditions' => array('ProductSubCategory.id' => $id)));
        if (!empty($data)) {
            if (!empty($this->request->data)) {
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->ProductSubCategory->set($this->request->data);
                if ($this->request->data['ProductSubCategory']['name'] == $data['ProductSubCategory']['name']) {
                    unset($this->request->data['ProductSubCategory']['name']);
                }
                if ($this->ProductSubCategory->validates()) {
                    if ($this->ProductSubCategory->save($this->request->data)) {
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
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
    function shophead_deleted($category_id = "")
    {
        $id = base64_decode($category_id);
        $categroy_data = $this->ProductSubCategory->find('first', array('conditions' => array('ProductSubCategory.id' => $id)));
        App::import('Model', 'ProductItem');
        $this -> ProductItem = new ProductItem();
        $subcategories=$this->ProductItem->find("list",array('conditions'=>array("ProductItem.product_sub_category_id"=>$id)));

        if(count($subcategories)==0){
            if (!empty($categroy_data)) {
                $new_business_data = $categroy_data['ProductSubCategory']['id'];
               if ($this->ProductSubCategory->delete($new_business_data)) {
                    $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                    $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
                } else {
                    $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                    $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
            }
        }else{
            $this->Session->write('flash', array(SUBCAT_CHILD_EXIST, 'failure'));
            $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for product category in-active  in admin panel
     * @param string $category_id
     */
      public function shophead_disabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $category_id = base64_decode($id);
            $this->ProductSubCategory->id = $category_id;
            if ($this->ProductSubCategory->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'ProductSubCategoryies', 'action' => 'index'));
        }
    }

    /**
     *  This function use for product category record active  in admin panel
     * @param null $id
     */
    public function shophead_enabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $category_id = base64_decode($id);
            $this->ProductSubCategory->id = $category_id;
            if ($this->ProductSubCategory->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'ProductSubCategories', 'action' => 'index'));
        }
    }
	
}