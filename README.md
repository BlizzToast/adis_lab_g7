# Roary - Social Media Platform

A simple social media platform built with PHP following the MVC pattern.

## Project Structure

```
src/
├── Controllers/        # Request handling and business logic
├── Models/            # Database operations
├── Views/             # HTML templates
├── Core/              # Router, Request, Response, Session
├── public/            # Static assets (CSS, JS, fonts)
└── data/              # SQLite database
```

## Local Setup with Docker

1. Clone the repository
2. Configure admin password in `docker-compose.yml`:
   ```yaml
   environment:
     - ADMIN_PASSWORD=your_secure_password_here
   ```
3. Start the application:
   ```bash
   docker-compose up -d
   ```
4. Access at http://localhost:8080

## Default Credentials

- Admin: `admin` / password set via `ADMIN_PASSWORD` environment variable
- Test users are created automatically on first run

## Features

- User registration and authentication
- Post messages (roars)
- Delete own posts
- Admin panel for user management
- CSRF protection and secure password hashing

## Requirements

- Docker
- Docker Compose

## Stop Application

```bash
docker-compose down
```

## Redis Cache Inspection

Useful commands to inspect Redis cache behavior:

```bash
# Connect to Redis CLI
docker compose exec web redis-cli

# Check cache statistics
INFO stats | grep keyspace_hits
INFO stats | grep keyspace_misses

# Timeline (sorted set of post IDs)
ZCARD posts:timeline              # Count timeline entries (limited to 500)
ZREVRANGE posts:timeline 0 9      # Get newest 10 post IDs
ZRANGE posts:timeline 0 9         # Get oldest 10 post IDs
ZSCORE posts:timeline 3601        # Check if specific ID is in timeline

# Cached posts (JSON with TTL)
KEYS post:*                       # List all cached post keys (avoid in production)
EXISTS post:3601                  # Check if specific post is cached
GET post:3601                     # View cached post JSON
TTL post:3601                     # Check remaining TTL (seconds)

# Memory and database
DBSIZE                            # Total keys in Redis
MEMORY USAGE posts:timeline       # Memory used by timeline (bytes)
INFO keyspace                     # Keys count, expires, avg TTL

# Clear cache (testing only)
FLUSHALL                          # Delete all keys
DEL posts:timeline                # Delete timeline only
```

## Load Testing with k6

Performance testing using k6 in Docker.

### Test Scenarios

1. **doom-scroll** - 95% browsing existing roars, 5% creating new roars  

2. **live-ticker** - 80% creating new roars, 20% browsing (ADIS exercise commentary)  

3. **shout-out** - All VUs creating new accounts and logging in


### Running Tests using Docker

```bash
# Local test
docker-compose run --rm k6 run /scripts/rest/api-ticker.js

# Remote test
docker-compose run --rm -e BASE_URL=https://your-server.com k6 run /scripts/rest/api-ticker.js
```

### Test Configuration

To customize defaults, edit the scenario files in `k6/`:
- `doom-scroll.js` - Adjust VUs, duration, browse/post ratio
- `live-ticker.js` - Adjust VUs, duration, post/browse ratio  
- `shout-out.js` - Registration timing and behavior

Or pass environment variables:
```powershell
docker-compose run --rm `
  -e BASE_URL=http://localhost `
  -e SHARED_USERNAME=testuser1 `
  -e SHARED_PASSWORD=TestPass1234 `
  -e TEST_USERNAME=k6user `
  -e TEST_PASSWORD=K6TestPass1234 `
  k6 run /scripts/doom-scroll.js
```
