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
      <li class="list-group-item list-group-item-action dropdown-notifications-item ${!notification.read_at ? 'marked-as-unread' : ''}" data-id="${notification.id}" style="cursor: pointer;">
        <div class="d-flex">
          <div class="flex-shrink-0 me-3">
            <div class="avatar">
              <span class="avatar-initial rounded-circle ${bgClass}"><i class="bx ${iconClass}"></i></span>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1 ${readClass}">${title}</h6>
            <p class="mb-0 ${readClass}">${message}</p>
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
        } else {
          notificationList.append(emptyTemplate);
        }
      },
      error: function (xhr) {
        console.error('Failed to fetch notifications', xhr);
      }
    });
  }

  // Mark as read
  $(document).on('click', '.dropdown-notifications-item', function (e) {
    const item = $(this);
    const id = item.data('id');

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
