<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Network\Email\Email;

class ClaveController extends AppController
 {
    public function initialize() {
        parent::initialize();
        $this->loadModel('Users');
    }
    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index() {
        $this->viewBuilder()->layout('/Home/clave');
    }
    public function clave () 
    {
      //  $this->Fifa->clave();
      $this->viewBuilder()->layout('/Home/clave');
      if ($this->request->is('post')) 
      {
      }    


    }
}

