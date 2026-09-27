#!/bin/bash

set -euo pipefail

CONFIG="/etc/webapp/db.json"
MIGRATION_DIR="/opt/website/database/migrations"

echo "Starting database migrations..."

DB_HOST=$(jq -r '.host' "$CONFIG")
DB_USER=$(jq -r '.username' "$CONFIG")
DB_PASSWORD=$(jq -r '.password' "$CONFIG")
DB_NAME=$(jq -r '.database' "$CONFIG")

MYSQL_CONFIG=$(mktemp)

cat > "$MYSQL_CONFIG" <<EOF
[client]
host=$DB_HOST
user=$DB_USER
password=$DB_PASSWORD
database=$DB_NAME
EOF

chmod 600 "$MYSQL_CONFIG"

trap 'rm -f "$MYSQL_CONFIG"' EXIT

# Create migration history table
mysql --defaults-extra-file="$MYSQL_CONFIG" <<'SQL'
CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL

for migration in "$MIGRATION_DIR"/*.sql; do

    [ -e "$migration" ] || continue

    filename=$(basename "$migration")

    already_applied=$(mysql \
        --defaults-extra-file="$MYSQL_CONFIG" \
        --batch \
        --skip-column-names \
        -e "SELECT COUNT(*) FROM schema_migrations WHERE migration='$filename';")

    if [ "$already_applied" = "0" ]; then

        echo "Applying migration: $filename"

        mysql \
            --defaults-extra-file="$MYSQL_CONFIG" \
            < "$migration"

        mysql \
            --defaults-extra-file="$MYSQL_CONFIG" \
            -e "INSERT INTO schema_migrations (migration) VALUES ('$filename');"

        echo "Migration completed: $filename"

    else

        echo "Skipping already applied migration: $filename"

    fi

done

echo "All database migrations completed successfully."


