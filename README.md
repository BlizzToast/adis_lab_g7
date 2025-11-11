# adis_lab_g7



## Quick Start Docker

1. **Build and start the container:**
   ```bash
   docker-compose up -d
   ```

2. **Access page:**
   - Main page: http://localhost:8080

3. **Reload Nginx configuration (after changing `docker/nginx/default.conf`)**; may also require cache clear in browser to see changes:
   ```bash
   docker compose exec web nginx -s reload
   ```

4. **Stop the container:**
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

## Run with local PHP Installation on MAC with Homebrew

```bash 
php -S localhost:8000 -t /Users/<user>/Developer/adis_lab_g7/src
```    

## Useful commands
Reload nginx configuration on (debian-based systems):
```bash
sudo systemctl reload nginx
```

## Common Issues
### Permission Issues with SQLite Database
If you encounter permission issues with the SQLite database file (`users.db`), ensure that the web server user (e.g., `www-data` for Nginx/Apache) has the necessary read and write permissions to the `data/` directory and the database file.