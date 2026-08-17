<?php
class ControllerExtensionModuleSitezillaProductGroups extends Controller {
    private $error = array();
    public function index() {
        $this->load->language('extension/module/sitezilla_product_groups');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/sitezilla_product_groups');
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            if (isset($this->request->post['save_group'])) { $this->model_extension_module_sitezilla_product_groups->saveGroup($this->request->post); $this->session->data['success']=$this->language->get('text_saved'); }
            if (isset($this->request->post['save_profile'])) { $this->model_extension_module_sitezilla_product_groups->saveProfile($this->request->post); $this->session->data['success']=$this->language->get('text_saved'); }
            $this->response->redirect($this->url->link('extension/module/sitezilla_product_groups','token='.$this->session->data['token'],true));
        }
        $data['heading_title']=$this->language->get('heading_title');$data['token']=$this->session->data['token'];$data['action']=$this->url->link('extension/module/sitezilla_product_groups','token='.$data['token'],true);$data['import_url']=$this->url->link('extension/module/sitezilla_product_groups/importLegacy','token='.$data['token'],true);$data['groups']=$this->model_extension_module_sitezilla_product_groups->getGroups();$data['profiles']=$this->model_extension_module_sitezilla_product_groups->getProfiles();$data['success']=isset($this->session->data['success'])?$this->session->data['success']:'';unset($this->session->data['success']);$data['header']=$this->load->controller('common/header');$data['column_left']=$this->load->controller('common/column_left');$data['footer']=$this->load->controller('common/footer');$this->response->setOutput($this->load->view('extension/module/sitezilla_product_groups.tpl',$data));
    }
    public function install(){ $this->load->model('extension/module/sitezilla_product_groups');$this->model_extension_module_sitezilla_product_groups->install();$this->load->model('user/user_group');$this->model_user_user_group->addPermission($this->user->getGroupId(),'access','extension/module/sitezilla_product_groups');$this->model_user_user_group->addPermission($this->user->getGroupId(),'modify','extension/module/sitezilla_product_groups'); }
    public function importLegacy(){ $this->load->language('extension/module/sitezilla_product_groups');$this->load->model('extension/module/sitezilla_product_groups');if(!$this->user->hasPermission('modify','extension/module/sitezilla_product_groups')){$this->session->data['success']=$this->language->get('error_permission');}else{$result=$this->model_extension_module_sitezilla_product_groups->importLegacyHpm();$this->session->data['success']=!$result['available']?$this->language->get('text_legacy_missing'):sprintf($this->language->get('text_legacy_imported'),$result['groups'],$result['items']);}$this->response->redirect($this->url->link('extension/module/sitezilla_product_groups','token='.$this->session->data['token'],true)); }
    private function validate(){ if(!$this->user->hasPermission('modify','extension/module/sitezilla_product_groups'))$this->error['warning']=$this->language->get('error_permission');return !$this->error; }
}
