<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class HomesController extends AppController {
    public $name = 'Homes';
    public $uses = array("CurrentCart","Product","ProductCategory","ProductSubCategory","ProductPrice","Postcode","PostcodesGeo","ProductDiscount","ProductItem","User","TermsCondition","WoolworthsProduct","ColesProduct","ColesPrice","WoolworthsPrice");
    public $components = array('Paginator','Email');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index','users','key_expired','login','logout','join_now','getAustraliaAddress','parseAddress','ajaxgetPostcodes','generateActivationkey','activated','cart_details','loginToWoolWorth','loginToColes','ColesAddToTrolley','getproducts','browse','test','forgot_password','validateemail');
        $this->layout="price_layout";
    }
    // type is Specials or Non Specials
    
    

    public function browse($limit=null,$type=null,$category_name=null,$subcategory_name=null,$product_item=null){
        $this->set('pagetitle',"Price Comparison");
        if($category_name!=null){            
        $category_name= str_replace('-and-',' & ', $category_name);
        }
        $conditions=array('ColesProduct.is_deleted'=>0,'ColesProduct.status'=>1,'WoolworthsProduct.is_deleted'=>0);
            $cart_details="";
            $this->set('type',$type);
            if($type=="specials"){
                $conditions['ColesProduct.type']="Specials";
            }
            if($limit=="" || $limit<PRODUCT_LIMIT){
                $limit=PRODUCT_LIMIT;
            }
            $this->set('limit',$limit);
            
            
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
            
            // Get Cart Details of user
            $cart_details=$this->cart_details();
            $this->set('cart_details',$cart_details);
            if(isset($category_name)){
            $this->set('category_name',$category_name);
            $category_id=$this->ProductCategory->find('first',array('fields'=>'ProductCategory.id','conditions'=>array('ProductCategory.name'=>$category_name)));            
            $category_id=$category_id['ProductCategory']['id'];
            $this->set('category_id',$category_id);
            $conditions['ColesProduct.category_id']=$category_id;
            }
            
            if(isset($subcategory_name)){
                $subcategory_id=$this->ProductSubCategory->find('first',array('fields'=>'ProductSubCategory.id','conditions'=>array('ProductSubCategory.name'=>$subcategory_name)));
                $subcategory_id=$subcategory_id['ProductSubCategory']['id'];
                $this->set('subcategory_id',$subcategory_id);
                $conditions['ColesProduct.subcategory_id']=$subcategory_id;
                $this->set('subcategory_name',$subcategory_name);
            }
            if(isset($product_item)){
                $product_item_id=$this->ProductItem->find('first',array('fields'=>'ProductItem.id','conditions'=>array('ProductItem.name'=>$product_item)));
                $product_item_id=$product_item_id['ProductItem']['id'];
                $this->set('product_item_id',$product_item_id);
                $conditions['ColesProduct.product_item_id']=$product_item_id;
                $this->set('product_item',$product_item);
            }
            $productcategories_list=$this->ProductCategory->find('all',
array('conditions'=>array('ProductCategory.status'=>1,'ProductCategory.is_deleted'=>0),'fields'=>array('ProductCategory.id','ProductCategory.name'),'contains'=>array('ProductSubCategory.id'),'order'=>array('ProductCategory.sort_order'=>'Asc')));
            $this->set('productcategories_list',$productcategories_list);
            
            $coles_woolworth_join=array('table'=>'woolworths_products','alias'=>'WoolworthsProduct','type'=>'inner','conditions'=>array('WoolworthsProduct.category_id=ColesProduct.category_id','WoolworthsProduct.subcategory_id=ColesProduct.subcategory_id','WoolworthsProduct.product_item_id=ColesProduct.product_item_id','WoolworthsProduct.size_type=ColesProduct.size_type','WoolworthsProduct.volume =ColesProduct.volume'));

$colesproduct_colesprice_join=array('table'=>'coles_prices','alias'=>'ColesPrice','type'=>'inner','conditions'=>array('ColesProduct.id=ColesPrice.coles_product_id'));

$woolworthprice_join=array('table'=>'woolworths_prices','alias'=>'WoolworthsPrice','type'=>'inner','conditions'=>array('WoolworthsProduct.id=WoolworthsPrice.woolworths_product_id'));

$this->paginate = array(
            'recursive' => 0,
            'limit' => $limit,            
            'conditions' => $conditions,
            'fields'=>array('`ColesProduct`.`id`','ColesProduct.name', 'GROUP_CONCAT(`WoolworthsProduct`.`id`) as id', 'GROUP_CONCAT(`ColesProduct`.`name`) as ColesProductname', '`ColesProduct`.`type`', '`ColesProduct`.`volume`','`ColesProduct`.`size_type`', '`ColesProduct`.`image`', 'GROUP_CONCAT(`ColesProduct`.`description`) as Colesdescription','GROUP_CONCAT(`ColesProduct`.`special_text`) as Colesspecial_text','`ColesPrice`.`cart_detail`',  'GROUP_CONCAT(`WoolworthsProduct`.`name`) as WoolworthsProduct_name','GROUP_CONCAT(`WoolworthsProduct`.`description`) as Woolsdescription','GROUP_CONCAT(`WoolworthsProduct`.`special_text`) as Woolsspecial_text','GROUP_CONCAT(`WoolworthsProduct`.`image`) as Woolsimage', '`ColesPrice`.`id`', '`ColesPrice`.`coles_product_id`', 'GROUP_CONCAT(`WoolworthsPrice`.`id`) as WoolworthsPriceId' , 'GROUP_CONCAT(`ColesPrice`.`category`) as category', '`ColesPrice`.`subcategory`', '`ColesPrice`.`product_item`', '`ColesPrice`.`current_price`', '`ColesPrice`.`previous_price`', '(`ColesPrice`.`cup_price`)', '(`ColesPrice`.`stockcode`)', '(`ColesPrice`.`url`) as Coles_url','GROUP_CONCAT(`WoolworthsPrice`.`url`) as Woolworths_url', 'GROUP_CONCAT(`WoolworthsPrice`.`current_price`) as Woolworths_current_price', 'GROUP_CONCAT(`WoolworthsPrice`.`previous_price`) as Woolworths_previous_price','GROUP_CONCAT(`WoolworthsPrice`.`stockcode`) as Woolworths_stockcode'),
            'order' => array(
                'ColesProduct.type' => 'Desc'
            ),          
            'group'=>array('`ColesProduct`.`id`'),            
            'joins'=>array($coles_woolworth_join,$colesproduct_colesprice_join,$woolworthprice_join)
        );
        try {
        $result = $this->paginate('ColesProduct');       
        //pr($result);
        foreach ($result as $key => $value) {
           
            $woolid=explode(',', $value[0]['id']);
            $colesproductname=explode(',', $value[0]['ColesProductname']);            
            $woolproductname=explode(',', $value[0]['WoolworthsProduct_name']);
            
            
            $woolurl=explode(',', $value[0]['Woolworths_url']);
            
            $colesdescription=explode(',', $value[0]['Colesdescription']);            
            $wooldescription=explode(',', $value[0]['Woolsdescription']);
            
            $coles_discount=explode(',', $value[0]['Colesspecial_text']);            
            $wools_discount=explode(',', $value[0]['Woolsspecial_text']);
            
            $woolworth_price_id=explode(',', $value[0]['WoolworthsPriceId']);            
            $woolcurrent_price=explode(',', $value[0]['Woolworths_current_price']);
            $woolprevious_price=explode(',', $value[0]['Woolworths_previous_price']);
            $woolstockcode=explode(',', $value[0]['Woolworths_stockcode']);
            $woolsimage=explode(',', $value[0]['Woolsimage']);

           
           
            
            foreach ($woolproductname as $woolprodkey => $woolprodname) {
                $percent[$woolprodkey]=similar_text($value['ColesProduct']['name'],$woolprodname);
                
            }
            
            $max= max($percent);
            foreach ($percent as $perkey => $percentvalue) {
                if($percentvalue==$max){
                    $finalkey=$perkey;
                 }
            }
            
            $result[$key]['WoolworthsProduct']['id']=isset($woolid[$finalkey])?$woolid[$finalkey]:'';
            $result[$key]['WoolworthsProduct']['name']=isset($woolproductname[$finalkey])?$woolproductname[$finalkey]:'';
            $result[$key]['ColesProduct']['description']=isset($colesdescription[$finalkey])?$colesdescription[$finalkey]:'';
            $result[$key]['WoolworthsProduct']['description']=isset($wooldescription[$finalkey])?$wooldescription[$finalkey]:'';
            $result[$key]['WoolworthsProduct']['discount']=isset($wools_discount[$finalkey])?$wools_discount[$finalkey]:'';
            $result[$key]['ColesProduct']['discount']=isset($coles_discount[$finalkey])?$coles_discount[$finalkey]:'';
            
            $result[$key]['WoolworthsProduct']['image']=isset($woolsimage[$finalkey])?$woolsimage[$finalkey]:'';
            $result[$key]['WoolworthsPrice']['id']=isset($woolworth_price_id[$finalkey])?$woolworth_price_id[$finalkey]:'';
            $result[$key]['WoolworthsPrice']['url']=isset($woolurl[$finalkey])?$woolurl[$finalkey]:'';
            
            $result[$key]['WoolworthsPrice']['current_price']=isset($woolcurrent_price[$finalkey])?$woolcurrent_price[$finalkey]:'';
            $result[$key]['WoolworthsPrice']['previous_price']=isset($woolprevious_price[$finalkey])?$woolprevious_price[$finalkey]:'';
            $result[$key]['WoolworthsPrice']['stockcode']=isset($woolstockcode[$finalkey])?$woolstockcode[$finalkey]:'';
            unset($percent);
        }       
        
        $this->set('products', $result);
        
        
    } catch (NotFoundException $e) {
        $this->redirect(array('controller' => 'Homes', 'action' => 'pagenotfound'));
    }

    }
    
	
    public function users($activate=null,$userid='',$key=''){
        $this->set('pagetitle',"Price Comparison- Reset Password");
        $id=base64_decode($userid);
        $status=$this->User->find('first',array('conditions'=>array('User.id'=>$id),'fields'=>array('User.status','User.password')));
        if($status['User']['status']==0 || $status['User']['password']=='')
        {
            $this->set('id',$id);
            $data['User']['id']=$id;
            $data['User']['status']=1;
            $this->User->save($data,false);
            $this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
            if($this->request->is('post')){
                if($this->request->data['User']['password']==$this->request->data['User']['confirm_pass']){
                    $update['User']['id']=$id;
                    $update['User']['password']=$this->request->data['User']['password'];
                    if($this->User->save($update,false)){
                        $this->redirect(array('controller'=>'Homes','action'=>'login'));
                    }
                }else{
                    $this->redirect(array('controller'=>'Homes','action'=>'users','activate',$userid,$key));
                }
            }

        }else{
            $this->redirect(array('controller' => 'Homes', 'action' => 'key_expired'));
        }
    }

    public function key_expired(){
        $this->set('pagetitle',"Price Comparison- Key Expired");
    }
    
    public function login(){
        if($this->check_session(2)){
            $this->redirect(array('controller' => 'Homes', 'action' => 'browse',PRODUCT_LIMIT));
        }
        $this->set('pagetitle',"Price Comparison- Login");
        
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            $this->User->validator()->remove('email', 'unique');
            if ($this->User->validates()) {
                if ($this->Auth->login()) {
                    $this->Session->write("Woolworth_login",1);
                    $this->Session->write("Coles_login",1);
                    //$this->redirect(array('controller' => 'Homes', 'action' => 'index',PRODUCT_LIMIT));
                    $this->redirect(array('controller' => 'Homes', 'action' => 'loginToWoolWorth'));
                    
                } else {
                    $this->Session->write('flash', array('You Have entered wrong username or password.', 'failure'));
                    $this->redirect(array('controller' => 'homes', 'action' => 'login'));
                }
            }
        }
    }

    public function forgot_password(){
        if($this->check_session(2)){
            $this->redirect(array('controller' => 'Homes', 'action' => 'browse',PRODUCT_LIMIT));
        }
        $this->set('pagetitle',"Price Comparison- Forgot password");
        
        if ($this->request->is('post')) {
            
            $this->User->set($this->request->data);
            $this->User->validator()->remove('email', 'unique');
            if ($this->User->validates()) {                $user=$this->User->find('first',array("fields"=>array("User.id"),'conditions'=>array('User.email'=>$this->request->data['User']['email'],'User.security_answer'=>$this->request->data['User']['security_answer'])));
            if(!empty($user)){
                $ref_id=$this->generateActivationkey(9);    
                $update['User']['id']=$user['User']['id'];
                $update['User']['activation_key']=$ref_id;
                $update['User']['status']=0;
                $update['User']['forgotpassword']='';
                if($this->User->save($update,false)){
                   $this->_email($this->request->data['User']['email'],$ref_id,$user['User']['id'],"forgotpass");
                    $this->Session->write('flash', array(FORGORTMAIL_SUCCESS, 'success'));
                    $this->redirect(array("controller"=>"Homes","action"=>"forgot_password"));
                 }
            }else{
                $this->Session->write('flash', array('Fields does not match', 'failure'));
                $this->redirect(array("controller"=>"Homes","action"=>"forgot_password"));
            }
            }
        }
    }
    
    /**
     * This function use for logout.
     */
    public function logout()
    {
        if ($this->Auth->logout()) {
            $this->Session->write('flash', array('You Have successfully logged out.', 'success'));
            $this->redirect(array('controller' => 'Homes', 'action' => 'browse',PRODUCT_LIMIT));
        }
    }
    
    public function pagenotfound(){
        $this->set('pagetitle',"Price Comparison- Page Not Found");
    }
    
    public function join_now(){
        if($this->check_session(2)){
            $this->redirect(array("controller"=>"Homes","action"=>"browse",PRODUCT_LIMIT));
        }
        $this->set('pagetitle',"Price Comparison- Join Now");
        $state=$this->PostcodesGeo->find("all",array("fields"=>array("DISTINCT PostcodesGeo.state")));
        foreach ($state as $key => $value) {
            $newstate[$value['PostcodesGeo']['state']]=$value['PostcodesGeo']['state'];
        }
        $this->set('state',$newstate);
        
        $terms=$this->TermsCondition->find("all",array("conditions"=>array("TermsCondition.status"=>1)));
        $this->set('terms',$terms[0]['TermsCondition']['description']);
        if($this->request->is('post')){
            $this->User->set($this->request->data);
            $ref_id=$this->generateActivationkey(9);
            $this->request->data['User']['activation_key']=$ref_id;
            $this->request->data['User']['user_type']=2;
            if ($this->User->validates()) {
                unset($this->request->data['User']['autosuggest']);
                unset($this->request->data['User']['confirmemail']);
                unset($this->request->data['User']['confirm_pass']);
                if(isset($this->request->data['personalized_offer'])){
                    $this->request->data['User']['personalized_offer']=$this->request->data['personalized_offer'];
                }
                if($this->User->save($this->request->data)){
                    $user_id = $this->User->id;
                    $this -> _email($this->request->data['User']['email'], $ref_id, $user_id, "sys");
                    $this->Session->write('flash', array(REG_SUCCESS, 'success'));
                }else{
                    $this->Session->write('flash', array(ERROR_MSG, 'failure'));
                }
                $this->redirect(array('controller' => 'Homes', 'action' => 'login'));
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
    
    public function activated($userid='',$key=''){
        $this->render(false);
        $this->layout=false;
        $this->set('pagetitle',"Price Comparison- Activate Account");
        $id=base64_decode($userid);
        $status=$this->User->find('first',array('conditions'=>array('User.id'=>$id,'User.activation_key'=>$key),'fields'=>array('User.status')));
        if($status['User']['status']==0){
            $update['User']['id']=$id;
            $update['User']['status']=1;
            if($this->User->save($update,false)){
                    $this->Session->write('flash', array(USER_ACTIVATE_RECORD, 'success'));
                    $this->redirect(array('controller'=>'Homes','action'=>'login'));
           }
        }else{
            $this->redirect(array('controller' => 'Homes', 'action' => 'key_expired'));
        }
    }
    

    public function getAustraliaAddress($address=''){
        $this->render(false);
        $this->layout=false;
        $address=explode(' ', $address);
        $string='';
        foreach ($address as $key => $value) {
            $string.=$value."+";
        }        
        //$url='http://www.addressify.com.au/scripts/addressAutoComplete.php?term='.rtrim($string,'+').'';
        $url='http://www2.woolworthsonline.com.au/Shop/SearchAddressLine?search='.rtrim($string,'+').'';
        header('Content-type: application/json');
        $json=file_get_contents($url);  
        echo $json;exit;
    }
    
    public function parseAddress($address=''){
        $this->render(false);
        $this->layout=false;
        $string=ltrim(substr($address,strpos($address,',',0)),',');
        $address_array=explode('  ', trim($string));
        $result=$this->PostcodesGeo->find("first",array("conditions"=>array("PostcodesGeo.suburb"=>$address_array[0],"PostcodesGeo.state"=>$address_array[1],"PostcodesGeo.postcode"=>$address_array[2])));
        echo json_encode($result['PostcodesGeo']);
    }
    
    function ajaxgetPostcodes($postcode=null,$selected=null) {
        $this -> layout = false;
        $this -> render(false);
        $value="";
        if(isset($selected)){
            $value="selected";
        }
        if($postcode!=null){
        App::import('Model', 'PostcodesGeo');
        $this -> PostcodesGeo = new PostcodesGeo();
        $suburb=$this -> PostcodesGeo -> find('all', array("fields" => array("PostcodesGeo.id","PostcodesGeo.suburb"), "conditions" => array( "PostcodesGeo.postcode" => trim($postcode))));        
        if(!empty($suburb)){
            $opt = '<option value="">--------------------Select Suburb--------------------</option>';
        foreach ($suburb as $k => $v) {
            if(isset($selected)){
                if($selected==$v['PostcodesGeo']['id']){
                    $opt .= "<option value='" . ucwords($v['PostcodesGeo']['id']) . "' selected >" . ucwords($v['PostcodesGeo']['suburb']) . "</option>";
                }else{
                    $opt .= "<option value='" . ucwords($v['PostcodesGeo']['id']) . "' >" . ucwords($v['PostcodesGeo']['suburb']) . "</option>";
                }
            }else{
                $opt .= "<option value='" . ucwords($v['PostcodesGeo']['id']) . "' >" . ucwords($v['PostcodesGeo']['suburb']) . "</option>";
            }
        }
        echo $opt;       
        }else{
            $opt = '<option value="">----Select Suburb----</option>';
            echo $opt;
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
            $data['url'] = "<a href=" . Router::url('/', true) . "homes/activated/" .base64_encode($lastid). "/" . $activate . ">click here for varification</a>";
            $data['logo']="<img width='200' src='" . Router::url('/', true) . "img/price/logo.png' /> ";
        }else if($platform== "forgotpass"){
            $this->Email->template='forgotpassword';
            $this -> Email -> subject ="Forgot Password";
            $data['url'] = "<a href=" . Router::url('/', true) . "homes/users/activated/" .base64_encode($lastid). "/" . $activate . ">Reset Your Password</a>";
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

    
   public function cart_details(){
       $cart_details=array();
        if($this->check_session(2)){
            $userid=$this->Session->read('Auth.User.id');
            $cart_details=$this->CurrentCart->find("all",array("conditions"=>array("CurrentCart.user_id"=>$userid)));            
            }  
        return $cart_details;     
            
    }
   
   public function loginToWoolWorth(){
       $this->set('pagetitle',"Price Comparison- Login to Woolworth");
       if(!$this->check_session(2)){
            $this->redirect(array('controller' => 'Homes', 'action' => 'login'));
        }
       App::Import("Model","ManageAccount");
       $this->ManageAccount=new ManageAccount();
       $userid=$this->Session->read('Auth.User.id');       
       $account=$this->ManageAccount->find("first",array("conditions"=>array("ManageAccount.user_id"=>$userid)));       
       if(!empty($account)){
           if(isset($account['ManageAccount']['woolworths_email']) && isset($account['ManageAccount']['coles_email'])){
              if($this->Session->read("Woolworth_login")){
                $this->set("key",1);
                $this->set('account',$account);  
                $this->Session->delete("Woolworth_login");
            }
           }else{
               $this->redirect(array('controller' => 'MyAccount', 'action' => 'manage'));
           }
       }else{
           $this->redirect(array('controller' => 'MyAccount', 'action' => 'manage'));
       }
      
   }
   
   public function loginToColes(){
       $this->set('pagetitle',"Price Comparison- Login to Woolworth");
       if(!$this->check_session(2)){
            $this->redirect(array('controller' => 'Homes', 'action' => 'login'));
        }
       App::Import("Model","ManageAccount");
       $this->ManageAccount=new ManageAccount();
       $userid=$this->Session->read('Auth.User.id');
       $account=$this->ManageAccount->find("first",array("ManageAccount.user_id"=>$userid));
       if(!empty($account)){
           if(isset($account['ManageAccount']['woolworths_email']) && isset($account['ManageAccount']['coles_email'])){
              if($this->Session->read("Coles_login")){
                $this->set('account',$account);  
                $this->Session->delete("Coles_login");
            }
           }else{
               $this->redirect(array('controller' => 'MyAccount', 'action' => 'manage'));
           }
       }
   }

    public function ColesAddToTrolley(){
       $this->set('pagetitle',"Price Comparison- Coles");
       
    }
    public function validateemail($email){
        $this->render(false);
        $this->layout=false;
        $email_exist=$this->User->find('first',array('fields'=>array('User.email'),'conditions'=>array('User.email'=>$email)));
        if(!empty($email_exist)){
            echo "check.png";
        }else{
            echo "cross.png";
        }
    }
   
}

?>