import{c as i,o as A,b as o,d as n,e,f as d,u as l,g as c,j as p,F as P,h as x,l as u,L as w,n as _,A as M,B as z,t as g,G as C,H as B,I as L}from"../admin/admin.js";import{D as W}from"../download/download-429eaf12.js";import{S as j}from"../star/star-17cb9fd4.js";/**
 * @license lucide-vue-next v1.0.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const v=i("circle-check",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]]);/**
 * @license lucide-vue-next v1.0.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const q=i("git-branch",[["path",{d:"M15 6a9 9 0 0 0-9 9V3",key:"1cii5b"}],["circle",{cx:"18",cy:"6",r:"3",key:"1h7g24"}],["circle",{cx:"6",cy:"18",r:"3",key:"fqmcym"}]]);/**
 * @license lucide-vue-next v1.0.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const N=i("send",[["path",{d:"M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z",key:"1ffxy3"}],["path",{d:"m21.854 2.147-10.94 10.939",key:"12cjpa"}]]);/**
 * @license lucide-vue-next v1.0.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const S=i("sparkles",[["path",{d:"M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z",key:"1s2grr"}],["path",{d:"M20 2v4",key:"1rf3ol"}],["path",{d:"M22 4h-4",key:"gwowj6"}],["circle",{cx:"4",cy:"20",r:"2",key:"6kqj1y"}]]);/**
 * @license lucide-vue-next v1.0.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const V=i("webhook",[["path",{d:"M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c.01-.7.2-1.4.57-2",key:"q3hayz"}],["path",{d:"m6 17 3.13-5.78c.53-.97.1-2.18-.5-3.1a4 4 0 1 1 6.89-4.06",key:"1go1hn"}],["path",{d:"m12 6 3.13 5.73C15.66 12.7 16.9 13 18 13a4 4 0 0 1 0 8",key:"qlwsc0"}]]);/**
 * @license lucide-vue-next v1.0.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const D=i("zap",[["path",{d:"M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z",key:"1xq2db"}]]),F={class:"wpask-content-inner"},E={class:"wpask-page-header"},G={key:0},I=["href"],U={key:0,class:"wpask-addons-upgrade-banner"},H={class:"wpask-addons-upgrade-banner-icon"},T={class:"wpask-addons-upgrade-banner-body"},X=["href"],Y={key:1,class:"wpask-addons-loading"},Z={key:2,class:"wpask-addons-grid"},$={class:"wpask-addon-card-top"},J={key:0,class:"wpask-addon-lock-badge"},K={key:0,class:"wpask-addon-pro-badge"},O={class:"wpask-addon-card-inner"},Q={style:{"min-width":"0"}},R={class:"wpask-addon-name"},aa={class:"wpask-addon-desc"},sa={class:"wpask-addon-action",style:{"flex-shrink":"0"}},ea={key:0,class:"wpask-installed-badge"},oa={key:1,class:"wpask-included-badge"},na=["href"],ca={__name:"Addons",setup(ta){const y=u(!0),h=u([]),k=u(!1),t=u("https://wpask.io/pricing"),m={mail:B,"git-branch":q,download:W,"mouse-pointer-click":L,webhook:V,send:N,zap:D,star:j};function f(r){return m[r]||C}async function b(){const r=window.WPAskAdminConfig||{};k.value=!!r.is_pro,t.value=r.upgrade_url||t.value;try{const a=await fetch(`${r.api_url}/addons`,{headers:{"X-WP-Nonce":r.nonce}});if(!a.ok)throw new Error("Failed to load add-ons");const s=await a.json();k.value=!!s.is_pro,t.value=s.upgrade_url||t.value,h.value=s.addons||[]}catch(a){console.error(a),h.value=[]}finally{y.value=!1}}return A(b),(r,a)=>(o(),n("div",F,[e("div",E,[a[1]||(a[1]=e("div",null,[e("h1",{class:"wpask-page-title"},"Add-ons"),e("p",{class:"wpask-page-subtitle"},"Extend WPAsk with integrations and advanced features.")],-1)),k.value?p("",!0):(o(),n("div",G,[e("a",{href:t.value,target:"_blank",rel:"noopener noreferrer",class:"wpask-btn wpask-btn-primary"},[d(l(S)),a[0]||(a[0]=c(" Upgrade to Pro ",-1))],8,I)]))]),k.value?p("",!0):(o(),n("div",U,[e("div",H,[d(l(w))]),e("div",T,[a[2]||(a[2]=e("strong",null,"You're using WPAsk Lite.",-1)),a[3]||(a[3]=c(" Unlock heatmaps, webhooks, integrations, and more with ",-1)),e("a",{href:t.value,target:"_blank",rel:"noopener noreferrer"},"WPAsk Pro",8,X),a[4]||(a[4]=c(". ",-1))])])),y.value?(o(),n("div",Y,"Loading add-ons…")):(o(),n("div",Z,[(o(!0),n(P,null,x(h.value,s=>(o(),n("div",{key:s.id,class:_(["wpask-addon-card",{"wpask-addon-card--locked":s.locked,"wpask-addon-card--pro":s.tier==="pro"}])},[e("div",$,[e("div",{class:_(["wpask-addon-icon",{"wpask-addon-icon--locked":s.locked}])},[(o(),M(z(f(s.icon)))),s.locked?(o(),n("span",J,[d(l(w))])):p("",!0)],2),s.tier==="pro"?(o(),n("span",K,"Pro")):p("",!0)]),e("div",O,[e("div",Q,[e("div",R,g(s.name),1),e("p",aa,g(s.description),1)]),e("div",sa,[s.active?(o(),n("span",ea,[d(l(v)),a[5]||(a[5]=c(" Active ",-1))])):s.installed&&!s.locked?(o(),n("span",oa,[d(l(v)),a[6]||(a[6]=c(" Included ",-1))])):s.locked?(o(),n("a",{key:2,href:s.upgrade_url||t.value,target:"_blank",rel:"noopener noreferrer",class:"wpask-btn wpask-btn-secondary wpask-btn-sm wpask-addon-upgrade-btn"},[d(l(w)),a[7]||(a[7]=c(" Get Pro ",-1))],8,na)):p("",!0)])])],2))),128))]))]))}};export{ca as default};
