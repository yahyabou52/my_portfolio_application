import {chromium} from 'playwright';import assert from 'node:assert/strict';import {readFile} from 'node:fs/promises';
const root=process.env.TEST_BASE_URL||'http://localhost:8765',accounts=JSON.parse(await readFile(new URL('../.local/builder-test-sites.json',import.meta.url),'utf8')),a=accounts[0],base=root+'/s/'+a.slug;
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
const context=await browser.newContext(),page=await context.newPage();await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(a.email);await page.locator('[name=password]').fill(a.password);await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
await page.goto(base+'/manage');await page.getByRole('button',{name:'Projects',exact:true}).click();await page.locator('#wfe-new-item').click();
const title='Project '+Date.now();await page.locator('[name=title]').fill(title);await page.locator('[name=short_description]').fill('Created through the shared item drawer.');await page.locator('#wfe-draft-item').click();await page.locator('#wfe-item').waitFor({state:'hidden'});
const visitor=await browser.newPage();await visitor.goto(base);assert.ok(!(await visitor.content()).includes(title));
await page.getByRole('button',{name:title+' — Draft',exact:true}).click();await page.locator('#wfe-save-item').click();await page.locator('#wfe-item').waitFor({state:'hidden'});await visitor.reload();assert.ok((await visitor.content()).includes(title));
await page.goto(base+'/edit');await page.evaluate(()=>window.collectionEditorToken='preserved');const card=page.locator('[data-collection-type=projects]').filter({hasText:title});await card.click();await page.locator('#wfe-live-note').waitFor({state:'visible'});
await page.locator('[name=title]').fill(title+' live edit');await page.locator('#wfe-save-item').click();await page.locator('#wfe-item').waitFor({state:'hidden'});await page.locator('main [data-collection-type=projects]').filter({hasText:title+' live edit'}).waitFor();assert.equal(await page.evaluate(()=>window.collectionEditorToken),'preserved');await visitor.reload();assert.ok((await visitor.content()).includes(title+' live edit'));
await page.getByRole('button',{name:title+' live edit — Published',exact:true}).click();await page.locator('#wfe-draft-item').click();await page.locator('#wfe-item').waitFor({state:'hidden'});await page.locator('main [data-collection-type=projects]').filter({hasText:title}).waitFor({state:'hidden'});assert.equal(await page.evaluate(()=>window.collectionEditorToken),'preserved');await visitor.reload();assert.ok(!(await visitor.content()).includes(title));
const checks=await page.evaluate(async()=>{
const c=JSON.parse(document.getElementById('wfe-collections-config').textContent),results=[];
const post=async(body,csrf=c.csrf)=>{const r=await fetch(c.api+'collection-save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify(body)});return {status:r.status,data:await r.json()};};
for(const [type,fields] of Object.entries({skills:{name:'Unique skill',category:'Test skills',proficiency_level:'85'},experience:{title:'Engineer',organization:'Example',date_range:'2024–2026'},services:{title:'Research',description:'Research service'},testimonials:{client_name:'A client',testimonial_text:'Helpful work'}})){
const made=await post({type,fields,status:'draft'});results.push([type,made.status]);
let listing=await (await fetch(c.api+'collection-list?type='+type)).json(),item=listing.items.find(i=>i.id==made.data.id);
results.push([type+' default draft',item.status]);
const published=await post({type,id:item.id,version:item._version,fields,status:'published'});results.push([type+' publish',published.status]);
const stale=await post({type,id:item.id,version:item._version,fields,status:'draft'});results.push([type+' stale',stale.status]);
}

results.push(['unsafe URL',(await post({type:'projects',fields:{title:'bad',project_url:'javascript:alert(1)'}})).status]);
return results;});
for(const [name,value] of checks)assert.equal(value,name.endsWith('default draft')?'draft':name.endsWith('stale')?409:name==='CSRF'?403:name==='unsafe URL'?422:200,name);
const badCsrf=await context.request.post(base+'/editor-api/collection-save',{headers:{'X-CSRF-Token':'invalid'},data:{type:'projects',fields:{title:'bad'}}});assert.equal(badCsrf.status(),403);
const anonymous=await visitor.request.get(base+'/editor-api/collection-list?type=projects');assert.equal(anonymous.status(),403);
await visitor.goto(base);const csrf=await visitor.locator('meta[name=csrf-token]').getAttribute('content');
await visitor.request.post(base+'/contact',{form:{csrf_token:csrf,name:'Test visitor',email:'visitor@example.com',subject:'Local test enquiry',message:'This is a local test message for the owner.'}});
await page.goto(base+'/manage');const message=page.locator('details').filter({hasText:'Local test enquiry'}).first();await message.locator('summary').click();await Promise.all([page.waitForEvent('load'),message.getByRole('button',{name:'Mark read'}).click()]);
const readMessage=page.locator('details').filter({hasText:'Local test enquiry'}).first();await readMessage.locator('summary').click();assert.ok((await readMessage.innerText()).includes('Read'));page.once('dialog',d=>d.accept());await Promise.all([page.waitForEvent('load'),readMessage.getByRole('button',{name:'Delete',exact:true}).click()]);
console.log('PASS: shared inline/dashboard drawer, draft/publish/unpublish/live saves, every collection, stale item conflict, CSRF and URL validation.');
}finally{await browser.close();}
