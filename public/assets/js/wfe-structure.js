(() => {
'use strict';
const c=JSON.parse(document.getElementById('wfe-config').textContent),dialog=document.getElementById('wfe-structure'),list=document.getElementById('wfe-section-list');
document.getElementById('wfe-sections').onclick=()=>dialog.showModal();
const error=document.getElementById('wfe-structure-error');
async function apply(data){
 try{await window.wfeEditor.flush();await window.wfeEditor.action('structure',{page:c.page,...data});location.reload();}
 catch(e){error.textContent=e.message;}
}
for(const [index,s] of c.sections.entries()){
 const row=document.createElement('div');row.className='wfe-section-row';
 const title=document.createElement('strong');title.textContent=s.type+' — '+(s.visible?'Shown':'Hidden');row.append(title);
 for(const [label,data] of [['↑',{operation:'move',direction:'up'}],['↓',{operation:'move',direction:'down'}],[s.visible?'Hide':'Show',{operation:'visibility'}],['Remove',{operation:'remove'}]]){
  const button=document.createElement('button');button.type='button';button.textContent=label;button.setAttribute('aria-label',label+' '+s.type);button.onclick=()=>apply({id:s.id,...data});row.append(button);
 }
 const variants=c.variants[s.type]||[];
 if(variants.length>1){const select=document.createElement('select');select.setAttribute('aria-label',s.type+' layout');for(const value of variants){const option=new Option(value,value,value===s.variant,value===s.variant);select.add(option);}select.onchange=()=>apply({id:s.id,operation:'variant',variant:select.value});row.append(select);}
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
