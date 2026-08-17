<div class="sz-product-groups" data-group-id="<?php echo (int)$group['group_id']; ?>">
  <?php if($group['name']){?><div class="sz-product-groups__title"><?php echo $group['name'];?></div><?php }?>
  <div class="sz-product-groups__items">
    <?php foreach($group['items'] as $item){?>
      <a class="sz-product-groups__item<?php echo $item['active']?' is-active':'';?>" href="<?php echo $item['href'];?>" title="<?php echo $item['name'];?>">
        <?php if($item['thumb']){?><img src="<?php echo $item['thumb'];?>" alt="<?php echo $item['name'];?>"><?php }?>
        <span><?php echo $item['model']?$item['model']:$item['name'];?></span>
      </a>
    <?php }?>
  </div>
</div>
<style>.sz-product-groups{margin:15px 0}.sz-product-groups__title{font-weight:600;margin-bottom:8px}.sz-product-groups__items{display:flex;flex-wrap:wrap;gap:8px}.sz-product-groups__item{display:flex;align-items:center;gap:6px;border:1px solid #ddd;border-radius:6px;padding:5px 8px;text-decoration:none}.sz-product-groups__item.is-active{border-width:2px}.sz-product-groups__item img{width:36px;height:36px;object-fit:contain}</style>
