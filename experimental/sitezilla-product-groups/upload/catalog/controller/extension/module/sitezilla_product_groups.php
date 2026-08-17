<?php
class ControllerExtensionModuleSitezillaProductGroups extends Controller {
    public function product($product_id=0) {
        $product_id=$product_id?(int)$product_id:(isset($this->request->get['product_id'])?(int)$this->request->get['product_id']:0);
        if(!$product_id)return '';
        $this->load->model('extension/module/sitezilla_product_groups');
        $group=$this->model_extension_module_sitezilla_product_groups->getGroupByProductId($product_id);
        if(!$group || count($group['items'])<2)return '';
        $this->load->model('tool/image');
        foreach($group['items'] as &$item){$image=$item['image_override']?$item['image_override']:$item['image'];$item['thumb']=$image?$this->model_tool_image->resize($image,60,60):'';$item['href']=$this->url->link('product/product','product_id='.(int)$item['product_id']);$item['active']=((int)$item['product_id']===$product_id);}unset($item);
        $data['group']=$group;$data['current_product_id']=$product_id;
        return $this->load->view('extension/module/sitezilla_product_groups.tpl',$data);
    }
}
