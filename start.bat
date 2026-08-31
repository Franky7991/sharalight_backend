@echo off
title Shara Light - Launcher

REM ============================================================
REM  Shara Light - avvio unico di backend e webapp
REM    1) Backend Laravel : php artisan serve      (porta 8000)
REM    2) Webapp          : php -S localhost:3000  (porta 3000)
REM  Le due finestre restano aperte: chiudile per fermare i server.
REM ============================================================

REM PHP deve essere raggiungibile dal PATH
where php >nul 2>nul
if errorlevel 1 (
    echo [ERRORE] PHP non trovato nel PATH. Installa PHP o aggiungilo al PATH.
    pause
    exit /b 1
)

REM Cartella progetto = genitore della cartella "backend" che contiene questo file
for %%I in ("%~dp0..") do set "ROOT_DIR=%%~fI"
set "BACKEND_DIR=%ROOT_DIR%\backend"
set "WEBAPP_DIR=%ROOT_DIR%\webapp"

echo.
echo ==============================================
echo   Shara Light - avvio Backend + Webapp
echo ==============================================
echo.

echo [1/2] Avvio backend Laravel su http://localhost:8000 ...
start "Shara Light - Backend :8000" cmd /k "cd /d "%BACKEND_DIR%" && php artisan serve"

echo [2/2] Avvio webapp su http://localhost:3000 ...
start "Shara Light - Webapp :3000" cmd /k "cd /d "%WEBAPP_DIR%" && php -S localhost:3000"

REM Piccola attesa prima di aprire il browser (i server si avviano)
ping -n 4 127.0.0.1 >nul

start "" "http://localhost:3000/index.html"

echo.
echo   Backend : http://localhost:8000/api  (register, login, user, logout)
echo   Webapp  : http://localhost:3000/index.html
echo.
echo   Per fermare i server chiudi le due finestre aperte.
echo.
pause
