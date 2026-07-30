# Deploy — Backend on EC2

Runbook to run the DevMind backend in production on the EC2 host. Database is
RDS (managed, not a container). Frontend deploy is a later step — not covered here.

## Steps

1. Pull latest code on the EC2, inside `~/devmind-ai-project`:
   ```
   git pull
   ```

2. Create the real production env from the example (do this on the server —
   never commit this file):
   ```
   cp backend/.env.production.example backend/.env
   ```
   Edit `backend/.env` and set the real values:
   - `DB_HOST` → RDS endpoint
   - `DB_PASSWORD` → RDS password
   - `OPENAI_API_KEY`
   - `APP_URL` / `SANCTUM_STATEFUL_DOMAINS` → EC2 public IP

3. Build and start the stack:
   ```
   docker compose -f docker-compose.prod.yml up -d --build
   ```

4. Install PHP dependencies for production (no dev packages):
   ```
   docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
   ```

5. Generate the app key:
   ```
   docker compose -f docker-compose.prod.yml exec app php artisan key:generate
   ```

6. Run migrations against RDS:
   ```
   docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
   ```

7. Cache config and routes:
   ```
   docker compose -f docker-compose.prod.yml exec app php artisan config:cache
   docker compose -f docker-compose.prod.yml exec app php artisan route:cache
   ```

8. Smoke test:
   ```
   curl -I http://localhost/api/v1/health
   ```
   Expect `200`. Then confirm from a browser: `http://EC2_PUBLIC_IP/api/v1/health`.

9. The `worker` service already runs `php artisan queue:work` and picks up
   queued analyses automatically — no manual step needed.

## Troubleshooting

- **502 Bad Gateway** — app/php not reachable. Check `docker compose -f docker-compose.prod.yml ps`
  for `app` status, and confirm `fastcgi_pass app:9000;` in `docker/nginx/prod.conf` matches
  the `app` service name.
- **DB connection error** — check `DB_HOST`/`DB_PASSWORD` in `backend/.env`, and confirm
  the RDS security group allows inbound 5432 from the EC2 instance's security group.
