import {chromium} from 'playwright';
import {readFile,mkdir,writeFile} from 'node:fs/promises';
import assert from 'node:assert/strict';
const root=process.env.TEST_BASE_URL||'http://localhost:8765';
const fixture=JSON.parse(await readFile(new URL('../.local/editor-fixture.json',import.meta.url),'utf8'));
const base=root+'/s/'+fixture.slug;
const out=new URL('../test-results/editor/',import.meta.url);await mkdir(out,{recursive:true});
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const context=await browser.newContext({reducedMotion:'reduce'}),visitor=await browser.newContext({reducedMotion:'reduce'});
 const page=await context.newPage();await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(fixture.username);await page.locator('[name=password]').fill(fixture.password);
 await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
 const results=[];
 for(const width of [1440,768,390]){
  await page.setViewportSize({width,height:1000});await page.goto(base,{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
  const off=await page.locator('main section').evaluateAll(nodes=>nodes.map(n=>({key:n.dataset.sectionKey,width:n.getBoundingClientRect().width,height:n.getBoundingClientRect().height})));
  await writeFile(new URL('public-'+width+'.png',out),await page.screenshot({fullPage:true}));
  await page.goto(base+'/edit',{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
  const on=await page.locator('main section').evaluateAll(nodes=>nodes.map(n=>({key:n.dataset.sectionKey,width:n.getBoundingClientRect().width,height:n.getBoundingClientRect().height})));
  assert.deepEqual(on,off,'Editing changed section dimensions at '+width);
  await writeFile(new URL('edit-'+width+'.png',out),await page.screenshot({fullPage:true}));
  results.push({width,sectionDimensionsEqual:true});
 }
 await page.setViewportSize({width:1440,height:1000});await page.goto(base+'/edit',{waitUntil:'networkidle'});
 const heading=page.locator('[data-edit-field="hero.hero_title"]');await heading.fill('Private inline title');await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();
 const publicPage=await visitor.newPage();const response=await publicPage.goto(base);
 const source=await response.text();assert.ok(!source.includes('Private inline title'));assert.ok(!/wfe-editor|wfe-config|data-edit-field|data-owner-edit/.test(source),'Visitor receives editor markup');
 await page.locator('#wfe-undo').click();await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();assert.notEqual(await heading.innerText(),'Private inline title');
 await page.locator('#wfe-redo').click();await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();
 await page.locator('#wfe-publish').click();await page.locator('#wfe-status').filter({hasText:'Published'}).waitFor();
 await publicPage.reload();assert.equal((await publicPage.locator('.hero-title').innerText()).trim(),'Private inline title');
 await heading.fill('Throw this draft away');await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();
 page.once('dialog',dialog=>dialog.accept());await Promise.all([page.waitForEvent('load'),page.locator('#wfe-discard').click()]);
 assert.equal((await page.locator('[data-edit-field="hero.hero_title"]').innerText()).trim(),'Private inline title');
 // Concurrent tab retains an old revision and must receive a visible conflict.
 const other=await context.newPage();await other.goto(base+'/edit',{waitUntil:'networkidle'});
 await page.locator('[data-edit-field="hero.hero_title"]').fill('Newest saved draft');await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();
 await other.locator('[data-edit-field="hero.hero_title"]').fill('Stale tab edit');
 await other.locator('#wfe-reload').waitFor({state:'visible'});assert.ok((await other.locator('#wfe-status').innerText()).includes('another session'));
 // Button drawer updates label and validates schemes.
 await page.locator('[data-edit-kind=button]').first().click();await page.locator('#wfe-label').fill('Talk with me');await page.locator('#wfe-url').fill('javascript:alert(1)');await page.locator('#wfe-apply-button').click();
 assert.ok(await page.locator('#wfe-dialog-error').innerText());await page.locator('#wfe-url').fill('https://example.com');await page.locator('#wfe-apply-button').click();await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();
 // Uploaded image uses the real owner-checked upload endpoint.
 const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7V8AAAAASUVORK5CYII=','base64');
 const chooserPromise=page.waitForEvent('filechooser');await page.locator('[data-edit-kind=image]').first().click();await (await chooserPromise).setFiles({name:'editor-test.png',mimeType:'image/png',buffer:png});
 await page.locator('#wfe-status').filter({hasText:'Saved draft'}).waitFor();assert.ok((await page.locator('[data-edit-kind=image]').first().getAttribute('src')).includes('assets/uploads/sites/'));
 await writeFile(new URL('report.json',out),JSON.stringify({passed:true,dimensions:results,checks:['draft privacy','publish UI','discard UI','undo/redo','conflict UI','button validation','real image upload','visitor assets']},null,2));
 console.log('PASS: real-browser editor save/publish/discard, undo/redo, stale revision, buttons, uploads, visitor isolation and unchanged section dimensions at all widths.');
}finally{await browser.close();}
