<?php

use GuzzleHttp\Client;

class Fruit_model extends CI_Model {

    private $_client;

    public function __construct()
    {
        $this->_client = new Client([
            'base_uri' => 'http://localhost/restful-api-crud/rest-server/api/',
            'auth' => ['adit', '050801']
        ]);
    }

    public function getAll() {
        // return $this->db->get('fruits')->result();
        $response = $this->_client->request('GET', 'fruits', [
            'query' => [
                'apikey' => '050801'
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result['data'];
    }

    public function getById($id) {
        // return $this->db->get_where('fruits', ['id' => $id])->row();
        $response = $this->_client->request('GET', 'fruits', [
            'query' => [
                'apikey' => '050801',
                'id' => $id
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result['data'];
    }

    public function save() {
        $id = uniqid();

        $data = [
            'id' => $id,
            'name' => $this->input->post('name', true),
            'price' => $this->input->post('price', true),
            'image' => $this->_uploadImage($id),
            'apikey' => '050801'
        ];

        // return $this->db->insert('fruits', $data);

        $response = $this->_client->request('POST', 'fruits', [
            'form_params' => $data            
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result['data'];   
    }

    public function update() {
        $id = $this->input->post('id', true);

        $data = [
            'id' => $id,
            'name' => $this->input->post('name', true),
            'price' => $this->input->post('price', true),
            'apikey' => '050801'
        ];

        if (!empty($_FILES['image']['name'])) {
            $data['image'] = $this->_uploadImage($id);
        } else {
            $this->image = $post['old_image'];
        }

        $response = $this->_client->request('PUT', 'fruits', [
            'form_params' => $data
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result;

        // return $this->db->update('fruits', $data, array('id' => $post['id']));
    }

    public function delete($id) {
        $this->_deleteImage($id);
        // return $this->db->delete('fruits', array('id' => $id));
        $response = $this->_client->request('DELETE', 'fruits', [
            'form_params' => [
                'id' => $id,
                'apikey' => '050801'
            ]
        ]);
    }

    private function _uploadImage($id) {
        $config['upload_path']      = './assets/img/';
        $config['allowed_types']    = 'jpg|jpeg|png';
        $config['file_name']        = $id;
        $config['overwrite']        = true;
        $config['max_size']         = '10000';

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('image')) {
            return $this->upload->data('file_name');
        }

        return "default.jpg";
    }

    private function _deleteImage($id) {
        $fruit = $this->getById($id);

        if ($fruit['image'] != "default.jpg") {
            $file_name = explode(".", $fruit['image'])[0];
            return array_map('unlink', glob(FCPATH. "assets/img/$file_name.*"));
        }
    }

}