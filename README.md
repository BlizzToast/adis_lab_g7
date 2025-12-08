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

## Load Testing with k6

Performance testing using k6 in Docker.

### Test Scenarios

1. **doom-scroll** - 95% browsing existing roars, 5% creating new roars  

2. **live-ticker** - 80% creating new roars, 20% browsing (ADIS exercise commentary)  

3. **shout-out** - All VUs creating new accounts and logging in


### Running Tests

**Basic usage:**
```powershell
.\run-k6-test.ps1 -Scenario doom-scroll
.\run-k6-test.ps1 -Scenario live-ticker
.\run-k6-test.ps1 -Scenario shout-out
```

**Against remote server:**
```powershell
.\run-k6-test.ps1 -Scenario doom-scroll -Target https://your-server.com
```

**Custom parameters:**
```powershell
# Custom virtual users and duration
.\run-k6-test.ps1 -Scenario doom-scroll -VUs 200 -Duration 1m

# Enable validation checks (stricter testing)
.\run-k6-test.ps1 -Scenario live-ticker -Validate
```

**Direct Docker usage:**
```bash
# Local test
docker-compose run --rm k6 run /scripts/doom-scroll.js

# Remote test
docker-compose run --rm -e BASE_URL=https://your-server.com k6 run /scripts/live-ticker.js
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
