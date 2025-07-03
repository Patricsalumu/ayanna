@echo off
echo === Preparation du deploiement Ayanna ===

echo Arret des services WAMP (si necessaire)...
net stop wampapache64 2>nul
net stop wampmysqld64 2>nul

timeout /t 2 /nobreak >nul

echo Suppression des fichiers temporaires...
rmdir /s /q node_modules 2>nul
rmdir /s /q vendor 2>nul
del storage\logs\*.log 2>nul
del .env 2>nul

echo Fermeture des processus PHP potentiels...
taskkill /f /im php.exe 2>nul
taskkill /f /im httpd.exe 2>nul

timeout /t 3 /nobreak >nul

echo Creation de l'archive (methode alternative)...

REM Créer un script PowerShell temporaire pour gérer l'archive
echo $ErrorActionPreference = 'SilentlyContinue' > temp_archive.ps1
echo try { >> temp_archive.ps1
echo   $source = Get-Location >> temp_archive.ps1
echo   $destination = Join-Path $source "ayanna-deploy.zip" >> temp_archive.ps1
echo   if (Test-Path $destination) { Remove-Item $destination -Force } >> temp_archive.ps1
echo   Add-Type -AssemblyName System.IO.Compression.FileSystem >> temp_archive.ps1
echo   [System.IO.Compression.ZipFile]::CreateFromDirectory($source, $destination, 'Optimal', $false) >> temp_archive.ps1
echo   Write-Host "Archive creee avec succes: ayanna-deploy.zip" >> temp_archive.ps1
echo } catch { >> temp_archive.ps1
echo   Write-Host "Erreur lors de la creation de l'archive: $_" >> temp_archive.ps1
echo   Write-Host "Tentative avec une methode alternative..." >> temp_archive.ps1
echo   Compress-Archive -Path * -DestinationPath ayanna-deploy.zip -Force >> temp_archive.ps1
echo } >> temp_archive.ps1

powershell -ExecutionPolicy Bypass -File temp_archive.ps1

del temp_archive.ps1 2>nul

REM Vérifier si l'archive a été créée
if exist ayanna-deploy.zip (
    echo ✅ Archive creee avec succes: ayanna-deploy.zip
    dir ayanna-deploy.zip
) else (
    echo ❌ Erreur lors de la creation de l'archive
    echo Tentative manuelle avec 7-Zip si disponible...
    "C:\Program Files\7-Zip\7z.exe" a -tzip ayanna-deploy.zip * -x!node_modules -x!vendor -x!.env 2>nul
    if exist ayanna-deploy.zip (
        echo ✅ Archive creee avec 7-Zip
    ) else (
        echo ❌ Archive non creee. Veuillez compresser manuellement.
    )
)

echo.
echo Redemarrage des services WAMP...
net start wampapache64 2>nul
net start wampmysqld64 2>nul

echo.
echo === Preparation terminee ===
echo Vous pouvez maintenant uploader cette archive sur votre serveur.
pause
