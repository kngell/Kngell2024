#!/bin/bash
#
# backup.sh — Full project + database backup with email report
# Works with Anacron + msmtp
# ------------------------------------------------------------

### CONFIGURATION ###

PROJECT_DIR="/home/kngell/projects/kngell-ecom"
BACKUP_DIR="/mnt/g/backups/kngell-ecom"
DB_NAME="kngell_eshopping"
MY_CNF="/home/kngell/.my.cnf"

EMAIL_TO="daniel.akono@kngell.com"
EMAIL_SUBJECT="Kngell Backup Status — $(date '+%Y-%m-%d %H:%M')"

EXCLUDES=(
    "--exclude=public/"
    "--exclude=App/Views/"
    "--exclude=node_modules/"
    "--exclude=vendor/"
    "--exclude=.git"
    "--exclude=**/.temp_cache"
    "--exclude=**/.cache"
    "--exclude=session_dir/"
    "--exclude=Temp/"
    "--exclude=cache/"
    "--exclude=logs/"
)

RETENTION_DAYS=14
LOG_FILE="/tmp/backup_$(date '+%Y%m%d_%H%M%S').log"

### FUNCTIONS ###

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"
}

send_email_report() {
    # Send the email with a subject line
    if msmtp -a default "$EMAIL_TO" <<EOF
To: $EMAIL_TO
Subject: $EMAIL_SUBJECT

$(cat "$LOG_FILE")
EOF
    then
        log "✓ Email report sent to $EMAIL_TO"
    else
        log "ERROR: Failed to send email report to $EMAIL_TO"
    fi
}

backup_database() {
    local db_file="$BACKUP_DIR/db_backup_${DB_NAME}_$(date '+%Y%m%d').sql.gz"
    log "i Dumping database '$DB_NAME' using $MY_CNF credentials..."
    if mariadb-dump --defaults-extra-file="$MY_CNF" "$DB_NAME" | gzip > "$db_file"; then
        log "✓ Database backup saved to: $db_file"
    else
        log "ERROR: Database backup failed."
    fi
}

backup_files() {
    local archive_name="files_backup_$(date '+%Y%m%d').tar.gz"
    local archive_path="$BACKUP_DIR/$archive_name"
    log "> Backing up project files..."
    if rsync -a --delete "${EXCLUDES[@]}" "$PROJECT_DIR/" "$BACKUP_DIR/files_temp/" >> "$LOG_FILE" 2>&1; then
        tar -czf "$archive_path" -C "$BACKUP_DIR/files_temp" .
        rm -rf "$BACKUP_DIR/files_temp"
        log "✓ Files archived to: $archive_path"
    else
        log "ERROR: File backup failed."
    fi
}

rotate_backups() {
    local PROTECT_TAG="_keep"
    log "↻ Rotating backups older than $RETENTION_DAYS days (excluding *$PROTECT_TAG*)..."
    OLD_BACKUPS=$(find "$BACKUP_DIR" -type f -mtime +$RETENTION_DAYS ! -name "*$PROTECT_TAG*")
    if [[ -z "$OLD_BACKUPS" ]]; then
        log "No old backups to delete."
    else
        echo "$OLD_BACKUPS" | while read -r file; do
            log "Deleting old backup: $file"
            rm -f "$file"
        done
        log "Old backups removed (except protected)."
    fi
}

### MAIN EXECUTION ###

log "=== Starting Backup Job ==="
mkdir -p "$BACKUP_DIR/files_temp"
mkdir -p "$BACKUP_DIR"

backup_files
backup_database
rotate_backups

log "=== Backup Job Finished ==="

send_email_report


# #!/bin/bash
# #
# # backup.sh — Full project + database backup with email report
# # Works with Anacron + ssmtp
# # ------------------------------------------------------------

# ### CONFIGURATION ###

# # Project directory (source)
# PROJECT_DIR="/home/kngell/projects/kngell-ecom"

# # Backup destination
# BACKUP_DIR="/mnt/g/backups/kngell-ecom"

# # Database name
# DB_NAME="kngell_eshopping"

# # Path to MySQL config file with credentials
# MY_CNF="/home/kngell/.my.cnf"

# # Email settings (already in /etc/ssmtp/ssmtp.conf & /etc/ssmtp/revaliases)
# EMAIL_TO="daniel.akono@kngell.com"
# EMAIL_SUBJECT="Kngell Backup Status — $(date '+%Y-%m-%d %H:%M')"

# # Files/folders to exclude
# EXCLUDES=(
#     "--exclude=public/"
#     "--exclude=App/Views/"
#     "--exclude=node_modules/"
#     "--exclude=vendor/"
#     "--exclude=.git"
#     "--exclude=**/.temp_cache"
#     "--exclude=**/.cache"
#     "--exclude=session_dir/"
#     "--exclude=Temp/"
#     "--exclude=cache/"
#     "--exclude=logs/"
# )

# # Number of days to keep backups
# RETENTION_DAYS=14

# # Temp log file
# LOG_FILE="/tmp/backup_$(date '+%Y%m%d_%H%M%S').log"

# ### FUNCTIONS ###

# log() {
#     echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"
# }

# send_email_report() {
#     if cat "$LOG_FILE" | msmtp "$EMAIL_TO"; then
#         log "✓ Email report sent to $EMAIL_TO"
#     else
#         log "ERROR: Failed to send email report to $EMAIL_TO"
#     fi
# }


# # send_email_report() {
# #     cat "$LOG_FILE" | ssmtp "$EMAIL_TO"
# # }

# backup_database() {
#     local db_file="$BACKUP_DIR/db_backup_${DB_NAME}_$(date '+%Y%m%d').sql.gz"
#     log "i Dumping database '$DB_NAME' using $MY_CNF credentials..."
#     if mariadb-dump --defaults-extra-file="$MY_CNF" "$DB_NAME" | gzip > "$db_file"; then
#         log "✓ Database backup saved to: $db_file"
#     else
#         log "ERROR: Database backup failed."
#     fi
# }

# backup_files() {
#     local archive_name="files_backup_$(date '+%Y%m%d').tar.gz"
#     local archive_path="$BACKUP_DIR/$archive_name"
#     log "> Backing up project files..."
#     if rsync -a --delete "${EXCLUDES[@]}" "$PROJECT_DIR/" "$BACKUP_DIR/files_temp/" >> "$LOG_FILE" 2>&1; then
#         tar -czf "$archive_path" -C "$BACKUP_DIR/files_temp" .
#         rm -rf "$BACKUP_DIR/files_temp"
#         log "✓ Files archived to: $archive_path"
#     else
#         log "ERROR: File backup failed."
#     fi
# }

# rotate_backups() {
#     local PROTECT_TAG="_keep"
#     log "↻ Rotating backups older than $RETENTION_DAYS days (excluding *$PROTECT_TAG*)..."

#     OLD_BACKUPS=$(find "$BACKUP_DIR" -type f -mtime +$RETENTION_DAYS ! -name "*$PROTECT_TAG*")

#     if [[ -z "$OLD_BACKUPS" ]]; then
#         log "No old backups to delete."
#     else
#         echo "$OLD_BACKUPS" | while read -r file; do
#             log "Deleting old backup: $file"
#             rm -f "$file"
#         done
#         log "Old backups removed (except protected)."
#     fi
# }


# ### MAIN EXECUTION ###

# log "=== Starting Backup Job ==="
# mkdir -p "$BACKUP_DIR/files_temp"
# mkdir -p "$BACKUP_DIR"

# backup_files
# backup_database
# rotate_backups

# log "=== Backup Job Finished ==="

# send_email_report


