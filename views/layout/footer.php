    </main>
<?php if ($isAdminSection): ?>
  </div>
</div>
<?php endif; ?>
<footer class="site-footer">
    <div class="container">Outil de commandes en ligne — commandes ouvertes du dimanche à leur publication jusqu'au lundi 22h.</div>
</footer>
<script>
document.addEventListener('click', function (e) {
    document.querySelectorAll('details.profile-menu[open]').forEach(function (d) {
        if (!d.contains(e.target)) d.removeAttribute('open');
    });
});
</script>
</body>
</html>
