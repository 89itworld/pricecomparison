<?php
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class AdminsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
		public $name = 'Admins';

/**
 * This controller does not use a model
 *
 * @var array
 */
        public $uses = array('Admin','User','Product');
        public $helpers = array('Html','Form');
        public $components = array('RequestHandler','Session');
		
		
		public function admin_index(){
		}
		
		
/**
 * This function is used to diaply the admin dashboard
 *
 * @var array
 */
		public function shophead_dashboard(){
			$this->layout = "admin_layout";
            $this->set('title','Welcome to Dashboard');
            $activeusers=$this->User->find('all',array('conditions'=>array('User.status'=>1),'fields'=>array('User.status')));
            $inactiveusers=$this->User->find('all',array('conditions'=>array('User.status'=>0),'fields'=>array('User.status')));
            $activeproducts=$this->Product->find('all',array('conditions'=>array('Product.status'=>1),'fields'=>array('Product.status')));
            $inactiveproducts=$this->Product->find('all',array('conditions'=>array('Product.status'=>0),'fields'=>array('Product.status')));
            $this->set('activeusers',count($activeusers));
            $this->set('inactiveusers',count($inactiveusers));
            $this->set('activeproducts',count($activeproducts));
            $this->set('inactiveproducts',count($inactiveproducts));
		}

}