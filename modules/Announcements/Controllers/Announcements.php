<?php
namespace Modules\Announcements\Controllers;

use App\Controllers\BaseController;
use Modules\Announcements\Models as Models;

class Announcements extends BaseController
{
    public function __construct() {
        $this->announceModel = new Models\AnnouncementModel();
    }

    public function index() {
        // checking roles and permissions
        $data['perm_id'] = check_role('10', 'ANN', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        $data['announcements'] = $this->announceModel->viewUploader();
        // echo '<pre>';
        // print_r($data['announcements']);
        // die();
        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'announcements';
        $data['title'] = 'Announcements';
        return view('Modules\Announcements\Views\index', $data);
    }

    public function add() {
        // checking roles and permissions
        $data['perm_id'] = check_role('10', 'ANN', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        helper('text');
        $data['edit'] = false;
        if($this->request->getMethod() == 'post') {
            if($this->validate('announcements')){
                $file = $this->request->getFile('image');
                $ann = $_POST;
                $ann['image'] = $file->getRandomName();
                $ann['link'] = random_string('alnum', 5);
                $ann['uploader'] = $this->session->get('user_id');
                if($this->announceModel->insert($ann)) {
                    $file->move('uploads/announcements', $ann['image']);
                    if ($file->hasMoved()) {
                        if(isset($_POST['sendMail']) == 'yes') {
                            $this->sendMail();
                        }
                        else {
                            $this->session->setFlashData('successMsg', 'Adding annoucement successful');
                        }
                    } else {
                        $this->session->setFlashData('failMsg', 'There is an error on adding announcement. Please try again.');
                    }
                    return redirect()->to(base_url('admin/announcements'));
                } else {
                    $this->session->setFlashData('failMsg', 'There is an error on adding announcement. Please try again.');
                }
            } else {
                $data['value'] = $_POST;
                $data['errors'] = $this->validation->getErrors();
            }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'announcements';
        $data['title'] = 'Announcements';
        return view('Modules\Announcements\Views\form', $data);
    }

    public function edit($link) {
        // checking roles and permissions
        $data['perm_id'] = check_role('10', 'ANN', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        helper('text');
        $data['edit'] = true;
        $data['link'] = $link;
        $data['value'] = $this->announceModel->where('link', $link)->first();
        $data['id'] = $data['value']['id'];
        if($this->request->getMethod() == 'post') {
            if($this->validate('announcements')){
                $file = $this->request->getFile('image');
                $ann = $_POST;
                $ann['image'] = $file->getRandomName();
                $ann['link'] = random_string('alnum', 5);
                $ann['uploader'] = $this->session->get('user_id');
                if($this->announceModel->update($data['id'], $ann)) {
                    $file->move('uploads/announcements', $ann['image']);
                    if ($file->hasMoved()) {
                        $this->session->setFlashData('successMsg', 'Editing annoucement successful.');
                    } else {
                        $this->session->setFlashData('failMsg', 'There is an error on editing announcement. Please try again.');
                    }
                    return redirect()->to(base_url('admin/announcements'));
                } else {
                    $this->session->setFlashData('failMsg', 'There is an error on editing announcement. Please try again.');
                }
            } else {
                $data['value'] = $_POST;
                $data['errors'] = $this->validation->getErrors();
            }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'announcements';
        $data['title'] = 'Announcements';
        return view('Modules\Announcements\Views\form', $data);
    }

    public function delete($link) {
        // checking roles and permissions
        $data['perm_id'] = check_role('13', 'ANN', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        if($this->announceModel->where('link', $link)->delete()) {
          $this->session->setFlashData('successMsg', 'Successfully deleted announcement');
        } else {
          $this->session->setFlashData('errorMsg', 'Something went wrong!');
        }
        return redirect()->to(base_url('admin/announcements'));
    }

    private function sendMail() {
        $mails = $this->userModel->where('email !=', $this->session->get('email'))->findColumn('email');

        foreach($mails as $mail) {
            $this->email->clear();
            $this->email->setFrom('facultyea@gmail.com', 'Faculty and Employees Association');
            $this->email->setTo($mail);
            $this->email->setSubject($_POST['title']);
            $content = view('Modules\Announcements\Views\email', $_POST);
            $this->email->setMessage($content);
            if($this->email->send()) {
                echo 'Email successfully sent';
                die();
            }
            else {
                $data = $this->email->printDebugger(['headers']);
                print_r($data);
                die();
            }
        }
    }
}