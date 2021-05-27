@echo off

cls

echo ----------------------------------------------------
echo RUNNING APPLICATION TESTS
echo ----------------------------------------------------
echo.
echo Run specific testsuites with "--testsuite SuiteName"
echo.
echo - Application
echo - Collection
echo - Countries
echo - DataGrids
echo - Forms
echo - Functions
echo - Global
echo - Installer
echo - OAuth
echo - Ratings
echo - UI
echo - User
echo.
echo -----------------------------------------------------
echo.

call vendor/bin/phpunit %*

echo.
echo.

pause
