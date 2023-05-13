<?php

echo "The program is executed correctly (程式已正確執行)<br>\n";
echo "<a href='?info'>查看系統資訊</a><br>\n";
echo "<br>\n";


if (isset($_GET['info'])) {
    phpinfo();
}