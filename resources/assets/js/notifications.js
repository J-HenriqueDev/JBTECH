/**
 * Notification Manager
 */

'use strict';

$(function () {
  const notificationBadge = $('.badge-notifications');
  const notificationList = $('.dropdown-notifications-list .list-group');
  const markAllReadBtn = $('.dropdown-notifications-all');

  // Templates
  const notificationTemplate = notification => {
    const data = notification.data || {};
    const title = data.title || 'Notificação';
    const message = data.message || '';
    const type = data.type || 'info'; // success, info, warning, danger
    const link = data.link || '';
    const requireConfirm = !!data.require_confirm;
    const imageUrl = data.image_url || '';
    const displayMessageRaw = message.length > 30 ? message.slice(0, 30) + '...' : message;
    let time = '';
    if (typeof moment !== 'undefined') {
      time = moment(notification.created_at).fromNow();
    } else {
      const date = new Date(notification.created_at);
      time = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
    const readClass = notification.read_at ? 'text-muted' : '';
    const unreadIndicator = !notification.read_at ? '<span class="badge badge-dot bg-primary me-1"></span>' : '';

    let iconClass = 'bx-info-circle';
    let bgClass = 'bg-label-info';

    if (type === 'success') {
      iconClass = 'bx-check-circle';
      bgClass = 'bg-label-success';
    }
    if (type === 'warning') {
      iconClass = 'bx-error';
      bgClass = 'bg-label-warning';
    }
    if (type === 'danger') {
      iconClass = 'bx-x-circle';
      bgClass = 'bg-label-danger';
    }

    return `
      <li class="list-group-item list-group-item-action dropdown-notifications-item ${!notification.read_at ? 'marked-as-unread' : ''}" data-id="${notification.id}" data-title="${$('<div>').text(title).html()}" data-message="${$('<div>').text(message).html()}" data-type="${type}" data-link="${link}" data-require_confirm="${requireConfirm}" data-image_url="${imageUrl}" style="cursor: pointer;">
        <div class="d-flex">
          <div class="flex-shrink-0 me-3">
            <div class="avatar">
              <span class="avatar-initial rounded-circle ${bgClass}"><i class="bx ${iconClass}"></i></span>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1 ${readClass}">${title}</h6>
            <p class="mb-0 ${readClass}">${$('<div>').text(displayMessageRaw).html()}</p>
            <small class="text-muted">${time}</small>
          </div>
          <div class="flex-shrink-0 dropdown-notifications-actions">
            ${unreadIndicator}
          </div>
        </div>
      </li>
    `;
  };

  const emptyTemplate = `
    <li class="list-group-item list-group-item-action dropdown-notifications-item">
      <div class="d-flex justify-content-center align-items-center p-3">
        <div class="text-center">
          <i class="bx bx-bell-off fs-1 text-muted mb-2"></i>
          <p class="mb-0 text-muted">Nenhuma notificação nova</p>
        </div>
      </div>
    </li>
  `;

  // Fetch Notifications
  function fetchNotifications() {
    $.ajax({
      url: '/notifications',
      method: 'GET',
      success: function (response) {
        const notifications = response.notifications;
        const unreadCount = response.unread_count;

        // Update badge
        notificationBadge.text(unreadCount);

        // Update list
        notificationList.empty();

        if (notifications.length > 0) {
          notifications.forEach(n => {
            notificationList.append(notificationTemplate(n));
          });
          notifyBrowserIfNeeded(notifications, unreadCount);
        } else {
          notificationList.append(emptyTemplate);
        }
      },
      error: function (xhr) {
        console.error('Failed to fetch notifications', xhr);
      }
    });
  }

  // Browser Notifications
  let lastNotifiedIds = new Set();
  function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission();
    }
  }
  function showBrowserNotification(n) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const data = n.data || {};
    const title = data.title || 'Notificação';
    const message = data.message || '';
    const link = data.link || '';
    const notification = new Notification(title, {
      body: message,
      icon: '/assets/img/front-pages/landing-page/jblogo_black.png'
    });
    notification.onclick = function () {
      if (link) {
        window.open(link, '_blank');
      }
    };
  }
  function notifyBrowserIfNeeded(notifications, unreadCount) {
    requestNotificationPermission();
    const newItems = notifications.filter(n => !n.read_at && !lastNotifiedIds.has(n.id));
    if (newItems.length > 0) {
      // Notify only the most recent one to avoid spamming
      showBrowserNotification(newItems[0]);
      newItems.forEach(n => lastNotifiedIds.add(n.id));
    }
  }

  // Mark as read
  $(document).on('click', '.dropdown-notifications-item', function (e) {
    const item = $(this);
    const id = item.data('id');
    const title = item.data('title') || 'Notificação';
    const message = item.data('message') || '';
    const type = item.data('type') || 'info';
    const link = item.data('link') || '';
    const requireConfirm = !!item.data('require_confirm');

    if (message && message.length > 30) {
      const modalEl = document.getElementById('notificationModal');
      if (modalEl) {
        document.getElementById('notificationModalTitle').textContent = title;
        document.getElementById('notificationModalMessage').textContent = message;
        const modalImage = document.getElementById('notificationModalImage');
        const imgUrl = item.data('image_url') || '';
        if (modalImage) {
          if (imgUrl) {
            modalImage.src = imgUrl;
            modalImage.style.display = 'block';
          } else {
            modalImage.removeAttribute('src');
            modalImage.style.display = 'none';
          }
        }
        const iconEl = document.getElementById('notificationModalIcon');
        const badgeEl = document.getElementById('notificationModalBadge');
        let iconClass = 'bx-info-circle';
        let badgeClass = 'bg-label-info';
        if (type === 'success') { iconClass = 'bx-check-circle'; badgeClass = 'bg-label-success'; }
        if (type === 'warning') { iconClass = 'bx-error'; badgeClass = 'bg-label-warning'; }
        if (type === 'danger') { iconClass = 'bx-x-circle'; badgeClass = 'bg-label-danger'; }
        iconEl.className = 'bx ' + iconClass;
        badgeEl.className = 'avatar-initial rounded-circle ' + badgeClass;
        const linkEl = document.getElementById('notificationModalLink');
        if (link) {
          linkEl.href = link;
          linkEl.style.display = 'inline-block';
        } else {
          linkEl.style.display = 'none';
        }
        const confirmArea = document.getElementById('notificationConfirmArea');
        const confirmBtn = document.getElementById('notificationConfirmBtn');
        const declineBtn = document.getElementById('notificationDeclineBtn');
        if (requireConfirm) {
          confirmArea.style.display = 'block';
          confirmBtn.onclick = function () {
            $.ajax({
              url: `/notifications/${id}/ack`,
              method: 'POST',
              data: { status: 'accepted' },
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              success: function () {
                fetchNotifications();
                bootstrap.Modal.getInstance(modalEl).hide();
              }
            });
          };
          declineBtn.onclick = function () {
            $.ajax({
              url: `/notifications/${id}/ack`,
              method: 'POST',
              data: { status: 'declined' },
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              success: function () {
                fetchNotifications();
                bootstrap.Modal.getInstance(modalEl).hide();
              }
            });
          };
        } else {
          confirmArea.style.display = 'none';
          confirmBtn.onclick = null;
          declineBtn.onclick = null;
        }
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    }

    if (item.hasClass('marked-as-unread')) {
      $.ajax({
        url: `/notifications/${id}/read`,
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function () {
          item.removeClass('marked-as-unread');
          item.find('.badge-dot').remove();
          fetchNotifications(); // Refresh count
        }
      });
    }
  });

  // Mark all as read
  markAllReadBtn.on('click', function () {
    $.ajax({
      url: '/notifications/read-all',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function () {
        fetchNotifications();
      }
    });
  });

  // Initial fetch
  fetchNotifications();

  // Poll every 60 seconds
  setInterval(fetchNotifications, 60000);
});
