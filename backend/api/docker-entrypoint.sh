#!/bin/sh
set -e

# Espera o MySQL estar disponível
echo "Aguardando MySQL em $DB_HOST:$DB_PORT..."
until php -r "new PDO(\"mysql:host=$DB_HOST;port=$DB_PORT\", \"$DB_USERNAME\", \"$DB_PASSWORD\");" > /dev/null 2>&1; do
  sleep 1
done
echo "MySQL disponível!"

# Roda migrações
php bin/hyperf.php migrate

# Inicia servidor Hyperf
exec php bin/hyperf.php start
