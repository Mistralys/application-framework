@echo off

cls

echo ----------------------------------------------------
echo RUNNING APPLICATION TESTS
echo ----------------------------------------------------
echo.
echo Run specific testsuites with "--testsuite SuiteName"
echo.
echo - Application
echo - DataGrids
echo - Forms
echo - Functions
echo - Global
echo - OAuth
echo - UI
echo - User
echo.
echo -----------------------------------------------------
echo.

call vendor/bin/phpunit %*

echo.
echo.

pause
