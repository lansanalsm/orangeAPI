<?php

// Send the headers
header('Content-type: text/html');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');
echo '<?xml version="1.0" encoding="utf-8"?>';


echo '<html>';
echo '<body>';
echo 'Please enter your name:<br/>';
echo '  <form action="display_name.php">';
echo '    <input type="text" name="response"/>';
echo '  </form>';
echo '</body>';
echo '</html>';

?>