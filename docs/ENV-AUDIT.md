# Audit variabile .env

Generat prin scanarea tuturor fisierelor PHP din repo, excluzand `vendor/` si `node_modules/`, dupa apeluri reale `env(...)`. Apelurile comentate nu sunt considerate folosite. Valorile `config(...)` din app se rezolva prin fisierele `config/*.php`, deci sunt acoperite de acest tabel.

## Rezumat

- Chei `env()` folosite in cod/config: **196**.
- Chei folosite, dar lipsa din `.env.example`: **124**.
- Chei in `.env.example`, dar nefolosite direct in repo: **4**.
- Chei folosite, dar lipsa din `.env.prod.example` dupa actualizare: **0**.
- Chei in `.env.prod.example`, dar nefolosite direct dupa actualizare: **0** (`MAIL_ENCRYPTION` si `FILAMENT_FILESYSTEM_DISK` au fost eliminate — vezi mai jos).

## Tabel chei

| cheie | folosita in | obligatorie? | valoare implicita | scop scurt | de unde se ia |
| --- | --- | --- | --- | --- | --- |
| `APP_DEBUG` | `config/app.php` | nu | `false` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_ENV` | `config/app.php` | da in prod | `'production'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_FAKER_LOCALE` | `config/app.php` | nu | `'en_US'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_FALLBACK_LOCALE` | `config/app.php` | nu | `'en'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_KEY` | `config/app.php` | da | `null` | Setare aplicatie Laravel. | `php artisan key:generate` rulat pe server (sau local, apoi copiat) |
| `APP_LOCALE` | `config/app.php` | nu | `'en'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_MAINTENANCE_DRIVER` | `config/app.php` | nu | `'file'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_MAINTENANCE_STORE` | `config/app.php` | nu | `'database'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_NAME` | `config/app.php`, `config/mail.php`, `config/database.php`, `config/cache.php`, `config/session.php`, `config/logging.php` | nu | `'Laravel'` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_PREVIOUS_KEYS` | `config/app.php` | nu | `''` | Setare aplicatie Laravel. | setat de dev (valori standard aplicatie) |
| `APP_URL` | `config/app.php`, `config/mail.php`, `config/filesystems.php` | da in prod | `'http://localhost'` | Setare aplicatie Laravel. | domeniul real (https://decor-urban.ro) |
| `AUTH_GUARD` | `config/auth.php` | nu | `'web'` | Configurare autentificare Laravel. | default Laravel; nu se seteaza de regula |
| `AUTH_MODEL` | `config/auth.php` | nu | `User::class` | Configurare autentificare Laravel. | default Laravel; nu se seteaza de regula |
| `AUTH_PASSWORD_BROKER` | `config/auth.php` | nu | `'users'` | Configurare autentificare Laravel. | default Laravel; nu se seteaza de regula |
| `AUTH_PASSWORD_RESET_TOKEN_TABLE` | `config/auth.php` | nu | `'password_reset_tokens'` | Configurare autentificare Laravel. | default Laravel; nu se seteaza de regula |
| `AUTH_PASSWORD_TIMEOUT` | `config/auth.php` | nu | `10800` | Configurare autentificare Laravel. | default Laravel; nu se seteaza de regula |
| `AWS_ACCESS_KEY_ID` | `config/services.php`, `config/cache.php`, `config/queue.php`, `config/filesystems.php` | nu | `null` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `AWS_BUCKET` | `config/filesystems.php` | nu | `null` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `AWS_DEFAULT_REGION` | `config/services.php`, `config/cache.php`, `config/queue.php`, `config/filesystems.php` | nu | `'us-east-1'` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `AWS_ENDPOINT` | `config/filesystems.php` | nu | `null` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `AWS_SECRET_ACCESS_KEY` | `config/services.php`, `config/cache.php`, `config/queue.php`, `config/filesystems.php` | nu | `null` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `AWS_URL` | `config/filesystems.php` | nu | `null` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `config/filesystems.php` | nu | `false` | Filesystem S3, SES sau SQS. | cont AWS, doar daca se foloseste S3/SES/SQS |
| `BEANSTALKD_QUEUE` | `config/queue.php` | nu | `'default'` | Cozi Laravel. | serverul Beanstalkd, doar daca se foloseste |
| `BEANSTALKD_QUEUE_HOST` | `config/queue.php` | nu | `'localhost'` | Cozi Laravel. | serverul Beanstalkd, doar daca se foloseste |
| `BEANSTALKD_QUEUE_RETRY_AFTER` | `config/queue.php` | nu | `90` | Cozi Laravel. | serverul Beanstalkd, doar daca se foloseste |
| `BING_SITE_VERIFICATION` | `config/business.php` | nu | `''` | Verificare domeniu in motoare/platforme. | Bing Webmaster Tools - verificare meta tag |
| `BUSINESS_ADDRESS` | `config/company.php`, `config/business.php` | nu | `'Str. Băltați nr. 149, Sat Băltați, Oraș Scornicești, Județul Olt, ...` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_EMAIL` | `config/contact.php`, `config/business.php` | nu | `'contact@decor-urban.ro'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_FACEBOOK` | `config/contact.php`, `config/business.php` | nu | `'https://www.facebook.com/profile.php?id=61592205237734'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_GOOGLE_MAPS_EMBED_URL` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | Google Maps - Share - Embed a map (src-ul iframe-ului) |
| `BUSINESS_GOOGLE_MAPS_URL` | `config/business.php` | nu | `'https://share.google/sWYL0KoX1P7j3O06B'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | Google Maps - Share pentru locatia firmei |
| `BUSINESS_GOOGLE_PLACE_ID` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | Google Place ID Finder / Google Business Profile |
| `BUSINESS_GOOGLE_REVIEW_URL` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | Google Business Profile - "Ask for reviews" (link scurt g.page) |
| `BUSINESS_INSTAGRAM` | `config/contact.php`, `config/business.php` | nu | `'https://www.instagram.com/decor.urban.ro'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_LATITUDE` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | Google Maps - click dreapta pe locatie (coordonate) |
| `BUSINESS_LEGAL_NAME` | `config/company.php`, `config/business.php` | nu | `'MOBILIER-STRADAL RO 2026 S.R.L.'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_LINKEDIN` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_LONGITUDE` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | Google Maps - click dreapta pe locatie (coordonate) |
| `BUSINESS_NAME` | `config/contact.php`, `config/company.php`, `config/business.php` | nu | `'Decor Urban'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_PHONE` | `config/contact.php`, `config/business.php` | nu | `'+40 758 522 227'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_REGISTRATION_NUMBER` | `config/company.php`, `config/business.php` | nu | `'J2026018529009'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_TIKTOK` | `config/business.php` | nu | `'https://www.tiktok.com/@decor.urban.ro'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_VAT_NUMBER` | `config/company.php`, `config/business.php` | nu | `'54295156'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_WEBSITE` | `config/business.php` | nu | `'https://decor-urban.ro'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_WHATSAPP` | `config/business.php` | nu | `'+40 756 222 260'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_WHATSAPP_DIGITS` | `config/contact.php`, `config/business.php` | nu | `'40756222260'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_WHATSAPP_PREFILLED_URL` | `config/business.php` | nu | `'https://wa.me/40756222260?text=Bun%C4%83%20ziua%2C%20doresc%20mai%...` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_WHATSAPP_URL` | `config/business.php` | nu | `'https://wa.me/40756222260'` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `BUSINESS_YOUTUBE` | `config/business.php` | nu | `''` | Date publice Decor Urban, NAP, social, Maps sau review-uri. | date oficiale firma (ONRC/CUI) si profiluri publice; vezi valorile pre-completate |
| `CACHE_PREFIX` | `config/cache.php` | nu | `derivat din APP_NAME` | Cache si lock-uri Laravel. | default Laravel; nu se seteaza de regula |
| `CACHE_STORAGE_DISK` | `config/cache.php` | nu | `null` | Cache si lock-uri Laravel. | default Laravel; nu se seteaza de regula |
| `CACHE_STORAGE_PATH` | `config/cache.php` | nu | `'framework/cache/data'` | Cache si lock-uri Laravel. | default Laravel; nu se seteaza de regula |
| `CACHE_STORE` | `config/cache.php` | nu | `'database'` | Cache si lock-uri Laravel. | default Laravel; nu se seteaza de regula |
| `COMMAND_LOG_DAYS` | `config/logging.php` | nu | `90` | Setare framework/serviciu. | default OK (retentie log /commands) |
| `COMMAND_RATE_LIMIT` | `config/commands.php` | nu | `30` | Setare framework/serviciu. | default OK; mareste doar daca deploy-ul loveste limita |
| `COMPANY_ADDRESS` | `config/company.php` | nu | `BUSINESS_ADDRESS sau adresa oficiala default` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_BRAND` | `config/company.php` | nu | `BUSINESS_NAME sau Decor Urban` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_CAEN` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_CPV` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_CUI` | `config/company.php` | nu | `BUSINESS_VAT_NUMBER sau 54295156` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_EUID` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_FOUNDED` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_IS_PLACEHOLDER` | `config/company.php` | nu | `false` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_LEGAL_NAME` | `config/company.php` | nu | `BUSINESS_LEGAL_NAME sau denumirea legala default` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_PROJECTS` | `config/company.php` | nu | `0` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_PROJECTS_LIST` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_REFERENCES` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_REG_COM` | `config/company.php` | nu | `BUSINESS_REGISTRATION_NUMBER sau J2026018529009` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_SEAP_PRESENT` | `config/company.php` | nu | `false` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_STANDARDS` | `config/company.php` | nu | `''` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_SUPPLIER_LABEL` | `config/company.php` | nu | `'producător / furnizor direct'` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `COMPANY_YEARS` | `config/company.php` | nu | `0` | Date juridice, institutii, SEAP si social proof. | date juridice firma (certificat ONRC, coduri CAEN/CPV, istoric SEAP) |
| `CONTACT_BRAND` | `config/contact.php` | nu | `BUSINESS_NAME sau Decor Urban` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_CITY` | `config/contact.php` | nu | `'Scornicești, Olt'` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_EMAIL` | `config/contact.php` | nu | `BUSINESS_EMAIL sau contact@decor-urban.ro` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_FACEBOOK` | `config/contact.php` | nu | `BUSINESS_FACEBOOK sau profilul Facebook oficial` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_INSTAGRAM` | `config/contact.php` | nu | `BUSINESS_INSTAGRAM sau profilul Instagram oficial` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_IS_PLACEHOLDER` | `config/contact.php` | nu | `false` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_PHONE` | `config/contact.php` | nu | `BUSINESS_PHONE sau +40 758 522 227` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `CONTACT_WHATSAPP` | `config/contact.php` | nu | `BUSINESS_WHATSAPP_DIGITS sau 40756222260` | Date contact afisate si destinatar formulare. | date oficiale de contact; gol = fallback pe BUSINESS_* |
| `DB_CACHE_CONNECTION` | `config/cache.php` | nu | `null` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_CACHE_LOCK_CONNECTION` | `config/cache.php` | nu | `null` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_CACHE_LOCK_TABLE` | `config/cache.php` | nu | `null` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_CACHE_TABLE` | `config/cache.php` | nu | `'cache'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_CHARSET` | `config/database.php` | nu | `'utf8mb4'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_COLLATION` | `config/database.php` | nu | `'utf8mb4_unicode_ci'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_CONNECTION` | `config/database.php`, `config/queue.php` | da in prod | `'sqlite'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_DATABASE` | `config/database.php` | da in prod | `database_path('database.sqlite'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_FOREIGN_KEYS` | `config/database.php` | nu | `true` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_HOST` | `config/database.php` | da in prod | `'127.0.0.1'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_PASSWORD` | `config/database.php` | da in prod | `''` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_PORT` | `config/database.php` | da in prod | `'3306'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_QUEUE` | `config/queue.php` | nu | `'default'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_QUEUE_CONNECTION` | `config/queue.php` | nu | `null` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_QUEUE_RETRY_AFTER` | `config/queue.php` | nu | `90` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_QUEUE_TABLE` | `config/queue.php` | nu | `'jobs'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_SOCKET` | `config/database.php` | nu | `''` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_SSLMODE` | `config/database.php` | nu | `'prefer'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_URL` | `config/database.php` | nu | `null` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DB_USERNAME` | `config/database.php` | da in prod | `'root'` | Conexiune baza de date. | cPanel - MySQL Databases (host/user/parola/nume DB); restul raman pe default |
| `DYNAMODB_CACHE_TABLE` | `config/cache.php` | nu | `'cache'` | Cache si lock-uri Laravel. | cont AWS, doar daca se foloseste DynamoDB |
| `DYNAMODB_ENDPOINT` | `config/cache.php` | nu | `null` | Cache si lock-uri Laravel. | cont AWS, doar daca se foloseste DynamoDB |
| `FACEBOOK_DOMAIN_VERIFICATION` | `config/business.php` | nu | `''` | Verificare domeniu in motoare/platforme. | Meta Business Manager - Brand Safety - Domains |
| `FILESYSTEM_DISK` | `config/filesystems.php` | nu | `'local'` | Disk implicit fisiere. | default Laravel (local pe shared hosting) |
| `GA4_MEASUREMENT_ID` | `config/business.php` | nu | `''` | Tracking, incarcat doar conform consent. | Google Analytics 4 - Data Streams (G-XXXX) |
| `GEMINI_API_KEY` | `config/services.php` | nu | `null` | Gemini pentru enrich texte; nu este atins de audit. | Google AI Studio (aistudio.google.com) - API keys |
| `GEMINI_TEXT_MODEL` | `config/services.php` | nu | `'gemini-3.5-flash'` | Gemini pentru enrich texte; nu este atins de audit. | lista de modele Gemini disponibile; default OK |
| `GOOGLE_PLACES_API_KEY` | `config/business.php` | nu | `''` | Google Places API server-side pentru recenzii. | Google Cloud Console - APIs & Services - Credentials (Places API activat) |
| `GOOGLE_SITE_VERIFICATION` | `config/business.php` | nu | `''` | Verificare domeniu in motoare/platforme. | Google Search Console - Settings - Ownership verification (meta tag) |
| `GTM_CONTAINER_ID` | `config/business.php` | nu | `''` | Tracking, incarcat doar conform consent. | Google Tag Manager - ID container (GTM-XXXX) |
| `LOG_CHANNEL` | `config/logging.php` | nu | `'stack'` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_DAILY_DAYS` | `config/logging.php` | nu | `14` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_DEPRECATIONS_CHANNEL` | `config/logging.php` | nu | `'null'` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_DEPRECATIONS_TRACE` | `config/logging.php` | nu | `false` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_LEVEL` | `config/logging.php` | nu | `'debug'` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_PAPERTRAIL_HANDLER` | `config/logging.php` | nu | `SyslogUdpHandler::class` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_SLACK_EMOJI` | `config/logging.php` | nu | `':boom:'` | Logging si notificari operationale. | webhook Slack, doar daca se folosesc notificari Slack |
| `LOG_SLACK_USERNAME` | `config/logging.php` | nu | `APP_NAME sau Laravel` | Logging si notificari operationale. | webhook Slack, doar daca se folosesc notificari Slack |
| `LOG_SLACK_WEBHOOK_URL` | `config/logging.php` | nu | `null` | Logging si notificari operationale. | webhook Slack, doar daca se folosesc notificari Slack |
| `LOG_STACK` | `config/logging.php` | nu | `'single'` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_STDERR_FORMATTER` | `config/logging.php` | nu | `null` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `LOG_SYSLOG_FACILITY` | `config/logging.php` | nu | `LOG_USER` | Logging si notificari operationale. | default Laravel; nu se seteaza de regula |
| `MAIL_EHLO_DOMAIN` | `config/mail.php` | nu | `parse_url((string` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_FROM_ADDRESS` | `config/mail.php` | da in prod | `'hello@example.com'` | Trimitere email. | adresa oficiala de contact (contact@decor-urban.ro) |
| `MAIL_FROM_NAME` | `config/mail.php` | da in prod | `APP_NAME sau Laravel` | Trimitere email. | numele brandului |
| `MAIL_HOST` | `config/mail.php` | da in prod | `'127.0.0.1'` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_LOG_CHANNEL` | `config/mail.php` | nu | `null` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_MAILER` | `config/mail.php` | da in prod | `'log'` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_PASSWORD` | `config/mail.php` | da in prod | `null` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_PORT` | `config/mail.php` | da in prod | `2525` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_SCHEME` | `config/mail.php` | nu | `null` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_SENDMAIL_PATH` | `config/mail.php` | nu | `'/usr/sbin/sendmail -bs -i'` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_URL` | `config/mail.php` | nu | `null` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MAIL_USERNAME` | `config/mail.php` | da in prod | `null` | Trimitere email. | cPanel - Email Accounts (SMTP host/port/user/parola) |
| `MEMCACHED_HOST` | `config/cache.php` | nu | `'127.0.0.1'` | Cache si lock-uri Laravel. | hosting, doar daca se foloseste Memcached |
| `MEMCACHED_PASSWORD` | `config/cache.php` | nu | `null` | Cache si lock-uri Laravel. | hosting, doar daca se foloseste Memcached |
| `MEMCACHED_PERSISTENT_ID` | `config/cache.php` | nu | `null` | Cache si lock-uri Laravel. | hosting, doar daca se foloseste Memcached |
| `MEMCACHED_PORT` | `config/cache.php` | nu | `11211` | Cache si lock-uri Laravel. | hosting, doar daca se foloseste Memcached |
| `MEMCACHED_USERNAME` | `config/cache.php` | nu | `null` | Cache si lock-uri Laravel. | hosting, doar daca se foloseste Memcached |
| `META_FEED_TOKEN` | `config/business.php` | nu | `''` | Token optional pentru feed Meta Catalog. | generat local, random; acelasi token se pune in URL-ul feedului din Meta Commerce Manager |
| `META_PIXEL_ID` | `config/business.php` | nu | `''` | Tracking, incarcat doar conform consent. | Meta Events Manager - ID pixel |
| `MYSQL_ATTR_SSL_CA` | `config/database.php` | nu | `null` | Conexiune baza de date. | cPanel/hosting, doar daca DB cere SSL |
| `PAPERTRAIL_PORT` | `config/logging.php` | nu | `null` | Logging si notificari operationale. | cont Papertrail, doar daca se foloseste |
| `PAPERTRAIL_URL` | `config/logging.php` | nu | `null` | Logging si notificari operationale. | cont Papertrail, doar daca se foloseste |
| `POSTMARK_API_KEY` | `config/services.php` | nu | `null` | Trimitere email. | cont Postmark, doar daca se foloseste |
| `QUEUE_CONNECTION` | `config/queue.php` | nu | `'database'` | Cozi Laravel. | default Laravel; nu se seteaza de regula |
| `QUEUE_FAILED_DRIVER` | `config/queue.php` | nu | `'database-uuids'` | Cozi Laravel. | default Laravel; nu se seteaza de regula |
| `REDIS_BACKOFF_ALGORITHM` | `config/database.php` | nu | `'decorrelated_jitter'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_BACKOFF_BASE` | `config/database.php` | nu | `100` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_BACKOFF_CAP` | `config/database.php` | nu | `1000` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_CACHE_CONNECTION` | `config/cache.php` | nu | `'cache'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_CACHE_DB` | `config/database.php` | nu | `'1'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_CACHE_LOCK_CONNECTION` | `config/cache.php` | nu | `'default'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_CLIENT` | `config/database.php` | nu | `'phpredis'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_CLUSTER` | `config/database.php` | nu | `'redis'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_DB` | `config/database.php` | nu | `'0'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_HOST` | `config/database.php` | nu | `'127.0.0.1'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_MAX_RETRIES` | `config/database.php` | nu | `3` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_PASSWORD` | `config/database.php` | nu | `null` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_PERSISTENT` | `config/database.php` | nu | `false` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_PORT` | `config/database.php` | nu | `'6379'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_PREFIX` | `config/database.php` | nu | `derivat din APP_NAME` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_QUEUE` | `config/queue.php` | nu | `'default'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_QUEUE_CONNECTION` | `config/queue.php` | nu | `'default'` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_QUEUE_RETRY_AFTER` | `config/queue.php` | nu | `90` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_URL` | `config/database.php` | nu | `null` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `REDIS_USERNAME` | `config/database.php` | nu | `null` | Redis cache/session/queue. | hosting/serverul Redis, doar daca se foloseste Redis |
| `RESEND_API_KEY` | `config/services.php` | nu | `null` | Trimitere email. | cont Resend, doar daca se foloseste |
| `SECRET` | `config/commands.php` | nu | `null` | Cheie permanenta pentru /commands. | generat local, lung si random (ex. `php artisan tinker` + `Str::random(48)`) |
| `SEED_ADMIN_1_EMAIL` | `config/seed.php` | nu | `null` | User admin creat de seeder. | ales de owner; folosit doar la seed-ul initial al userilor admin |
| `SEED_ADMIN_1_NAME` | `config/seed.php` | nu | `'Admin'` | User admin creat de seeder. | ales de owner; folosit doar la seed-ul initial al userilor admin |
| `SEED_ADMIN_1_PASSWORD` | `config/seed.php` | nu | `null` | User admin creat de seeder. | ales de owner; folosit doar la seed-ul initial al userilor admin |
| `SEED_ADMIN_2_EMAIL` | `config/seed.php` | nu | `null` | User admin creat de seeder. | ales de owner; folosit doar la seed-ul initial al userilor admin |
| `SEED_ADMIN_2_NAME` | `config/seed.php` | nu | `'Admin'` | User admin creat de seeder. | ales de owner; folosit doar la seed-ul initial al userilor admin |
| `SEED_ADMIN_2_PASSWORD` | `config/seed.php` | nu | `null` | User admin creat de seeder. | ales de owner; folosit doar la seed-ul initial al userilor admin |
| `SESSION_CONNECTION` | `config/session.php` | nu | `null` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_COOKIE` | `config/session.php` | nu | `Str::slug(APP_NAME).'-session'` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_DOMAIN` | `config/session.php` | nu | `null` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_DRIVER` | `config/session.php` | nu | `'database'` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_ENCRYPT` | `config/session.php` | nu | `false` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_EXPIRE_ON_CLOSE` | `config/session.php` | nu | `false` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_HTTP_ONLY` | `config/session.php` | nu | `true` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_LIFETIME` | `config/session.php` | nu | `120` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_PARTITIONED_COOKIE` | `config/session.php` | nu | `false` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_PATH` | `config/session.php` | nu | `'/'` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_SAME_SITE` | `config/session.php` | nu | `'lax'` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_SECURE_COOKIE` | `config/session.php` | nu | `null` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_STORE` | `config/session.php` | nu | `null` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SESSION_TABLE` | `config/session.php` | nu | `'sessions'` | Sesiuni si cookie Laravel. | default Laravel; nu se seteaza de regula |
| `SLACK_BOT_USER_DEFAULT_CHANNEL` | `config/services.php` | nu | `null` | Logging si notificari operationale. | app Slack (OAuth token), doar daca se folosesc notificari Slack |
| `SLACK_BOT_USER_OAUTH_TOKEN` | `config/services.php` | nu | `null` | Logging si notificari operationale. | app Slack (OAuth token), doar daca se folosesc notificari Slack |
| `SQS_PREFIX` | `config/queue.php` | nu | `'https://sqs.us-east-1.amazonaws.com/your-account-id'` | Cozi Laravel. | cont AWS, doar daca se foloseste SQS |
| `SQS_QUEUE` | `config/queue.php` | nu | `'default'` | Cozi Laravel. | cont AWS, doar daca se foloseste SQS |
| `SQS_SUFFIX` | `config/queue.php` | nu | `null` | Cozi Laravel. | cont AWS, doar daca se foloseste SQS |
| `TIKTOK_PIXEL_ID` | `config/business.php` | nu | `''` | Tracking, incarcat doar conform consent. | TikTok Ads Manager - Events - Web Events |

## Lipsa din `.env.example`

Aceste chei sunt folosite in cod/config, dar nu apar in `.env.example`:

- `APP_MAINTENANCE_STORE`
- `APP_PREVIOUS_KEYS`
- `AUTH_GUARD`
- `AUTH_MODEL`
- `AUTH_PASSWORD_BROKER`
- `AUTH_PASSWORD_RESET_TOKEN_TABLE`
- `AUTH_PASSWORD_TIMEOUT`
- `AWS_ACCESS_KEY_ID`
- `AWS_BUCKET`
- `AWS_DEFAULT_REGION`
- `AWS_ENDPOINT`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_URL`
- `AWS_USE_PATH_STYLE_ENDPOINT`
- `BEANSTALKD_QUEUE`
- `BEANSTALKD_QUEUE_HOST`
- `BEANSTALKD_QUEUE_RETRY_AFTER`
- `CACHE_PREFIX`
- `CACHE_STORAGE_DISK`
- `CACHE_STORAGE_PATH`
- `COMMAND_LOG_DAYS`
- `COMMAND_RATE_LIMIT`
- `COMPANY_ADDRESS`
- `COMPANY_BRAND`
- `COMPANY_CAEN`
- `COMPANY_CPV`
- `COMPANY_CUI`
- `COMPANY_EUID`
- `COMPANY_FOUNDED`
- `COMPANY_IS_PLACEHOLDER`
- `COMPANY_LEGAL_NAME`
- `COMPANY_PROJECTS`
- `COMPANY_PROJECTS_LIST`
- `COMPANY_REFERENCES`
- `COMPANY_REG_COM`
- `COMPANY_SEAP_PRESENT`
- `COMPANY_STANDARDS`
- `COMPANY_SUPPLIER_LABEL`
- `COMPANY_YEARS`
- `CONTACT_BRAND`
- `CONTACT_CITY`
- `CONTACT_EMAIL`
- `CONTACT_FACEBOOK`
- `CONTACT_INSTAGRAM`
- `CONTACT_IS_PLACEHOLDER`
- `CONTACT_PHONE`
- `CONTACT_WHATSAPP`
- `DB_CACHE_CONNECTION`
- `DB_CACHE_LOCK_CONNECTION`
- `DB_CACHE_LOCK_TABLE`
- `DB_CACHE_TABLE`
- `DB_CHARSET`
- `DB_COLLATION`
- `DB_FOREIGN_KEYS`
- `DB_QUEUE`
- `DB_QUEUE_CONNECTION`
- `DB_QUEUE_RETRY_AFTER`
- `DB_QUEUE_TABLE`
- `DB_SOCKET`
- `DB_SSLMODE`
- `DB_URL`
- `DYNAMODB_CACHE_TABLE`
- `DYNAMODB_ENDPOINT`
- `GEMINI_API_KEY`
- `GEMINI_TEXT_MODEL`
- `LOG_DAILY_DAYS`
- `LOG_DEPRECATIONS_TRACE`
- `LOG_PAPERTRAIL_HANDLER`
- `LOG_SLACK_EMOJI`
- `LOG_SLACK_USERNAME`
- `LOG_SLACK_WEBHOOK_URL`
- `LOG_STDERR_FORMATTER`
- `LOG_SYSLOG_FACILITY`
- `MAIL_EHLO_DOMAIN`
- `MAIL_LOG_CHANNEL`
- `MAIL_SENDMAIL_PATH`
- `MAIL_URL`
- `MEMCACHED_HOST`
- `MEMCACHED_PASSWORD`
- `MEMCACHED_PERSISTENT_ID`
- `MEMCACHED_PORT`
- `MEMCACHED_USERNAME`
- `MYSQL_ATTR_SSL_CA`
- `PAPERTRAIL_PORT`
- `PAPERTRAIL_URL`
- `POSTMARK_API_KEY`
- `QUEUE_FAILED_DRIVER`
- `REDIS_BACKOFF_ALGORITHM`
- `REDIS_BACKOFF_BASE`
- `REDIS_BACKOFF_CAP`
- `REDIS_CACHE_CONNECTION`
- `REDIS_CACHE_DB`
- `REDIS_CACHE_LOCK_CONNECTION`
- `REDIS_CLUSTER`
- `REDIS_DB`
- `REDIS_MAX_RETRIES`
- `REDIS_PERSISTENT`
- `REDIS_PREFIX`
- `REDIS_QUEUE`
- `REDIS_QUEUE_CONNECTION`
- `REDIS_QUEUE_RETRY_AFTER`
- `REDIS_URL`
- `REDIS_USERNAME`
- `RESEND_API_KEY`
- `SEED_ADMIN_1_EMAIL`
- `SEED_ADMIN_1_NAME`
- `SEED_ADMIN_1_PASSWORD`
- `SEED_ADMIN_2_EMAIL`
- `SEED_ADMIN_2_NAME`
- `SEED_ADMIN_2_PASSWORD`
- `SESSION_CONNECTION`
- `SESSION_COOKIE`
- `SESSION_EXPIRE_ON_CLOSE`
- `SESSION_HTTP_ONLY`
- `SESSION_PARTITIONED_COOKIE`
- `SESSION_SAME_SITE`
- `SESSION_SECURE_COOKIE`
- `SESSION_STORE`
- `SESSION_TABLE`
- `SLACK_BOT_USER_DEFAULT_CHANNEL`
- `SLACK_BOT_USER_OAUTH_TOKEN`
- `SQS_PREFIX`
- `SQS_QUEUE`
- `SQS_SUFFIX`

## In `.env.example`, dar nefolosite direct

Aceste chei apar in `.env.example`, dar nu au apel `env()` in codul repo-ului:

- `BCRYPT_ROUNDS` — citita de framework (`vendor/laravel/framework/config/hashing.php`); ramane.
- `BROADCAST_CONNECTION` — citita de framework (`config/broadcasting.php` din vendor); ramane.
- `VITE_APP_NAME` — conventie Vite (variabilele `VITE_*` sunt expuse in JS); ramane.
- `FILAMENT_FILESYSTEM_DISK` — **moarta**, eliminata din `.env.example`. Filament v5 citeste `FILESYSTEM_DISK` (`vendor/filament/support/config/filament.php`), iar toate upload-urile din admin folosesc explicit `->disk('public')`.

## Cross-check `.env.prod.example`

Dupa actualizare, chei folosite in cod dar lipsa din `.env.prod.example`:

- Nicio cheie lipsa dupa actualizare.

Chei in `.env.prod.example`, dar nefolosite direct:

- Niciuna. `MAIL_ENCRYPTION` (inlocuita in Laravel 12 de `MAIL_SCHEME`) si `FILAMENT_FILESYSTEM_DISK` (moarta in Filament v5) au fost eliminate.

## Telefon vs WhatsApp

Configuratia are campuri separate:

- telefon general: `BUSINESS_PHONE` / fallback `CONTACT_PHONE` = `+40 758 522 227`;
- WhatsApp afisat: `BUSINESS_WHATSAPP` = `+40 756 222 260`;
- numar WhatsApp pentru `wa.me`: `BUSINESS_WHATSAPP_DIGITS` / `CONTACT_WHATSAPP` = `40756222260`;
- URL WhatsApp: `BUSINESS_WHATSAPP_URL` si `BUSINESS_WHATSAPP_PREFILLED_URL` folosesc `40756222260`.

Concluzie: telefonul general si WhatsApp sunt modelate separat. `.env.prod.example` a fost corectat sa foloseasca `40756222260` pentru `CONTACT_WHATSAPP`, nu telefonul general.
