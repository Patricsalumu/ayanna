@echo off
echo === Preparation manuelle du deploiement ===

echo 1. Arret de WAMP...
echo Veuillez arreter WAMP manuellement (icone systeme) puis appuyez sur une touche.
pause

echo 2. Nettoyage...
rmdir /s /q node_modules 2>nul
rmdir /s /q vendor 2>nul
del storage\logs\*.log 2>nul
del .env 2>nul

echo 3. Fichiers a exclure de l'archive :
echo - node_modules/
echo - vendor/
echo - .env
echo - storage/logs/*.log
echo - ayanna-deploy.zip (si existant)

echo 4. Vous devez maintenant :
echo    a) Selectionner tous les fichiers SAUF ceux listes ci-dessus
echo    b) Clic droit > Envoyer vers > Dossier compresse
echo    c) Renommer l'archive en "ayanna-deploy.zip"

echo 5. Alternative : Utilisez WinRAR ou 7-Zip pour creer l'archive

pause
