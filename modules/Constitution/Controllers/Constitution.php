<?php
namespace Modules\Constitution\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use Modules\Constitution\Models as Models;
use App\Libraries as Libraries;

class Constitution extends BaseController
{
	function __construct() {
        $this->constiModel = new Models\ConstitutionModel();
        $this->pdf = new Libraries\Pdf();
        $this->mpdf = new \Mpdf\Mpdf();
	}
	
	public function index() {
        $data['perm_id'] = check_role('', '', $this->session->get('role'));
        $data['rolePermission'] = $data['perm_id']['rolePermission'];
        // if($this->session->get('role') != '1') {
        //     $this->session->setFlashdata('sweetalertfail', true);
        //     return redirect()->to(base_url());
        // }

        $data['consti'] = $this->constiModel->findAll();
        // echo '<pre>';
        // print_r($data['consti']);
        // die();

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'constitution';
        $data['title'] = 'Constitution';
        return view('Modules\Constitution\Views\index', $data);
	}

    public function add() {
        $data['perm_id'] = check_role('', '', $this->session->get('role'));
        $data['rolePermission'] = $data['perm_id']['rolePermission'];
        if($this->session->get('role') != '1') {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }

        $data['edit'] = false;
        if($this->request->getMethod() === 'post') {
          if($this->validate('constitution')){
            if($this->constiModel->insert($_POST)){
              $this->session->setFlashData('successMsg', 'Sucessfully added a constitution');
            } else {
              $this->session->setFlashData('failMsg', 'Something went wrong!');
            }
            return redirect()->to(base_url('constitution'));
          } else {
            $data['value'] = $_POST;
            $data['errors'] = $this->validation->getErrors();
          }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'constitution';
        $data['title'] = 'Constitution';
        return view('Modules\Constitution\Views\form', $data);
    }

    public function edit($id) {
        $data['perm_id'] = check_role('', '', $this->session->get('role'));
        $data['rolePermission'] = $data['perm_id']['rolePermission'];
        if($this->session->get('role') != '1') {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }

        $data['edit'] = true;
        $data['id'] = $id;
        $data['value'] = $this->constiModel->where(['id' => $id])->first();
        if($this->request->getMethod() === 'post') {
          if($this->validate('constitution')){
            if($this->constiModel->update($id, $_POST)){
              $this->session->setFlashData('successMsg', 'Sucessfully edited a constitution');
            } else {
              $this->session->setFlashData('failMsg', 'Something went wrong!');
            }
            return redirect()->to(base_url('constitution'));
          } else {
            $data['value'] = $_POST;
            $data['errors'] = $this->validation->getErrors();
          }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'constitution';
        $data['title'] = 'Constitution';
        return view('Modules\Constitution\Views\form', $data);
    }

    public function generatePDF() {
        $data['consti'] = $this->constiModel->findAll();
        $html = view('Modules\Constitution\Views\pdfgen', $data);
        $this->mpdf->SetHeader('FEA Constitution|'.date('M d,Y').'|Page: {PAGENO}');
        $this->mpdf->SetFooter('FEA Constitution');
        $this->mpdf->WriteHTML($html);
        $this->response->setHeader('Content-Type', 'application/pdf');
        $this->mpdf->Output('FEA Constitution.pdf','I');
    }

    public function delete($id) {
        $data = $this->constiModel->where(['id' => $id])->first();
        if($this->constiModel->delete($id)){
          $this->session->setFlashData('successMsg', 'Sucessfully deleted a constitution');
        } else {
          $this->session->setFlashData('failMsg', 'Something went wrong!');
        }
        return redirect()->to(base_url('constitution'));
    }
}