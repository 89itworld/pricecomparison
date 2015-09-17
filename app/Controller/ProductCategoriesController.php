<?php
/**
 * User: Ankur Chauhan
 * Date: 19/06/14
 * Time: 08:57 AM
 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class ProductCategoriesController extends AppController
{
    public $name = 'ProductCategories';
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
        $this->set('title','Manage Product Categories');
        $conditions = array('ProductCategory.is_deleted' => 0);
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions,
            'order' => array(
                'ProductCategory.sort_order' => 'Asc'
            )
        );
        $result = $this->paginate('ProductCategory');
		//pr($result);
        $this->set('result', $result);
    }
	 /**
     * This function use for product category add  in admin panel
     */
	function shophead_add()
    {
        $this->layout = 'admin_layout';
        if ($this->request->is('post')) {
            $this->ProductCategory->set($this->request->data);
			 $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
            if ($this->ProductCategory->validates()) {
                if ($this->ProductCategory->save($this->request->data)) {
                    $this->Session->write('flash', array(ADD_RECORD, 'success'));

                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
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
        $id = base64_decode($category_id);
        // pr($id);
        $this->loadModel('ProductCategory');
        $data = $this->ProductCategory->find('first', array('conditions' => array('ProductCategory.id' => $id)));
        if (!empty($data)) {
            if (!empty($this->request->data)) {
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->ProductCategory->set($this->request->data);
                if ($this->request->data['ProductCategory']['name'] == $data['ProductCategory']['name']) {
                    unset($this->request->data['ProductCategory']['name']);
                }
                if ($this->ProductCategory->validates()) {
                    if ($this->ProductCategory->save($this->request->data)) {
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
                    }
                }
            }
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for product category delete  in admin panel
     * @param string $category_id
     */
    function shophead_deleted($category_id = "")
    {
        $id = base64_decode($category_id);
        $categroy_data = $this->ProductCategory->find('first', array('conditions' => array('ProductCategory.id' => $id)));
        
        App::import('Model', 'ProductSubCategory');
        $this -> ProductSubCategory = new ProductSubCategory();
        $subcategories=$this->ProductSubCategory->find("list",array('conditions'=>array("ProductSubCategory.product_category_id"=>$id)));
       
        if(count($subcategories)==0){
            if (!empty($categroy_data)) {
                $new_business_data = $categroy_data['ProductCategory']['id'];
               if ($this->ProductCategory->delete($new_business_data)) {
                    $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                    $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
                } else {
                    $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                    $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
            }
        }else{
            $this->Session->write('flash', array(CAT_CHILD_EXIST, 'failure'));
            $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
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
            $this->ProductCategory->id = $category_id;
            if ($this->ProductCategory->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'ProductCategoryies', 'action' => 'index'));
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
            $this->ProductCategory->id = $category_id;
            if ($this->ProductCategory->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'ProductCategories', 'action' => 'index'));
        }
    }
	
}