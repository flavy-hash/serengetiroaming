#!/usr/bin/env bash
# Finds out why photos don't show on the live site, and (with --fix) repairs what can be repaired.
#
#   cd to the project folder on the server, then:
#     bash scripts/check-images.sh          # only looks and reports
#     bash scripts/check-images.sh --fix    # also repairs the uploads link and the permissions
#
# Safe to run more than once. It never deletes your photos.
set -uo pipefail

FIX=0
[ "${1:-}" = "--fix" ] && FIX=1

ok()   { printf '  \033[32mOK\033[0m    %s\n' "$*"; }
bad()  { printf '  \033[31mPROBLEM\033[0m %s\n' "$*"; PROBLEMS=$((PROBLEMS + 1)); }
note() { printf '  ..    %s\n' "$*"; }
PROBLEMS=0

if [ ! -f artisan ]; then
    echo "Run this from the project folder (the one that contains the file 'artisan')." >&2
    exit 1
fi
ROOT="$(pwd -P)"
TARGET="$ROOT/storage/app/public"
LINK="$ROOT/public/storage"

echo "Project folder: $ROOT"
echo

echo "1. The folder where photos are stored"
if [ -d "$TARGET" ]; then
    FILES=$(find "$TARGET" -type f ! -name '.gitignore' 2>/dev/null | wc -l | tr -d ' ')
    if [ "$FILES" -gt 0 ]; then ok "storage/app/public exists and holds $FILES files"; else bad "storage/app/public is EMPTY: there are no photos on this server yet (see step 5)"; fi
    for f in tour-pages/safaris/lion.jpg destinations/tarangire.jpg vehicles/coaster.jpg; do
        [ -f "$TARGET/$f" ] && note "found $f" || note "missing $f"
    done
else
    bad "storage/app/public does not exist"
    [ "$FIX" = 1 ] && mkdir -p "$TARGET" && note "created it"
fi
echo

echo "2. The link that makes the photos reachable (public/storage)"
if [ -L "$LINK" ]; then
    DEST="$(readlink "$LINK")"
    if [ -e "$LINK" ] && [ "$(readlink -f "$LINK")" = "$(readlink -f "$TARGET")" ]; then
        ok "public/storage -> $DEST"
    else
        bad "public/storage is a link to '$DEST', which is wrong or broken (probably uploaded from another computer)"
        if [ "$FIX" = 1 ]; then rm "$LINK" && php artisan storage:link && ok "link recreated"; fi
    fi
elif [ -d "$LINK" ]; then
    bad "public/storage is a normal folder, not a link (it was uploaded like a folder)"
    if [ "$FIX" = 1 ]; then
        mv "$LINK" "$LINK.backup-$(date +%Y%m%d%H%M%S)" && php artisan storage:link && ok "moved it aside (nothing deleted) and created the proper link"
    fi
else
    bad "public/storage does not exist"
    if [ "$FIX" = 1 ]; then php artisan storage:link && ok "link created"; fi
fi
echo

echo "3. Permissions (a 403 'Forbidden' is most often this)"
# Every folder on the way to the photos must be enterable by the web server.
P="$TARGET"
BLOCKED=""
while [ "$P" != "/" ] && [ "$P" != "$(dirname "$HOME")" ]; do
    MODE=$(stat -c '%a' "$P" 2>/dev/null || echo "?")
    case "$MODE" in ?*[1357]) ;; ?) ;; *) BLOCKED="$BLOCKED\n      $P ($MODE)";; esac
    P="$(dirname "$P")"
done
HOMEMODE=$(stat -c '%a' "$HOME" 2>/dev/null || echo "?")
if [ -n "$BLOCKED" ]; then
    bad "these folders can't be entered by the web server:$(printf "$BLOCKED")"
else
    ok "folders on the way to the photos can be entered"
fi
note "home folder permission: $HOMEMODE (711 or 755 is normal)"
BADFILES=$(find "$TARGET" \( -type d ! -perm -005 -o -type f ! -perm -004 \) 2>/dev/null | wc -l | tr -d ' ')
if [ "$BADFILES" -gt 0 ]; then
    bad "$BADFILES photo files/folders can't be read by the web server"
else
    ok "all photo files and folders are readable"
fi
if [ "$FIX" = 1 ]; then
    find "$TARGET" -type d -exec chmod 755 {} + 2>/dev/null
    find "$TARGET" -type f -exec chmod 644 {} + 2>/dev/null
    chmod 755 "$ROOT/storage" "$ROOT/storage/app" "$ROOT/public" 2>/dev/null
    ok "permissions set: folders 755, files 644"
fi
echo

echo "4. What the website itself says"
APP_URL="$(grep -E '^APP_URL=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"')"
note "APP_URL in .env: ${APP_URL:-<not set>}"
SAMPLE="$(cd "$TARGET" 2>/dev/null && find . -type f \( -name '*.jpg' -o -name '*.webp' -o -name '*.png' \) | head -1 | sed 's|^\./||')"
if [ -n "${APP_URL:-}" ] && [ -n "${SAMPLE:-}" ] && command -v curl >/dev/null 2>&1; then
    CODE=$(curl -s -o /dev/null -m 20 -w '%{http_code}' "$APP_URL/storage/$SAMPLE" 2>/dev/null || echo "---")
    note "$APP_URL/storage/$SAMPLE -> HTTP $CODE"
    case "$CODE" in
        200) ok "the photos are being served" ;;
        403) bad "403 Forbidden: fix steps 2 and 3 above (run again with --fix)" ;;
        404) bad "404 Not Found: the link or the file is missing (steps 1 and 2)" ;;
        *)   bad "unexpected answer ($CODE): check that APP_URL in .env is the website's real address, then run: php artisan config:clear" ;;
    esac
else
    note "skipped (needs APP_URL in .env, at least one photo and curl)"
fi
echo

echo "5. If the photos are missing from this server"
note "Photos are not part of the code: they live in storage/app/public and must be uploaded separately."
note "Copy your local storage/app/public folder to the same place here (a zip in cPanel File Manager works),"
note "then run this script again with --fix. Starter photos for destinations, vehicles and activities are"
note "also re-created by:  php artisan db:seed --class=DestinationSeeder --force  (and VehicleSeeder, ActivitySeeder)."
echo

if [ "$PROBLEMS" -eq 0 ]; then
    echo "Everything looks fine. If photos still don't show, clear the browser cache (Ctrl+Shift+R)."
else
    echo "$PROBLEMS problem(s) found."
    [ "$FIX" = 0 ] && echo "Run again with --fix to repair the link and permissions:  bash scripts/check-images.sh --fix"
fi
