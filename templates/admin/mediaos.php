<section class="admin-head"><div><p class="kicker">MediaOS</p><h1>Product media roles</h1><p>Assign semantic roles, accessible alt text, focal points and rights status. Staging-reference media remains clearly separated from production-approved assets.</p></div></section>
<div class="admin-note"><strong>Production gate:</strong> mark only owned, licensed or generated assets as approved after visual/rights review. Hero and front roles are required for Sacred Spaces.</div>
<div class="mediaos-list">
<?php foreach($products as $p): $entry=mediaos_product((string)$p['guid']); $complete=mediaos_completeness($p); ?>
<details class="admin-card mediaos-product">
  <summary><span><strong><?=e($p['name'])?></strong><small><?=e($p['sku'])?> · <?=e(category_label($p['category']))?></small></span><span><?=count(mediaos_gallery($p))?> media · <?=$complete['production_ready']?'production ready':'staging'?></span></summary>
  <form method="post" action="<?=e(url('/admin/mediaos/save'))?>" class="mediaos-form">
    <?=csrf_field()?> <input type="hidden" name="product_guid" value="<?=e($p['guid'])?>">
    <div class="mediaos-role-grid">
    <?php foreach(mediaos_roles() as $role=>$label): $r=$entry['roles'][$role]??[]; ?>
      <fieldset>
        <legend><?=e($label)?></legend>
        <label>File<select name="file[<?=e($role)?>]"><option value="">— not assigned —</option><?php foreach($files as $m):?><option value="<?=e($m['name'])?>" <?=($r['file']??'')===$m['name']?'selected':''?>><?=e($m['name'])?></option><?php endforeach;?></select></label>
        <label>Alt text<input name="alt[<?=e($role)?>]" value="<?=e($r['alt']??'')?>" maxlength="180"></label>
        <div class="form-two"><label>Status<select name="status[<?=e($role)?>]"><?php foreach(['staging','approved','retired'] as $v):?><option <?=$v===($r['status']??'staging')?'selected':''?>><?=e($v)?></option><?php endforeach;?></select></label><label>Rights<select name="rights[<?=e($role)?>]"><?php foreach(['unverified','owned','licensed','generated'] as $v):?><option <?=$v===($r['rights']??'unverified')?'selected':''?>><?=e($v)?></option><?php endforeach;?></select></label></div>
        <div class="form-three"><label>Focal X<input type="number" min="0" max="100" name="focal_x[<?=e($role)?>]" value="<?=e((string)($r['focal_x']??50))?>"></label><label>Focal Y<input type="number" min="0" max="100" name="focal_y[<?=e($role)?>]" value="<?=e((string)($r['focal_y']??50))?>"></label><label>Sort<input type="number" min="1" max="99" name="sort[<?=e($role)?>]" value="<?=e((string)($r['sort']??50))?>"></label></div>
      </fieldset>
    <?php endforeach; ?>
    </div>
    <button class="btn dark">Save media roles</button>
  </form>
</details>
<?php endforeach; ?>
</div>
