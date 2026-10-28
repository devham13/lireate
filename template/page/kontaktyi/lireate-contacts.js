(function () {
  'use strict';

  function parseTeam() {
    var node = document.getElementById('lc-team-json');
    if (!node) {
      return [];
    }
    try {
      return JSON.parse(node.textContent || '[]');
    } catch (e) {
      return [];
    }
  }

  function initTeamModal() {
    var team = parseTeam();
    var modal = document.getElementById('lc-team-modal');
    if (!modal || !team.length) {
      return;
    }
    var photo = document.getElementById('lc-modal-photo');
    var name = document.getElementById('lc-modal-name');
    var role = document.getElementById('lc-modal-role');

    function openAt(index) {
      var member = team[index];
      if (!member) {
        return;
      }
      if (photo) {
        photo.src = member.photo || '';
        photo.alt = member.name || '';
      }
      if (name) {
        name.textContent = member.name || '';
      }
      if (role) {
        role.textContent = member.role ? 'Должность: ' + member.role : '';
      }
      modal.hidden = false;
      document.documentElement.style.overflow = 'hidden';
    }

    function closeModal() {
      modal.hidden = true;
      document.documentElement.style.overflow = '';
    }

    document.querySelectorAll('[data-lc-team-index]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var idx = parseInt(btn.getAttribute('data-lc-team-index') || '0', 10);
        openAt(idx);
      });
    });

    modal.querySelectorAll('[data-lc-modal-close]').forEach(function (el) {
      el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && !modal.hidden) {
        closeModal();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTeamModal);
  } else {
    initTeamModal();
  }
})();
