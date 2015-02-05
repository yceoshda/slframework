<?php

namespace spacelife;

use spacelife\core\Controller;

/**
* Cache
*/
class CacheController extends Controller
{

    protected $template_dir = 'cache';

    protected $translate_admin = ['cache_admin'];

    /**
    *   index
    *
    **/
    public function admin_index()
    {
        $cache_list = $this->cache->getList();

        // debug($cache_list);

        $tpl = $this->template('index');
        $tpl->set('files', $cache_list);
        $tpl->set('form', $this->createForm([], [], 'cache_admin'));

        $this->setContent($tpl->render());
    }

    /**
    *   admin_list
    *
    **/
    public function admin_list()
    {
        return $this->admin_index();
    }

    /**
    *   admin_delete
    *
    **/
    public function admin_delete($file, $batch = false)
    {
        $delete = $this->cache->delete($file);

        if ($delete) {
            $this->session->setFlash(['message' => $this->T->cache_admin->delete->success, 'type' => 'success', 'timeout' => 5]);
        } else {
            $this->session->setFlash(['message' => $this->T->cache_admin->delete->failure, 'type' => 'error', 'timeout' => 5]);
        }

        if ($batch) {
            return 1;
        }

        $this->redirect('admin/cache/index');
    }

    /**
    *   admin_deleteall
    *
    **/
    public function admin_deleteall()
    {
        $list = $this->cache->getList();

        foreach ($list as $file) {
            $this->admin_delete($file['filename'].'.'.$file['extension'], true);
        }

        $this->redirect('admin/cache/index');
    }


}