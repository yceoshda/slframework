<?php

namespace spacelife;

use spacelife\core\Controller;

/**
* Home
*/
class HomeController extends Controller
{
    //  templates
    protected $template_dir = 'home';

    //  load translations
    public $translate = ['home'];

    /*
    *   index
    *       default page
    */
    public function index()
    {
        $tpl = $this->template('index');
        $this->setContent($tpl->render());
    }

    /*
    *   about
    *       about page
    */
    public function about()
    {
        $tpl = $this->template('about');
        $this->setContent($tpl->render());
    }

    /*
    *   contact
    *       contact page
    */
    public function contact()
    {
        $tpl = $this->template('contact');
        $this->setContent($tpl->render());
    }


    /**
    *   ADMIN Methods
    *
    */

    /*
    *   admin_index
    *       admin default page
    */
    public function admin_index()
    {
        $tpl = $this->template('admin_index');
        $this->setContent($tpl->render());
    }
}