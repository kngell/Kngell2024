#!/bin/bash

# === CONFIGURATION ===
SOURCE="$HOME/projects/kngell-ecom"
DEST_BASE="/mnt/g/Data/kngell-ecom"
DATE=$(date +%Y%m%d_%H%M%S)
DEST="$DEST_BASE/project_backup_$DATE"
LOG="/mnt/g/BackupLogs/backup_$DATE.log"
TMP_TAR="/tmp/kngell-ecom_backup_$DATE.tar.gz"
FINAL_TAR="$DEST_BASE/kngell-ecom_backup_$DATE.tar.gz"
KEEP_DAYS=7
PROTECT_TAG="_keep"

EXCLUDES=(
    --exclude="node_modules"
    --exclude=".git"
    --exclude="vendor"
    --exclude="public"
    --exclude="App/Views/**"
    --exclude="**/.temp_cache"
    --exclude="**/.cache"
    --exclude="session_dir/"
    --exclude="Temp/"
    --exclude="cache/"
    --exclude="logs/"
    --exclude="*.log"
    --exclude="*.tmp"
    --exclude="kngell-ecom/"  # Avoid recursion if backups reside inside source
)

# === STEP 1: CREATE DESTINATION AND START LOGGING ===
mkdir -p "$DEST"
echo "🚀 Starting backup from $SOURCE to $DEST" | tee "$LOG"

# === STEP 2: RSYNC FILES EXCLUDING NON-ESSENTIALS ===
echo "📁 Starting rsync backup..." | tee -a "$LOG"

rsync -avL --no-perms --no-owner --no-group --delete "${EXCLUDES[@]}" \
  "$SOURCE/" "$DEST/" 2>>"$LOG"
RSYNC_EXIT_CODE=$?

if [[ "$RSYNC_EXIT_CODE" -eq 23 ]]; then
  echo "⚠️ WARNING: rsync exited with code 23 (some files were skipped, likely due to www-data ownership)." | tee -a "$LOG"
  echo "           These are excluded by design: sessions, caches, logs, tmp files." | tee -a "$LOG"
elif [[ "$RSYNC_EXIT_CODE" -ne 0 ]]; then
  echo "❌ ERROR: rsync failed with exit code $RSYNC_EXIT_CODE" | tee -a "$LOG"
  exit $RSYNC_EXIT_CODE
fi

# === STEP 2.5: DUMP MARIADB DATABASE ===
echo "🗃️ Dumping MariaDB database 'kngell_eshopping'..." | tee -a "$LOG"

DB_BACKUP="$DEST/mariadb_kngell_eshopping.sql.gz"

mysqldump --defaults-file=~/.my.cnf kngell_eshopping | gzip > "$DB_BACKUP"
DUMP_EXIT=$?

if [[ "$DUMP_EXIT" -ne 0 ]]; then
  echo "❌ ERROR: Database dump failed with exit code $DUMP_EXIT" | tee -a "$LOG"
  exit $DUMP_EXIT
fi

echo "✅ Database backup saved to $DB_BACKUP" | tee -a "$LOG"

# === STEP 3: TAR + GZIP BACKUP IN /tmp THEN MOVE TO DEST BASE ===
echo "📦 Archiving to $TMP_TAR" | tee -a "$LOG"
START=$(date +%s)
tar -czf "$TMP_TAR" -C "$DEST_BASE" "project_backup_$DATE" >> "$LOG" 2>&1
TAR_EXIT=$?
END=$(date +%s)

if [[ $TAR_EXIT -ne 0 ]]; then
  echo "❌ ERROR: Failed to create tar.gz archive." | tee -a "$LOG"
  exit 1
fi

echo "⏱️ Compression took $((END - START)) seconds" | tee -a "$LOG"

# === STEP 4: Move compressed archive to final location ===
mv "$TMP_TAR" "$FINAL_TAR"
echo "✅ Archive saved to $FINAL_TAR" | tee -a "$LOG"

# === STEP 5: Cleanup intermediate extracted backup ===
rm -rf "$DEST"
echo "🧹 Removed temporary backup folder $DEST" | tee -a "$LOG"

# === STEP 6: Rotate old backups (except protected) ===
echo "♻️ Rotating backups older than $KEEP_DAYS days (excluding *$PROTECT_TAG*)" | tee -a "$LOG"

OLD_BACKUPS=$(find "$DEST_BASE" -maxdepth 1 -type f -name "*.tar.gz" \
    ! -name "*$PROTECT_TAG*.tar.gz" \
    -mtime +$KEEP_DAYS)

if [[ -z "$OLD_BACKUPS" ]]; then
    echo "🟢 No old backups to delete." | tee -a "$LOG"
else
    echo "$OLD_BACKUPS" | tee -a "$LOG"
    echo "$OLD_BACKUPS" | xargs -r rm -v | tee -a "$LOG"
fi

# === STEP 7: Open the log (optional) ===
echo "📖 Opening log file $LOG" | tee -a "$LOG"
xdg-open "$LOG" &>/dev/null &

echo "✅ Backup completed successfully." | tee -a "$LOG"

if [[ $? -eq 0 ]]; then
  echo -e "From: daniel.akono@kngell.com\nTo: daniel.akono@kngell.com\nSubject: ✅ Backup Success\n\nBackup completed successfully at $(date)" | ssmtp daniel.akono@kngell.com
else
  echo -e "From: daniel.akono@kngell.com\nTo: daniel.akono@kngell.com\nSubject: ❌ Backup Failed\n\nBackup failed at $(date)" | ssmtp daniel.akono@kngell.com
fi

exit 0
