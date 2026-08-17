<?php
class ModelExtensionModuleSitezillaProductGroups extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sz_product_group` (`group_id` INT(11) NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL DEFAULT '', `main_product_id` INT(11) NOT NULL DEFAULT '0', `profile_id` INT(11) NOT NULL DEFAULT '0', `legacy_parent_id` INT(11) NOT NULL DEFAULT '0', `status` TINYINT(1) NOT NULL DEFAULT '1', `date_added` DATETIME NOT NULL, `date_modified` DATETIME NOT NULL, PRIMARY KEY (`group_id`), UNIQUE KEY `legacy_parent_id` (`legacy_parent_id`), KEY `main_product_id` (`main_product_id`), KEY `profile_id` (`profile_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sz_product_group_item` (`group_id` INT(11) NOT NULL, `product_id` INT(11) NOT NULL, `sort_order` INT(11) NOT NULL DEFAULT '0', `image_override` VARCHAR(255) NOT NULL DEFAULT '', PRIMARY KEY (`group_id`,`product_id`), KEY `product_id` (`product_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sz_product_group_profile` (`profile_id` INT(11) NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL DEFAULT '', `dimensions` MEDIUMTEXT NOT NULL, `status` TINYINT(1) NOT NULL DEFAULT '1', PRIMARY KEY (`profile_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sz_product_group_to_store` (`group_id` INT(11) NOT NULL, `store_id` INT(11) NOT NULL, PRIMARY KEY (`group_id`,`store_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }
    public function saveGroup($data) {
        $group_id=!empty($data['group_id'])?(int)$data['group_id']:0; $name=$this->db->escape(isset($data['name'])?$data['name']:''); $main_product_id=(int)$data['main_product_id']; $profile_id=(int)$data['profile_id']; $status=!empty($data['status'])?1:0;
        if($group_id){$this->db->query("UPDATE `".DB_PREFIX."sz_product_group` SET name='".$name."', main_product_id='".$main_product_id."', profile_id='".$profile_id."', status='".$status."', date_modified=NOW() WHERE group_id='".$group_id."'");}else{$this->db->query("INSERT INTO `".DB_PREFIX."sz_product_group` SET name='".$name."', main_product_id='".$main_product_id."', profile_id='".$profile_id."', status='".$status."', date_added=NOW(), date_modified=NOW()");$group_id=$this->db->getLastId();}
        $this->db->query("DELETE FROM `".DB_PREFIX."sz_product_group_item` WHERE group_id='".$group_id."'");
        if(!empty($data['items']))foreach($data['items'] as $item){$product_id=(int)$item['product_id'];if(!$product_id)continue;$sort_order=isset($item['sort_order'])?(int)$item['sort_order']:0;$image=$this->db->escape(isset($item['image_override'])?$item['image_override']:'');$this->db->query("INSERT IGNORE INTO `".DB_PREFIX."sz_product_group_item` SET group_id='".$group_id."', product_id='".$product_id."', sort_order='".$sort_order."', image_override='".$image."'");}
        return $group_id;
    }
    public function saveProfile($data) {
        $profile_id=!empty($data['profile_id'])?(int)$data['profile_id']:0;$name=$this->db->escape(isset($data['name'])?$data['name']:'');$dimensions=$this->db->escape(json_encode(isset($data['dimensions'])?array_values($data['dimensions']):array()));$status=!empty($data['status'])?1:0;
        if($profile_id){$this->db->query("UPDATE `".DB_PREFIX."sz_product_group_profile` SET name='".$name."', dimensions='".$dimensions."', status='".$status."' WHERE profile_id='".$profile_id."'");return $profile_id;}
        $this->db->query("INSERT INTO `".DB_PREFIX."sz_product_group_profile` SET name='".$name."', dimensions='".$dimensions."', status='".$status."'");return $this->db->getLastId();
    }
    public function getGroups(){return $this->db->query("SELECT g.*,pd.name AS main_product_name FROM `".DB_PREFIX."sz_product_group` g LEFT JOIN `".DB_PREFIX."product_description` pd ON(pd.product_id=g.main_product_id AND pd.language_id='".(int)$this->config->get('config_language_id')."') ORDER BY g.group_id DESC")->rows;}
    public function getProfiles(){return $this->db->query("SELECT * FROM `".DB_PREFIX."sz_product_group_profile` ORDER BY name ASC")->rows;}
    public function importLegacyHpm(){
        $exists=$this->db->query("SHOW TABLES LIKE '".$this->db->escape(DB_PREFIX."hpmodel_links")."'");if(!$exists->num_rows)return array('groups'=>0,'items'=>0,'available'=>false);
        $rows=$this->db->query("SELECT parent_id,product_id,sort,image,type_id FROM `".DB_PREFIX."hpmodel_links` ORDER BY parent_id,sort,product_id")->rows;$by_parent=array();foreach($rows as $row)$by_parent[(int)$row['parent_id']][]=$row;$groups=0;$items=0;
        foreach($by_parent as $parent_id=>$legacy_items){$found=$this->db->query("SELECT group_id FROM `".DB_PREFIX."sz_product_group` WHERE legacy_parent_id='".(int)$parent_id."' LIMIT 1");if($found->num_rows)continue;$this->db->query("INSERT INTO `".DB_PREFIX."sz_product_group` SET name='Legacy HPM #".(int)$parent_id."', main_product_id='".(int)$parent_id."', profile_id='0', legacy_parent_id='".(int)$parent_id."', status='1', date_added=NOW(), date_modified=NOW()");$group_id=$this->db->getLastId();foreach($legacy_items as $item){$this->db->query("INSERT IGNORE INTO `".DB_PREFIX."sz_product_group_item` SET group_id='".(int)$group_id."', product_id='".(int)$item['product_id']."', sort_order='".(int)$item['sort']."', image_override='".$this->db->escape($item['image'])."'");$items++;}$groups++;}
        return array('groups'=>$groups,'items'=>$items,'available'=>true);
    }
}
