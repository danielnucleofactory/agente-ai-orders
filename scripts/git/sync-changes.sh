#!/usr/bin/env bash
# Añade todos los cambios, hace commit y empuja la rama actual al remoto.
set -euo pipefail

if [[ -z "${*:-}" ]]; then
  echo "Uso: scripts/git/sync-changes.sh <mensaje de commit>" >&2
  echo "Ejemplo: scripts/git/sync-changes.sh \"fix: corrige validación del formulario\"" >&2
  exit 1
fi

MSG="$*"
REMOTE="${GIT_REMOTE:-origin}"
BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ "$BRANCH" == "HEAD" ]]; then
  echo "No estás en una rama (detached HEAD). Cambia a una rama antes." >&2
  exit 1
fi

if git diff --quiet && git diff --cached --quiet; then
  echo "No hay cambios para commitear. Working tree limpio." >&2
  exit 1
fi

echo "→ git add -A"
git add -A

echo "→ git commit"
git commit -m "$MSG"

echo "→ push $REMOTE $BRANCH"
git push -u "$REMOTE" "$BRANCH"

echo "Listo. Cambios sincronizados en $REMOTE/$BRANCH."
