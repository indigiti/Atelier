<section class="admin-head"><div><p class="kicker">Leads · Encrypted</p><h1>Consultation enquiries</h1><p>Private enquiries submitted from the storefront. Contact details are never included in public feeds.</p></div></section>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Received</th><th>Contact</th><th>Project brief</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php if(!$enquiries):?><tr><td colspan="5">No enquiries received yet.</td></tr><?php endif;?>
<?php foreach($enquiries as $row):?><tr>
<td><?=e(date('d M Y H:i',strtotime($row['created_at']??'now')))?></td>
<td><strong><?=e($row['name']??'')?></strong><p><?=e($row['phone']??'')?></p><small><?=e($row['email']??'')?></small></td>
<td style="max-width:380px;white-space:normal"><?=e($row['project']??'')?></td>
<td><?=e($row['status']??'NEW')?></td>
<td><form method="post" action="<?=e(url('/admin/enquiries/'.($row['guid']??'').'/status'))?>"><?=csrf_field()?><label><span class="sr-only">Status</span><select name="status"><?php foreach(['NEW','CONTACTED','CLOSED'] as $status):?><option value="<?=e($status)?>" <?=($row['status']??'')===$status?'selected':''?>><?=e($status)?></option><?php endforeach;?></select></label><button type="submit" class="btn outline">Save</button></form></td>
</tr><?php endforeach;?></tbody></table></div>
