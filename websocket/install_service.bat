@echo off
setlocal enabledelayedexpansion

:: Check if NSSM is installed
where nssm >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo NSSM (Non-Sucking Service Manager) is not installed.
    echo Downloading NSSM...
    powershell -Command "Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile 'nssm.zip'"
    
    echo Extracting NSSM...
    powershell -Command "Expand-Archive -Path 'nssm.zip' -DestinationPath 'nssm_temp' -Force"
    
    echo Installing NSSM...
    copy "nssm_temp\nssm-2.24\win64\nssm.exe" "%SystemRoot%\System32\nssm.exe"
    
    echo Cleaning up...
    rmdir /s /q nssm_temp
    del nssm.zip
    
    echo NSSM has been installed successfully.
)

:: Get the full path to the PHP executable
set "PHP_EXECUTABLE=php"

:: Get the full path to the WebSocket server script
set "SCRIPT_PATH=%~dp0NotificationServer.php"
set "SCRIPT_PATH=%SCRIPT_PATH:\=\\%"

:: Service name
set "SERVICE_NAME=PILAR_WebSocket_Server"

:: Check if the service already exists
sc query "%SERVICE_NAME%" >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo Service %SERVICE_NAME% already exists.
    echo Removing existing service...
    nssm remove "%SERVICE_NAME%" confirm
    sc delete "%SERVICE_NAME%"
    timeout /t 2 >nul
)

echo Installing %SERVICE_NAME% service...

:: Install the service
nssm install "%SERVICE_NAME%" "%PHP_EXECUTABLE%" "%SCRIPT_PATH%"
nssm set "%SERVICE_NAME%" AppDirectory "%~dp0.."
nssm set "%SERVICE_NAME%" Description "PILAR Asset Inventory WebSocket Server"
nssm set "%SERVICE_NAME%" Start SERVICE_AUTO_START

:: Start the service
echo Starting %SERVICE_NAME% service...
net start "%SERVICE_NAME%"

if %ERRORLEVEL% equ 0 (
    echo.
    echo ===============================================
    echo %SERVICE_NAME% has been installed and started successfully!
    echo ===============================================
) else (
    echo.
    echo ===============================================
    echo Failed to start %SERVICE_NAME% service.
    echo Please check the Windows Event Viewer for details.
    echo ===============================================
)

pause
