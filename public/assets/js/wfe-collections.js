(() => {
'use strict';
const c=JSON.parse(document.getElementById('wfe-collections-config').textContent),listDialog=document.getElementById('wfe-items'),itemDialog=document.getElementById('wfe-item'),select=document.getElementById('wfe-collection');
let type='projects',items=[],schema={},item=null;
const error=document.getElementById('wfe-item-error'),fields=document.getElementById('wfe-item-fields');
async function load(next=type){type=next;select.value=type;const response=await fetch(c.api+'collection-list?type='+encodeURIComponent(type));const data=await response.json();if(!response.ok)throw new Error(data.error);items=data.items;schema=data.schema;const list=document.getElementById('wfe-items-list');list.replaceChildren();if(!items.length){const p=document.createElement('p');p.textContent='Add your first '+(type==='experience'?'experience item':type.replace(/s$/,''))+'.';list.append(p);}for(const row of items){const button=document.createElement('button');button.type='button';button.className='wfe-item-row';button.textContent=(row.title||row.name||row.client_name)+' — '+(row.status==='published'?'Published':'Draft');button.onclick=()=>open(row);list.append(button);}}
function showError(e){document.getElementById('wfe-items-error').textContent=e.message;}
async function show(next='projects',id=null){try{await load(next);if(id){const row=items.find(i=>Number(i.id)===Number(id));if(!row)throw new Error('Item not found.');open(row);}else listDialog.showModal();}catch(e){showError(e);}}
window.wfeCollections={show};
document.querySelectorAll('[data-manage-collection]').forEach(button=>button.onclick=e=>{e.preventDefault();show(button.dataset.manageCollection);});
document.querySelectorAll('[data-collection-item]').forEach(node=>{
node.tabIndex=0;node.setAttribute('aria-label','Edit '+node.dataset.collectionType+' item');
node.addEventListener('click',e=>{e.preventDefault();e.stopPropagation();show(node.dataset.collectionType,node.dataset.collectionItem);});
node.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();show(node.dataset.collectionType,node.dataset.collectionItem);}});
});
select.onchange=()=>load(select.value).catch(showError);
document.getElementById('wfe-new-item').onclick=()=>open(null);
function open(row){
item=row;error.textContent='';fields.replaceChildren();
document.getElementById('wfe-item-title').textContent=(row?'Edit ':'Add ')+type;
document.getElementById('wfe-item-state').textContent=row?.status==='published'?'Published':'Draft';
document.getElementById('wfe-live-note').hidden=row?.status!=='published';
document.getElementById('wfe-save-item').textContent=row?.status==='published'?'Save live changes':'Publish item';
document.getElementById('wfe-delete-item').hidden=!row;
for(const [key,rule] of Object.entries(schema)){
 const [kind,max]=rule.split(':'),label=document.createElement('label');label.textContent=key.replaceAll('_',' ');
 const input=document.createElement(kind==='textarea'?'textarea':'input');input.name=key;input.dataset.kind=kind;
 let value=row?.[key]??(key==='rating'?5:key==='proficiency_level'?80:key==='sort_order'||key==='is_education'?0:'');
 if(kind==='list'){try{value=JSON.parse(value||'[]').join(', ');}catch{value='';}}
 input.value=value??'';if(kind==='number'||kind==='decimal'){input.type='number';input.min='0';input.max=max;input.step=kind==='decimal'?'0.01':'1';}else input.maxLength=Number(max);
 if(kind==='textarea')input.rows=4;
 label.append(input);
 if(kind==='image'){input.readOnly=true;const upload=document.createElement('input');upload.type='file';upload.accept='image/jpeg,image/png,image/webp,image/gif';upload.setAttribute('aria-label','Upload '+key.replaceAll('_',' '));upload.onchange=async()=>{if(!upload.files[0])return;const body=new FormData();body.append('file',upload.files[0]);try{const r=await fetch(c.api+'upload',{method:'POST',headers:{'X-CSRF-Token':c.csrf},body}),data=await r.json();if(!r.ok)throw new Error(data.error);input.value=data.path;}catch(e){error.textContent=e.message;}};label.append(upload);}
 fields.append(label);
}
itemDialog.showModal();fields.querySelector('input,textarea')?.focus();
}
async function save(status,operation='save'){
error.textContent='';const buttons=[...document.querySelectorAll('#wfe-item-form button')];buttons.forEach(b=>b.disabled=true);
try{
 const data=Object.fromEntries(new FormData(document.getElementById('wfe-item-form')));
 const response=await fetch(c.api+'collection-save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':c.csrf},body:JSON.stringify({type,id:item?.id||0,version:item?._version||'',status,operation,fields:data})});
 const result=await response.json();if(!response.ok)throw new Error(result.error);
 itemDialog.close();await load();
 const notice=document.getElementById('wfe-items-error');notice.textContent=status==='published'?'Published item saved. Refresh the website to see it.':'Draft saved. This item is hidden from visitors.';
 if(!listDialog.open)listDialog.showModal();
}catch(e){error.textContent=e.message;}finally{buttons.forEach(b=>b.disabled=false);}
}
document.getElementById('wfe-item-form').onsubmit=e=>{e.preventDefault();save('published');};
document.getElementById('wfe-draft-item').onclick=()=>save('draft');
document.getElementById('wfe-delete-item').onclick=()=>{if(confirm('Delete this item? This cannot be undone.'))save('draft','delete');};
})();
