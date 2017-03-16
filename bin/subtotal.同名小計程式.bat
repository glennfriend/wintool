::
:: config
::
@CALL ../config/config.cmd

::
:: 啟動伺服器
::
@ECHO ========================================
@ECHO Server will start
@ECHO Please open browser to "http://localhost"
@ECHO ========================================
@%MY_XAMPP_PATH%\php\php.exe -S localhost:80 ..\src\subtotal\public\index.php
@ECHO.
