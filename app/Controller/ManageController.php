<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class ManageController extends AppController
{
    public $name = 'Manage';
    public $uses = array("User","PostcodesGeo");
    public $components = array('Paginator','FileWrite','Email');

    // before filter function of Manage Controller
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
	
     /**
     * This function use for User Listing  in admin panel
     */
    function shophead_index()
    {
        $this->layout = 'admin_layout';
        $this->set('title','User Management');
        $conditions = array('User.is_deleted' => 0,'User.user_type != 1');
		if (!empty($this->request->data)) {
            if ($this->request->data['User']['status'] != "") {
                $conditions['User.status'] = $this->request->data['User']['status'];
                $this->request->params['named']['User.status'] = $this->request->data['User']['status'];
            }
            if ($this->request->data['User']['keyword'] != "") {
                $cond = array();
                $complete_name = explode(" ", $this->request->data['User']['keyword']);
                $cond['User.firstname LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
                $cond['User.lastname LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
                $cond['User.username LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
				 $cond['User.created LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
                 $conditions['OR'] = $cond;
                $this->request->params['named']['User.keyword'] = $this->request->data['User']['keyword'];
            }
            //$this->set('searching', 'searching');
        } else {
            if (isset($this->request->params['named']['User.status']) && $this->request->params['named']['User.status'] != "") {
                $conditions['User.status'] = $this->request->params['named']['User.status'];
                $this->request->data['User']['status'] = $this->request->params['named']['User.status'];
            }
            if (isset($this->request->params['named']['User.keyword']) && $this->request->params['named']['User.keyword'] != "") {
                $cond = array();
                $cond['User.firstname LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
                $cond['User.lastname LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
                $cond['User.username LIKE'] = "%" . trim($this->request->data['User']['keyword']) . "%";
				$cond['User.created LIKE'] = "%" . trim($this->request->params['named']['User.keyword']) . "%";
                 $conditions['OR'] = $cond;
                $this->request->data['User']['keyword'] = $this->request->params['named']['User.keyword'];
            }
        }
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions,
            'order' => array(
                'User.updated' => 'Desc'
            )
        );
        $result = $this->paginate('User');
        $this->set('User_data', $result);
    }
	 /**
     * This function use for Users add  in admin panel
     */
	function shophead_add()
    {
        $this->layout = 'admin_layout';
        $postcode_string="";
		$postcodes=$this->PostcodesGeo->find('all',array('fields'=>'DISTINCT PostcodesGeo.suburb'));
        foreach($postcodes as $postcode){
           $postcode_string.='"'.$postcode['PostcodesGeo']['suburb'].'",';
        }
        $this->set("postcode",rtrim($postcode_string,','));
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
			 $file = $this->request->data['User']['userimage'];
            $image_name = $file['name'];
			//pr($this->request->data);die;
			 $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
             //pr($this->request->data);die;
             $ref_id=$this->generateActivationkey(9);
             $this->request->data['User']['activation_key']=$ref_id;
             //$this->request->data['User']['password']=md5($password);
            if ($this->User->validates()) {
            	$this->request->data['User']['userimage'] = NULL;
                if ($this->User->save($this->request->data)) {                    
                	$user_id = $this->User->id;                    
					$this -> _email($this->request->data['User']['email'], $ref_id, $user_id, "sys");
                 if (!empty($image_name)) {
                            $this->_write_user_image($file, $user_id);
                        }
				 $this->Session->write('flash', array(ADD_RECORD, 'success'));
                } else {
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            }
        }
    }

    function generateActivationkey($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

     /**
     * This function use for User image upload
     * @param string $file
     * @param string $prd_id
     */
    private function _write_user_image($file = '', $prd_id = '')
    {
        $val = rand(999, 99999999);
        $image_name = "PRO" . $val . $prd_id . ".png";
        //$this->upload_image($file, $image_name);
        $this->request->data['User']['userimage'] = $image_name;
        $this->request->data['User']['id'] = $prd_id;
        $this->User->save($this->request->data, false);
       /* if (!empty($file)) {
            $this->FileWrite->file_write_path = PRODUCT_IMAGE_PATH;
            $this->FileWrite->_write_file($file, $image_name);
        }*/
		include ("SimpleImage.php");
         $image = new SimpleImage();
	    if (!empty($file)) {
		$image -> load($file['tmp_name']);
        $image -> save(User_IMAGE_PATH . $image_name);
		$image -> resizeToWidth(150, 150);
	    $image -> save(User_IMAGE_THUMB_PATH . $image_name);
    	}
    }
	 
   
     /**
     *This function use for User edit  in admin panel
     * @param string $user_id
     */
    function shophead_edit($user_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($user_id);
        $data = $this->User->find('first', array('conditions' => array('User.id' => $id))); 
        $postcodegeo=$this->PostcodesGeo->find("list",array('conditions'=>array('PostcodesGeo.id'=>$data['User']['postcodes_geo_id']),'fields'=>array('PostcodesGeo.id','PostcodesGeo.suburb')));       
        if (!empty($data)) {
            if (!empty($this->request->data)) {
            	$file = $this->request->data['User']['userimage'];
                $exist_img = $file['name'];
                $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
                $this->User->set($this->request->data);
                if ($this->request->data['User']['firstname'] == $data['User']['firstname']) {
                    unset($this->request->data['User']['firstname']);
                }
				if (empty($exist_img)) {
                    unset($this->request->data['User']['userimage']); 
                }
                if ($this->User->validates()) {
                	if (!empty($exist_img)) {
                        $this->request->data['User']['userimage'] = "";
                    }else{
                    $this->request->data['User']['userimage']=$this->request->data['User']['old_image'];	
                    }
					//pr($this->request->data);
					//die();
                    if ($this->User->save($this->request->data['User'],false)) {
                    	$prd_id = $this->request->data['User']['id'];
						
  
                    	if (!empty($exist_img)) {
                            if (!empty($this->request->data['User']['old_image'])) {
                                $path = WWW_ROOT . DS . User_IMAGE_PATH . $this->request->data['User']['old_image'];
                                $this->FileWrite->delete_file($path);
                            }
                            $this->_write_user_image($file, $prd_id);
                        }
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
                    } else {
                        $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                        $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
                    }
                }
            }
            $this->request->data = $data;
			$this->set('data',$data);
            $this->set('postcode',$postcodegeo);
			
        } else {
            $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
        }
    }
    function shophead_view($user_id = "")
    {
        $this->layout = 'admin_layout';
        $id = base64_decode($user_id);
      
        $data = $this->User->find('first', array('conditions' => array('User.id' => $id)));
		$postcodegeo=$this->PostcodesGeo->find("first",array('conditions'=>array('PostcodesGeo.id'=>$data['User']['postcodes_geo_id']),'fields'=>array('PostcodesGeo.suburb')));
        if(isset($postcodegeo['PostcodesGeo']['suburb'])){
            $this->set('postcode',$postcodegeo['PostcodesGeo']['suburb']);
        }
		$this->set('data', $data);
        if (!empty($data)) {
        		
            $this->request->data = $data;
        } 
    }
	
	 /**
     * This function use for User delete  in admin panel
     * @param string $user_id
     */
    function shophead_deleted($user_id = "")
    {
        $id = base64_decode($user_id);
        $categroy_data = $this->User->find('first', array('conditions' => array('User.id' => $id)));
        $new_product_data=array();
        if (!empty($categroy_data)) {
            $new_product_data['User']['is_deleted'] = 1;
            $new_product_data['User']['id'] = $categroy_data['User']['id'];
           if ($this->User->save($new_product_data)) {
                $this->Session->write('flash', array(DELETE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
        }
    }
	
	 /**
     * This function use for User in-active  in admin panel
     * @param string $user_id
     */
    public function shophead_disabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $user_id = base64_decode($id);
            $this->User->id = $user_id;
            if ($this->User->saveField('status', 0)) {
                $this->Session->write('flash', array(INACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
        }
    }

    /**
     *  This function use for User record active  in admin panel
     * @param null $id
     */
    public function shophead_enabled($id = null)
    {
        $this->layout = "admin_layout";
        if (!empty($id)) {
            $user_id = base64_decode($id);
            $this->User->id = $user_id;
            if ($this->User->saveField('status', 1)) {
                $this->Session->write('flash', array(ACTIVATE_RECORD, 'success'));
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            } else {
                $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
                $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
            }
        } else {
            $this->Session->write('flash', array(FAILURE_MSG, 'failure'));
            $this->redirect(array('controller' => 'Manage', 'action' => 'index'));
        }
    }
    
    function shophead_ajaxgetPostcodes($postcode=null) {
        $this -> layout = false;
        $this -> render(false);
        if($postcode!=null){
        App::import('Model', 'PostcodesGeo');
        $this -> PostcodesGeo = new PostcodesGeo();
        $suburb=$this -> PostcodesGeo -> find('all', array("fields" => array("PostcodesGeo.id","PostcodesGeo.suburb"), "conditions" => array( "PostcodesGeo.postcode" => trim($postcode))));        
        if(!empty($suburb)){
            $opt = '<option value="">--------------------Select Suburb--------------------</option>';
        foreach ($suburb as $k => $v) {            
            $opt .= "<option value='" . ucwords($v['PostcodesGeo']['id']) . "'>" . ucwords($v['PostcodesGeo']['suburb']) . "</option>";
        }
        echo $opt;       
        }else{
            $opt = '<option value="">----Select Suburb----</option>';
            echo $opt;
        }
        }
    }
    
    function shophead_ajaxgetState($suburb=null) {
        $this -> layout = false;
        $this -> render(false);
        if($suburb!=null){
        App::import('Model', 'PostcodesGeo');
        $this -> PostcodesGeo = new PostcodesGeo();
        $suburb=$this -> PostcodesGeo -> find('first', array("fields" => array("PostcodesGeo.id","PostcodesGeo.state"), "conditions" => array( "PostcodesGeo.id" => trim($suburb))));
        if(!empty($suburb)){
        echo trim($suburb['PostcodesGeo']['state']);
        }else{           
           echo "Invalid State";
        }
        }
    }

    function _email($email = null, $activate = null, $lastid, $platform = null) {

        $this -> autoRender = false;
        $this -> Email -> delivery = MAIL_DELIVERY;

        $this -> Email -> smtpOptions = array('host' => SMTP_HOST, 'username' => SMTP_USERNAME, 'password' => SMTP_PWD, 'port' => SMTP_PORT);

        $this -> Email -> to = $email;
        $this -> Email -> cc = CC;
        $this -> Email -> subject = 'for varification';
        $from = EMAIL_NOTIFICATION;
        if (!is_null($from) && trim($from) != "") {
            $this -> Email -> from = $from;
        } else {
            $this -> Email -> from = false;
            $this -> Email -> fromName = false;
        }
        $this -> Email -> template = 'template';

        if ($platform == "app") {
            $data = $activate;
        } else if ($platform == "sys") {
            $data['url'] = "<a href=" . Router::url('/', true) . "homes/users/activate/" .base64_encode($lastid). "/" . $activate . ">click here for varification</a>/";
            $data['logo']="<img width='200' src='" . Router::url('/', true) . "img/price/logo.png' /> ";
        }
        $this -> set("name", $data);

        $this -> Email -> replyTo = NOREPLY_EMAIL;
        $this -> Email -> sendAs = 'both';

        if ($this -> Email -> send()) {
            return "mail successfully delivered check for account activation";
        } else {
            return false;
        }
    }

    

}