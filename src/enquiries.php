<?php
declare(strict_types=1);

/** Encrypted, audit-minimised public consultation leads. */
function enquiry_store_path(): string { return data_root().'/private-domain/commerce/enquiries.json'; }
function enquiries_all(): array { return confidential_json_read(enquiry_store_path(),'atelierwood:enquiries:v1',[]); }
function enquiry_submit(array $input): array {
    // Explicit honeypot: humans never fill this optional hidden field.
    if(trim((string)($input['website']??''))!=='')return [true,'accepted'];
    $name=trim((string)($input['name']??''));
    $phone=preg_replace('/[^0-9+]/','',(string)($input['phone']??''));
    $project=trim((string)($input['project']??''));
    $email=text_lower(trim((string)($input['email']??'')));
    if(text_len($name)<2||text_len($name)>120)return [false,'Enter your name (2–120 characters).'];
    if(strlen(preg_replace('/\\D/','',$phone))<10||strlen($phone)>16)return [false,'Enter a valid phone number.'];
    if(text_len($project)<10||text_len($project)>3000)return [false,'Describe your project (10–3000 characters).'];
    if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL))return [false,'Enter a valid email address.'];
    $rate=api_rate_limit('consultation-enquiry',5,600);
    if(!$rate['allowed'])return [false,'Too many enquiries. Try again later.'];
    if(confidential_key()===null)return [false,'Enquiry service is not configured.'];
    $entry=['guid'=>uuidv7(),'name'=>$name,'phone'=>$phone,'email'=>$email,
      'project'=>$project,'status'=>'NEW','created_at'=>gmdate('c'),'updated_at'=>gmdate('c')];
    confidential_json_mutate(enquiry_store_path(),'atelierwood:enquiries:v1',
      function(array $rows)use($entry){$rows[]=$entry;return$rows;},'enquiry.create');
    audit_event('enquiry.create',['enquiry_guid'=>$entry['guid']]);
    return [true,$entry['guid']];
}
function enquiry_set_status(string $guid,string $status): void {
    require_admin();
    if(!in_array($status,['NEW','CONTACTED','CLOSED'],true))throw new RuntimeException('Invalid enquiry status.');
    $found=false;
    confidential_json_mutate(enquiry_store_path(),'atelierwood:enquiries:v1',
      function(array $rows)use($guid,$status,&$found){
        foreach($rows as &$row)if(($row['guid']??'')===$guid){$row['status']=$status;$row['updated_at']=gmdate('c');$found=true;break;}
        return $rows;
      },'enquiry.status');
    if(!$found)throw new RuntimeException('Enquiry not found.');
    audit_event('enquiry.status',['enquiry_guid'=>$guid,'status'=>$status]);
}
