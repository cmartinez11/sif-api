<?php
echo "<h1>Diagnóstico de PHP para PDFs</h1>";
echo "<p><strong>Estado de la extensión GD:</strong> " . (extension_loaded('gd') ? "<span style='color:green'>ACTIVA (¡Todo listo!)</span>" : "<span style='color:red'>DESACTIVADA (Necesitas habilitarla)</span>") . "</p>";
echo "<p><strong>Archivo de configuración (php.ini) cargado:</strong> " . php_ini_loaded_file() . "</p>";
echo "<hr>";
phpinfo();
