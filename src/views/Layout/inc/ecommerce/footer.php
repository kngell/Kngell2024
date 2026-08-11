<footer class="footer">
    <?= $footer ?? '' ?>
</footer>
<?= $this->js('runtime') ?>
<?= $this->js('js/librairies/librairy') ?>
<?= $this->js('js/frontend/main/main')?>
<?= $this->content('footer'); ?>
<!-- DevServer Reload Client (dev mode only) -->
<script src="/__dev__/reload-client.js"></script>
</body>

</html>