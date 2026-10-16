import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
const base=process.env.TEST_BASE_URL||'http://localhost:8765';
const accounts=JSON.parse(await readFile(new URL('../.local/builder-test-sites.json',import.meta.url),'utf8'));
class Client {
 cookies=new Map();
 async request(path,data,follow=true){
  const response=await fetch(new URL(path,base),{method:data?'POST':'GET',headers:{Cookie:[...this.cookies].map(([k,v])=>k+'='+v).join('; '),...(data?{'Content-Type':'application/x-www-form-urlencoded'}:{})},body:data?new URLSearchParams(data):undefined,redirect:'manual'});
  for(const raw of response.headers.getSetCookie()){const pair=raw.split(';')[0],eq=pair.indexOf('=');this.cookies.set(pair.slice(0,eq),pair.slice(eq+1));}
  if(follow&&response.status>=300&&response.status<400)return this.request(response.headers.get('location'));
  const text=await response.text();assert.ok(!/Fatal error|Warning:|Uncaught /.test(text),text.slice(0,1000));return {status:response.status,text,headers:response.headers};
 }
}
const token=html=>html.match(/name="csrf_token" value="([^"]+)"/)?.[1]||html.match(/name="csrf-token" content="([^"]+)"/)?.[1];
const a=new Client(),b=new Client(),visitor=new Client();
for(const [client,account] of [[a,accounts[0]],[b,accounts[1]]]){
 const login=await client.request('/s/'+account.slug+'/admin/login');assert.equal(login.status,200);
 const result=await client.request('/s/'+account.slug+'/admin/login',{csrf_token:token(login.text),username:account.email,password:account.password});
 assert.equal(result.status,200);assert.ok(result.text.includes('data-owner-edit'),'Owner sees floating edit control');
}
const site='/s/'+accounts[0].slug;
const live=await visitor.request(site);assert.equal(live.status,200);assert.ok(!live.text.includes('DRAFT_SECRET_HTTP'));assert.ok(!live.text.includes('owner-edit'));assert.ok(!live.text.includes('wfe-config'));assert.ok(!live.text.includes('section-projects'));
const draft=await a.request(site+'/edit');assert.equal(draft.status,200);assert.ok(draft.text.includes('DRAFT_SECRET_HTTP'));assert.ok(draft.text.includes('wfe-config'));
assert.equal((await visitor.request(site+'/edit')).status,403);
assert.equal((await b.request(site+'/edit')).status,403);
assert.ok(!(await visitor.request(site+'?preview=1&draft=1')).text.includes('DRAFT_SECRET_HTTP'));
const media=await a.request(site+'/admin/media');const csrf=token(media.text);assert.ok(csrf);
assert.equal((await a.request(site+'/admin/website',{group:'identity',site_title:'FORBIDDEN'})).status,403,'Missing CSRF rejected');
assert.equal((await a.request(site+'/admin/website',{csrf_token:csrf,group:'identity',site_title:'FORBIDDEN'})).status,409,'Legacy writes blocked');
assert.ok(!(await visitor.request(site)).text.includes('FORBIDDEN'));
for(const path of ['/about','/services','/contact','/portfolio'])assert.equal((await visitor.request(site+path)).status,200);
const privateProject=await visitor.request(site+'/portfolio/private-project');assert.equal(privateProject.status,404);assert.ok(!privateProject.text.includes('PRIVATE PROJECT'));assert.ok(!privateProject.text.includes(accounts[0].email),'Compatibility error page does not publish account email');
const publicProject=await visitor.request('/s/'+accounts[1].slug+'/portfolio/public-project');assert.equal(publicProject.status,200);assert.ok(publicProject.text.includes('Published canonical profile'),'Project detail reads canonical published profile');
assert.ok((await visitor.request('/s/'+accounts[1].slug)).text.includes('Public project'),'Published collection item appears without page republish');
const forged=new Client();forged.cookies.set('portfolio_'+accounts[0].site_id,b.cookies.get('portfolio_'+accounts[1].site_id)||'invalid');
assert.equal((await forged.request(site+'/edit')).status,403,'Forged cross-tenant cookie rejected');
// Existing users retain their routes and dashboard.
const legacy=new Client();const legacyLogin=await legacy.request('/s/demo/admin/login');
await legacy.request('/s/demo/admin/login',{csrf_token:token(legacyLogin.text),username:'admin',password:'password'});
for(const route of ['dashboard','website','hero','about','timeline','services','services/features','services/process','services/pricing','services/faqs','footer','navigation','media','users','theme','pages','projects','testimonials','skills','featured-projects','services/preview','home/page-cta','messages']){
 assert.equal((await legacy.request('/s/demo/admin/'+route)).status,200,route);
}
for(const route of ['','/about','/services','/contact','/portfolio'])assert.equal((await visitor.request('/s/demo'+route)).status,200,'Legacy '+route);
// Signup still works, URL stays canonical /s/{slug}; reserved names are rejected.
const signupClient=new Client();const signup=await signupClient.request('/signup');
const reserved=await signupClient.request('/signup',{csrf_token:token(signup.text),slug:'account',name:'Reserved',email:'reserved@example.com',password:'not-used-password-123'});
assert.equal(reserved.status,200);assert.ok(!reserved.headers.has('location'));
const slug='builder-signup-'+Date.now().toString(36);
const signupResult=await signupClient.request('/signup',{csrf_token:token(signup.text),slug,name:'Fresh owner',email:slug+'@example.com',password:'temporary-test-password-123'},false);
assert.equal(signupResult.status,302);assert.ok(signupResult.headers.get('location').includes('/s/'+slug+'/admin/login'));
const fresh=await visitor.request('/s/'+slug);assert.equal(fresh.status,200);assert.ok(fresh.text.includes('Fresh owner'));assert.ok(!fresh.text.includes('section-projects'));
console.log('PASS: HTTP owner/visitor isolation, draft privacy, CSRF, blocked legacy writes, canonical URLs, signup, 23 legacy dashboard routes and preserved public URLs.');
