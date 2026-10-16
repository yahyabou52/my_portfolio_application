import {chromium} from 'playwright';
import {readFile,mkdir} from 'node:fs/promises';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';
const accounts=JSON.parse(await readFile(new URL('../.local/builder-test-sites.json',import.meta.url),'utf8')),a=accounts[0],base=(process.env.TEST_BASE_URL||'http://localhost:8765')+'/s/'+a.slug;
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(a.email);await page.locator('[name=password]').fill(a.password);await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
 await page.goto(base+'/edit');await page.evaluate(()=>window.contextToken='same');
 page.on('pageerror',e=>console.log('PAGE ERROR',e.message));
 const refreshed=()=>page.evaluate(()=>new Promise((resolve,reject)=>{const timer=setTimeout(()=>reject(new Error(document.getElementById('wfe-structure-error').textContent||'Refresh timed out')),15000);document.addEventListener('wfe:refreshed',()=>{clearTimeout(timer);resolve(true);},{once:true});}));
 await page.locator('main .hero-section').hover({position:{x:30,y:80}});
 await page.getByRole('button',{name:'Section settings',exact:true}).click();
 assert.equal(await page.locator('.wfe-section-row.wfe-selected').count(),1);
 await page.locator('#wfe-quick-fields textarea').first().fill('Quick section edit');await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();assert.ok((await page.locator('main').innerText()).includes('Quick section edit'));
 await page.getByRole('button',{name:'Close settings'}).click();
 await page.locator('#wfe-pages').click();await page.locator('#wfe-page-select').selectOption('about');await page.locator('#wfe-page-title').fill('Private About title');
 await Promise.all([refreshed(),page.locator('#wfe-save-page').click()]);
 assert.equal(await page.evaluate(()=>window.contextToken),'same');
 const visitor=await browser.newPage();await visitor.goto(base+'/about');assert.notEqual(await visitor.title(),'Private About title');
 await page.locator('#wfe-page-visible').uncheck();await Promise.all([refreshed(),page.locator('#wfe-save-page').click()]);
 assert.equal(await page.locator('#wfe-page-visible').isChecked(),false);
 await page.locator('#wfe-page-select').selectOption('home');assert.equal(await page.locator('#wfe-page-visible').isDisabled(),true);
 const bad=await page.evaluate(async()=>{try{await window.wfeEditor.action('structure',{operation:'page',page:'home',title:'Home',visible:false});return 'accepted';}catch(e){return e.message;}});assert.match(bad,/must remain visible/);
 await page.locator('#wfe-page-select').selectOption('about');
 await Promise.all([page.waitForURL('**/edit?page=about'),page.locator('#wfe-open-page').click()]);
 assert.equal(await page.title(),'Private About title');
 await page.goto(base+'/edit');
 const out=new URL('../test-results/context/',import.meta.url);await mkdir(out,{recursive:true});
 for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});await page.locator('main .hero-section').hover({position:{x:30,y:80}});
 await page.screenshot({path:fileURLToPath(new URL('section-'+width+'.png',out))});
 const b=await page.locator('.wfe-section-tools').boundingBox();assert.ok(b.x>=0&&b.x+b.width<=width);
 }
 page.once('dialog',d=>d.accept());if(await page.locator('#wfe-actions').isHidden())await page.locator('#wfe-toggle').click();await Promise.all([refreshed(),page.locator('#wfe-discard').click()]);
 console.log('PASS: on-page section settings, page configuration, draft privacy, home visibility validation, page navigation and mobile overlay bounds.');
}finally{await browser.close();}
