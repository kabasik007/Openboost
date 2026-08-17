<?php
class ModelExtensionModuleSitezillaProductGroups extends Model {
    public function getGroupByProductId($product_id) {
        $query=$this->db->query("SELECT g.* FROM `".DB_PREFIX."sz_product_group_item` gi INNER JOIN `".DB_PREFIX."sz_product_group` g ON(g.group_id=gi.group_id) WHERE gi.product_id='".(int)$product_id."' AND g.status='1' LIMIT 1");
        if(!$query->num_rows)return array();
        $group=$query->row;
        $group['items']=$this->db->query("SELECT gi.product_id,gi.sort_order,gi.image_override,p.model,p.sku,p.image,p.quantity,p.status,pd.name FROM `".DB_PREFIX."sz_product_group_item` gi INNER JOIN `".DB_PREFIX."product` p ON(p.product_id=gi.product_id) INNER JOIN `".DB_PREFIX."product_description` pd ON(pd.product_id=p.product_id AND pd.language_id='".(int)$this->config->get('config_language_id')."') WHERE gi.group_id='".(int)$group['group_id']."' AND p.status='1' ORDER BY gi.sort_order,gi.product_id")->rows;
        $group['dimensions']=array();
        if((int)$group['profile_id']){$profile=$this->db->query("SELECT dimensions FROM `".DB_PREFIX."sz_product_group_profile` WHERE profile_id='".(int)$group['profile_id']."' AND status='1' LIMIT 1");if($profile->num_rows){$decoded=json_decode($profile->row['dimensions'],true);if(is_array($decoded))$group['dimensions']=$decoded;}}
        return $group;
    }
}
