import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { Script } from 'node:vm';
const base = process.env.TEST_BASE_URL || 'http://localhost:8765';
const suffix = randomBytes(4).toString('hex');
const password = randomBytes(18).toString('hex');
class Client {
  cookies = new Map();
  async request(path, data, follow = true) {
    const url = new URL(path, base);
    const headers = {Cookie: [...this.cookies].map(([k,v])=>k+'='+v).join('; ')};
    if (data && !(data instanceof FormData)) headers['Content-Type']='application/x-www-form-urlencoded';
    const response=await fetch(url,{method:data?'POST':'GET',headers,body:data?(data instanceof FormData?data:new URLSearchParams(data)):undefined,redirect:'manual'});
    for(const raw of response.headers.getSetCookie()){const pair=raw.split(';')[0];const eq=pair.indexOf('=');this.cookies.set(pair.slice(0,eq),pair.slice(eq+1));}
    if(follow && response.status>=300 && response.status<400)return this.request(response.headers.get('location'));
    const text=await response.text();
    if(response.headers.get('content-type')?.includes('text/html')) {
      for(const match of text.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)) {
        if(match[1].includes('application/json') || match[1].includes('text/template'))continue;
        new Script(match[2], {filename:url.pathname});
      }
    }
    return {status:response.status,headers:response.headers,text,url:url.href};
  }
}
function token(html){const match=html.match(/name="csrf_token" value="([^"]+)"/);assert.ok(match,'CSRF token rendered');return match[1];}
const slugs=['qa-a-'+suffix,'qa-b-'+suffix];
const clients=[new Client(),new Client()];
for(let i=0;i<2;i++){
 const c=clients[i],slug=slugs[i];
 const signup=await c.request('/signup');
 const result=await c.request('/signup',{csrf_token:token(signup.text),slug,name:'Test Studio '+i,email:slug+'@example.com',password});
 assert.ok(result.url.includes('/s/'+slug+'/admin/login'),result.text.slice(-1500));
 const login=await c.request('/s/'+slug+'/admin/login',{csrf_token:token(result.text),username:slug+'@example.com',password});
 assert.ok(login.url.includes('/admin/dashboard'),'Owner can sign in');
 const site=await c.request('/s/'+slug);assert.equal(site.status,200);assert.ok(site.text.includes('Test Studio '+i));assert.ok(!site.text.includes('Fatal error'));
}
const [a,b]=clients;const [siteA,siteB]=slugs;
const editor=await a.request('/s/'+siteA+'/admin/website');assert.equal(editor.status,200);
let result=await a.request('/s/'+siteA+'/admin/website',{csrf_token:token(editor.text),group:'identity',site_title:'A unique studio '+suffix,site_tagline:'Independent work',site_description:'Test description',contact_email:'studio@example.com',contact_phone:'+123456789',contact_address:'Test location',nav_cta_text:'Contact us',nav_cta_url:'/contact',footer_text:'Studio footer',theme_default:'light',logo_id:'keep',favicon_id:'keep','social[github]':'https://github.com/example'});
assert.ok(!result.text.includes('could not be saved'),'Identity saved');
const publicA=await a.request('/s/'+siteA+'/contact');assert.ok(publicA.text.includes('studio@example.com'));assert.ok(publicA.text.includes('Test location'));
const publicB=await b.request('/s/'+siteB+'/contact');assert.ok(!publicB.text.includes('studio@example.com'),'Branding isolated');
const textPage=await a.request('/s/'+siteA+'/admin/website?group=contact');
const field=textPage.text.match(/name="fields\[([^\]]*\.text\.[^\]]+)\]"/);assert.ok(field,'Editable page text exists');
result=await a.request('/s/'+siteA+'/admin/website',{csrf_token:token(textPage.text),group:'contact',['fields['+field[1]+']']:'CUSTOM CONTACT '+suffix,'fields[contact.section.contact-header]':'1','fields[contact.section.contact-content]':'1','fields[contact.section.contact-faq]':'1'});
assert.ok((await a.request('/s/'+siteA+'/contact')).text.includes('CUSTOM CONTACT '+suffix),'Page text reaches public site');
assert.ok(!(await b.request('/s/'+siteB+'/contact')).text.includes('CUSTOM CONTACT '+suffix),'Page text isolated');
const bad=await a.request('/s/'+siteA+'/admin/website',{group:'identity',site_title:'HACK'},false);assert.equal(bad.status,403,'Missing CSRF rejected');
const anonymous=new Client();assert.equal((await anonymous.request('/s/'+siteA+'/admin/website',null,false)).status,302,'Anonymous dashboard rejected');
const forged=new Client();for(const [key,value] of a.cookies){forged.cookies.set(key,value);if(key.startsWith('portfolio_') && key!=='portfolio_0'){const bKey=[...b.cookies.keys()].find(k=>k.startsWith('portfolio_')&&k!=='portfolio_0');forged.cookies.set(bKey,value);}}
assert.equal((await forged.request('/s/'+siteB+'/admin/website',null,false)).status,302,'Cross-tenant session rejected');
assert.equal((await a.request('/s/no-such-website')).status,404);
assert.equal((await a.request('/login_bypass.php')).status,404);
for(const route of ['','/about','/services','/portfolio','/contact']){
 const page=await a.request('/s/demo'+route);assert.equal(page.status,200);assert.ok(!/Fatal error|Warning:/.test(page.text));
 for(const match of page.text.matchAll(/<img[^>]+src="([^"]+)"/g)){
  const imageUrl=match[1].replaceAll('&amp;','&');if(!imageUrl.startsWith(base))continue;
  const image=await a.request(imageUrl);assert.equal(image.status,200,imageUrl);assert.ok(image.headers.get('content-type')?.startsWith('image/'),imageUrl);
 }
}


