@echo off
REM Runs the Laravel dev server with APP_ENV overridden to "local" at the process level.
REM This does NOT modify the committed .env file (which stays APP_ENV=codecanyon).
REM Used only for local manual/browser verification of demo-seeded data.
set APP_ENV=local
"C:\Users\Wajahat\.config\herd\bin\php.bat" artisan serve --host=127.0.0.1 --port=8091
