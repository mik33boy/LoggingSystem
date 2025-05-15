@echo off
echo Starting XAMPP services...
cd C:\xampp
start xampp-control.exe

echo Opening browser for database setup...
timeout /t 5 /nobreak > nul
start http://localhost/LoggingSystem/backend/setup_database.php

echo Done! 