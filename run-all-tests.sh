#!/bin/bash

# Run all k6 tests for Assignment 4

echo "Starting all tests for Assignment 4"
echo "===================================="
echo ""

# Check if Docker is running
docker ps > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "Error: Docker is not running"
    exit 1
fi

# Start web container
echo "Starting web container..."
docker-compose up -d web
sleep 2

# SSR Tests
echo ""
echo "1. Running SSR Doom-Scroll Test (95% browse, 5% post)..."
echo "---------------------------------------------------------"
./run-tests.sh doom-scroll 100 30s

echo ""
echo "2. Running SSR Live-Ticker Test (80% post, 20% browse)..."
echo "---------------------------------------------------------"
./run-tests.sh live-ticker 100 30s

echo ""
echo "3. Running SSR Shout-Out Test (100% registration)..."
echo "----------------------------------------------------"
./run-tests.sh shout-out 100 30s

# REST API Tests
echo ""
echo "4. Running API Doom-Scroll Test (95% GET, 5% POST)..."
echo "-----------------------------------------------------"
./run-tests.sh api-doom 100 30s

echo ""
echo "5. Running API Live-Ticker Test (80% POST, 20% GET)..."
echo "------------------------------------------------------"
./run-tests.sh api-ticker 100 30s

echo ""
echo "===================================="
echo "All tests completed"
echo "Check the output above for results"
