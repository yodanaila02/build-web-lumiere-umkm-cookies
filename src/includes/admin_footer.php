    </main>
  </div>
</div>

<?php
/*
FILE: src/includes/admin_footer.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Penutup layout admin + script sidebar mobile
*/
?>
<script>
(function () {
  const sidebar = document.getElementById('adminSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggle  = document.getElementById('sidebarToggle');
  function open()  { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
  function close() { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
  toggle  && toggle.addEventListener('click', open);
  overlay && overlay.addEventListener('click', close);
})();
</script>
</body>
</html>
