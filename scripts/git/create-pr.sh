#!/usr/bin/env bash
# Abre un PR hacia develop con título/cuerpo derivados de los commits (GitHub CLI).
set -euo pipefail

REMOTE="${GIT_REMOTE:-origin}"
BASE="${BASE_BRANCH:-develop}"

if ! command -v gh >/dev/null 2>&1; then
  echo "Instala y autentica GitHub CLI: https://cli.github.com/  (gh auth login)" >&2
  exit 1
fi

HEAD=$(git rev-parse --abbrev-ref HEAD)
if [[ "$HEAD" == "HEAD" ]]; then
  echo "No estás en una rama (detached HEAD)." >&2
  exit 1
fi
if [[ "$HEAD" == "$BASE" ]]; then
  echo "No abras PR desde la rama '$BASE'. Usa una rama de feature." >&2
  exit 1
fi

git fetch "$REMOTE" "$BASE" 2>/dev/null || git fetch "$REMOTE"

# Asegurar que la rama está en el remoto
if ! git rev-parse --verify --quiet "@{u}" >/dev/null 2>&1; then
  echo "→ sin upstream; push de la rama actual..."
  git push -u "$REMOTE" "$HEAD"
fi

TITLE="${1:-}"
if [[ -z "$TITLE" ]]; then
  TITLE=$(git log -1 --pretty=%s)
fi

BODY_FILE=$(mktemp)
trap 'rm -f "$BODY_FILE"' EXIT

{
  echo "## Cambios incluidos"
  echo ""
  if git merge-base --is-ancestor "$REMOTE/$BASE" HEAD 2>/dev/null; then
    git log "$REMOTE/$BASE..HEAD" --pretty=format:'- %s (`%h`)' || true
  else
    git log -15 --pretty=format:'- %s (`%h`)'
  fi
  echo ""
  echo ""
  echo "---"
  echo "**Merge objetivo:** Squash and merge → \`$BASE\`"
  echo ""
  echo "_PR generado con \`scripts/git/create-pr.sh\`. Asigna reviewers en GitHub._"
} > "$BODY_FILE"

echo "→ gh pr create --base $BASE --head $HEAD"
# gh imprime la URL del PR en stdout (una línea en versiones recientes).
URL=$(gh pr create --base "$BASE" --head "$HEAD" --title "$TITLE" --body-file "$BODY_FILE")

echo ""
echo "Pull request: $URL"
