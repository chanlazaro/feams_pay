<?php
namespace Modules\Voting\Controllers;

use App\Controllers\BaseController;
use Modules\Voting\Models as Models;
use Modules\Elections\Models as Election;

class Voting2 extends BaseController
{
    public function __construct() {
        $this->electionModel = new Election\ElectionModel();
        $this->positionModel = new Election\PositionModel();
        $this->candidateModel = new Election\CandidateModel();
        $this->voteModel = new Models\VoteModel();
        $this->voteDetailModel = new Models\VoteDetailModel();
    }
    
    public function index() {
        // checking roles and permissions
        $data['perm_id'] = check_role('', '', $this->session->get('role'));
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        $data['elections'] = $this->electionModel->where('status', 'a')->findAll();

        $data['positions'] = $this->positionModel->findAll();
        if($this->request->getMethod() == 'post') {
            // save first to votes table
            $voter = [
                'election_id' => $this->request->getVar('election_id'),
                'voters_id' => $this->session->get('user_id'),
            ];
            if($this->voteModel->save($voter)) {
                $voterData = $this->voteModel->where(['election_id' => $voter['election_id'], 'voters_id' => $this->session->get('user_id')])->first();
                $data['electionPosition'] = $this->positionModel->where('election_id', $voter['election_id'])->findAll();
                // pagtapos mag save ng voter detail, isasave na votes
                foreach($data['electionPosition'] as $position) {
                    if($this->request->getVar($position['id']) != 0) {
                        $voteData = [
                            'votes_id' => $voterData['id'],
                            'position_id' => $position['id'],
                            'candidate_id' => $this->request->getVar($position['id']),
                        ];
                    } else {
                        $voteData = [
                            'votes_id' => $voterData['id'],
                            'position_id' => $position['id'],
                            'candidate_id' => 0,
                        ];
                    }
                    $this->voteDetailModel->save($voteData);
                }
                $this->session->setFlashdata('firstVoter', 'Vote casted.');
                // return redirect()->to(base_url('admin/elections'));
                return redirect()->back();
            } else {
                $this->session->setFlashdata('failMsg', 'Vote not casted, please try again.');
                // return redirect()->to(base_url('admin/elections'));
                return redirect()->back();
            }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'voting';
        $data['title'] = 'Voting';
        return view('Modules\Voting\Views\index2', $data);
    }

    public function other($id) {
        $data['election'] = $this->electionModel->where(['status' => 'a', 'id' => $id])->first();
        $data['positions'] = $this->positionModel->where('election_id', $id)->findAll();
        $data['candidates'] = $this->candidateModel->view($id);
        // echo '<pre>';
        // print_r($data);
        $voted = $this->voteModel->where(['election_id' => $id, 'voters_id' => $this->session->get('user_id')])->first();

        if(!empty($voted)) {
            echo 'You have voted for this election';
            $data['voteDetails'] = $this->voteDetailModel->where(['votes_id' => $voted['id']])->findAll();
            $data['votes'] = $this->voteDetailModel->candidateDetails($id,$this->session->get('user_id'));
            // echo '<pre>';
            // print_r($data['votes']);
            return view('Modules\Voting\Views\results2', $data);
        }
        if(empty($data['election'])) {
            echo 'Please select an election';
        }
        return view('Modules\Voting\Views\votingSection', $data);
    }
}