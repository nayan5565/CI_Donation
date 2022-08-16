<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';

use chriskacerguis\RestServer\RestController;

class Api extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Donation_Model');
        $this->load->library('upload');
        $this->load->dbforge();
    }


    function uniqidReal($lenght = 32)
    {
        // uniqid gives 32 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            $bytes = uniqid();
            // throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

    public function test_get()
    {
        echo 'Donation';
    }

    public function createTableIfNotExists()
    {
        // define table fields
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'title' => array(
                'type' => 'VARCHAR',
                'constraint' => 30
            ),
            'body' => array(
                'type' => 'VARCHAR',
                'constraint' => 30
            )
        );

        $this->dbforge->add_field($fields);

        // define primary key
        $this->dbforge->add_key('id', TRUE);

        // create table
        $this->dbforge->create_table('tbltest', TRUE);
    }

    //test api
    public function posts_add_post()
    {
        $this->createTableIfNotExists();
        date_default_timezone_set('Asia/Dhaka');
        $user = new Donation_Model;
        $data = array(
            'title' => $this->input->post('title'),
            'body' => $this->input->post('body')
        );

        $result = $user->insert_test_entry($data);
        if ($result > 0) {
            $response = array(
                'status' => 'success',
                'message' => 'Successfully added',
                'error' => FALSE,
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    //test api
    public function posts_list_get()
    {

        $user = new Donation_Model;

        $result = $user->test_list();
        $users = $result->result_array();

        // echo (sizeof($users));
        $count = sizeof($users);
        if ($result != null) {

            $response = array(
                'status' => 'success',
                'message' => 'Fetch list',
                'count' => $count,
                'error' => FALSE,
                'posts' => $users
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function signup_post()
    {
        date_default_timezone_set('Asia/Dhaka');
        $user = new Donation_Model;
        $dm_users = $user->dm_last_id();
        $dm_user_id = $dm_users + 1;
        $generate_dm_id = 'dm_00' . $dm_user_id;
        $userType = $this->input->post('user_type');
        $user_id = $userType  == 'DM' ? $generate_dm_id : $this->uniqidReal();
        $data = array(
            'first_name' => $this->input->post('firstname'),
            'under_dm_id' => $this->input->post('under_dm_id'),
            'email' => $this->input->post('email'),
            'phone' => $this->input->post('phone'),
            'user_type' => $this->input->post('user_type'),
            'user_id' => $user_id,
            'password' => $this->input->post('password'),
            'created_at' => date('Y-m-d H:i:s'),
        );
        $userData = $user->user_data_email($this->input->post('email'));



        if ($userData != null) {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'User already exists'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $result = $user->insert_entry($data);
            if ($result > 0) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Sign up successful',
                    'error' => FALSE,
                    'user' => $user->user_data($result)
                );
                $this->output
                    ->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
            } else {
                $response = array(
                    'status' => 'error',
                    'error' => TRUE,
                    'message' => 'something error'
                );
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
            }
        }
    }

    public function update_profile_post()
    {
        date_default_timezone_set('Asia/Dhaka');
        $targetfolder = "donationFiles";

        if (!file_exists($targetfolder)) {
            if (mkdir($targetfolder)) {
            }
        }
        $config['upload_path'] = "./$targetfolder/";
        $config['overwrite'] = TRUE;
        $config['allowed_types'] = 'gif|jpg|gif|png|jpeg|JPG|PNG|zip|mp4|docx';


        $photo = '';
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('profile_image')) {
            // print_r($this->upload->display_errors());
        } else {
            $img_data = array('upload_data' => $this->upload->data());
            $photo = $img_data['upload_data']['file_name'];
        }
        $id = $this->input->post('user_id');
        $user = new Donation_Model;
        $userData = $user->user_data2($id);

        $userData->first_name = $this->input->post('firstname');
        $userData->phone = $this->input->post('phone');
        $userData->is_lock = $this->input->post('is_lock');
        if (!empty($photo))
            $userData->photo = $photo;
        $userData->updated_at = date('Y-m-d H:i:s');



        $result = $user->update_entry($userData, $id);
        if ($result > 0) {
            $response = array(
                'status' => 'success',
                'message' => 'Sign in successful',
                'error' => FALSE,
                'user' => $userData
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function signIn_post()
    {
        $userType = '';
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $userType = $this->input->post('user_type');
        // echo ($userType);
        // if ($isDMLogin) {
        //     $userType = 'DM';
        // } else {
        //     $userType = 'Donor';
        // }
        $user = new Donation_Model;
        $result = $user->user_login($email, $password, $userType);

        if ($result != null) {
            // echo ($result->id);
            $response = array(
                'status' => 'success',
                'message' => 'Sign in successful',
                'error' => FALSE,
                'user' => $result
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'wrong email or password'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }
    public function user_details_post()
    {
        $id = $this->input->post('user_id');
        $user = new Donation_Model;
        $result = $user->user_data2($id);

        if ($result != null) {
            $response = array(
                'status' => 'success',
                'error' => FALSE,
                'user' => $result
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'user not found'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function create_donee_post()
    {
        date_default_timezone_set('Asia/Dhaka');
        $targetfolder = "donationFiles";

        if (!file_exists($targetfolder)) {
            if (mkdir($targetfolder)) {
            }
        }
        $config['upload_path'] = "./$targetfolder/";
        $config['overwrite'] = TRUE;
        $config['allowed_types'] = 'gif|jpg|gif|png|jpeg|JPG|PNG|zip|mp4|docx';

        $photo = '';
        $lat = '';
        $lon = '';
        $nid = '';
        $files = array();
        $files_upload_key = 'files';
        $postman_files_param = 'profile_image';
        $count = count($_FILES[$postman_files_param]['name']);
        $user = new Donation_Model;
        $this->upload->initialize($config);
        // if (!$this->upload->do_upload('profile_image')) {
        // print_r($this->upload->display_errors());
        // } else {
        //     $img_data = array('upload_data' => $this->upload->data());
        //     $photo = $img_data['upload_data']['file_name'];
        // }
        for ($i = 0; $i < $count; $i++) {
            $_FILES[$files_upload_key]['name'] = $_FILES[$postman_files_param]['name'][$i];
            $_FILES[$files_upload_key]['type'] = $_FILES[$postman_files_param]['type'][$i];
            $_FILES[$files_upload_key]['tmp_name'] = $_FILES[$postman_files_param]['tmp_name'][$i];
            $_FILES[$files_upload_key]['error'] = $_FILES[$postman_files_param]['error'][$i];
            $_FILES[$files_upload_key]['size'] = $_FILES[$postman_files_param]['size'][$i];

            if ($this->upload->do_upload($files_upload_key)) {
                $uploadData = array('upload_data' => $this->upload->data());
                $filename = $uploadData['upload_data']['file_name'];
                if ($i == $count - 1) {
                    $photo .= $filename;
                } else {
                    $photo .= $filename . ',';
                }
            }
        }
        $lat = $this->input->post('lat');
        $lon = $this->input->post('lon');
        $nid = $this->input->post('nid');
        $data = array(
            'first_name' => $this->input->post('firstname'),
            'father_name' => $this->input->post('fathername'),
            'mother_name' => $this->input->post('mothername'),
            'need_money' => $this->input->post('need_money'),
            'phone' => $this->input->post('phone'),
            'age' => $this->input->post('age'),
            'thana' => $this->input->post('thana'),
            'village' => $this->input->post('village'),
            'current_address' => $this->input->post('current_address'),
            'lat' => $lat,
            'lon' => $lon,
            'nid' => $nid,
            'dm_id' => $this->input->post('dm_id'),
            'photo' => $photo,
            'brn' => $this->input->post('brn'),
            'created_at' => date('Y-m-d H:i:s'),
        );
        // $userData = $user->user_data_email($this->input->post('email'));

        // if ($userData != null) {
        //     $response = array(
        //         'status' => 'error',
        //         'error' => TRUE,
        //         'message' => 'User already exists'
        //     );
        //     $this->output
        //         ->set_status_header(500)
        //         ->set_content_type('application/json')
        //         ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        // } else {
        $doneeID = $user->insert_donee($data);
        if ($doneeID > 0) {

            $response = array(
                'status' => 'success',
                'message' => 'successfully donee added!!',
                'error' => FALSE,
                'donee' => $user->user_donee($doneeID)
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }
    // }

    public function upload_files_post()
    {
        $targetfolder = "donationFiles";

        if (!file_exists($targetfolder)) {
            if (mkdir($targetfolder)) {
            }
        }
        $video = $this->input->post('video');
        $this->load->library('upload');
        $files = array();
        $filenames = '';
        $files_upload_key = 'files';
        $postman_files_param = 'upload_files';
        $count = count($_FILES[$postman_files_param]['name']);

        $config['upload_path'] = "./$targetfolder/";
        $config['overwrite'] = TRUE;
        if ($video == 'video') {
            $config['allowed_types'] = 'mp4';
        } else
            $config['allowed_types'] = 'gif|jpg|gif|png|jpeg|JPG|PNG|zip|mp4|docx';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        for ($i = 0; $i < $count; $i++) {
            $_FILES[$files_upload_key]['name'] = $_FILES[$postman_files_param]['name'][$i];
            $_FILES[$files_upload_key]['type'] = $_FILES[$postman_files_param]['type'][$i];
            $_FILES[$files_upload_key]['tmp_name'] = $_FILES[$postman_files_param]['tmp_name'][$i];
            $_FILES[$files_upload_key]['error'] = $_FILES[$postman_files_param]['error'][$i];
            $_FILES[$files_upload_key]['size'] = $_FILES[$postman_files_param]['size'][$i];

            if ($this->upload->do_upload($files_upload_key)) {
                $uploadData = array('upload_data' => $this->upload->data());
                $filename = $uploadData['upload_data']['file_name'];
                if ($i == $count - 1) {
                    $filenames .= $filename;
                } else {
                    $filenames .= $filename . ',';
                }

                $files[$i]['file_name'] = $filename;
                $files[$i]['user_id'] = $i + 1 . '';
                $files[$i]['is_profile'] = 1;
                $files[$i]['is_video'] = 1;
            }
        }

        if (!empty($filenames)) {
            // if (count($files) > 0) {
            $user = new Donation_Model;
            // $files[0]['user_id'] = '1';
            // $files[0]['file_name'] = $filenames;
            // $files[0]['is_profile'] = 1;
            $result = $user->insert_multiple_file($files);
            if ($result > 0) {
                $response = array(
                    'status' => 'success',
                    'error' => false,
                    'message' => $files,
                );
                $this->output
                    ->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
            } else {
                $response = array(
                    'status' => 'error',
                    'error' => TRUE,
                    'message' => 'something error'
                );
                $this->output
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
            }
        } else {
            $error = array('status' => $this->upload->display_errors());
            $response = array(
                $error,
                'error' => TRUE,
                'files' => 'Total Seleced ' . $count . ' files',
                'message' => $files
            );
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function donation_files_post()
    {
        $id = $this->input->post('user_id');
        $isVideo = $this->input->post('is_video');
        $user = new Donation_Model;
        $result = $user->donation_files($id, $isVideo);

        if ($result != null) {
            $response = array(
                'status' => 'success',
                'error' => FALSE,
                'message' => 'fetch data',
                'files' => $result->result_array()
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'user not found'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function donation_users_post()
    {

        $user = new Donation_Model;
        $userType = $this->input->post('user_type');
        $result = $user->donation_users($userType);
        $users = $result->result_array();

        // echo (sizeof($users));
        $count = sizeof($users);
        if ($result != null) {
            // for ($x = 0; $x < $count; $x++) {
            //     $donate_receive_result = $user->donate_receive_list($users[$x]['id']);
            //     $donate_receives = $donate_receive_result->result_array();
            //     $users[$x]['donate_receive'] = $donate_receives;
            // }
            $response = array(
                'status' => 'success',
                'message' => 'Fetch ' . $userType . ' list',
                'count' => $count,
                'error' => FALSE,
                'users' => $users
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function donee_list_get()
    {

        $user = new Donation_Model;

        $result = $user->donee_list();
        $users = $result->result_array();

        // echo (sizeof($users));
        $count = sizeof($users);
        if ($result != null) {
            for ($x = 0; $x < $count; $x++) {
                $donate_receive_result = $user->donate_receive_list($users[$x]['id']);
                $donate_receives = $donate_receive_result->result_array();
                $users[$x]['donate_receive'] = $donate_receives;
            }
            $response = array(
                'status' => 'success',
                'message' => 'Fetch donee list',
                'count' => $count,
                'error' => FALSE,
                'donees' => $users
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function donee_profile_post()
    {
        $id = $this->input->post('donee_id');
        $user = new Donation_Model;
        $result = $user->user_donee($id);
        $donate_receive_result = $user->donate_receive_list($id);
        $donate_receives = $donate_receive_result->result_array();
        $result->donate_receive = $donate_receives;

        if ($result != null) {
            $response = array(
                'status' => 'success',
                'error' => FALSE,
                'message' => 'Fetch donee profile data',
                'donee_user' => $result,
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'user not found'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function receive_donate_post()
    {
        date_default_timezone_set('Asia/Dhaka');
        $user = new Donation_Model;
        $donorID = $this->input->post('donor_id');
        $donorUser = $user->user_data2($donorID);
        $data = array(
            'amount' => $this->input->post('amount'),
            'donee_id' => $this->input->post('donee_id'),
            'donor_id' => $donorID,
            'donor_name' => $donorUser->first_name,
            'donor_email' => $donorUser->email,
            'donor_phone' => $donorUser->phone,
            'dm_id' => $this->input->post('dm_id'),
            'receive_date' => date('Y-m-d H:i:s'),
        );
        $result = $user->record_donate_receive($data);
        if ($result > 0) {
            $response = array(
                'status' => 'success',
                'message' => 'successfully record data',
                'error' => FALSE
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'something error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function donation_records_post()
    {
        $donate_receives = '';
        $message = '';
        $user = new Donation_Model;
        $id =  $this->input->post('id');
        $receive_donate =  $this->input->post('receive_donate');
        if ($receive_donate == 'true') {
            $message = 'Fetch donate receive data';
            $donate_receive_result = $user->donate_receive_list($id);
            $donate_receives = $donate_receive_result->result_array();
        } else {
            $message = 'Fetch donate paid data';
            $donate_receive_result = $user->donate_paid_list($id);
            $donate_receives = $donate_receive_result->result_array();
        }

        if ($donate_receive_result > 0) {
            $response = array(
                'message' => $message,
                'status' => 'success',
                'error' => FALSE,
                'donate_receive_paid' => $donate_receives
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'error' => TRUE,
                'message' => 'something wrong',
                'status' => 'error',
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function connect_donee_donor_post()
    {
        $user = new Donation_Model;
        $doneeID = $this->input->post('donee_id');
        $donorID = $this->input->post('donor_id');
        $donorUser = $user->user_data2($donorID);
        $data = array(
            'donor_name' => $donorUser->first_name,
            'donor_email' => $donorUser->email,
            'donor_phone' => $donorUser->phone,
            'donee_id' => $doneeID,
            'donor_id' => $donorID,
            'dm_id' => $this->input->post('dm_id'),
            'connected_date' => date('Y-m-d H:i:s'),
        );

        $userData = $user->donee_donor_data($doneeID, $donorID);

        if ($userData != null) {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'Donor already added'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $result = $user->record_donee_donor($data);
            if ($result > 0) {
                $donee_doners = $user->donee_donor_list($doneeID)->result_array();
                $count = sizeof($donee_doners);
                $response = array(
                    'status' => 'success',
                    'count' => $count,
                    'message' => 'successfully donor added',
                    'error' => FALSE,
                    'donee_doners' => $donee_doners
                );
                $this->output
                    ->set_status_header(200)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
            } else {
                $response = array(
                    'status' => 'error',
                    'error' => TRUE,
                    'message' => 'something error'
                );
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
            }
        }
    }

    public function donee_donor_list_post()
    {
        $donee_donors = '';
        $user = new Donation_Model;
        $id =  $this->input->post('id');
        $donorList =  $this->input->post('donorList');
        if ($donorList == 'true') {
            $donee_donor_result = $user->donee_donor_list($id);
            $donee_donors = $donee_donor_result->result_array();
        } else {
            $donee_donor_result = $user->donor_donee_list($id);
            $donee_donors = $donee_donor_result->result_array();
        }

        $count = sizeof($donee_donors);
        if ($donee_donors > 0) {
            $response = array(
                'status' => 'success',
                'count' => $count,
                'message' => 'fetch donee donor data',
                'error' => FALSE,
                'donee_doners' => $donee_donors
            );
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'error' => TRUE,
                'message' => 'error'
            );
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

    public function send_email_post()
    {
        $this->load->library('email');
        // $config['protocol'] = 'sendmail';
        // $config['mailpath'] = '/usr/sbin/sendmail';
        // $config['charset'] = 'iso-8859-1';
        // $config['wordwrap'] = TRUE;

        // $this->email->initialize($config);

        $this->email->from('nayan5565@gmail.com', 'Your Name');
        $this->email->to('developernayan5565@gmail.com');
        // $this->email->cc('another@another-example.com');
        // $this->email->bcc('them@their-example.com');

        $this->email->subject('Email Test');
        $this->email->message('Testing the email class.');

        if ($this->email->send()) {
            $response = array(
                'status' => 'success',
                'message' => 'successfully sent email',
                'error' => FALSE
            );

            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'failed sent email',
                'error' => TRUE
            );

            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }
}
