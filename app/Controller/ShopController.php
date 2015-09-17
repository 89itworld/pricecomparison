<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class ShopController extends AppController {
    public $name = 'Shops';
    public $components = array('Paginator','Email');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('AddToTrolley','DeleteCart','ClearTrolley','Checkout','increase_quantity','decrease_quantity');
        $this->layout="price_layout";
        if(!$this->check_session(2)){
            $this->redirect(array("controller"=>"Homes","action"=>"login"));
        }
    }
   
    public function AddToTrolley($woolworthid=null,$colesid=null,$quantity=null,$colesprice_id=null,$woolworthprice_id=null){
        $this->set('pagetitle',"Price Comparison- Add To Trolley");
        $this->layout=false;
        $this->render(false);
        $userid=$this->Session->read('Auth.User.id');
        App::import('Model', 'CurrentCart');
        $this -> CurrentCart = new CurrentCart();
        $colesid=base64_decode($colesid);
        $woolworthid=base64_decode($woolworthid);
        $colesprice_id=base64_decode($colesprice_id);
        $woolworthprice_id=base64_decode($woolworthprice_id);
        $data['CurrentCart']['coles_product_id']=$colesid;
        $data['CurrentCart']['woolworths_product_id']=$woolworthid;
        $data['CurrentCart']['user_id']=$userid;
        $data['CurrentCart']['quantity']=$quantity;
        $data['CurrentCart']['coles_price_id']=$colesprice_id;
        $data['CurrentCart']['woolworths_price_id']=$woolworthprice_id;
        App::import('Model','ColesPrice');
        $this->ColesPrice=new ColesPrice();
        $colesprice=$this->ColesPrice->find('first',array('conditions'=>array('ColesPrice.id'=>$colesprice_id),'fields'=>array('ColesPrice.cart_detail')));
        
        
        if(!empty($colesprice['ColesPrice']['cart_detail'])){
            
            if(preg_match('/{.*?}/is',$colesprice['ColesPrice']['cart_detail'],$table))
                {
                    $table=str_replace(array('"','','{','}'), '', $table[0]);
                    $table=str_replace(":", "=", $table);
                    $cart_data=explode(',', $table) ;
                    $final_cart=array();
                    foreach ($cart_data as $cartkey => $cartvalue) {
                        $final_cart[$cartkey]=explode('=', $cartvalue);
                    }
                   //pr($cart_data);
                    $carturl="quantity=".$quantity."&catEntryId=".trim($final_cart[7][1])."&storeId=".trim($final_cart[2][1])."&catalogId=10574&langId=-1&orderId=.&calculationUsage=-1&divId=catEntry_".trim($final_cart[3][1])."&fulfillmentCenterId=11574&forStoreId=10401&doInventory=N&doPrice=Y&calculateOrder=-1&context=productElement.trolleyAdd&serviceId=AjaxOrderChangeServiceItemAdd&expectedType=json-comment-filtered";
                    $carturl="http://shop.coles.com.au/online/nsw-metro-pagewood/AjaxOrderChangeServiceItemAdd?".$carturl;
                    //echo $carturl."<br><br>";
                    
                }
        }
        //die();
        $Check_Current_Cart=$this->CurrentCart->find("first",array("conditions"=>array("CurrentCart.user_id"=>$userid,"CurrentCart.coles_product_id"=>$colesid,"CurrentCart.coles_price_id"=>$colesprice_id,"CurrentCart.woolworths_price_id"=>$woolworthprice_id)));
        if(empty($Check_Current_Cart)){
            $this->CurrentCart->save($data);
            echo "Product Added Successfully";
        }else{
            $update['CurrentCart']['id']=$Check_Current_Cart['CurrentCart']['id'];
            $update['CurrentCart']['quantity']=$Check_Current_Cart['CurrentCart']['quantity']+$quantity;
            $this->CurrentCart->save($update);
            echo "Product Added Successfully";
        }
    }

    public function DeleteCart($cartid=null){
        $this->layout=false;
        $this->render(false);
        $id=base64_decode($cartid);
        App::import('Model', 'CurrentCart');
        $this -> CurrentCart = new CurrentCart();       
        $this->CurrentCart->delete($id);
        $this->redirect(array("controller"=>"Homes","action"=>"browse",PRODUCT_LIMIT));
    }
    
    public function ClearTrolley(){
        $this->layout=false;
        $this->render(false);
        App::import('Model', 'CurrentCart');
        $this -> CurrentCart = new CurrentCart();
        $userid=$this->Session->read('Auth.User.id');
        $conditions=array("CurrentCart.user_id"=>$userid);
        $this->CurrentCart->deleteAll($conditions);
        $this->redirect(array("controller"=>"Homes","action"=>"browse",PRODUCT_LIMIT));
    }

    // type will be Coles or Woolworths
    public function Checkout($type=null){
        $this->layout=false;
        $this->render(false);
        App::import('Model', 'CheckoutCart');
        $this -> CheckoutCart = new CheckoutCart();  
        App::import('Model', 'CurrentCart');
        $this -> CurrentCart = new CurrentCart();
        $userid=$this->Session->read('Auth.User.id');
        $current_cart=$this->CurrentCart->find("all",array("conditions"=>array("CurrentCart.user_id"=>$userid)));
        if(!empty($current_cart)){
            $supermarket=base64_decode($type);
            if($this->request->is('post')){
                $data['CheckoutCart']=$this->request->data;
                $data['CheckoutCart']['supermarket']=$supermarket;
                $data['CheckoutCart']['user_id']=$userid;
                if($this->CheckoutCart->save($data)){
                    $checkoutcart_id=$this->CheckoutCart->getLastInsertId();
                    App::import('Model', 'HistoricalCart');
                    $this -> HistoricalCart = new HistoricalCart();
                    $saved_cart=array();
                    foreach ($current_cart as $currentcart_key => $ccart) {
                        $saved_cart['HistoricalCart']=$ccart['CurrentCart'];
                        $saved_cart['HistoricalCart']['checkout_cart_id']=$checkoutcart_id;
                        $this->HistoricalCart->create();
                        $this->HistoricalCart->save($saved_cart);
                    }
                    $conditions=array("CurrentCart.user_id"=>$userid);
                    $this->CurrentCart->deleteAll($conditions);
                }
            }
            echo json_encode(array("Cart"=>"1"));
        }else{
            echo json_encode(array("Cart"=>"0"));
        }
    }

    public function increase_quantity($cartid=null,$quantity=null){
        $this->layout=false;
        $this->render(false);
        App::import('Model', 'CurrentCart');
        $this -> CurrentCart = new CurrentCart();
        $data['CurrentCart']['id']=$cartid;
        $data['CurrentCart']['quantity']=$quantity;
        $this->CurrentCart->save($data);
    }
    
    public function decrease_quantity($cartid=null,$quantity=null){
        $this->layout=false;
        $this->render(false);
        App::import('Model', 'CurrentCart');
        $this -> CurrentCart = new CurrentCart();
        $data['CurrentCart']['id']=$cartid;
        $data['CurrentCart']['quantity']=$quantity;
        if($quantity==0){
                 $this->CurrentCart->delete($cartid);
        }else{
        $this->CurrentCart->save($data);
        }
    }    

    public function demo(){
        
    }
    
    public function pagenotfound(){
        $this->set('pagetitle',"Price Comparison- Page Not Found");
    }
    
    public function AddToColesCart(){
        $this->layout=false;
        $this->render(false);
        
        $coles_url="http://shop.coles.com.au/online/national/livefree-natural-cheese-shredded";
        
//$url="http://shop.coles.com.au/online/nsw-metro-pagewood/AjaxOrderChangeServiceItemAdd?quantity=1&catEntryId=144291&storeId=10503&catalogId=10574&langId=-1&orderId=.&calculationUsage=-1&divId=catEntry_87845&fulfillmentCenterId=11574&forStoreId=10401&doInventory=N&doPrice=Y&calculateOrder=-1&context=productElement.trolleyAdd&serviceId=AjaxOrderChangeServiceItemAdd&expectedType=json-comment-filtered";
        include("simple_html_dom.php");
        
        $data = file_get_html($coles_url);
        $output= html_entity_decode($data);
        $html = str_get_html($output);
        //echo $html;
        
          
        
   
        foreach ($html->find('.prodtile') as $form) {
           if(preg_match('/<div[^>]+data-refresh\W+addCalculationUsage\W.*?>(.*?)>/is',$form,$table))
            {
                if(preg_match('/data-refresh\W.*?>(.*?)>/is',$table[0],$data)){
                    pr($data);
                }
                
                
                
            }
           
                    
            
          
        }
        
        
    }
    
    /***************************************Crawling Functions***************************************/

    function _curler($Url, $cookie = null) {
        set_time_limit(0);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $Url);
        $agent = "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/532.4 (KHTML, like Gecko) Chrome/4.0.233.0 Safari/532.4";
        $referer = "http://www2.woolworthsonline.com.au/";
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        if ($cookie != null) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_COOKIEJAR, "gmovie.txt");
        }
        //curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);

        return $output;
    }
   
}

?>
