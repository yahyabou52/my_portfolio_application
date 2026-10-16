import {chromium} from 'playwright';
import {readFile} from 'node:fs/promises';
import assert from 'node:assert/strict';
const f=JSON.parse(await readFile(new URL('../.local/editor-fixture.json',import.meta.url),'utf8')),base=(process.env.TEST_BASE_URL||'http://localhost:8765')+'/s/'+f.slug;
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(f.username);await page.locator('[name=password]').fill(f.password);await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
 await page.goto(base+'/edit',{waitUntil:'networkidle'});await page.evaluate(()=>window.panelToken='same');
 const original=await page.locator('main section').evaluateAll(ns=>ns.map(n=>({key:n.dataset.sectionKey,w:n.offsetWidth,h:n.offsetHeight})));
 async function drag(name,dx,dy){const b=await page.getByRole('button',{name,exact:true}).boundingBox();await page.mouse.move(b.x+b.width/2,b.y+b.height/2);await page.mouse.down();await page.mouse.move(b.x+b.width/2+dx,b.y+b.height/2+dy,{steps:12});await page.mouse.up();}
 const before=await page.locator('.wfe-toolbar').boundingBox();await drag('Move editor toolbar',-180,-300);const after=await page.locator('.wfe-toolbar').boundingBox();assert.ok(Math.abs(after.x-(before.x-180))<2);assert.ok(Math.abs(after.y-(before.y-300))<2);
 await page.locator('main section').first().hover({position:{x:30,y:200}});
 const first=await page.locator('.wfe-section-tools').boundingBox();await drag('Move section toolbar',150,180);const moved=await page.locator('.wfe-section-tools').boundingBox();assert.ok(Math.abs(moved.x-first.x-150)<2);assert.ok(Math.abs(moved.y-first.y-180)<2);
 await page.mouse.move(1400,500);await page.locator('main section').first().hover({position:{x:30,y:200}});const kept=await page.locator('.wfe-section-tools').boundingBox();assert.equal(kept.x,moved.x);assert.equal(kept.y,moved.y);
 assert.equal(await page.evaluate(()=>window.panelToken),'same');
 assert.deepEqual(await page.locator('main section').evaluateAll(ns=>ns.map(n=>({key:n.dataset.sectionKey,w:n.offsetWidth,h:n.offsetHeight}))),original);
 await page.reload({waitUntil:'networkidle'});const restored=await page.locator('.wfe-toolbar').boundingBox();assert.equal(restored.x,after.x);assert.equal(restored.y,after.y);
 await page.getByRole('button',{name:'Move editor toolbar',exact:true}).focus();await page.keyboard.press('ArrowRight');assert.equal((await page.locator('.wfe-toolbar').boundingBox()).x,restored.x+10);
 await page.setViewportSize({width:390,height:900});
 for(const selector of ['.wfe-toolbar']){const b=await page.locator(selector).boundingBox();assert.ok(b.x>=0&&b.x+b.width<=390&&b.y>=0&&b.y+b.height<=900);}
 await page.getByRole('button',{name:'Move editor toolbar',exact:true}).focus();await page.keyboard.press('Home');assert.equal(await page.locator('.wfe-toolbar').getAttribute('data-pinned'),null);
 console.log('PASS: both panels drag independently, retain placement, restore after reload, support keyboard/reset, stay in mobile viewport, and do not change website geometry.');
}finally{await browser.close();}
