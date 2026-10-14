# Deploying to the live server

The live app runs from a git checkout at
`/var/www/vhosts/munazzah.com/nextspace.munazzah.com/virtual-workplace` (Plesk, MariaDB 10.6, PHP `/opt/plesk/php/8.2/bin/php`, realtime service under pm2 as `nextspace-realtime`). The release branch is `ulaspace-rebuild`.

## Before you deploy

1. The commits are pushed to `origin/ulaspace-rebuild`.
2. CI is green on that commit (SQLite + tenant-scope run, MariaDB 10.6, Pint, Larastan, CSS sync check).
3. You have the SSH credentials in your environment — never in a file in the repo:

```bash
export DEPLOY_HOST=…  DEPLOY_USER=…  DEPLOY_PASSWORD=…    # or DEPLOY_KEY_FILE=~/.ssh/…
```

## Deploy

```bash
python ops/deploy.py --dry-run   # read the steps
python ops/deploy.py             # run them
```

The script stops at the first failing step. It runs, in order: a database backup to `storage/backups/predeploy-<time>.sql.gz` (checked for "Dump completed"), a copy of `public/uploads` to `/root/deploy-uploads-<time>`, `git checkout -B ulaspace-rebuild origin/ulaspace-rebuild`, restoring any upload the checkout removed, `migrate --force`, `npm ci && npm run build`, rebuilding Laravel's caches, ownership of `storage`, `bootstrap/cache` and `public/uploads` back to the app's owner, a check that the vhost root is still `psaserv 750`, a pm2 restart of the realtime service, and smoke checks on `/` and `/login`.

## Rules learned the hard way

- **Never `chown` the vhost root** (`…/nextspace.munazzah.com`). A recursive chown once changed its group away from `psaserv` and Apache answered 403 for the whole site. Only chown inside `virtual-workplace/`.
- **Uploads are user data.** They are out of git since roadmap phase 3, but checking out a commit from before that (a rollback) can still delete them; the script copies them aside first, and rollback step 3 restores them.
- **SQLite tests do not prove MySQL behaviour.** That is what the MariaDB CI job is for — check it before deploying a migration.
- **Artisan runs as root**, so caches it writes are root-owned until the ownership step runs.

## After deploying

Open the site, sign in, open the office and the dashboard. For migrations that change data, check the affected screens. Remove the uploads copy (`rm -rf /root/deploy-uploads-<time>`) once all is well.

## Rollback

1. `git checkout -B ulaspace-rebuild <previous commit>` in the app directory, then the build and cache steps again (`npm ci && npm run build`, `artisan optimize:clear`, `config:cache`, `route:cache`, `view:cache`, the ownership step).
2. If a migration changed data, restore the backup: `zcat storage/backups/predeploy-<time>.sql.gz | mysql …` (credentials from `.env`). This replaces everything written since the backup.
3. If uploads are missing: `cp -an /root/deploy-uploads-<time>/. public/uploads/`.
