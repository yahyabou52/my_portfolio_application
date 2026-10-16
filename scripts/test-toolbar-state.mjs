import {chromium} from 'playwright';
import {readFile} from 'node:fs/promises';
import assert from 'node:assert/strict';
const f=JSON.parse(await readFile(new URL('../.local/editor-fixture.json',import.meta.url),'utf8')),base=(process.env.TEST_BASE_URL||'http://localhost:8765')+'/s/'+f.slug;
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(f.username);await page.locator('[name=password]').fill(f.password);await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
 await page.goto(base+'/edit',{waitUntil:'networkidle'});
 const toolbar=page.locator('.wfe-section-tools');await page.locator('main section').first().hover({position:{x:30,y:200}});
 await page.evaluate(()=>window.originalGrip=document.querySelector('[data-wfe-panel=section]'));
 await page.locator('main section').first().hover({position:{x:40,y:260}});
 assert.equal(await page.evaluate(()=>window.originalGrip===document.querySelector('[data-wfe-panel=section]')),true,'Child hovers must not rebuild controls');
 await page.locator('#wfe-toggle').hover();assert.equal(await toolbar.isVisible(),true,'Crossing page chrome must not hide controls');
 const grip=page.getByRole('button',{name:'Move section toolbar',exact:true});await grip.focus();await page.keyboard.press('ArrowRight');
 await page.evaluate(()=>window.scrollTo({top:document.body.scrollHeight,behavior:'instant'}));
 assert.equal(await toolbar.isVisible(),true,'Pinned controls remain available off section');
 await page.getByRole('button',{name:'Section settings',exact:true}).click();await page.getByRole('button',{name:'Close settings'}).click();assert.equal(await toolbar.isVisible(),true);
 await page.evaluate(()=>window.wfeEditor.refresh());assert.equal(await toolbar.isVisible(),true,'Draft refresh retains selection');
 await page.keyboard.press('Escape');await page.evaluate(()=>window.scrollBy({top:-100,behavior:'instant'}));await page.waitForTimeout(100);assert.equal(await toolbar.isHidden(),true,'Scroll must not resurrect dismissed controls');
 await page.setViewportSize({width:390,height:900});await page.locator('main section').first().hover({position:{x:30,y:200}});await page.locator('#wfe-toggle').hover();assert.equal(await toolbar.isVisible(),true);
 console.log('PASS: stable toolbar nodes, no hover-gap flicker, pinned scroll persistence, working settings, selection after refresh, Escape dismissal and mobile visibility.');
}finally{await browser.close();}
