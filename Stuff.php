<?php

namespace App\Controllers;

class Stuff extends BaseController
{
    public function __construct()
    {
        helper('form');
        $this->validation = \config\Services::validation();
        $this->session = session();
    }

    public function index()
    {
        $stuffModel = new \App\Models\StuffModel();
        $stuffs = $stuffModel->findAll();

        return view('stuff/index', [
            'stuffs' => $stuffs,
        ]);
    }

    public function view()
    {
        $id = $this->request->uri->getSegment(3);

        $stuffModel = new \App\Models\StuffModel();

        $stuff = $stuffModel->find($id);

        return view('stuff/view.php', [
            'stuff' => $stuff,
        ]);
    }

    public function create()
    {
        if ($this->request->getPost()) {
            //jika data yang dipost
            $data = $this->request->getPost();
            $this->validation->run($data, 'stuff');
            $errors = $this->validation->getErrors();

            if (!$errors) {
                //simpan datanya
                $stuffModel = new \App\Models\StuffModel();

                $stuff = new \App\Entities\Stuff();

                $stuff->fill($data);
                $stuff->gambar = $this->request->getFile('gambar');
                $stuff->create_by = $this->session->get('id');
                $stuff->create_date = date("Y-m-d H:i:s");

                $stuffModel->save($stuff);

                $id = $stuffModel->insertID();

                $segments = ['stuff', 'view', $id];

                return redirect()->to(site_url($segments));
            }
        }
        return view('stuff/create');
    }

    public function update()
    {
        $id = $this->request->uri->getSegment(3);

        $stuffModel = new \App\Models\StuffModel();

        $stuff = $stuffModel->find($id);

        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $this->validation->run($data, 'stuffupdate');
            $errors = $this->validation->getErrors();

            if (!$errors) {
                $s = new \App\Entities\Stuff();
                $s->id = $id;
                $s->fill($data);

                if ($this->request->getFile('gambar')->isValid()) {
                    $s->gambar = $this->request->getFile('gambar');
                }

                $s->updated_by = $this->session->get('id');
                $s->updated_date = date("Y-m-d H:i:s");

                $stuffModel->save($s);

                $segments = ['stuff', 'view', $id];

                return redirect()->to(base_url($segments));
            }
        }
        return view('stuff/update', [
            'stuff' => $stuff,
        ]);
    }

    public function delete()
    {
        $id = $this->request->uri->getSegment(3);

        $modelStuff = new \App\Models\StuffModel();
        $delete = $modelStuff->delete($id);

        return redirect()->to(site_url('stuff/index'));
    }
}
