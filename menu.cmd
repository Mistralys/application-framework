@echo off
rem Interactive developer menu for the Application Framework.
rem Thin wrapper that invokes the PHP menu script.
rem
rem Usage:
rem   menu.cmd
cd /d "%~dp0"
php tools/menu.php
