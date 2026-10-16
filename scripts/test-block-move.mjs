import {chromium} from 'playwright';
import {readFile} from 'node:fs/promises';
import assert from 'node:assert/strict';
const f=JSON.parse(await readFile(new URL('../.local/editor-fixture.json',import.meta.url),'utf8')),base=(process.env.TEST_BASE_URL||'http://localhost:8765')+'/s/'+f.slug;
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1440,height:900}});await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(f.username);await page.locator('[name=password]').fill(f.password);await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
 await page.goto(base+'/edit',{waitUntil:'networkidle'});await page.evaluate(()=>window.moveToken='preserved');
 const refresh=()=>page.evaluate(()=>new Promise((resolve,reject)=>{const timer=setTimeout(()=>reject(new Error(document.getElementById('wfe-status').textContent)),15000);document.addEventListener('wfe:refreshed',()=>{clearTimeout(timer);resolve(true);},{once:true});}));
 const order=()=>page.locator('main section[data-section-key]').evaluateAll(nodes=>nodes.map(n=>n.dataset.sectionKey));
 const original=await order();assert.ok(original.length>1);
 const visitor=await browser.newPage();await visitor.goto(base);const live=await visitor.locator('main section[data-section-key]').evaluateAll(nodes=>nodes.map(n=>n.dataset.sectionKey));
 await page.locator('main section[data-section-key]').first().hover({position:{x:30,y:200}});
 const handle=page.getByRole('button',{name:'Move block',exact:true});const box=await handle.boundingBox();
 await page.mouse.move(box.x+box.width/2,box.y+box.height/2);await page.mouse.down();await page.mouse.move(box.x+box.width/2,box.y+box.height/2+20);
 const drop=await page.locator('main section[data-section-key]').nth(1).evaluate(node=>{const y=node.getBoundingClientRect().bottom+scrollY;window.scrollTo({top:y-450,behavior:'instant'});return y;});
 await page.mouse.move(150,await page.evaluate(y=>y-scrollY,drop));await page.locator('.wfe-drop-marker').waitFor({state:'visible'});
 await Promise.all([refresh(),page.mouse.up()]);
 const moved=await order();assert.equal(moved[0],original[1]);assert.equal(moved[1],original[0]);assert.equal(await page.evaluate(()=>window.moveToken),'preserved');
 await visitor.reload();assert.deepEqual(await visitor.locator('main section[data-section-key]').evaluateAll(nodes=>nodes.map(n=>n.dataset.sectionKey)),live);
 // Tap/click picker works on a narrow viewport.
 await page.setViewportSize({width:390,height:900});await page.locator('main section[data-section-key]').first().hover({position:{x:30,y:200}});
 await page.getByRole('button',{name:'Move block',exact:true}).click();await page.locator('#wfe-move-position').selectOption('after');
 const target=await page.evaluate(()=>window.wfeStructure.config.sections.find(s=>s.id===document.querySelectorAll('main section[data-section-key]')[1].dataset.sectionKey).id);
 await page.locator('#wfe-move-target').selectOption(target);await Promise.all([refresh(),page.locator('#wfe-move-apply').click()]);
 assert.deepEqual(await order(),original);
 await page.locator('main section[data-section-key]').first().hover({position:{x:30,y:200}});
 await page.getByRole('button',{name:'Move block',exact:true}).focus();
 await Promise.all([refresh(),page.keyboard.press('ArrowDown')]);
 assert.notDeepEqual(await order(),original);
 await page.locator('main section[data-section-key]').filter({has:page.locator('[data-edit-field="hero.hero_title"]')}).hover({position:{x:30,y:200}});
 await page.getByRole('button',{name:'Move block',exact:true}).focus();
 await Promise.all([refresh(),page.keyboard.press('ArrowUp')]);
 assert.deepEqual(await order(),original);
 const rejected=await page.evaluate(async()=>{try{await window.wfeEditor.action('structure',{page:'home',operation:'place',id:window.wfeStructure.config.sections[0].id,target:'unknown',position:'before'});return 'accepted';}catch(e){return e.message;}});
 assert.match(rejected,/Invalid section position/);
 console.log('PASS: pointer drag, mobile position picker, draft-only placement, no reload, preserved section order and invalid target rejection.');
}finally{await browser.close();}
