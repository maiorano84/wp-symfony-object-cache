#!/bin/bash

# OSX Utility script - Starts a local installation
# Usage (Detached Mode): ./bin/up.sh
# Usage (Watch Mode): ./bin/up.sh -w

COMPOSE_ACTION="up -d"
while getopts ":w" opt; do
  case ${opt} in
  w)
    COMPOSE_ACTION=watch
    ;;
  \?)
    echo "Invalid option: $OPTARG" 1>&2
    exit
    ;;
  :)
    echo "Invalid option: $OPTARG requires an argument" 1>&2
    exit
    ;;
  esac
done
shift $((OPTIND - 1))

docker compose $COMPOSE_ACTION