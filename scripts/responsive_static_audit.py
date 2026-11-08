from playwright.sync_api import sync_playwright
import json, os
from pathlib import Path
indir=Path('/mnt/data/v0981_static')
outdir=Path('/mnt/data/audit_v0981_final')
outdir.mkdir(exist_ok=True)
viewports=[('desktop1440',1440,1000),('desktop1280',1280,900),('tablet1024',1024,900),('tablet820',820,900),('tablet768',768,900),('mobile430',430,900),('mobile390',390,844),('mobile360',360,800)]
pages=['home','shop','product','sacred']
results=[]
with sync_playwright() as p:
    browser=p.chromium.launch(headless=True,executable_path='/usr/bin/chromium',args=['--no-sandbox','--allow-file-access-from-files'])
    for vp,w,h in viewports:
        for name in pages:
            page=browser.new_page(viewport={'width':w,'height':h},device_scale_factor=1)
            errors=[]
            page.on('console',lambda m,arr=errors: arr.append('console:'+m.text) if m.type=='error' else None)
            page.on('pageerror',lambda e,arr=errors: arr.append('pageerror:'+str(e)))
            try:
                r=page.goto((indir/f'{name}.html').as_uri(),wait_until='load',timeout=30000)
                page.wait_for_timeout(250)
                page.evaluate("document.querySelectorAll('.reveal').forEach(e=>e.classList.add('in'))")
                dims=page.evaluate("({sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,bw:document.body.scrollWidth})")
                broken=page.evaluate("Array.from(document.images).filter(i=>i.complete&&i.naturalWidth===0).map(i=>i.src)")
                searches=page.locator('input[type=search]:visible').count()
                pdp=True
                if name=='product':
                    # Ensure summary appears in first viewport at <=1023, and desktop summary otherwise.
                    loc=page.locator('.product-mobile-summary:visible, .desktop-product-summary:visible').first
                    pdp=loc.is_visible() if loc.count() else False
                    if pdp:
                        box=loc.bounding_box(); pdp=bool(box and box['y'] < h*0.55)
                page.screenshot(path=str(outdir/f'{name}_{vp}.png'),full_page=False)
                results.append({'viewport':vp,'width':w,'page':name,'overflow':max(dims['sw'],dims['bw'])>dims['cw']+1,'scrollWidth':dims['sw'],'clientWidth':dims['cw'],'broken_images':len(broken),'search_visible':searches>0,'pdp_summary_first_view':pdp,'errors':errors})
            except Exception as e:
                results.append({'viewport':vp,'width':w,'page':name,'overflow':None,'broken_images':None,'search_visible':False,'pdp_summary_first_view':False,'errors':[str(e)]})
            page.close()
    # interactions
    for label,w,h in [('desktop',1440,900),('tablet',820,900),('mobile',390,844)]:
        page=browser.new_page(viewport={'width':w,'height':h})
        page.goto((indir/'shop.html').as_uri(),wait_until='load');page.wait_for_timeout(100)
        d={'interaction':True,'viewport':label,'errors':[]}
        try:
            if label=='desktop':
                btn=page.locator('[data-mega-toggle]');btn.click();d['mega_open']=page.locator('[data-mega]').is_visible();page.keyboard.press('Escape');d['mega_escape_closes']=not page.locator('[data-mega]').is_visible()
            else:
                btn=page.locator('[data-menu-toggle]');btn.click();d['menu_open']=page.locator('[data-menu]').is_visible();btn.click();d['menu_close']=not page.locator('[data-menu]').is_visible()
                page.locator('[data-filter-toggle]').click();d['filter_open']=page.locator('[data-filter-sheet]').is_visible();page.locator('[data-filter-sheet] [data-sheet-close]').last.click();d['filter_close']=not page.locator('[data-filter-sheet]').is_visible()
                page.locator('[data-sort-toggle]').click();d['sort_open']=page.locator('[data-sort-sheet]').is_visible();page.keyboard.press('Escape');d['sort_escape_closes']=not page.locator('[data-sort-sheet]').is_visible()
        except Exception as e:d['errors']=[str(e)]
        results.append(d);page.close()
    page=browser.new_page(viewport={'width':390,'height':844});page.goto((indir/'product.html').as_uri(),wait_until='load');page.wait_for_timeout(100)
    results.append({'pdp_interaction':True,'viewport':'mobile','sticky_purchase':page.locator('.mobile-purchase-bar').is_visible(),'configure_target':page.locator('#configure').count()==1})
    page.close();browser.close()

Path('/mnt/data/atelierwood_staging_v0.9.8.1/RESPONSIVE_UX_RESULTS.json').write_text(json.dumps(results,indent=2))
fail=[]
for r in results:
    if r.get('interaction') or r.get('pdp_interaction'): continue
    if r.get('overflow') or r.get('broken_images') or not r.get('search_visible') or r.get('errors') or (r.get('page')=='product' and not r.get('pdp_summary_first_view')): fail.append(r)
interaction_fail=[]
for r in results:
    if r.get('interaction'):
        if r.get('errors') or any(v is False for k,v in r.items() if k not in ('interaction','viewport','errors') and isinstance(v,bool)):interaction_fail.append(r)
    if r.get('pdp_interaction') and (not r.get('sticky_purchase') or not r.get('configure_target')):interaction_fail.append(r)
print('screens',len(viewports)*len(pages),'screen_failures',len(fail),'interaction_failures',len(interaction_fail))
if fail: print('SCREEN FAILS',json.dumps(fail,indent=2))
if interaction_fail: print('INTERACTION FAILS',json.dumps(interaction_fail,indent=2))
if fail or interaction_fail: raise SystemExit(1)
print('PASS')
