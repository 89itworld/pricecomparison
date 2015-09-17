<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
App::import('Controller','Homes');
class MyAccountController extends AppController {
    public $name = 'MyAccounts';
    public $uses = array("ProductDiscount","ProductItem","User","ManageAccount","PostcodesGeo");
    public $components = array('Email');
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index','manage','contact_details','current_cart','historical_cart','change_password');
        $this->layout="price_layout";
        $Homes=new HomesController;
        $Homes->constructClasses();
        $cart_details=$Homes->cart_details();
        $this->set('cart_details',$cart_details);
        if(!$this->check_session(2)){
            $this->redirect(array("controller"=>"Homes","action"=>"login"));
        }
    }
    public function index(){
        $this->set('pagetitle',"Price Comparison- My Account");
    }
    public function manage(){
        $this->set('pagetitle',"Price Comparison- Manage Coles/Woolworths account details");
        $userid=$this->Session->read('Auth.User.id');
$manage_id=$this->ManageAccount->find("first",array("conditions"=>array("ManageAccount.user_id"=>$userid)));
        $this->set('manage',$manage_id);
        if($this->request->is('post')){
            $this->ManageAccount->set($this->request->data);
            $this->request->data['ManageAccount']['user_id']=$userid;
            if(isset($manage_id)){
                $this->request->data['ManageAccount']['id']=$manage_id['ManageAccount']['id'];
            }
            if($this->request->data['ManageAccount']['type']=="Coles"){
                if($this->ManageAccount->validates()){
                    unset($this->request->data['ManageAccount']['type']);
                    if($this->ManageAccount->save($this->request->data)){
                        $this->Session->write('flash', array(ADD_RECORD_COLES, 'success'));
                    }
                $this->redirect(array('controller' => 'MyAccount', 'action' => 'manage'));
            }
            }else{
                if($this->ManageAccount->validates()){
                    unset($this->request->data['ManageAccount']['type']);
                    if($this->ManageAccount->save($this->request->data)){
                        $this->Session->write('flash', array(ADD_RECORD_WOOLWORTHS, 'success'));
                    }
                $this->redirect(array('controller' => 'MyAccount', 'action' => 'manage'));
            }
            }
        }
    }

    public function contact_details(){
        $this->set('pagetitle',"Price Comparison- Contact details");
        $state=$this->PostcodesGeo->find("all",array("fields"=>array("DISTINCT PostcodesGeo.state")));
        foreach ($state as $key => $value) {
            $newstate[$value['PostcodesGeo']['state']]=$value['PostcodesGeo']['state'];
        }
        $this->set('state',$newstate);
        $user=$this->User->find("first",array("conditions"=>array("User.id"=>$this->Session->read('Auth.User.id'))));
        $this->set('user',$user);
        
        if($this->request->is('post')){
            $this->User->set($this->request->data);
            $this->request->data['User']['id']=$this->Session->read('Auth.User.id');
            if(!isset($this->request->data['User']['personalized_offer'])){
                $this->request->data['User']['personalized_offer']=0;
            }
            unset($this->request->data['User']['autosuggest']);
            $this->User->validator()->remove('email', 'unique');
            if($this->User->validates()){
                if($this->User->save($this->request->data)){
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller'=>'MyAccount','action'=>'contact_details'));
                    }
            }
        }
    }
    
    public function current_cart(){
        $this->set('pagetitle',"Price Comparison- Current Cart");
    }
    
    public function historical_cart(){
        $this->set('pagetitle',"Price Comparison- Saved Cart");
        App::Import("Model","CheckoutCart");
        $this->CheckoutCart=new CheckoutCart();
        $userid=$this->Session->read('Auth.User.id');
        $historical_cart=$this->CheckoutCart->find("all",array("conditions"=>array("CheckoutCart.user_id"=>$userid)));
        $this->set('historical_cart',$historical_cart);
    }

    public function view_cart(){
        $this->set('pagetitle',"Price Comparison- View Cart");
        App::Import("Model","HistoricalCart");
        $this->HistoricalCart=new HistoricalCart();
        $historical_cart=array();
        if($this->request->is('post')){
            if($this->request->data['HistoricalCart']['supermarket']=="Woolworths"){                $fields=array("HistoricalCart.checkout_cart_id,HistoricalCart.quantity,HistoricalCart.woolworths_product_id,HistoricalCart.woolworths_price_id,HistoricalCart.updated,WoolworthsProduct.id,WoolworthsProduct.name,WoolworthsPrice.id,WoolworthsPrice.current_price");            
            }else{                $fields=array("HistoricalCart.checkout_cart_id,HistoricalCart.quantity,HistoricalCart.coles_product_id,HistoricalCart.coles_price_id,HistoricalCart.updated,ColesProduct.id,ColesProduct.name,ColesPrice.id,ColesPrice.current_price");
            }
$historical_cart=$this->HistoricalCart->find("all",array("fields"=>$fields,"conditions"=>array("HistoricalCart.checkout_cart_id"=>base64_decode($this->request->data['HistoricalCart']['id']))));        
        }
        $this->set('historical_cart',$historical_cart);
        
    }
    
    public function change_password(){
        $this->set('pagetitle',"Price Comparison- Change Password");
        if($this->request->is('post')){
            $this->User->set($this->request->data);
            if($this->User->validates()){
                $userid=$this->Session->read('Auth.User.id');
                $pass=AuthComponent::password($this->request->data['User']['old_password']);
$validate_user=$this->User->find('first',array("fields"=>array("User.password"),"conditions"=>array("User.id"=>$userid)));
                if($validate_user['User']['password']==$pass){
                    $update['User']['id']=$userid;
                    $update['User']['password']=$this->request->data['User']['confirm_pass'];
                    if($this->User->save($update,false)){
                        $this->Session->write('flash', array(EDIT_RECORD, 'success'));
                        $this->redirect(array('controller'=>'MyAccount','action'=>'change_password'));
                    }
                }else{
                    $this->Session->write('flash', array(OLD_PASSWORD_INCORRECT, 'success'));
                    $this->redirect(array('controller'=>'MyAccount','action'=>'change_password'));
                }
            }
        }
    }
}