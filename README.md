# adis_lab_g7

## Quick Start

1. **Build and start the container:**
   ```bash
   docker-compose up -d
   ```

2. **Access page:**
   - Main page: http://localhost:8080

3. **Stop the container:**
   ```bash
   docker-compose down
   ```


### Viewing Logs

Inside the container, check Nginx logs at `/var/log/nginx`

View container output (startup messages, PHP errors, etc.):
```bash
docker-compose logs -f web
```

Access container shell for debugging:
```bash
docker exec -it adis_lab_g7_web bash
```