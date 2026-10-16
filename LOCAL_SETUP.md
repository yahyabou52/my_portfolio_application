# Local setup

Platform: http://localhost:8765

Demo website: http://localhost:8765/s/demo

Demo editor: http://localhost:8765/s/demo/admin/website

Demo login: admin / password. New customers choose email/password at /signup.

Restart from this repository: powershell -File .local/start.ps1

PHP listens on 127.0.0.1:8765. Isolated MariaDB listens on 127.0.0.1:3308. Logs, process IDs and data are in .local/. Runtime paths are recorded in .local/start.ps1.

The original database remains portfolio_db. The registry is portfolio_platform. New customers each receive a portfolio_site_* database. Do not rerun the old .local/bootstrap.php: it imports a destructive fresh schema.

See README.md and docs/SAAS_ARCHITECTURE.md for code structure, testing and remaining scope.
