#!/usr/bin/env bash
# Makes uploaded photos readable by the web server again. LiteSpeed (Namecheap) answers 403 Forbidden
# for files in group-writable folders, e.g. after "chmod -R 775 storage". PHP runs as the account owner,
# so 755 folders / 644 files are still writable for Laravel.
#
#   cd ~/public_html && bash scripts/fix-permissions.sh
#
# Safe to run more than once. It never deletes anything.
set -u
cd "$(dirname "$0")/.." || exit 1

echo "Setting folders to 755 and files to 644 in storage/ ..."
find storage -type d -exec chmod 755 {} +
find storage -type f -exec chmod 644 {} +
chmod 755 bootstrap bootstrap/cache public 2>/dev/null
find bootstrap/cache -type f -exec chmod 644 {} + 2>/dev/null
echo "Done."
echo

# Checks (and repairs) the public/storage link, then asks the website for a photo.
bash scripts/check-images.sh --fix
