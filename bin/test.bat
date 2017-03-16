::
:: @chcp 65001
::

::
:: config
::
@CALL ../config/config.cmd

::
:: 執行 script 腳本檔案
::
@ECHO ========================================
@ECHO exec shell
@ECHO ========================================
@%MY_XAMPP_PATH%\php\php.exe ..\src\test\index.php
@ECHO.

::
:: 啟動伺服器
::
@ECHO ========================================
@ECHO Server will start
@ECHO Please open browser to "http://localhost"
@ECHO ========================================
@%MY_XAMPP_PATH%\php\php.exe -S localhost:80 ..\src\test\index.php
@ECHO.
