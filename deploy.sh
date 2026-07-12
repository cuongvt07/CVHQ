#!/usr/bin/env bash
# ==========================================================================
# CVHQ — deploy nhanh trên server (chạy trong ~/CVHQ).
#   ./deploy.sh [branch] [--dump]
#     branch : nhánh cần deploy (mặc định: stagging)
#     --dump : xuất thêm 1 file SQL sau khi deploy
# Ví dụ:  ./deploy.sh              # deploy stagging
#         ./deploy.sh master       # deploy master
#         ./deploy.sh stagging --dump
# ==========================================================================
set -e

BRANCH="stagging"
DO_DUMP=0
for arg in "$@"; do
  case "$arg" in
    --dump) DO_DUMP=1 ;;
    *) BRANCH="$arg" ;;
  esac
done

APP_CONTAINER="cvhq-app"
DB_CONTAINER="cvhq-db"
DB_NAME="anvwclyo_cvhq"
DB_USER="cvhq"
DB_PASS="cvhq_secret"

cd "$(dirname "$0")"

echo "==> Cập nhật code ($BRANCH)"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

echo "==> Build + khởi động container"
docker compose up -d --build app

echo "==> Migrate + xoá cache"
docker exec "$APP_CONTAINER" php artisan migrate --force
docker exec "$APP_CONTAINER" php artisan optimize:clear

echo "==> Kiểm tra"
git log --oneline -1
curl -s -o /dev/null -w "login: %{http_code}\n" http://localhost:8000/login || true

if [ "$DO_DUMP" = "1" ]; then
  TS=$(date +%Y%m%d_%H%M%S)
  OUT="$HOME/cvhq_${TS}.sql"
  echo "==> Xuất SQL -> $OUT"
  docker exec "$DB_CONTAINER" sh -c \
    "mariadb-dump -u $DB_USER -p$DB_PASS --single-transaction --no-tablespaces --skip-lock-tables --routines --triggers $DB_NAME" > "$OUT"
  ls -lh "$OUT"
fi

echo "==> Xong."
