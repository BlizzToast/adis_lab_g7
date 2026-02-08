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

## Docker Deployment

Roary runs with six separate containers:
- **php-fpm**: PHP application with SQLite and Redis extensions
- **nginx**: Web server (official nginx:alpine image)
- **redis**: Caching layer (official redis:alpine image)
- **prometheus**: Metrics collection (official prometheus image)
- **node-exporter**: Host metrics (prom/node-exporter image)
- **grafana**: Metrics visualization (official grafana image)


### Quick Start (Local Development)

```bash
docker compose up -d
```

Access at http://localhost

### Production with SSL

For production deployment with SSL certificates (e.g., from Let's Encrypt):

```bash
docker compose -f docker-compose.ssl.yml up -d
```

This expects SSL certificates at:
- `/etc/letsencrypt/live/<domain>/fullchain.pem`
- `/etc/letsencrypt/live/<domain>/privkey.pem`

To use custom certificate paths, edit `docker-compose.ssl.yml` and update the volume mounts.

### Monitoring
**Access monitoring panels:**
- Grafana: http://localhost:3000 (admin/admin)
- Prometheus: http://localhost:9090


### Data Persistence

SQLite database is stored in `./data/` and mounted into the container. This ensures data survives container restarts.

### Stop Application

```bash
docker compose down
```

### Rebuild After Code Changes

```bash
docker compose up -d --build
```

## Architecture

| Service | Image | Port | Purpose |
|---------|-------|------|---------|
| php-fpm | Custom (Dockerfile) | 9000 (internal) | PHP application |
| nginx | nginx:alpine | 80, 443 | Web server |
| redis | redis:alpine | 6379 (internal) | Cache |
| prometheus | prom/prometheus | 9090 | Metrics collection |
| node-exporter | prom/node-exporter | 9100 | Host metrics |
| grafana | grafana/grafana | 3000 | Metrics visualization |
## Legacy Configuration

Previous monolithic Docker setup (Nginx + PHP-FPM + Redis in one container) is preserved in:
- `docker-compose.legacy.yml`
- `docker/web/Dockerfile.legacy`

## Redis Cache Inspection

```bash
# Connect to Redis CLI
docker compose exec redis redis-cli

# Check cache statistics
INFO stats | grep keyspace_hits

# Timeline (sorted set of post IDs)
ZCARD posts:timeline
ZREVRANGE posts:timeline 0 9

# Cached posts
GET post:1
TTL post:1

# Clear cache
FLUSHALL
```

## Load Testing with k6

Performance testing using k6 in Docker.

### Test Scenarios

1. **doom-scroll** - 95% browsing existing roars, 5% creating new roars
2. **live-ticker** - 80% creating new roars, 20% browsing
3. **shout-out** - All VUs creating new accounts and logging in

### Running Tests

```bash
# Start k6 container (uses legacy compose for network mode)
docker compose -f docker-compose.legacy.yml --profile test run --rm k6 run /scripts/rest/api-ticker.js

# Remote test
docker compose -f docker-compose.legacy.yml --profile test run --rm -e BASE_URL=https://your-server.com k6 run /scripts/rest/api-ticker.js
```
