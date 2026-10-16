(() => {
'use strict';
const structure=window.wfeStructure,c=structure.config;
const marker=document.createElement('div');marker.className='wfe-drop-marker';marker.hidden=true;marker.textContent='Drop block here';document.body.append(marker);
const announcement=document.createElement('span');announcement.className='wfe-sr-only';announcement.setAttribute('role','status');document.body.append(announcement);
const dialog=document.createElement('dialog');dialog.className='wfe-dialog';dialog.setAttribute('aria-label','Move block');
dialog.innerHTML='<h2>Move block</h2><p class="wfe-help">Choose its place in the page. Your layout stays responsive.</p><label>Place<select id="wfe-move-position"><option value="before">Before</option><option value="after">After</option></select></label><label>Block<select id="wfe-move-target"></select></label><button type="button" id="wfe-move-apply">Move block here</button><form method="dialog"><button>Cancel</button></form>';
document.body.append(dialog);
let drag=null,frame=0,movingId=null,suppressClick=false;
function section(node){return c.sections.find(s=>s.id===node.dataset.sectionId||s.id===node.dataset.sectionKey||c.sectionKeys[s.type]===node.dataset.sectionKey);}
function candidates(){return [...document.querySelectorAll('main section')].map(node=>({node,s:section(node)})).filter(x=>x.s&&x.s.id!==drag.id);}
function locate(){
 let best=null;
 for(const {node,s} of candidates()){const r=node.getBoundingClientRect();for(const [position,y] of [['before',r.top],['after',r.bottom]]){const distance=Math.abs(drag.y-y);if(!best||distance<best.distance)best={target:s.id,position,y,distance};}}
 drag.target=best;marker.hidden=!best;if(best)marker.style.top=Math.max(4,Math.min(innerHeight-28,best.y))+'px';
}
function tick(){if(!drag?.active)return;const speed=drag.y<90?-14:drag.y>innerHeight-90?14:0;if(speed)window.scrollBy({top:speed,behavior:'instant'});locate();frame=requestAnimationFrame(tick);}
function cleanup(){cancelAnimationFrame(frame);marker.hidden=true;document.body.classList.remove('wfe-moving');window.wfeDragging=false;drag=null;}
async function place(id,target,position){announcement.textContent='Moving block…';const saved=await structure.apply({operation:'place',id,target,position});announcement.textContent=saved?'Block position updated.':'The block could not be moved. Check the save status and try again.';}
document.addEventListener('pointerdown',e=>{
 const handle=e.target.closest('[data-wfe-move]');if(!handle||e.button!==0)return;
 suppressClick=false;drag={id:handle.dataset.wfeMove,x:e.clientX,y:e.clientY,startY:e.clientY,startX:e.clientX,active:false,pointer:e.pointerId,handle};handle.setPointerCapture(e.pointerId);window.wfeDragging=true;
});
document.addEventListener('pointermove',e=>{
 if(!drag||e.pointerId!==drag.pointer)return;drag.y=e.clientY;
 if(!drag.active&&Math.hypot(e.clientX-drag.startX,e.clientY-drag.startY)>6){drag.active=true;document.body.classList.add('wfe-moving');frame=requestAnimationFrame(tick);}
 if(drag.active)e.preventDefault();
},{passive:false});
document.addEventListener('pointerup',e=>{
 if(!drag||e.pointerId!==drag.pointer)return;const d=drag;
 if(d.active){locate();suppressClick=true;setTimeout(()=>suppressClick=false,0);}
 cleanup();if(d.active&&d.target)place(d.id,d.target.target,d.target.position);
});
document.addEventListener('pointercancel',cleanup);
document.addEventListener('keydown',e=>{
 if(e.key==='Escape'&&drag){suppressClick=true;cleanup();announcement.textContent='Move cancelled.';return;}
 const handle=e.target.closest('[data-wfe-move]');if(!handle||!['ArrowUp','ArrowDown'].includes(e.key))return;
 e.preventDefault();structure.apply({id:handle.dataset.wfeMove,operation:'move',direction:e.key==='ArrowUp'?'up':'down'});
});
document.addEventListener('click',e=>{
 const handle=e.target.closest('[data-wfe-move]');if(!handle)return;e.preventDefault();
 if(suppressClick)return;movingId=handle.dataset.wfeMove;
 const select=document.getElementById('wfe-move-target');select.replaceChildren();
 for(const s of c.sections)if(s.id!==movingId)select.add(new Option(s.type+(s.visible?'':' (hidden)'),s.id));
 document.getElementById('wfe-move-apply').disabled=!select.options.length;dialog.showModal();
});
document.getElementById('wfe-move-apply').onclick=async()=>{const id=movingId,target=document.getElementById('wfe-move-target').value,position=document.getElementById('wfe-move-position').value;dialog.close();await place(id,target,position);};
document.addEventListener('wfe:refreshed',cleanup);
})();
