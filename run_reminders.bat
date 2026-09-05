@echo off

set TWILIO_SID=AC0fbc63fb5a1dc769027aab37418172b1
set TWILIO_AUTH_TOKEN=beb129cf321e261e6c0ab499d5e2a587
set TWILIO_PHONE_NUMBER=+14174903647


echo [%date% %time%] Running AutoMind AI License Check... >> "C:\xampp\htdocs\AutoMind-AI\reminder_log.txt"

"C:\xampp\php\php.exe" "C:\xampp\htdocs\AutoMind-AI\send_reminders_master.php" >> "C:\xampp\htdocs\AutoMind-AI\reminder_log.txt" 2>&1


echo [%date% %time%] Check complete. >> "C:\xampp\htdocs\AutoMind-AI\reminder_log.txt"
exit