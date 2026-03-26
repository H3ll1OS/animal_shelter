document.addEventListener('DOMContentLoaded', () => {
  const notifToggle = document.getElementById('notification-toggle');
  const notifPanel = document.getElementById('notification-panel');
  const notifCount = document.getElementById('notification-count');
  const notifList = document.getElementById('notification-list');
  if (!notifToggle || !notifPanel) return;

  notifToggle.style.cursor = 'pointer';

  // Apply stored read list
  if (notifList && notifCount) {
    const readKeys = JSON.parse(sessionStorage.getItem('adminNotifRead') || '[]');
    notifList.querySelectorAll('.notification-item').forEach(item => {
      const key = item.dataset.key;
      if (key && readKeys.includes(key)) {
        item.remove();
      }
    });
    const remaining = notifList.querySelectorAll('.notification-item').length;
    if (remaining > 0) {
      notifCount.textContent = remaining;
    } else {
      notifCount.remove();
      notifList.innerHTML = '<p class="text-gray-600">No new notifications.</p>';
    }
  }

  notifToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    notifPanel.classList.toggle('hidden');
  });

  document.addEventListener('click', (e) => {
    if (!notifPanel.classList.contains('hidden') && !notifPanel.contains(e.target) && !notifToggle.contains(e.target)) {
      notifPanel.classList.add('hidden');
    }
  });

  if (notifList && notifCount) {
    notifList.addEventListener('click', (e) => {
      const item = e.target.closest('.notification-item');
      if (!item) return;
      e.preventDefault();
      const href = item.getAttribute('href');
      const key = item.dataset.key;
      const current = parseInt(notifCount.textContent || '0', 10);
      if (current > 0) {
        const next = current - 1;
        if (next > 0) {
          notifCount.textContent = next;
        } else {
          notifCount.remove();
        }
      }
      item.remove();
      if (key) {
        const prev = JSON.parse(sessionStorage.getItem('adminNotifRead') || '[]');
        const updated = Array.from(new Set([...(prev || []), key]));
        sessionStorage.setItem('adminNotifRead', JSON.stringify(updated));
      }
      if (!notifList.querySelector('.notification-item')) {
        notifList.innerHTML = '<p class="text-gray-600">No new notifications.</p>';
      }
      if (href) {
        setTimeout(() => { window.location.href = href; }, 50);
      }
    });
  }

  const profileToggle = document.getElementById('profile-toggle');
  const profilePanel = document.getElementById('profile-panel');
  if (profileToggle && profilePanel) {
    profileToggle.style.cursor = 'pointer';
    profileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      profilePanel.classList.toggle('hidden');
    });
    document.addEventListener('click', (e) => {
      if (!profilePanel.classList.contains('hidden') && !profilePanel.contains(e.target) && !profileToggle.contains(e.target)) {
        profilePanel.classList.add('hidden');
      }
    });
  }

  // Sidebar toggle (mobile)
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('main-content');
  const sidebarToggle = document.getElementById('sidebar-toggle');
  if (sidebar && mainContent && sidebarToggle) {
    const closeSidebar = () => {
      sidebar.classList.remove('open', 'translate-x-0');
      sidebar.classList.add('-translate-x-full');
      mainContent.classList.remove('open');
    };
    const openSidebar = () => {
      sidebar.classList.add('open', 'translate-x-0');
      sidebar.classList.remove('-translate-x-full');
      mainContent.classList.add('open');
    };

    closeSidebar(); // default closed

    sidebarToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      if (sidebar.classList.contains('open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });

    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        closeSidebar();
      }
    });
  }
});
