</div> <!-- Cierre del div .container -->
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema de Matrícula de Natación. Todos los derechos reservados.</p>
    </footer>
    <!-- La URL del JS también debe ser absoluta -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>assets/js/main.js"></script>
</body>
</html>
