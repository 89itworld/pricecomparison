<?php
/**
 * User: Ankur Chauhan
 * Date: 19/06/14
 * Time: 11:30 AM
 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class TermsConditionsController extends AppController
{
    public $name = 'TermsConditions';
    public $uses = array("TermsCondition");
    public $components = array('Paginator','FileWrite');

    // before filter function of TernsConditions Controller
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
	
     /**
     * This function use for TermsConditions Listing  in admin panel
     */
    function shophead_index()
    {
        $this->layout = 'admin_layout';
        $this->set('title','Terms and Conditions');      
        if (!empty($this->request->data)) {            
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
            'order' => array(
                'TermsCondition.updated' => 'Desc'
            )
        );
        $result = $this->paginate('TermsCondition');
        //pr($result);
        $this->set('Terms_data', $result);
    }
    
    /**
     * This function use for TermsConditions add  in admin panel
     */
    function shophead_add(){
        $this->layout = 'admin_layout';
        $this->set('title','Terms and Conditions');
        if ($this->request->is('post')) {
            $this->TermsCondition->set($this->request->data);
             $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));                      
            if ($this->TermsCondition->validates()) {
                if ($this->TermsCondition->save($this->request->data)) {
                 $this->Session->write('flash', array(ADD_RECORD, 'success'));
                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            }
        }
    }
	
     /**
     *This function use for TermsCondition  edit  in admin panel
     * @param string $product_id
     */
    function shophead_edit($terms_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($terms_id);        
        $data = $this->TermsCondition->find('first', array('conditions' => array('TermsCondition.id' => $id)));
        if (!empty($data)) {        	
            if (!empty($this->request->data)) {                
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->TermsCondition->set($this->request->data);               
                if ($this->TermsCondition->validates()) {
                $productprice=array();
                    if ($this->TermsCondition->save($this->request->data['TermsCondition'],false)) {
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
                    }
                }
            }
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
        }
    }
    function shophead_view($terms_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($terms_id);
        $data = $this->TermsCondition->find('first', array('conditions' => array('TermsCondition.id' => $id)));
		$this->set('data', $data);
        if (!empty($data)) {
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
        }
    }

/**
     * This function use for product in-active  in admin panel
     * @param string $terms_id
     */
    public function shophead_disabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $terms_id = base64_decode($id);
            $this->TermsCondition->id = $terms_id;
            if ($this->TermsCondition->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
        }
    }
    
     /**
     *  This function use for terms and conditions record active  in admin panel
     * @param null $id
     */
    public function shophead_enabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $terms_id = base64_decode($id);
            $this->TermsCondition->id = $terms_id;
            if ($this->TermsCondition->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
        }
    }
     /**
     * This function use for terms and conditions delete  in admin panel
     * @param string $product_id
     */
    function shophead_deleted($terms_id = "")
    {
        $id = base64_decode($terms_id);
        $categroy_data = $this->TermsCondition->find('first', array('conditions' => array('TermsCondition.id' => $id)));
        $new_product_data=array();
        if (!empty($categroy_data)) {            
            $this->TermsCondition->id = $categroy_data['TermsCondition']['id'];
           if ($this->TermsCondition->delete()) {
                $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'TermsConditions', 'action' => 'index'));
        }
    }    
                
	
}