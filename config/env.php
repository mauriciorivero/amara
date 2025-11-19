# Configuración de Base de Datos - Sistema AMARA
# IMPORTANTE: Este archivo contiene información sensible y NO debe ser incluido en el control de versiones

# Configuración de MySQL
DB_HOST=localhost
DB_NAME=minerva
DB_USERNAME=adminminerva
DB_PASSWORD=m1n3rv@_2025
DB_CHARSET=utf8mb4

# Configuración de la aplicación
APP_NAME=AMARA
APP_ENV=development
APP_DEBUG=true

# Configuración de sesión
SESSION_TIMEOUT=14400
SESSION_NAME=amara_session

# Configuración de seguridad
CSRF_TOKEN_NAME=amara_csrf_token
PASSWORD_HASH_ALGO=PASSWORD_DEFAULT

# Configuración de logs
LOG_ERRORS=true
LOG_LEVEL=error