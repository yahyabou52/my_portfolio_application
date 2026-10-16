import {fileURLToPath} from 'node:url';
import {chromium} from 'playwright';
import {readFile,mkdir} from 'node:fs/promises';
import assert from 'node:assert/strict';
const root=process.env.TEST_BASE_URL||'http://localhost:8765';
const f=JSON.parse(await readFile(new URL('../.local/editor-fixture.json',import.meta.url),'utf8'));
const base=root+'/s/'+f.slug,out=new URL('../test-results/workspace/',import.meta.url);
await mkdir(out,{recursive:true});
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({reducedMotion:'reduce'});
 await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(f.username);await page.locator('[name=password]').fill(f.password);
 await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:900});await page.goto(base+'/edit',{waitUntil:'networkidle'});
  if(await page.locator('#wfe-actions').isHidden())await page.locator('#wfe-toggle').click();
  for(const id of ['wfe-toolbar','wfe-publish']){const n=page.locator(id==='wfe-toolbar'?'.wfe-toolbar':'#'+id);const b=await n.boundingBox();assert.ok(b.x>=0&&b.x+b.width<=width&&b.y+b.height<=900,'Dock must fit viewport');}
  await page.screenshot({path:fileURLToPath(new URL('editor-'+width+'.png',out))});
  await page.locator('#wfe-sections').click();await page.locator('[data-wfe-pane=sections]').waitFor({state:'visible'});
  await page.screenshot({path:fileURLToPath(new URL('sections-'+width+'.png',out))});
  await page.locator('[data-wfe-tab=appearance]').click();assert.equal(await page.locator('[data-wfe-pane=sections]').isHidden(),true);await page.locator('#wfe-font').waitFor({state:'visible'});
  await page.keyboard.press('Escape');await page.locator('#wfe-content').click();await page.locator('#wfe-items').waitFor({state:'visible'});
  await page.screenshot({path:fileURLToPath(new URL('content-'+width+'.png',out))});
  await page.keyboard.press('Escape');await page.locator('#wfe-appearance').click();await page.locator('#wfe-font').waitFor({state:'visible'});await page.keyboard.press('Escape');
 }
 await page.evaluate(()=>{window.noReloadToken='kept';});
 await page.route('**/edit',route=>route.fulfill({status:503,body:'Temporary failure'}));
 await page.evaluate(()=>window.wfeEditor.refresh().catch(()=>{}));
 await page.getByRole('button',{name:'Retry preview',exact:true}).waitFor();
 await page.unroute('**/edit');
 await Promise.all([page.evaluate(()=>new Promise(resolve=>document.addEventListener('wfe:refreshed',()=>resolve(true),{once:true}))),page.getByRole('button',{name:'Retry preview',exact:true}).click()]);
 assert.equal(await page.evaluate(()=>window.noReloadToken),'kept');
 assert.equal(await page.getByRole('button',{name:'Retry preview',exact:true}).isHidden(),true);
 console.log('PASS: preview failure recovery without navigation, editor navigation, appearance tabs, shared content drawer, mobile dock bounds, Escape dismissal and desktop/mobile screenshots.');
}finally{await browser.close();}
