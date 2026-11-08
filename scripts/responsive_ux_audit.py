from playwright.sync_api import sync_playwright
import json, os, time
base='http://127.0.0.1:8124'
outdir='/mnt/data/audit_v0981_final'
os.makedirs(outdir,exist_ok=True)
viewports=[('desktop1440',1440,1000),('desktop1280',1280,900),('tablet1024',1024,900),('tablet820',820,900),('tablet768',768,900),('mobile430',430,900),('mobile390',390,844),('mobile360',360,800)]
pages=[('home','/'),('shop','/shop'),('product','/product/nordic-ladder-wall-shelf'),('sacred','/collections/sacred-spaces')]
results=[]
with sync_playwright() as p:
    browser=p.chromium.launch(headless=True,executable_path='/usr/bin/chromium',args=['--no-sandbox'])
    for vp,w,h in viewports:
        for name,path in pages:
            page=browser.new_page(viewport={'width':w,'height':h}, device_scale_factor=1)
            errors=[]
            page.on('console', lambda msg, arr=errors: arr.append('console:'+msg.text) if msg.type=='error' else None)
            page.on('pageerror', lambda exc, arr=errors: arr.append('pageerror:'+str(exc)))
            try:
                r=page.goto(base+path,wait_until='networkidle',timeout=30000)
                status=r.status if r else 0
                page.evaluate("document.querySelectorAll('.reveal').forEach(e=>e.classList.add('in'))")
                dims=page.evaluate("({sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,body:document.body.scrollWidth})")
                broken=page.evaluate("Array.from(document.images).filter(i=>i.complete&&i.naturalWidth===0).map(i=>i.src)")
                visible_search=page.locator('input[type=search]:visible').count()>0
                # first-viewport PDP conversion visibility
                pdp_summary=True
                if name=='product':
                    pdp_summary=page.locator('text=Nordic Ladder Wall Shelf').first.is_visible() and page.locator('text=₹').first.is_visible()
                shot=os.path.join(outdir,f'{name}_{vp}.png')
                page.screenshot(path=shot,full_page=False)
                results.append({'viewport':vp,'width':w,'page':name,'status':status,'overflow':max(dims['sw'],dims['body'])>dims['cw']+1,'scrollWidth':dims['sw'],'clientWidth':dims['cw'],'broken_images':len(broken),'search_visible':visible_search,'pdp_summary_visible':pdp_summary,'errors':errors})
            except Exception as e:
                results.append({'viewport':vp,'width':w,'page':name,'status':0,'overflow':None,'broken_images':None,'search_visible':False,'pdp_summary_visible':False,'errors':[str(e)]})
            page.close()
    # Interaction tests at representative widths
    for label,w,h in [('desktop',1440,900),('tablet',820,900),('mobile',390,844)]:
        page=browser.new_page(viewport={'width':w,'height':h})
        errs=[]; page.on('pageerror',lambda e,arr=errs: arr.append(str(e)))
        page.goto(base+'/shop',wait_until='networkidle')
        data={'viewport':label,'interaction':True}
        try:
            if label=='desktop':
                btn=page.locator('[data-mega-toggle]'); btn.click(); data['mega_open']=page.locator('[data-mega]').is_visible(); page.keyboard.press('Escape'); data['mega_escape_closes']=not page.locator('[data-mega]').is_visible()
            else:
                page.locator('[data-menu-toggle]').click(); data['menu_open']=page.locator('[data-menu]').is_visible(); page.locator('[data-menu-toggle]').click(); data['menu_close']=not page.locator('[data-menu]').is_visible()
                page.locator('[data-filter-toggle]').click(); data['filter_open']=page.locator('[data-filter-sheet]').is_visible(); page.locator('[data-filter-sheet] [data-sheet-close]').last.click(); data['filter_close']=not page.locator('[data-filter-sheet]').is_visible()
                page.locator('[data-sort-toggle]').click(); data['sort_open']=page.locator('[data-sort-sheet]').is_visible(); page.keyboard.press('Escape'); data['sort_escape_closes']=not page.locator('[data-sort-sheet]').is_visible()
            data['errors']=errs
        except Exception as e: data['errors']=errs+[str(e)]
        results.append(data); page.close()
    # PDP pincode + mobile sticky
    page=browser.new_page(viewport={'width':390,'height':844})
    page.goto(base+'/product/nordic-ladder-wall-shelf',wait_until='networkidle')
    pd={'viewport':'mobile','pdp_interaction':True}
    try:
        pd['sticky_purchase']=page.locator('.mobile-purchase-bar').is_visible()
        page.locator('#pdp-pin').fill('411001'); page.locator('[data-pincode-check]').click(); page.wait_for_timeout(500)
        pd['pincode_result']=page.locator('[data-pincode-result]').inner_text(); pd['pincode_success']='working days' in pd['pincode_result'].lower() or 'installation' in pd['pincode_result'].lower() or 'delivery' in pd['pincode_result'].lower()
    except Exception as e: pd['error']=str(e)
    results.append(pd);page.close()
    browser.close()
open('/mnt/data/atelierwood_staging_v0.9.8.1/RESPONSIVE_UX_RESULTS.json','w').write(json.dumps(results,indent=2))
fail=[]
for r in results:
    if r.get('interaction') or r.get('pdp_interaction'): continue
    if r.get('status')!=200 or r.get('overflow') or r.get('broken_images') or not r.get('search_visible') or r.get('errors') or (r.get('page')=='product' and not r.get('pdp_summary_visible')): fail.append(r)
print('screens',sum(1 for r in results if not r.get('interaction') and not r.get('pdp_interaction')),'failures',len(fail))
if fail:
    print(json.dumps(fail,indent=2)); raise SystemExit(1)
print('PASS')