for(const route of ['website','hero','about','timeline','services','services/features','services/process','services/pricing','services/faqs','footer','navigation','media','users','theme','pages','projects','testimonials','skills','featured-projects','services/preview','home/page-cta','messages']) {
 const page=await a.request('/s/'+siteA+'/admin/'+route);assert.equal(page.status,200,route);assert.ok(!/Fatal error|Warning:/.test(page.text),route);
}

// Upload bytes, publish the uploaded logo, and verify customer-owned file paths.
const mediaPage=await a.request('/s/'+siteA+'/admin/media');
const upload=new FormData();upload.append('csrf_token',token(mediaPage.text));
const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7V8AAAAASUVORK5CYII=','base64');
upload.append('file',new Blob([png],{type:'image/png'}),'logo.png');
const uploaded=await a.request('/s/'+siteA+'/admin/media/upload',upload);
assert.equal(uploaded.status,200,uploaded.text);const uploadedFile=JSON.parse(uploaded.text).file;
assert.ok(uploadedFile.file_path.startsWith('assets/uploads/sites/'),'Upload uses tenant directory');
const imageResponse=await a.request('/'+uploadedFile.file_path);assert.equal(imageResponse.status,200);assert.ok(imageResponse.headers.get('content-type').startsWith('image/'));
const identityAgain=await a.request('/s/'+siteA+'/admin/website');
await a.request('/s/'+siteA+'/admin/website',{csrf_token:token(identityAgain.text),group:'identity',site_title:'A unique studio '+suffix,contact_email:'studio@example.com',nav_cta_url:'/contact',logo_id:String(uploadedFile.id),favicon_id:'keep'});
assert.ok((await a.request('/s/'+siteA)).text.includes(uploadedFile.file_path),'Uploaded logo is rendered publicly');
assert.ok(!(await b.request('/s/'+siteB+'/admin/media')).text.includes(uploadedFile.file_path),'Media library isolated');
const unsafe=new FormData();unsafe.append('csrf_token',token(mediaPage.text));unsafe.append('file',new Blob(['<?php echo "unsafe"; ?>'],{type:'image/png'}),'fake.png');
assert.equal((await a.request('/s/'+siteA+'/admin/media/upload',unsafe)).status,400,'Spoofed image rejected');

// Reordering sections changes actual document order.
const homeEditor=await a.request('/s/'+siteA+'/admin/website?group=home');
const homeFields=JSON.parse(await (await import('node:fs/promises')).readFile(new URL('../config/website-fields.json',import.meta.url),'utf8'));
const homeBody={csrf_token:token(homeEditor.text),group:'home'};
for(const [key,field] of Object.entries(homeFields)){if(field.group==='home')homeBody['fields['+key+']']=field.default;}
homeBody['fields[home.section.hero-section.order]']='999';
await a.request('/s/'+siteA+'/admin/website',homeBody);
const reordered=await a.request('/s/'+siteA);
assert.ok(reordered.text.indexOf('data-section-key="home.section.featured-work-section"') < reordered.text.indexOf('data-section-key="home.section.hero-section"'),'Section order applied');

// The section switch actually removes the section from public output.
const contactEditor=await a.request('/s/'+siteA+'/admin/website?group=contact');
await a.request('/s/'+siteA+'/admin/website',{csrf_token:token(contactEditor.text),group:'contact','fields[contact.section.contact-content]':'1'});
assert.ok(!(await a.request('/s/'+siteA+'/contact')).text.includes('<section class="contact-header'),'Hidden section is not rendered');
// Existing theme and footer dashboards publish into the same site.
const themePage=await a.request('/s/'+siteA+'/admin/theme');
await a.request('/s/'+siteA+'/admin/theme',{csrf_token:token(themePage.text),primary_color:'#123456',secondary_color:'#345678',accent_color:'#567890',dark_mode_enabled:'1',custom_css:'.hero-title { letter-spacing: 0; }'});
assert.ok((await a.request('/s/'+siteA)).text.includes('--primary-color:#123456'),'Theme save applied publicly');
const footerPage=await a.request('/s/'+siteA+'/admin/footer');
await a.request('/s/'+siteA+'/admin/footer',{csrf_token:token(footerPage.text),footer_text:'FOOTER '+suffix,footer_additional_text:'Extra footer '+suffix,footer_show_social:'1'});
const footerPublic=await a.request('/s/'+siteA);assert.ok(footerPublic.text.includes('FOOTER '+suffix));assert.ok(footerPublic.text.includes('Extra footer '+suffix));


