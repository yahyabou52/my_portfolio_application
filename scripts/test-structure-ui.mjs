import {chromium} from 'playwright';import assert from 'node:assert/strict';import {readFile,writeFile} from 'node:fs/promises';
const root=process.env.TEST_BASE_URL||'http://localhost:8765',accounts=JSON.parse(await readFile(new URL('../.local/builder-test-sites.json',import.meta.url),'utf8')),a=accounts[0],base=root+'/s/'+a.slug;
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
const context=await browser.newContext(),page=await context.newPage();
await page.goto(base+'/admin/login');await page.locator('[name=username]').fill(a.email);await page.locator('[name=password]').fill(a.password);await Promise.all([page.waitForURL(base),page.locator('button[type=submit]').click()]);
await page.goto(base+'/edit');page.once('dialog',d=>d.accept());await Promise.all([page.waitForEvent('load'),page.locator('#wfe-discard').click()]);await page.locator('#wfe-sections').click();assert.equal(await page.locator('.wfe-section-row').count(),6);
const first=page.locator('.wfe-section-row').first();await Promise.all([page.waitForEvent('load'),first.getByRole('button',{name:'Hide hero',exact:true}).click()]);
assert.equal(await page.locator('.hero-section').count(),0);
const visitor=await browser.newPage();await visitor.goto(base);assert.equal(await visitor.locator('.hero-section').count(),1);
await page.locator('#wfe-sections').click();await Promise.all([page.waitForEvent('load'),page.getByRole('button',{name:'Show hero',exact:true}).click()]);
await page.locator('#wfe-sections').click();await Promise.all([page.waitForEvent('load'),page.getByRole('button',{name:'Remove about',exact:true}).click()]);
await page.locator('#wfe-sections').click();await page.locator('#wfe-section-type').selectOption('about');await Promise.all([page.waitForEvent('load'),page.locator('#wfe-add-section').click()]);
await page.locator('#wfe-sections').click();assert.equal(await page.locator('.wfe-section-row').count(),6);
await Promise.all([page.waitForEvent('load'),page.getByRole('button',{name:'↑ projects',exact:true}).click()]);await page.locator('#wfe-sections').click();
await Promise.all([page.waitForEvent('load'),page.getByLabel('projects layout',{exact:true}).selectOption('list')]);await page.locator('#wfe-sections').click();
await page.locator('#wfe-color').fill('#154877');await Promise.all([page.waitForEvent('load'),page.locator('#wfe-save-theme').click()]);
assert.equal(await page.evaluate(()=>getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim()),'#154877');
await visitor.reload();assert.notEqual(await visitor.evaluate(()=>getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim()),'#154877');
await page.locator('#wfe-preview').click();await page.locator('#wfe-device').selectOption('390');
await page.frameLocator('#wfe-preview-frame').locator('.hero-section').waitFor();assert.equal(await page.frameLocator('#wfe-preview-frame').locator('#wfe-config').count(),0);assert.equal(await page.locator('#wfe-preview-frame').evaluate(n=>n.clientWidth),390);
await page.getByRole('button',{name:'Close preview'}).click();
page.once('dialog',d=>d.accept());await Promise.all([page.waitForEvent('load'),page.locator('#wfe-discard').click()]);
console.log('PASS: section show/hide/remove/add, draft theme isolation, same-renderer mobile preview and discard.');
}finally{await browser.close();}
