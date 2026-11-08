<section class="admin-head">
  <div>
    <p class="kicker">Discoverability · Technical SEO · AI Search</p>
    <h1>SEO Control Center</h1>
    <p>Live diagnostics from the public catalogue and editorial CMS. This is not a ranking or indexing report.</p>
  </div>
  <a class="btn outline" href="<?=e(url('/admin/seo/diagnostics.json'))?>">Export diagnostics JSON</a>
</section>
<?php $s=$report['summary']; ?>
<div class="admin-metrics seo-metrics">
  <div><strong><?=e((string)$s['products'])?></strong><span>Public products</span></div>
  <div><strong><?=e((string)$s['products_ready'])?></strong><span>Products with no local warnings</span></div>
  <div><strong><?=e((string)$s['product_issues'])?></strong><span>Catalog quality warnings</span></div>
  <div><strong><?=e((string)$s['published_articles'])?></strong><span>Published editorial entries</span></div>
</div>
<div class="admin-panels">
  <section class="admin-panel">
    <h2>Technical readiness</h2>
    <div class="release-list">
      <div><span>Sitemap XML</span><b class="<?=($report['sitemap_valid']===true)?'ok':'warn'?>"><?=($report['sitemap_valid']===true)?'Valid':($report['sitemap_valid']===false?'Invalid':'Parser unavailable')?></b></div>
      <div><span>Merchant feed XML</span><b class="<?=($report['merchant_valid']===true)?'ok':'warn'?>"><?=($report['merchant_valid']===true)?'Valid':($report['merchant_valid']===false?'Invalid':'Parser unavailable')?></b></div>
      <div><span>Duplicate public product titles</span><b class="<?=$s['duplicate_titles']===0?'ok':'warn'?>"><?=e((string)$s['duplicate_titles'])?></b></div>
      <div><span>Indexability</span><b class="<?=$report['site_indexable']?'ok':'warn'?>"><?=$report['site_indexable']?'Production rules active':'Staging: noindex'?></b></div>
    </div>
    <p class="admin-note">Valid XML and complete metadata do not establish Merchant Center eligibility or actual search indexing.</p>
  </section>
  <section class="admin-panel">
    <h2>External verification</h2>
    <div class="release-list">
      <?php foreach($report['external'] as $key=>$status): ?>
        <div><span><?=e(ucwords(str_replace('_',' ',$key)))?></span><b class="warn"><?=e($status)?></b></div>
      <?php endforeach; ?>
    </div>
    <p class="admin-note">Connect verified Google Search Console and Merchant Center properties after production deployment. AI citations and referrals must be measured separately.</p>
  </section>
</div>
<section class="admin-panel seo-source-panel">
  <h2>Crawlable sources</h2>
  <div class="seo-source-links">
    <a href="<?=e(url('/robots.txt'))?>" target="_blank" rel="noopener">robots.txt</a>
    <a href="<?=e(url('/sitemap.xml'))?>" target="_blank" rel="noopener">XML sitemap</a>
    <a href="<?=e(url('/feeds/google-merchant.xml'))?>" target="_blank" rel="noopener">Merchant feed</a>
    <a href="<?=e(url('/feeds/ai-products.json'))?>" target="_blank" rel="noopener">AI products</a>
    <a href="<?=e(url('/feeds/knowledge.json'))?>" target="_blank" rel="noopener">Entity graph</a>
    <a href="<?=e(url('/llms.txt'))?>" target="_blank" rel="noopener">llms.txt</a>
  </div>
</section>
<?php if($report['cms_warnings']): ?>
<section class="admin-panel">
  <h2>Editorial review needed</h2>
  <ul><?php foreach($report['cms_warnings'] as $warning):?><li><?=e($warning)?></li><?php endforeach;?></ul>
  <a href="<?=e(url('/admin/cms'))?>">Review CMS content →</a>
</section>
<?php endif; ?>
<section class="admin-head seo-product-head"><div>
  <h2>Product-level SEO and media audit</h2>
  <p>Review local warnings before Google Merchant submission and production indexing.</p>
</div></section>
<div class="admin-table-wrap">
  <table class="admin-table">
    <thead><tr><th>Product</th><th>Warnings</th><th>Recommended action</th><th></th></tr></thead>
    <tbody>
    <?php foreach($report['products'] as $row): ?>
      <tr>
        <td><strong><?=e($row['name'])?></strong><br><small><?=e($row['slug'])?></small></td>
        <td><span class="seo-count <?=$row['count']?'seo-count-warn':'seo-count-ok'?>"><?=e((string)$row['count'])?></span></td>
        <td><?php if(!$row['issues']):?><span class="seo-good">Local checks complete</span><?php else:?><ul class="seo-issue-list"><?php foreach($row['issues'] as $issue):?><li><?=e($issue)?></li><?php endforeach;?></ul><?php endif;?></td>
        <td><a href="<?=e(url('/admin/product/'.$row['guid']))?>">Edit →</a></td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table>
</div>