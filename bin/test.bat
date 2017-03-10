::
:: @chcp 65001
::

@ECHO "exec shell"
@..\xampp\php\php.exe ..\src\test\index.php

@ECHO "server up"
@ECHO "伺服器即將啟動, 請查看 http://localhost"
@ECHO "關閉此視窗, 將會關閉伺服器"
@..\xampp\php\php.exe -S localhost:80 ..\src\test\index.php
