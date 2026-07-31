# Deploy — DevMind on EC2

Runbook to run the DevMind backend and frontend in production on the EC2 host.
Database is RDS (managed, not a container). Single nginx serves both the
React SPA and the Laravel API.

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

## Frontend

Build is static — no node/vite container. Build on host (or Mac, see fallback),
nginx serves the output straight off disk via the `./frontend/dist` mount in
`docker-compose.prod.yml`.

1. Pull latest code on the EC2 (skip if just done in step 1 above):
   ```
   git pull
   ```

2. Build the frontend on the EC2. `frontend/.env.production` is committed and
   picked up automatically by `vite build`:
   ```
   cd frontend && npm ci && npm run build
   cd ..
   ```
   Output lands in `frontend/dist`.

   **Low-RAM caveat (1 GB instance):** `npm run build` can get OOM-killed. Two
   options:
   - Add 2 GB swap once, then retry the build:
     ```
     sudo fallocate -l 2G /swapfile
     sudo chmod 600 /swapfile
     sudo mkswap /swapfile
     sudo swapon /swapfile
     ```
   - Or build on the Mac and ship `dist/` to the EC2 instead:
     ```
     cd frontend && npm ci && npm run build
     rsync -avz --delete dist/ ec2-user@EC2_PUBLIC_IP:~/devmind-ai-project/frontend/dist/
     ```

3. Recreate/reload nginx to pick up the mounted `dist/` and the new
   `docker/nginx/prod.conf`:
   ```
   docker compose -f docker-compose.prod.yml up -d
   ```
   If only `prod.conf` changed and `dist/` is already in place, a lighter
   reload works too:
   ```
   docker compose -f docker-compose.prod.yml restart nginx
   ```

4. CORS changed (`backend/config/cors.php`) — clear the cached config so
   Laravel picks it up:
   ```
   docker compose -f docker-compose.prod.yml exec app php artisan config:cache
   ```

5. Test in a browser: open `http://EC2_PUBLIC_IP/` — the React app loads.
   Check the Network tab: API calls go to `http://EC2_PUBLIC_IP/api/v1/...`
   and return 200, no CORS errors. Confirm the backend didn't regress:
   ```
   curl -I http://EC2_PUBLIC_IP/api/v1/health
   ```
   Expect `200`.

## Troubleshooting

- **502 Bad Gateway** — app/php not reachable. Check `docker compose -f docker-compose.prod.yml ps`
  for `app` status, and confirm `fastcgi_pass app:9000;` in `docker/nginx/prod.conf` matches
  the `app` service name.
- **DB connection error** — check `DB_HOST`/`DB_PASSWORD` in `backend/.env`, and confirm
  the RDS security group allows inbound 5432 from the EC2 instance's security group.
- **Frontend build killed / `npm run build` exits with no output** — OOM on the
  1 GB instance. Add swap or build on the Mac, see the Frontend section above.
- **Blank page or 404 on `/`** — `frontend/dist` empty or not mounted. Confirm
  the build ran and `docker-compose.prod.yml` mounts
  `./frontend/dist:/usr/share/nginx/html:ro` on the `nginx` service.
- **CORS error in browser console** — `backend/config/cors.php` missing
  `http://EC2_PUBLIC_IP` in `allowed_origins`, or config cache stale. Re-run
  `php artisan config:cache` after editing.
