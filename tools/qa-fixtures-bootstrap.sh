#!/usr/bin/env bash
# Canonical Volume 1 editorial bootstrap QA lives in qa-editorial-bootstrap.sh
# (isolated Docker project; never the primary local volumes or production).
exec "$(cd "$(dirname "$0")" && pwd)/qa-editorial-bootstrap.sh"
