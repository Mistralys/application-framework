@echo off

set AnalysisLevel=5
set OutputFile=docs\phpstan\result.txt
set ConfigFile=docs\phpstan\config.neon
set BinFolder=vendor\bin

cls

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------
echo.

call %BinFolder%\phpstan analyse -l %AnalysisLevel% -c %ConfigFile% --memory-limit=900M src\classes > %OutputFile%

start "" "%OutputFile%"
