<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
class PersonalisedOffersController extends AppController {
    public $name = 'PersonalisedOffers';
    public $uses = array("PostcodesGeo","ProductDiscount","ProductItem","User");
    public $components = array('Paginator');
    
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    function shophead_index()
    {
        $this->layout = 'admin_layout';
        $this->set('title','Users applied for Personalised Offers');
        $conditions=array("User.personalized_offer"=>1);
        if (!empty($this->request->data)) {           
            if ($this->request->data['PersonalisedOffers']['keyword'] != "") {
                $cond = array();
                $complete_name = explode(" ", $this->request->data['PersonalisedOffers']['keyword']);
                $cond['User.firstname LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                $cond['User.lastname LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                $cond['User.username LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                $cond['User.created LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                 
                 $conditions['OR'] = $cond;
                $this->request->params['named']['PersonalisedOffers.keyword'] = $this->request->data['PersonalisedOffers']['keyword'];
            }
        } else {           
            if (isset($this->request->params['named']['PersonalisedOffers.keyword']) && $this->request->params['named']['PersonalisedOffers.keyword'] != "") {
                $cond = array();
                $cond['User.firstname LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                $cond['User.lastname LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                $cond['User.username LIKE'] = "%" . trim($this->request->data['PersonalisedOffers']['keyword']) . "%";
                $cond['User.created LIKE'] = "%" . trim($this->request->params['PersonalisedOffers']['PersonalisedOffers.keyword']) . "%";
                 $conditions['OR'] = $cond;
                $this->request->data['User']['keyword'] = $this->request->params['named']['User.keyword'];
            }
        }
        $this->paginate = array(
            'recursive' => 0,
            'limit' => LIMIT,
            'conditions' => $conditions
        );
        $result = $this->paginate('User');
        $this->set('offer', $result);
        
    }
}
?>