// UX regression: every editor group renders and each legacy settings route has one destination.
for(const group of ['home','about','services','portfolio','project','contact','navigation','footer','identity']) {
 const response=await a.request('/s/'+siteA+'/admin/website?group='+group);
 assert.equal(response.status,200,group);assert.ok(!/Fatal error|Warning:/.test(response.text),group);
 assert.ok(response.text.includes('id="websitePreview"'));assert.ok(response.text.includes('id="websiteEditor"'));
}
assert.ok((await a.request('/s/'+siteA+'/admin/footer')).url.includes('group=footer'));
assert.ok((await a.request('/s/'+siteA+'/admin/pages/about/edit')).url.includes('group=about'));
const identityUX=await a.request('/s/'+siteA+'/admin/website?group=identity');const uxToken=token(identityUX.text);
const invalid=await a.request('/s/'+siteA+'/admin/website',{csrf_token:uxToken,group:'identity',site_title:'Retain this draft',contact_email:'not-an-email'});
assert.ok(invalid.text.includes('Enter a valid contact email.'));
assert.ok(invalid.text.includes('value="Retain this draft"'),'Rejected draft retained');
assert.ok(!(await a.request('/s/'+siteA)).text.includes('Retain this draft'),'Invalid draft not published');
await a.request('/s/'+siteA+'/admin/theme',{csrf_token:uxToken,theme_default:'dark',primary_color:'#123456',dark_mode_enabled:'1'});
await a.request('/s/'+siteA+'/admin/website',{csrf_token:uxToken,group:'identity',site_title:'Final studio '+suffix});
let uxPublic=await a.request('/s/'+siteA);
assert.ok(uxPublic.text.includes('data-bs-theme="dark"'),'Identity save preserves appearance');
assert.ok(uxPublic.text.includes('FOOTER '+suffix),'Identity save preserves footer');
assert.ok(uxPublic.text.includes('https://github.com/example'),'Partial identity save preserves social links');
const footerSaved=await a.request('/s/'+siteA+'/admin/website',{csrf_token:uxToken,group:'footer',_footer:'1',_layout:'1','fields[footer.section.footer]':'1',footer_text:'Unified footer '+suffix,footer_additional_text:'Unified extra '+suffix,footer_show_social:'1','footer_links[0][title]':'Talk to us','footer_links[0][url]':'/contact'});
assert.ok(footerSaved.text.includes('Changes saved.'),'Unified footer transaction commits');
uxPublic=await a.request('/s/'+siteA);assert.ok(uxPublic.text.includes('Unified extra '+suffix));assert.ok(uxPublic.text.includes('href="'+base+'/s/'+siteA+'/contact" class="text-muted'),'Footer links remain in customer website');
await a.request('/s/'+siteA+'/admin/website',{csrf_token:uxToken,group:'about','page_meta[title]':'Private about '+suffix,'page_meta[meta_description]':'Saved search description'});
const visitor=new Client();assert.equal((await visitor.request('/s/'+siteA+'/about')).status,404);
assert.equal((await visitor.request('/s/'+siteA+'/about?preview=1')).status,404,'Anonymous cannot preview unpublished page');
const ownerPreview=await a.request('/s/'+siteA+'/about?preview=1');assert.equal(ownerPreview.status,200);assert.ok(ownerPreview.text.includes('Private about '+suffix));
const navHidden=(await visitor.request('/s/'+siteA)).text.match(/<nav[\s\S]*?<\/nav>/)?.[0]||'';
assert.ok(!navHidden.includes('/about'),'Unpublished page removed from navigation');
await a.request('/s/'+siteA+'/admin/website',{csrf_token:uxToken,group:'about','page_meta[title]':'Published about '+suffix,'page_meta[meta_description]':'Saved search description','page_meta[is_active]':'1'});
assert.equal((await visitor.request('/s/'+siteA+'/about')).status,200);
await a.request('/s/'+siteA+'/admin/website',{csrf_token:uxToken,group:'navigation',nav_cta_text:'',nav_cta_url:'/contact'});
const emptyCTA=(await visitor.request('/s/'+siteA)).text.match(/<nav[\s\S]*?<\/nav>/)?.[0]||'';assert.ok(!emptyCTA.includes('btn btn-primary btn-sm px-3'),'Empty CTA is hidden');
const overview=await a.request('/s/'+siteA+'/admin/dashboard');assert.ok(!overview.text.includes('12847'));assert.ok(!overview.text.includes('Project Gallery'));assert.ok(!overview.text.includes('Categories'));assert.ok(overview.text.includes('Unread messages'));
console.log('UX checks passed: editor groups, canonical routes, retained drafts, independent settings, unified footer, publication and owner preview.');

console.log(JSON.stringify({passed:true,sites:slugs,checks:['signup','login','identity editing','page text editing','tenant data isolation','session isolation','CSRF rejection','anonymous rejection','missing tenant','disabled bypass','public page image URLs','image upload and public logo','spoofed image rejection','media isolation','section visibility','section ordering','22 dashboard screens and inline JS','theme publishing','footer publishing']},null,2));
