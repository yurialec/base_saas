SET SCRIPT_DIR=%~dp0
cd %SCRIPT_DIR%

php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan config:cache
rem php artisan route:cache
rem php artisan optimize


rem pause