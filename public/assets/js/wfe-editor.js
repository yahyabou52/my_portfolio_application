(() => {
'use strict';
const config=JSON.parse(document.getElementById('wfe-config').textContent);
let revision=config.revision,values={...config.values},pending=new Map(),inFlight=null,paused=false,timer,retries=0;
const undo=[],redo=[],status=document.getElementById('wfe-status'),publication=document.getElementById('wfe-publication');
const retry=document.getElementById('wfe-retry'),reload=document.getElementById('wfe-reload');
const setStatus=(text,error=false)=>{status.textContent=text;status.dataset.error=String(error);};
const dirty=()=>{publication.textContent='Unpublished page changes';};
publication.textContent=config.published?'Published':'Unpublished page changes';setStatus(config.published?'Published':'Saved draft');
const schemaNodes=()=>[...document.querySelectorAll('[data-edit-field]')];
function writeText(node,text){
 const textNodes=[...node.childNodes].filter(n=>n.nodeType===Node.TEXT_NODE);
 if(node.querySelector('i')&&textNodes.length){textNodes.forEach((n,i)=>n.textContent=i===textNodes.length-1?' '+text:'');}
 else node.textContent=text;
}
function updateDOM(id,value,force=false){
 schemaNodes().filter(n=>n.dataset.editField===id).forEach(node=>{
  if(node.dataset.editKind==='image'){node.src=value.startsWith('http')?value:new URL('/'+value,location.origin).href;}
  else if(node!==document.activeElement||force)writeText(node,value);
 });
 document.querySelectorAll('[data-edit-link]').forEach(node=>{if(node.dataset.editLink===id)node.href=value;});
}
function buttons(){document.getElementById('wfe-undo').disabled=!undo.length;document.getElementById('wfe-redo').disabled=!redo.length;}
function change(id,value,record=true){
 const previous=values[id]??'';if(previous===value)return;
 if(record){undo.push({id,previous,value});if(undo.length>100)undo.shift();redo.length=0;}
 values[id]=value;pending.set(id,value);updateDOM(id,value,!record);dirty();setStatus('Unsaved changes');buttons();
 clearTimeout(timer);timer=setTimeout(()=>flush().catch(()=>{}),800);
}
async function request(action,body){
 const response=await fetch(config.api+action,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':config.csrf},body:JSON.stringify({...body,revision})});
 const result=await response.json().catch(()=>({error:'Unexpected response. Please reload.'}));
 if(!response.ok){const error=new Error(result.error||'Request failed.');error.status=response.status;throw error;}return result;
}
async function flush(){
 clearTimeout(timer);if(paused)throw new Error('Reload the latest draft before saving.');
 if(inFlight){await inFlight;return pending.size?flush():undefined;}
 if(!pending.size)return;
 const batch=new Map(pending);pending.clear();setStatus('Saving...');
 inFlight=(async()=>{
  try{
   const result=await request('save',{patches:Object.fromEntries(batch)});revision=result.revision;retries=0;retry.hidden=true;setStatus('Saved draft');dirty();
  }catch(error){
   for(const [key,value] of batch)if(!pending.has(key))pending.set(key,value);
   if(error.status===409){paused=true;reload.hidden=false;setStatus(error.message,true);}
   else{setStatus(error.message,true);retry.hidden=false;if((!error.status||error.status===429||error.status>=500)&&retries<3){retries++;timer=setTimeout(()=>flush().catch(()=>{}),800*2**retries);}}
   throw error;
  }finally{inFlight=null;}
 })();await inFlight;if(pending.size)return flush();
}
retry.addEventListener('click',()=>{retries=0;flush().catch(()=>{});});
reload.addEventListener('click',()=>{if(confirm('Reload the latest saved draft? Unsaved local edits will be lost.')){pending.clear();location.reload();}});
window.addEventListener('beforeunload',event=>{if(pending.size||inFlight){event.preventDefault();event.returnValue='';}});
for(const node of schemaNodes()){
 const kind=node.dataset.editKind;
 node.setAttribute('tabindex','0');
 if(kind==='text'){
  node.setAttribute('contenteditable','true');
  node.addEventListener('beforeinput',event=>{if(event.inputType.startsWith('format'))event.preventDefault();});
  node.addEventListener('drop',event=>{event.preventDefault();document.execCommand('insertText',false,event.dataTransfer.getData('text/plain'));});node.setAttribute('spellcheck','true');node.setAttribute('aria-label','Edit '+node.dataset.editField.replaceAll(/[.:_]/g,' '));
  node.addEventListener('paste',event=>{event.preventDefault();const text=event.clipboardData.getData('text/plain');document.execCommand('insertText',false,text);});
  node.addEventListener('input',()=>change(node.dataset.editField,node.innerText));
 }else{
  node.setAttribute('role','button');node.setAttribute('aria-label',kind==='image'?'Replace image':'Edit button');
  const activate=event=>{event.preventDefault();event.stopPropagation();if(kind==='image'){imageTarget=node;document.getElementById('wfe-image-file').click();}else openButton(node);};
  node.addEventListener('click',activate);node.addEventListener('keydown',event=>{if(event.key==='Enter'||event.key===' '){activate(event);}});
 }
}
document.getElementById('wfe-undo').addEventListener('click',()=>{const edit=undo.pop();if(edit){redo.push(edit);change(edit.id,edit.previous,false);buttons();}});
document.getElementById('wfe-redo').addEventListener('click',()=>{const edit=redo.pop();if(edit){undo.push(edit);change(edit.id,edit.value,false);buttons();}});
document.addEventListener('keydown',event=>{if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='z'&&!event.target.closest('.wfe-dialog')){event.preventDefault();document.getElementById(event.shiftKey?'wfe-redo':'wfe-undo').click();}});
document.getElementById('wfe-publish').addEventListener('click',async event=>{
 event.target.disabled=true;try{await flush();const result=await request('publish',{});revision=result.revision;publication.textContent='Published';setStatus('Published');}catch(error){setStatus(error.message,true);}finally{event.target.disabled=false;}
});
document.getElementById('wfe-discard').addEventListener('click',async()=>{
 if(!confirm('Discard all unpublished page changes and restore the published website?'))return;
 clearTimeout(timer);try{if(inFlight)await inFlight;const result=await request('discard',{});revision=result.revision;pending.clear();location.reload();}catch(error){setStatus(error.message,true);}
});
const toggle=document.getElementById('wfe-toggle'),actions=document.getElementById('wfe-actions');
if(matchMedia('(max-width:600px)').matches){actions.hidden=true;toggle.setAttribute('aria-expanded','false');}
toggle.addEventListener('click',()=>{actions.hidden=!actions.hidden;toggle.setAttribute('aria-expanded',String(!actions.hidden));});
const dialog=document.getElementById('wfe-field-dialog');let buttonTarget=null,imageTarget=null;
function openButton(node){
 buttonTarget=node;document.getElementById('wfe-label').value=values[node.dataset.editField]??node.innerText.trim();
 document.getElementById('wfe-url').value=node.href;document.getElementById('wfe-dialog-error').textContent='';dialog.showModal();document.getElementById('wfe-label').focus();
}
document.getElementById('wfe-apply-button').addEventListener('click',()=>{
 const label=document.getElementById('wfe-label').value,url=document.getElementById('wfe-url').value.trim();
 if(url&&!/^(https?:|mailto:|tel:)/i.test(url)){document.getElementById('wfe-dialog-error').textContent='Use an http, https, mailto or tel link.';return;}
 change(buttonTarget.dataset.editField,label);change(buttonTarget.dataset.editLink,url);dialog.close();buttonTarget.focus();
});
document.getElementById('wfe-image-file').addEventListener('change',async event=>{
 const file=event.target.files[0];if(!file||!imageTarget)return;setStatus('Uploading image...');
 const target=imageTarget,body=new FormData();body.append('file',file);
 try{const response=await fetch(config.api+'upload',{method:'POST',headers:{'X-CSRF-Token':config.csrf},body});const result=await response.json();if(!response.ok)throw new Error(result.error||'Upload failed.');change(target.dataset.editField,result.path);target.src=result.url;}
 catch(error){setStatus(error.message,true);}finally{event.target.value='';}
});
})();
