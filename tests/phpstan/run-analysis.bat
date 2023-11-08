@echo off
set MemoryLimit=900M
set AnalysisLevel=5
set OutputFile=result.txt
set ConfigFile=config.neon
set BinFolder=.\..\..\vendor\bin

cls

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------
echo.

call %BinFolder%\phpstan analyse -l %AnalysisLevel% -c %ConfigFile% --memory-limit=%MemoryLimit% > %OutputFile%

start "" "%OutputFile%"
