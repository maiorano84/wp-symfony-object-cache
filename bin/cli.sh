#!/bin/bash

# OSX Utility script - Runs a Docker CLI service
# Usage: ./bin/cli.sh <service> ...
# Example: ./bin/cli.sh npm run watch
# Example: ./bin/cli.sh npm run build

COMPOSE_PROJECT_NAME=the-source-website-cli
docker compose -p $COMPOSE_PROJECT_NAME -f cli.yml run --rm $@
