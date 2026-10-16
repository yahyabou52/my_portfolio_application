(() => {
'use strict';
const structure=window.wfeStructure,c=structure.config;
const controls=document.createElement('div');controls.className='wfe-section-tools';controls.hidden=true;controls.setAttribute('role','toolbar');controls.setAttribute('aria-label','Selected section');
document.body.append(controls);
let selected=null;
function model(node){return c.sections.find(s=>s.id===node.dataset.sectionId||s.id===node.dataset.sectionKey||c.sectionKeys[s.type]===node.dataset.sectionKey);}
function collection(s,node){if(c.collectionTypes.includes(s.type))return s.type;const key=node?.dataset.sectionKey||s.id;return Object.keys(c.sectionKeys).find(t=>c.sectionKeys[t]===key&&c.collectionTypes.includes(t));}
function button(label,action){const b=document.createElement('button');b.type='button';b.textContent=label;b.onclick=action;controls.append(b);return b;}
function position(){if(!selected||!selected.node.isConnected){controls.hidden=true;return;}const r=selected.node.getBoundingClientRect();if(r.bottom<30||r.top>innerHeight){controls.hidden=true;return;}controls.hidden=false;if(controls.dataset.pinned==='true'){window.wfePanels?.place('section');return;}controls.style.left=Math.max(8,Math.min(r.left+12,innerWidth-controls.offsetWidth-8))+'px';controls.style.top=Math.max((document.querySelector('.navbar')?.getBoundingClientRect().bottom||72)+8,Math.min(r.top+8,innerHeight-controls.offsetHeight-8))+'px';}
function settings(s){
 structure.open('sections');
 const rows=[...document.querySelectorAll('[data-wfe-section]')];rows.forEach(row=>row.classList.toggle('wfe-selected',row.dataset.wfeSection===s.id));
 const row=rows.find(row=>row.dataset.wfeSection===s.id);row?.scrollIntoView({block:'center'});row?.querySelector('button')?.focus({preventScroll:true});
 const context=document.getElementById('wfe-section-context');context.hidden=false;context.textContent='Editing '+s.type+'. Your changes save to the page draft.';
 let fields=document.getElementById('wfe-quick-fields');if(!fields){fields=document.createElement('div');fields.id='wfe-quick-fields';context.after(fields);}fields.replaceChildren();
 const node=selected?.node,seen=new Set();
 for(const field of node?.querySelectorAll('[data-edit-field]')||[]){
  if(field.closest('[data-collection-item]')||seen.has(field.dataset.editField))continue;seen.add(field.dataset.editField);
  if(field.dataset.editKind==='text'){
   const label=document.createElement('label');label.textContent=field.dataset.editField.split('.').pop().replaceAll('_',' ');
   const input=document.createElement('textarea');input.rows=2;input.value=field.innerText;input.oninput=()=>window.wfeEditor.setField(field.dataset.editField,input.value);label.append(input);fields.append(label);
  }else{
   const action=document.createElement('button');action.type='button';action.textContent=field.dataset.editKind==='image'?'Replace section image':'Edit section button';
   action.onclick=()=>{document.getElementById('wfe-structure').close();field.click();};fields.append(action);
  }
 }
 context.scrollIntoView({block:'start'});
}
function select(node){const s=model(node);if(!s)return;selected={node,s};controls.replaceChildren();
 const grip=button('⠿',()=>{});grip.className='wfe-panel-grip';grip.dataset.wfePanel='section';grip.setAttribute('aria-label','Move section toolbar');grip.title='Drag to move. Arrow keys move; Home resets position.';
 const label=document.createElement('span');label.textContent=s.type;controls.append(label);
 const move=button('Move block',()=>{});move.dataset.wfeMove=s.id;move.title='Drag to move. Click to choose a position, or use the arrow keys.';
 button('Section settings',()=>settings(s));
 const type=collection(s,node);if(type)button('Add / edit '+type,()=>window.wfeCollections.show(type));
 else button('Website details',()=>{location.href=c.api.replace(/editor-api\/$/,'website-settings');});
 button('Hide section',()=>structure.apply({id:s.id,operation:'visibility'}));
 button('Add section',()=>{structure.open('sections');document.getElementById('wfe-section-type').scrollIntoView({block:'center'});document.getElementById('wfe-section-type').focus();});
 position();
}
document.addEventListener('pointerover',e=>{if(window.wfeDragging||window.wfePanelDragging)return;const node=e.target.closest('main section');if(node)select(node);else if(!e.target.closest('.wfe-section-tools'))controls.hidden=true;});
document.addEventListener('focusin',e=>{const node=e.target.closest('main section');if(node)select(node);});
document.addEventListener('keydown',e=>{if(e.key==='Escape')controls.hidden=true;});
addEventListener('scroll',position,{passive:true});addEventListener('resize',position);
document.addEventListener('wfe:refreshed',()=>{selected=null;controls.hidden=true;document.getElementById('wfe-quick-fields')?.remove();document.getElementById('wfe-section-context').hidden=true;fillPages();});
const pageSelect=document.getElementById('wfe-page-select'),title=document.getElementById('wfe-page-title'),visible=document.getElementById('wfe-page-visible');
function showPage(){const p=c.pages[pageSelect.value];title.value=p.title;visible.checked=p.visible;visible.disabled=pageSelect.value==='home';document.getElementById('wfe-page-note').textContent=p.exists?'Edit this page to organize its sections and content.':'Save settings to configure this existing website route as its own editable page.';}
function fillPages(){const value=pageSelect.value||c.page;pageSelect.replaceChildren();for(const [key,p] of Object.entries(c.pages))pageSelect.add(new Option(key.charAt(0).toUpperCase()+key.slice(1)+(p.exists?'':' · Configure'),key));pageSelect.value=value;showPage();}
fillPages();pageSelect.onchange=showPage;
document.getElementById('wfe-pages').onclick=()=>structure.open('pages');
document.getElementById('wfe-save-page').onclick=()=>structure.apply({operation:'page',page:pageSelect.value,title:title.value,visible:visible.checked});
document.getElementById('wfe-open-page').onclick=async()=>{try{await window.wfeEditor.flush();const p=c.pages[pageSelect.value];if(!p.exists){document.getElementById('wfe-page-note').textContent='Save page settings first, then open the page.';return;}location.href=p.url;}catch(e){document.getElementById('wfe-page-note').textContent=e.message;}};
})();
