<?php
namespace Modules\Permissions\Controllers;

use App\Controllers\BaseController;
use Modules\Permissions\Models as Models;
use Modules\Roles\Models as RoleModels;
use Modules\Roles\Controllers as RoleControl;

class Permissions extends BaseController
{
    public function __construct() {
        $this->rolePermissionModel = new Models\RolePermissionModel();
        $this->permissionModel = new Models\PermissionModel();
        $this->roleModel = new RoleModels\RoleModel();
        $this->roleController = new RoleControl\Roles();
    }

    public function index() {
        // checking roles and permissions
        $data['perm_id'] = check_role('9', 'PERM', $this->session->get('role'));
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        $data['roles'] = $this->roleModel->findAll();
        $data['role_permissions'] = $this->rolePermissionModel->checkRole();
        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'permissions';
        $data['title'] = 'Permissions';
        return view('Modules\Permissions\Views\index', $data);
    }

    public function add($role_id) {
        $data['perm_id'] = check_role('10', 'PERM', $this->session->get('role'));
        // echo '<pre>';
        // print_r($data['perm_id']);
        // die();
        if(!$data['perm_id']['perm_access']) {
            $this->session->setFlashdata('sweetalertfail', true);
            return redirect()->to(base_url());
        }
        $data['rolePermission'] = $data['perm_id']['rolePermission'];

        $data['permissions'] = $this->permissionModel->findAll();
        $data['role_perms'] = $this->rolePermissionModel->where('role_id', $role_id)->get()->getResultArray();
        $data['selectedRole'] = $this->roleModel->where('id', $role_id)->first();
        $data['role_id'] = $role_id;
        
        if($this->request->getMethod() == 'post') {
            if($this->validate('role_perm')) {
                $this->rolePermissionModel->deleteAll($role_id);
                foreach($_POST['perm_id'] as $perms) {
                    $permissions = $this->permissionModel->where('id', $perms)->first();
                    $value = [
                        'role_id' => $this->request->getVar('role_id'),
                        'perm_id' => $perms,
                        'perm_mod' => $permissions['perm_mod'],
                    ];
                    if($this->rolePermissionModel->insert($value)) {
                    } else {
                        $this->session->setFlashData('failMsg', 'There is an error on adding role. Please try again.');
                        return redirect()->back()->withInput();
                    }
                }
                $this->session->setFlashData('successMsg', 'Edit permission successful');
                return redirect()->to(base_url('admin/permissions'));
            } else {
                $data['value'] = $_POST;
                $data['errors'] = $this->validation->getErrors();
            }
        }

        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'permissions';
        $data['title'] = 'Permissions';
        return view('Modules\Permissions\Views\form2', $data);
    }

    /*public function add() {
        $data['edit'] = false;
        $data['permissions'] = $this->permissionModel->findAll();
        $data['roles'] = $this->roleModel->findAll();

        if($this->request->getMethod() == 'post') {
            if($this->validate('role_perm')){
                // echo '<pre>';
                // print_r($_POST);
                // die();
                foreach($_POST['perm_id'] as $perms) {
                    $permissions = $this->permissionModel->where('id', $perms)->first();
                    $value = [
                        'role_id' => $this->request->getVar('role_id'),
                        'perm_id' => $perms,
                        'perm_mod' => $permissions['perm_mod'],
                    ];
                    if($this->rolePermissionModel->insert($value)) {
                    } else {
                        $this->session->setFlashData('failMsg', 'There is an error on adding role. Please try again.');
                        return redirect()->back()->withInput();
                    }
                }
                $this->session->setFlashData('successMsg', 'Adding permission successful');
                return redirect()->to(base_url('admin/permissions'));
            } else {
                $data['value'] = $_POST;
                $data['errors'] = $this->validation->getErrors();
            }
        }
        $data['user_details'] = user_details($this->session->get('user_id'));
        $data['active'] = 'permissions';
        $data['title'] = 'Permissions';
        return view('Modules\Permissions\Views\form', $data);
    }*/
}