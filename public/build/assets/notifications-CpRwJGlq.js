$(function(){const f=$(".badge-notifications"),o=$(".dropdown-notifications-list .list-group"),u=$(".dropdown-notifications-all"),p=t=>{const e=t.data||{},a=e.title||"Notificação",c=e.message||"",l=e.type||"info";let d="";if(typeof moment<"u")d=moment(t.created_at).fromNow();else{const m=new Date(t.created_at);d=m.toLocaleDateString()+" "+m.toLocaleTimeString()}const r=t.read_at?"text-muted":"",b=t.read_at?"":'<span class="badge badge-dot bg-primary me-1"></span>';let s="bx-info-circle",n="bg-label-info";return l==="success"&&(s="bx-check-circle",n="bg-label-success"),l==="warning"&&(s="bx-error",n="bg-label-warning"),l==="danger"&&(s="bx-x-circle",n="bg-label-danger"),`
      <li class="list-group-item list-group-item-action dropdown-notifications-item ${t.read_at?"":"marked-as-unread"}" data-id="${t.id}" style="cursor: pointer;">
        <div class="d-flex">
          <div class="flex-shrink-0 me-3">
            <div class="avatar">
              <span class="avatar-initial rounded-circle ${n}"><i class="bx ${s}"></i></span>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1 ${r}">${a}</h6>
            <p class="mb-0 ${r}">${c}</p>
            <small class="text-muted">${d}</small>
          </div>
          <div class="flex-shrink-0 dropdown-notifications-actions">
            ${b}
          </div>
        </div>
      </li>
    `},g=`
    <li class="list-group-item list-group-item-action dropdown-notifications-item">
      <div class="d-flex justify-content-center align-items-center p-3">
        <div class="text-center">
          <i class="bx bx-bell-off fs-1 text-muted mb-2"></i>
          <p class="mb-0 text-muted">Nenhuma notificação nova</p>
        </div>
      </div>
    </li>
  `;function i(){$.ajax({url:"/notifications",method:"GET",success:function(t){const e=t.notifications,a=t.unread_count;f.text(a),o.empty(),e.length>0?e.forEach(c=>{o.append(p(c))}):o.append(g)},error:function(t){console.error("Failed to fetch notifications",t)}})}$(document).on("click",".dropdown-notifications-item",function(t){const e=$(this),a=e.data("id");e.hasClass("marked-as-unread")&&$.ajax({url:`/notifications/${a}/read`,method:"POST",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(){e.removeClass("marked-as-unread"),e.find(".badge-dot").remove(),i()}})}),u.on("click",function(){$.ajax({url:"/notifications/read-all",method:"POST",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(){i()}})}),i(),setInterval(i,6e4)});
