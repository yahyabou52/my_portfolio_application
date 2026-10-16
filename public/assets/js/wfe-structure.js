(() => {
'use strict';
const c=JSON.parse(document.getElementById('wfe-config').textContent),dialog=document.getElementById('wfe-structure'),list=document.getElementById('wfe-section-list');
function tab(name){document.querySelectorAll('[data-wfe-pane]').forEach(n=>n.hidden=n.dataset.wfePane!==name);document.querySelectorAll('[data-wfe-tab]').forEach(n=>n.setAttribute('aria-pressed',String(n.dataset.wfeTab===name)));}
function open(name){tab(name);if(!dialog.open)dialog.showModal();}
document.getElementById('wfe-sections').onclick=()=>open('sections');
document.getElementById('wfe-appearance').onclick=()=>open('appearance');
document.getElementById('wfe-content').onclick=()=>window.wfeCollections.show('projects');
document.querySelectorAll('[data-wfe-tab]').forEach(n=>n.onclick=()=>tab(n.dataset.wfeTab));
const error=document.getElementById('wfe-structure-error');
async function apply(data){
 try{await window.wfeEditor.flush();await window.wfeEditor.action('structure',{page:c.page,...data});location.reload();}
 catch(e){error.textContent=e.message;}
}
for(const [index,s] of c.sections.entries()){
 const row=document.createElement('div');row.className='wfe-section-row';
 const title=document.createElement('strong');title.textContent=s.type.charAt(0).toUpperCase()+s.type.slice(1);const badge=document.createElement('span');badge.className='wfe-badge';badge.textContent=s.visible?'Visible':'Hidden';row.append(badge);row.append(title);
 for(const [label,data] of [['↑',{operation:'move',direction:'up'}],['↓',{operation:'move',direction:'down'}],[s.visible?'Hide':'Show',{operation:'visibility'}],['Remove',{operation:'remove'}]]){
  const button=document.createElement('button');button.type='button';button.textContent=label;button.setAttribute('aria-label',label+' '+s.type);if((data.direction==='up'&&index===0)||(data.direction==='down'&&index===c.sections.length-1))button.disabled=true;button.onclick=()=>apply({id:s.id,...data});row.append(button);
 }
 const variants=c.variants[s.type]||[];
 if(variants.length>1){const select=document.createElement('select');select.setAttribute('aria-label',s.type+' layout');for(const value of variants){const option=new Option(value,value,value===s.variant,value===s.variant);select.add(option);}select.onchange=()=>apply({id:s.id,operation:'variant',variant:select.value});row.append(select);}
 if(Object.hasOwn(c.collectionCounts,s.type)&&c.collectionCounts[s.type]===0){const add=document.createElement('button');add.type='button';add.textContent='Add your first '+(s.type==='experience'?'experience item':s.type.replace(/s$/,''));add.onclick=()=>{dialog.close();window.wfeCollections.show(s.type);};row.append(add);}
 list.append(row);
}
const add=document.getElementById('wfe-add-section');
if(c.compatibility)add.hidden=true;else for(const type of c.types){const option=new Option(type,type);document.getElementById('wfe-section-type').add(option);}
add.onclick=()=>apply({operation:'add',type:document.getElementById('wfe-section-type').value});
document.getElementById('wfe-save-theme').onclick=()=>apply({operation:'theme',primary_color:document.getElementById('wfe-color').value,background_color:document.getElementById('wfe-background').value,font:document.getElementById('wfe-font').value});
const preview=document.getElementById('wfe-preview-dialog'),frame=document.getElementById('wfe-preview-frame');
document.getElementById('wfe-preview').onclick=async()=>{try{await window.wfeEditor.flush();frame.src=c.preview;preview.showModal();}catch(e){error.textContent=e.message;}};
document.getElementById('wfe-device').onchange=e=>{frame.style.width=e.target.value+'px';};
})();
