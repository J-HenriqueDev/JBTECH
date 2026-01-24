$(function(){const v=$(".badge-notifications"),g=$(".dropdown-notifications-list .list-group"),N=$(".dropdown-notifications-all"),E=e=>{const t=e.data||{},i=t.title||"Notificação",n=t.message||"",a=t.type||"info",c=t.link||"",u=!!t.require_confirm,b=t.image_url||"",r=n.length>30?n.slice(0,30)+"...":n;let o="";if(typeof moment<"u")o=moment(e.created_at).fromNow();else{const l=new Date(e.created_at);o=l.toLocaleDateString()+" "+l.toLocaleTimeString()}const m=e.read_at?"text-muted":"",p=e.read_at?"":'<span class="badge badge-dot bg-primary me-1"></span>';let f="bx-info-circle",s="bg-label-info";return a==="success"&&(f="bx-check-circle",s="bg-label-success"),a==="warning"&&(f="bx-error",s="bg-label-warning"),a==="danger"&&(f="bx-x-circle",s="bg-label-danger"),`
      <li class="list-group-item list-group-item-action dropdown-notifications-item ${e.read_at?"":"marked-as-unread"}" data-id="${e.id}" data-title="${$("<div>").text(i).html()}" data-message="${$("<div>").text(n).html()}" data-type="${a}" data-link="${c}" data-require_confirm="${u}" data-image_url="${b}" style="cursor: pointer;">
        <div class="d-flex">
          <div class="flex-shrink-0 me-3">
            <div class="avatar">
              <span class="avatar-initial rounded-circle ${s}"><i class="bx ${f}"></i></span>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1 ${m}">${i}</h6>
            <p class="mb-0 ${m}">${$("<div>").text(r).html()}</p>
            <small class="text-muted">${o}</small>
          </div>
          <div class="flex-shrink-0 dropdown-notifications-actions">
            ${p}
          </div>
        </div>
      </li>
    `},I=`
    <li class="list-group-item list-group-item-action dropdown-notifications-item">
      <div class="d-flex justify-content-center align-items-center p-3">
        <div class="text-center">
          <i class="bx bx-bell-off fs-1 text-muted mb-2"></i>
          <p class="mb-0 text-muted">Nenhuma notificação nova</p>
        </div>
      </div>
    </li>
  `;function d(){$.ajax({url:"/notifications",method:"GET",success:function(e){const t=e.notifications,i=e.unread_count;v.text(i),g.empty(),t.length>0?(t.forEach(n=>{g.append(E(n))}),_(t)):g.append(I)},error:function(e){console.error("Failed to fetch notifications",e)}})}let h=new Set;function B(){"Notification"in window&&Notification.permission==="default"&&Notification.requestPermission()}function C(e){if(!("Notification"in window)||Notification.permission!=="granted")return;const t=e.data||{},i=t.title||"Notificação",n=t.message||"",a=t.link||"",c=new Notification(i,{body:n,icon:"/assets/img/front-pages/landing-page/jblogo_black.png"});c.onclick=function(){a&&window.open(a,"_blank")}}function _(e,t){B();const i=e.filter(n=>!n.read_at&&!h.has(n.id));i.length>0&&(C(i[0]),i.forEach(n=>h.add(n.id)))}$(document).on("click",".dropdown-notifications-item",function(e){const t=$(this),i=t.data("id"),n=t.data("title")||"Notificação",a=t.data("message")||"",c=t.data("type")||"info",u=t.data("link")||"",b=!!t.data("require_confirm");if(a&&a.length>30){const r=document.getElementById("notificationModal");if(r){document.getElementById("notificationModalTitle").textContent=n,document.getElementById("notificationModalMessage").textContent=a;const o=document.getElementById("notificationModalImage"),m=t.data("image_url")||"";o&&(m?(o.src=m,o.style.display="block"):(o.removeAttribute("src"),o.style.display="none"));const p=document.getElementById("notificationModalIcon"),f=document.getElementById("notificationModalBadge");let s="bx-info-circle",l="bg-label-info";c==="success"&&(s="bx-check-circle",l="bg-label-success"),c==="warning"&&(s="bx-error",l="bg-label-warning"),c==="danger"&&(s="bx-x-circle",l="bg-label-danger"),p.className="bx "+s,f.className="avatar-initial rounded-circle "+l;const y=document.getElementById("notificationModalLink");u?(y.href=u,y.style.display="inline-block"):y.style.display="none";const k=document.getElementById("notificationConfirmArea"),x=document.getElementById("notificationConfirmBtn"),w=document.getElementById("notificationDeclineBtn");b?(k.style.display="block",x.onclick=function(){$.ajax({url:`/notifications/${i}/ack`,method:"POST",data:{status:"accepted"},headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(){d(),bootstrap.Modal.getInstance(r).hide()}})},w.onclick=function(){$.ajax({url:`/notifications/${i}/ack`,method:"POST",data:{status:"declined"},headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(){d(),bootstrap.Modal.getInstance(r).hide()}})}):(k.style.display="none",x.onclick=null,w.onclick=null),new bootstrap.Modal(r).show()}}t.hasClass("marked-as-unread")&&$.ajax({url:`/notifications/${i}/read`,method:"POST",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(){t.removeClass("marked-as-unread"),t.find(".badge-dot").remove(),d()}})}),N.on("click",function(){$.ajax({url:"/notifications/read-all",method:"POST",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(){d()}})}),d(),setInterval(d,6e4)});
