::
:: config
::
@CALL ../config/config.cmd

::
:: validate
::
@IF EXIST %MY_XAMPP_PATH% (
    REM file exist
) else (
    @ECHO Error: "%MY_XAMPP_PATH%" not exists!
    PAUSE
    EXIT
)

::
:: 啟動伺服器
::
@ECHO ================================================================================
@ECHO Please open browser to "http://localhost"
@ECHO ================================================================================
@%MY_XAMPP_PATH%\php\php.exe -S localhost:80 ..\src\subtotal\public\index.php
@ECHO.
