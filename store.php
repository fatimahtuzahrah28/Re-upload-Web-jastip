<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Response;

class Store extends BaseController
{
    private $url = "https://pro.rajaongkir.com/api/";
    private $apiKey = "d94cd20d45f8ec72f7469d3ce0324db1";

    public function __construct()
    {
        helper('form');
        $this->validation = \config\Services::validation();
        $this->session = session();
    }

    public function index()
    {
        $stuff = new \App\Models\StuffModel();
        $model = $stuff->findAll();
        return view('store/index', [
            'model' => $model,
        ]);
    }

    public function beli()
    {
        $id = $this->request->uri->getSegment(3);

        $modelStuff = new \App\Models\StuffModel();

        $modelKomentar = new \App\Models\KomentarModel();

        $komentar = $modelKomentar->where('id_stuff', $id)->findAll();

        $model = $modelStuff->find($id);

        $provinsi = $this->rajaongkir('province');

        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $this->validation->run($data, 'transaksi');
            $errors = $this->validation->getErrors();

            if (!$errors) {
                $transaksiModel = new \App\Models\TransaksiModel();
                $transaksi = new \App\Entities\Transaksi();

                $modelStuff = new \App\Models\StuffModel();
                $id_stuff = $this->request->getPost('id_stuff');
                $jumlah_pembelian = $this->request->getPost('jumlah');

                $transaksi->fill($data);
                $transaksi->status = 0;
                $transaksi->create_by = $this->session->get('id');
                $transaksi->create_date = date("Y-m-d H:i:s");

                $transaksiModel->save($transaksi);

                $id = $transaksiModel->insertID();

                $segment = ['transaksi', 'view', $id];

                return redirect()->to(site_url($segment));
            }
        }

        return view('store/beli', [
            'model' => $model,
            'komentar' => $komentar,
            'provinsi' => json_decode($provinsi)->rajaongkir->results,
        ]);
    }

    public function getCity()
    {
        if ($this->request->isAJAX()) {
            $id_province = $this->request->getGET('id_province');
            $data = $this->rajaongkir('city', $id_province);
            return $this->response->setJSON($data);
        }
    }

    public function getCost()
    {
        if ($this->request->isAJAX()) {
            $origin = $this->request->getGET('origin');
            $destination = $this->request->getGET('destination');
            $weight = $this->request->getGET('weight');
            $courier = $this->request->getGET('courier');
            $data = $this->rajaongkircost($origin, $destination, $weight, $courier);

            return $this->response->setJSON($data);
        }
    }

    private function rajaongkircost($origin, $destination, $weight, $courier)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . $origin . "&originType=city" . "&destination=" . $destination . "&destinationType=city" . "&weight=" . $weight . "&courier=" . $courier,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . $this->apiKey
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }

    private function rajaongkir($method, $id_province = null)
    {
        $endPoint = $this->url . $method;

        if ($id_province != null) {
            $endPoint = $endPoint . "?province=" . $id_province;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: " . $this->apiKey
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }
}
