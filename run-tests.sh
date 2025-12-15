#!/bin/bash

# Simple k6 test runner for SSR and REST API tests

if [ $# -eq 0 ]; then
    echo "Usage: $0 <scenario> [vus] [duration]"
    echo ""
    echo "SSR Scenarios:"
    echo "  doom-scroll    - 95% browse, 5% post"
    echo "  live-ticker    - 80% post, 20% browse"
    echo "  shout-out      - 100% user registration"
    echo ""
    echo "API Scenarios:"
    echo "  api-doom       - 95% GET, 5% POST"
    echo "  api-ticker     - 80% POST, 20% GET"
    echo ""
    echo "Examples:"
    echo "  $0 doom-scroll"
    echo "  $0 api-doom 100 30s"
    exit 1
fi

SCENARIO=$1
VUS=${2:-100}
DURATION=${3:-30s}

# Determine script path
if [[ "$SCENARIO" == api-* ]]; then
    SCRIPT_PATH="/scripts/rest/${SCENARIO}.js"
    if [ ! -f "k6-rest/${SCENARIO}.js" ]; then
        echo "Error: k6-rest/${SCENARIO}.js not found"
        exit 1
    fi
else
    SCRIPT_PATH="/scripts/${SCENARIO}.js"
    if [ ! -f "k6/${SCENARIO}.js" ]; then
        echo "Error: k6/${SCENARIO}.js not found"
        exit 1
    fi
fi

echo "Running: $SCENARIO (VUs: $VUS, Duration: $DURATION)"
docker-compose run --rm -e BASE_URL=http://localhost k6 run --vus $VUS --duration $DURATION "$SCRIPT_PATH"
