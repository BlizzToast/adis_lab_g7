# REST API K6 Performance Tests

This directory contains k6 performance test scripts specifically designed for testing the REST API endpoints of the Roary application (Task 3 of Assignment 4).

## Overview

These tests are designed to compare the performance of the RESTful API implementation against the traditional server-side rendering (SSR) approach.

## Test Scenarios

### 1. api-doom (REST API Doom-Scroll)
- **Pattern**: Read-heavy (95% GET, 5% POST)
- **Endpoint**: `/api/posts`
- **Purpose**: Simulates users primarily browsing posts with occasional posting
- **File**: `api-doom.js`

### 2. api-ticker (REST API Live-Ticker)
- **Pattern**: Write-heavy (80% POST, 20% GET)
- **Endpoint**: `/api/posts`
- **Purpose**: Simulates high posting activity (e.g., during live events)
- **File**: `api-ticker.js`

## Prerequisites

1. REST API endpoints must be implemented:
   - `GET /api/posts` - Fetch all posts as JSON
   - `POST /api/posts` - Create a new post

2. Authentication via PHP session cookie (PHPSESSID)

3. Docker and Docker Compose installed

## Usage

### Using the Bash Script (Recommended)

```bash
# Basic usage
./run-k6-rest-test.sh api-doom

# With custom parameters
./run-k6-rest-test.sh api-doom --target https://your-server.com --vus 100 --duration 30s

# With session authentication
./run-k6-rest-test.sh api-ticker --session-id YOUR_PHPSESSID --validate

# Full example
./run-k6-rest-test.sh api-doom \
  --target https://1234abc.ul.bw-cloud-instance.org \
  --vus 200 \
  --duration 1m \
  --session-id abc123def456 \
  --validate
```

### Script Options

- `--target <url>` - Target server URL (default: http://localhost)
- `--vus <number>` - Number of virtual users (default: 100)
- `--duration <time>` - Test duration (e.g., 30s, 1m, 5m) (default: 30s)
- `--session-id <id>` - PHP session ID for authentication
- `--validate` - Enable response validation checks

### Direct Docker Compose Usage

```bash
# Run api-doom scenario
docker-compose run --rm \
  -e BASE_URL=http://localhost \
  -e PHPSESSID=your_session_id \
  k6 run --vus 100 --duration 30s /scripts/rest/api-doom.js

# Run api-ticker scenario
docker-compose run --rm \
  -e BASE_URL=http://localhost \
  -e PHPSESSID=your_session_id \
  k6 run --vus 100 --duration 30s /scripts/rest/api-ticker.js
```

## Expected API Response Formats

### GET /api/posts

Expected response:
```json
[
  {
    "id": 1,
    "username": "john_doe",
    "content": "Hello, World!",
    "created_at": "2024-01-15T10:30:00Z"
  },
  // ... more posts
]
```

### POST /api/posts

Request body:
```json
{
  "content": "My new roar!"
}
```

Expected response:
```json
{
  "success": true,
  "id": 123,
  "message": "Roar created successfully"
}
```

## Performance Metrics

The tests measure and report:

- **Request Duration**: Average, median, p90, p95
- **Request Rate**: Requests per second
- **Success Rate**: Percentage of successful requests
- **Data Transfer**: Bytes sent/received
- **Concurrent Users**: Number of virtual users
- **Iterations**: Total completed test iterations

## Comparing with SSR Performance

To compare REST API performance with SSR:

1. Run the original SSR tests:
   ```bash
   ./run-k6-test.sh doom-scroll --vus 100 --duration 30s
   ```

2. Run the REST API tests:
   ```bash
   ./run-k6-rest-test.sh api-doom --vus 100 --duration 30s
   ```

3. Compare the metrics, focusing on:
   - Response times (especially median and p95)
   - Request throughput (requests/sec)
   - Data transfer volumes
   - Error rates

## Troubleshooting

### Authentication Issues
If you see authentication errors, ensure:
1. You're using a valid PHPSESSID from an active session
2. The session hasn't expired
3. The cookie is being sent correctly

### 404 Errors
If the API endpoints return 404:
1. Verify `/api/posts.php` exists in `/var/www/html/api/`
2. Check that the web server is configured to handle the `/api/` path
3. Ensure the API implementation is complete

### JSON Parse Errors
If JSON parsing fails:
1. Check that the API returns valid JSON
2. Ensure Content-Type header is `application/json`
3. Verify there's no HTML mixed in the response

## Test Utilities (utils.js)

The `utils.js` file provides reusable functions:

- `setupSession()` - Configure authentication
- `fetchPostsAPI()` - GET request to fetch posts
- `createPostAPI()` - POST request to create a post
- `getRandomContent()` - Generate random post content
- `measureAPIvsSSR()` - Compare API vs SSR performance

## Notes

- All tests use a 0.5-second sleep between iterations to simulate realistic user behavior
- Virtual users (VUs) are ramped up gradually at test start
- Tests are configured for 100 VUs and 30s duration by default
- Validation checks can be enabled to ensure response correctness