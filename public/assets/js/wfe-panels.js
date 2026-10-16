(() => {
'use strict';
const panels={editor:document.querySelector('.wfe-toolbar'),section:document.querySelector('.wfe-section-tools')},positions={};
const key='wfe-panel-positions:'+document.documentElement.dataset.siteId;
try{const saved=JSON.parse(localStorage.getItem(key)||'{}');for(const name of Object.keys(panels))if(Number.isFinite(saved[name]?.x)&&Number.isFinite(saved[name]?.y))positions[name]=saved[name];}catch{}
function save(){try{localStorage.setItem(key,JSON.stringify(positions));}catch{}}
function place(name){
 const panel=panels[name],p=positions[name];if(!p)return;panel.dataset.pinned='true';if(panel.hidden)return;
 const r=panel.getBoundingClientRect(),x=Math.max(8,Math.min(p.x,innerWidth-r.width-8)),y=Math.max(8,Math.min(p.y,innerHeight-r.height-8));
 Object.assign(panel.style,{left:x+'px',top:y+'px',right:'auto',bottom:'auto',transform:'none'});
}
function reset(name){delete positions[name];delete panels[name].dataset.pinned;for(const prop of ['left','top','right','bottom','transform'])panels[name].style.removeProperty(prop);save();window.dispatchEvent(new Event('resize'));}
window.wfePanels={place};
for(const name of Object.keys(panels)){place(name);new ResizeObserver(()=>place(name)).observe(panels[name]);}
addEventListener('resize',()=>Object.keys(panels).forEach(place));
let drag=null;
document.addEventListener('pointerdown',e=>{
 const handle=e.target.closest('[data-wfe-panel]');if(!handle||e.button!==0)return;
 const name=handle.dataset.wfePanel,r=panels[name].getBoundingClientRect();
 drag={name,pointer:e.pointerId,startX:e.clientX,startY:e.clientY,x:r.left,y:r.top,previous:positions[name]?{...positions[name]}:null};
 handle.setPointerCapture(e.pointerId);window.wfePanelDragging=true;e.preventDefault();
});
document.addEventListener('pointermove',e=>{if(!drag||drag.pointer!==e.pointerId)return;positions[drag.name]={x:drag.x+e.clientX-drag.startX,y:drag.y+e.clientY-drag.startY};place(drag.name);e.preventDefault();},{passive:false});
function finish(){if(!drag)return;drag=null;window.wfePanelDragging=false;save();}
function cancel(){if(!drag)return;const d=drag;if(d.previous){positions[d.name]=d.previous;place(d.name);}else reset(d.name);finish();}
document.addEventListener('pointerup',finish);document.addEventListener('pointercancel',cancel);addEventListener('blur',cancel);
document.addEventListener('keydown',e=>{
 if(e.key==='Escape'&&drag){cancel();return;}
 const handle=e.target.closest('[data-wfe-panel]');if(!handle)return;const name=handle.dataset.wfePanel;
 if(e.key==='Home'){e.preventDefault();reset(name);return;}
 const delta={ArrowLeft:[-1,0],ArrowRight:[1,0],ArrowUp:[0,-1],ArrowDown:[0,1]}[e.key];if(!delta)return;
 e.preventDefault();const r=panels[name].getBoundingClientRect(),step=e.shiftKey?40:10;positions[name]={x:r.left+delta[0]*step,y:r.top+delta[1]*step};place(name);save();
});
document.addEventListener('dblclick',e=>{const handle=e.target.closest('[data-wfe-panel]');if(handle)reset(handle.dataset.wfePanel);});
})();
