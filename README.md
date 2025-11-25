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
