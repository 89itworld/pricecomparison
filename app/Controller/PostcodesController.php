<?php
/**
 * User: Ankur Chauhan
 * Date: 19/06/14
 * Time: 11:30 AM
 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class PostcodesController extends AppController
{
    public $name = 'Postcodes';
    public $uses = array("Postcode");
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
        $this->set('title','Postcode Management');
        $conditions = array('Postcode.is_deleted' => 0);
		if (!empty($this->request->data)) {
            if ($this->request->data['Postcode']['is_active'] != "") {
                $conditions['Postcode.is_active'] = $this->request->data['Postcode']['is_active'];
                $this->request->params['named']['Postcode.is_active'] = $this->request->data['Postcode']['is_active'];
            }
            if ($this->request->data['Postcode']['keyword'] != "") {
                $cond = array();
                $complete_name = explode(" ", $this->request->data['Postcode']['keyword']);
                $cond['Postcode.postcode LIKE'] = "%" . trim($this->request->data['Postcode']['keyword']) . "%";
				 $cond['Postcode.created LIKE'] = "%" . trim($this->request->data['Postcode']['keyword']) . "%";                
                 $conditions['OR'] = $cond;
                $this->request->params['named']['Postcode.keyword'] = $this->request->data['Postcode']['keyword'];
            }
            //$this->set('searching', 'searching');
        } else {
            if (isset($this->request->params['named']['Postcode.is_active']) && $this->request->params['named']['Postcode.is_active'] != "") {
                $conditions['Postcode.is_active'] = $this->request->params['named']['Postcode.is_active'];
                $this->request->data['Postcode']['is_active'] = $this->request->params['named']['Postcode.is_active'];
            }
            if (isset($this->request->params['named']['Postcode.keyword']) && $this->request->params['named']['Postcode.keyword'] != "") {
                $cond = array();
                $cond['Postcode.postcode LIKE'] = "%" . trim($this->request->params['named']['Postcode.keyword']) . "%";
				$cond['Postcode.created LIKE'] = "%" . trim($this->request->params['named']['Postcode.keyword']) . "%";              
                $conditions['OR'] = $cond;
                $this->request->data['Postcode']['keyword'] = $this->request->params['named']['Postcode.keyword'];
            }
        }
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions,
            'order' => array(
                'Postcode.created' => 'Desc'
            )
        );
        $result = $this->paginate('Postcode');
		//pr($result);
        $this->set('Postcode', $result);
    }

/**
     * This function use for postcode add  in admin panel
     */
    function shophead_add()
    {
        $this->layout = 'admin_layout';
        if ($this->request->is('post')) {
            $this->Postcode->set($this->request->data);            
             $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
            if ($this->Postcode->validates()) {
                if ($this->Postcode->save($this->request->data)) {
                    $postcode_id = $this->Postcode->id;                
                 $this->Session->write('flash', array(ADD_RECORD, 'success'));
                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            }
        }
    }

    function shophead_edit($postcode_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($postcode_id);        
        $data = $this->Postcode->find('first', array('conditions' => array('Postcode.id' => $id))); 
        if (!empty($data)) {            
            if (!empty($this->request->data)) {               
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->Postcode->set($this->request->data);
                if ($this->request->data['Postcode']['postcode'] == $data['Postcode']['postcode']) {
                    unset($this->request->data['Postcode']['postcode']);
                }
                if ($this->Postcode->validates()) {
                    if ($this->Postcode->save($this->request->data['Postcode'],false)) {
                        $prd_id = $this->request->data['Postcode']['id'];                                               
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
                    }
                }
            }
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
        }
    }

 /**
     * This function use for postcode delete  in admin panel
     * @param string $postcode_id
     */
    function shophead_deleted($postcode_id = "")
    {
        $id = base64_decode($postcode_id);
        $categroy_data = $this->Postcode->find('first', array('conditions' => array('Postcode.id' => $id)));
        $new_product_data=array();
        if (!empty($categroy_data)) {
            $new_product_data['Postcode']['is_deleted'] = 1;
            $new_product_data['Postcode']['id'] = $categroy_data['Postcode']['id'];
           if ($this->Postcode->save($new_product_data)) {
                $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
        }
    }
	
    /**
     *  This function use for postcode record active  in admin panel
     * @param null $id
     */
    public function shophead_enabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $postcode_id = base64_decode($id);
            $this->Postcode->id = $postcode_id;
            if ($this->Postcode->saveField('is_active', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
        }
    }
    
    /**
     * This function use for postcode in-active  in admin panel
     * @param string $postcode_id
     */
    public function shophead_disabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $postcode_id = base64_decode($id);
            $this->Postcode->id = $postcode_id;
            if ($this->Postcode->saveField('is_active', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
        }
    }
    
    function shophead_view($postcode_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($postcode_id);
        $postcode_list = $this->Postcode->find('list', array('conditions' => array('Postcode.is_deleted' => 0), 'fields' => array('Postcode.id', 'Postcode.postcode')));
        $this->set('postcode_list', $postcode_list);
        $data = $this->Postcode->find('first', array('conditions' => array('Postcode.id' => $id)));
        $this->set('data', $data);
        if (!empty($data)) {
            $this->request->data = $data;
        } else {
            $this->redirect(array('controller' => 'Postcodes', 'action' => 'index'));
        }
    }
    
    	
}