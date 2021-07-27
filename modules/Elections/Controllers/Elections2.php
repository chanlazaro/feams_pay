<?php
namespace Modules\Elections\Controllers;

use App\Controllers\BaseController;
use Modules\Elections\Models as Models;
use Modules\Voting\Models as VotingModels;
use App\Libraries as Libraries;
use App\Models as AppModels;

class Elections2 extends BaseController
{
    public function __construct() {
        $this->electionModel = new Models\ElectionModel();
        $this->positionModel = new Models\PositionModel();
        $this->candidateModel = new Models\CandidateModel();
        $this->voteModel = new VotingModels\VoteModel();
        $this->voteDetailModel = new VotingModels\VoteDetailModel();
        $this->pdf = new Libraries\Pdf();
        $this->mpdf = new \Mpdf\Mpdf();
        $this->activityLogModel = new AppModels\ActivityLogModel();

        $elections = $this->electionModel->findAll();
    }
    
    public function index() {
        // checking roles and permissions
        $data['perm_id'] = check_role('19', 'ELEC', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];
        
        $data['elections'] = $this->electionModel->findAll();

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'elections';
        $data['title'] = 'Elections';
        return view('Modules\Elections\Views\index', $data);
    }

    public function add() {
        // checking roles and permissions
        $data['perm_id'] = check_role('20', 'ELEC', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        $data['edit'] = false;
        if($this->request->getMethod() == 'post') {
            // echo '<pre>';
            // print_r($_POST);
            // die();
            if($this->validate('elections')){
                if($this->electionModel->save($_POST)) {
                    $activityLog['user'] = $this->session->get('user_id');
                    $activityLog['description'] = 'Added a new election';
                    $this->activityLogModel->save($activityLog);
                    $this->session->setFlashdata('successMsg', 'Successfully started an election');
                    return redirect()->to(base_url('admin/elections'));
                } else {
                    $this->session->setFlashdata('failMsg', 'Failed to start an election');
                }
            } else {
                $data['value'] = $_POST;
                $data['errors'] = $this->validation->getErrors();
            }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'elections';
        $data['title'] = 'Add Elections';
        // return view('Modules\Elections\Views\form', $data);
        return view('Modules\Elections\Views\formTime', $data);
    }

    public function add2() {
        // checking roles and permissions
        $data['perm_id'] = check_role('20', 'ELEC', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        // $activeElec = intval($this->electionModel->where('status', 'a')->countAllResults(false));
        // if($activeElec >= 1) {
        //     $this->session->setFlashdata('activeElec', 'An election is still ongoing, please wait for it to finish before adding.');
        //     return redirect()->to(base_url('admin/elections'));
        // }

        $data['edit'] = false;
        if($this->request->getMethod() == 'post') {
            // echo '<pre>';
            // print_r($_POST);
            // die();
            if($this->validate('elections')){
                $post = $_POST;
                $post['status'] = 'a';
                if($this->electionModel->save($post)) {
                    $this->session->setFlashdata('successMsg', 'Successfully started an election');
                    return redirect()->to(base_url('admin/elections'));
                } else {
                    $this->session->setFlashdata('failMsg', 'Failed to start an election');
                }
            } else {
                $data['value'] = $_POST;
                $data['errors'] = $this->validation->getErrors();
            }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'elections';
        $data['title'] = 'Add Elections';
        return view('Modules\Elections\Views\form', $data);
    }

    public function deactivate($elecID) {
        $data = [
            'id' => $elecID,
            'status' => 'Finished',
        ];
        if($this->electionModel->save($data)) {
            $activityLog['user'] = $this->session->get('user_id');
            $activityLog['description'] = 'Finished an election';
            $this->activityLogModel->save($activityLog);
            $this->session->setFlashdata('successMsg', 'Successfully finished an election');
            return redirect()->to(base_url('admin/elections'));
        } else {
            $this->session->setFlashdata('failMsg', 'Failed to finished an election');
            return redirect()->to(base_url('admin/elections'));
        }
    }

    public function info($id) {
        // checking roles and permissions
        $data['perm_id'] = check_role('19', 'ELEC', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        $data['election'] = $this->electionModel->where(['id' => $id])->first();
        if(empty($data['election'])) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['positions'] =  $this->electionModel->electionPositions($id);
        $data['candidates'] = $this->electionModel->electionCandidates($id);
        $data['voteCount'] = $this->voteModel->where(['election_id' => $id])->countAllResults(false);
        $data['positionCount'] = $this->positionModel->where(['election_id' => $id])->countAllResults(false);
        $data['candidateCount'] = $this->candidateModel->where(['election_id' => $id])->countAllResults(false);
        // $data['perCandiCount'] = $this->voteDetailModel->joinVotes($id);
        $data['perCandiCount'] = $this->electionModel->voteCount($id);
        // echo '<pre>';
        // print_r($voteDetails);
        // die();
        
        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'elections';
        $data['title'] = 'Election Details';
        return view('Modules\Elections\Views\details', $data);
    }

    public function generatePDF($id) {
        // checking roles and permissions
        $data['perm_id'] = check_role('19', 'ELEC', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }

        $elecDetails = $this->electionModel->where(['id' => $id, 'status' => 'a'])->first();
        if(empty($elecDetails)) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $positions = $this->positionModel->where(['election_id' => $id])->findAll();
        $this->pdf->AddPage('P', 'Legal');
		$this->pdf->SetFont('Arial','B',12);
        $this->pdf->Cell(70,10,$elecDetails['title'].' Election Reports');
        $this->pdf->Cell(47);
        $start_date = date("M d, Y", strtotime($elecDetails['start_date']));
        $end_date = date("M d, Y", strtotime($elecDetails['end_date']));
        $this->pdf->Cell(20,10,'Vote Date: '.$start_date.' - '.$end_date,0,0,'L');
		$this->pdf->Ln();
		$this->pdf->SetFont('Arial', 'B' ,8);
        
		$this->pdf->SetX(55);
		$this->pdf->Cell(50,10,'Positions',1);
		$this->pdf->Cell(50,10,'Votes',1);
        $this->pdf->Ln();
        
        $data['voteCounts'] = $this->electionModel->voteCountPerPosition($elecDetails['id']);
        // echo '<pre>';
        // print_r($data['voteCounts']);
        // die();
		foreach($data['voteCounts'] as $voteCount) {
            $this->pdf->SetX(55);
			$this->pdf->SetFont('Arial', '' ,8);
            
			$this->pdf->Cell(50,8,$voteCount['name'],1);
			$this->pdf->Cell(50,8,$voteCount['count'],1);
			$this->pdf->Ln();
		}
        $this->pdf->SetFont('Arial', 'B' ,9);
        $this->pdf->Cell(70,10,'Voters:');
        $this->pdf->Ln();
        // voters names here
        // next yung candidates  
        $this->response->setHeader('Content-Type', 'application/pdf');
		$this->pdf->Output('D', $elecDetails['title'].' Reports.pdf'); 
    }

    public function pdf($id) {
        // checking roles and permissions
        $data['perm_id'] = check_role('19', 'ELEC', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }

        $data['elecDetails'] = $this->electionModel->where(['id' => $id, 'status !=' => 'Application'])->first();
        if(empty($data['elecDetails'])) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['positions'] = $this->positionModel->where(['election_id' => $id])->findAll();
        $data['candidates'] = $this->candidateModel->view($id);
        $data['voteCounts'] = $this->electionModel->voteCount($data['elecDetails']['id']);
        $data['voteDetails'] = $this->voteDetailModel->findAll();
        // echo '<pre>';
        // print_r($data['positions']);
        // print_r($data['candidates']);
        // print_r($data['voteDetails']);
        // die();
        $html = view('Modules\Elections\Views\pdf', $data);
        // $this->mpdf->SetHeader($elecDetails['title'].' Results|'.date('M d,Y').'|Page: {PAGENO}');
        $this->mpdf->SetHTMLHeader('
            <p style="border-bottom: 1px solid; width: 100%;">'.$data['elecDetails']['title'].' Results</p>
        ');
        $this->mpdf->SetFooter($data['elecDetails']['title']);
        $this->mpdf->WriteHTML($html);
        $this->response->setHeader('Content-Type', 'application/pdf');
        $this->mpdf->Output($data['elecDetails']['title'].' Results.pdf','I');
    }
}