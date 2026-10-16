import {chromium} from 'playwright';
import {readFile,mkdir,writeFile} from 'node:fs/promises';
import pixelmatch from 'pixelmatch';
import {PNG} from 'pngjs';
import assert from 'node:assert/strict';
const root=process.env.TEST_BASE_URL||'http://localhost:8765';
const fixture=JSON.parse(await readFile(new URL('../.local/parity-fixture.json',import.meta.url),'utf8'));
const out=new URL('../test-results/design-parity/',import.meta.url);await mkdir(out,{recursive:true});
const browser=await chromium.launch({channel:'chrome',headless:true});
const result=[];
try{
 const context=await browser.newContext({reducedMotion:'reduce',deviceScaleFactor:1});
 const login=await context.newPage();await login.goto(root+'/s/'+fixture.slug+'/admin/login');
 await login.locator('[name=username]').fill(fixture.username);await login.locator('[name=password]').fill(fixture.password);
 await Promise.all([login.waitForURL('**/admin/dashboard'),login.locator('button[type=submit]').click()]);await login.close();
 const cases=[['home','hero','.hero-section'],['home','projects','.featured-work-section'],['home','skills','.skills-section'],['about','about','.about-hero'],['about','experience','.experience-section'],['contact','contact','.contact-content']];
 for(const width of [1440,768,390]){
  for(const [pageKey,name,selector] of cases){
   const buffers=[];const boxes=[];
   for(const mode of ['legacy','renderer']){
    const page=await context.newPage();await page.setViewportSize({width,height:1000});
    const path=mode==='legacy'?(pageKey==='home'?'':pageKey):'admin/builder-preview?page='+pageKey;
    const response=await page.goto(root+'/s/'+fixture.slug+'/'+path,{waitUntil:'networkidle'});assert.equal(response.status(),200,path);
    const source=await response.text();assert.ok(!/Fatal error|Warning:/.test(source),source.slice(-2000));
    assert.ok(!source.includes('builder.css'));assert.ok(!/wfe-|owner-edit|builder-bar/.test(source),'Parity source has no editor layer');
    await page.evaluate(()=>document.fonts.ready);const section=page.locator(selector);await section.scrollIntoViewIfNeeded();await page.waitForTimeout(1000);
    await page.evaluate(async()=>{await Promise.all([...document.images].map(i=>i.decode().catch(()=>{})));});
    boxes.push(await section.boundingBox());
    const png=await section.screenshot({animations:'disabled'});buffers.push(PNG.sync.read(png));
    await writeFile(new URL(name+'-'+width+'-'+mode+'.png',out),png);await page.close();
   }
   const [a,b]=buffers;
   const reference=PNG.sync.read(await readFile(new URL('../tests/visual-baselines/'+name+'-'+width+'-legacy.png',import.meta.url)));
   assert.equal(a.width,reference.width,'Legacy design width changed');assert.equal(a.height,reference.height,'Legacy design height changed');
   assert.equal(pixelmatch(a.data,reference.data,null,a.width,a.height,{threshold:0.1}),0,'Existing public design changed: '+name+' at '+width);
   let differences=-1;
   if(a.width===b.width&&a.height===b.height){const diff=new PNG({width:a.width,height:a.height});differences=pixelmatch(a.data,b.data,diff.data,a.width,a.height,{threshold:0.1});await writeFile(new URL(name+'-'+width+'-diff.png',out),PNG.sync.write(diff));}
   result.push({section:name,width,legacy:[a.width,a.height],renderer:[b.width,b.height],differentPixels:differences});
   console.log(JSON.stringify(result.at(-1)));
  }
 }
 await writeFile(new URL('report.json',out),JSON.stringify(result,null,2));
 assert.ok(result.every(r=>r.differentPixels===0),'Design parity failed; see test-results/design-parity/report.json');
}finally{await browser.close();}
