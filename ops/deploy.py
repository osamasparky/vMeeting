"""Deploy the Laravel app to the live server over SSH — the procedure in docs/DEPLOY.md.

No credentials live in this file. Set them in the environment:

    DEPLOY_HOST, DEPLOY_USER            (required)
    DEPLOY_PASSWORD or DEPLOY_KEY_FILE  (one of them)
    DEPLOY_PORT       default 22
    DEPLOY_APP_DIR    default /var/www/vhosts/munazzah.com/nextspace.munazzah.com/virtual-workplace
    DEPLOY_BRANCH     default ulaspace-rebuild
    DEPLOY_PHP        default /opt/plesk/php/8.2/bin/php
    DEPLOY_SITE_URL   default https://nextspace.munazzah.com

Usage:
    python ops/deploy.py --dry-run     print every step, run nothing
    python ops/deploy.py               deploy

Each step stops the deploy on failure. The database backup and the uploads
copy are taken before the code changes, so a failed deploy can be rolled back
(docs/DEPLOY.md, "Rollback").
"""
import os
import sys
import time

DRY_RUN = '--dry-run' in sys.argv


def env(name, default=None, required=False):
    value = os.environ.get(name, default)
    if required and not value:
        sys.exit(f'Missing {name} — see the header of ops/deploy.py')
    return value


HOST = env('DEPLOY_HOST', required=not DRY_RUN) or '<DEPLOY_HOST>'
USER = env('DEPLOY_USER', required=not DRY_RUN) or '<DEPLOY_USER>'
PORT = int(env('DEPLOY_PORT', '22'))
APP = env('DEPLOY_APP_DIR', '/var/www/vhosts/munazzah.com/nextspace.munazzah.com/virtual-workplace')
VHOST = os.path.dirname(APP)
BRANCH = env('DEPLOY_BRANCH', 'ulaspace-rebuild')
PHP = env('DEPLOY_PHP', '/opt/plesk/php/8.2/bin/php')
SITE = env('DEPLOY_SITE_URL', 'https://nextspace.munazzah.com')
STAMP = time.strftime('%Y%m%d-%H%M%S')
UPLOADS_COPY = f'/root/deploy-uploads-{STAMP}'

# Read DB settings from the server's .env without printing them.
ENV_READER = 'v(){ grep -E "^$1=" .env | head -1 | cut -d= -f2- | sed -e \'s/^"//\' -e \'s/"$//\'; }'

STEPS = [
    ('Where we start', f'cd {APP} && git branch --show-current && git log --oneline -1 && stat -c "%U:%G %a" {VHOST}'),
    ('Database backup', f'cd {APP} && {ENV_READER}; mkdir -p storage/backups && F=storage/backups/predeploy-{STAMP}.sql.gz && '
                        f'MYSQL_PWD="$(v DB_PASSWORD)" mysqldump --single-transaction -h "$(v DB_HOST)" -P "$(v DB_PORT)" -u "$(v DB_USERNAME)" "$(v DB_DATABASE)" | gzip > $F && '
                        f'zcat $F | tail -1 | grep -q "Dump completed" && echo "backup: $F"'),
    ('Copy uploads aside', f'cd {APP} && cp -a public/uploads {UPLOADS_COPY} && echo "uploads copied to {UPLOADS_COPY} ($(find {UPLOADS_COPY} -type f | wc -l) files)"'),
    ('Check out the release', f'cd {APP} && git fetch -q origin && git checkout -q -B {BRANCH} origin/{BRANCH} && git log --oneline -1'),
    ('Restore any upload the checkout removed', f'cd {APP} && mkdir -p public/uploads && cp -an {UPLOADS_COPY}/. public/uploads/ && echo "uploads now: $(find public/uploads -type f | wc -l) files"'),
    ('Migrate', f'cd {APP} && {PHP} artisan migrate --force'),
    ('Build front-end assets', f'cd {APP} && npm ci --no-audit --no-fund && npm run build'),
    ('Rebuild caches', f'cd {APP} && {PHP} artisan optimize:clear && {PHP} artisan config:cache && {PHP} artisan route:cache && {PHP} artisan view:cache'),
    # Inside the app only. A recursive chown of the vhost root once took the whole site down (403).
    ('Ownership inside the app', f'O=$(stat -c "%U:%G" {APP}) && chown -R "$O" {APP}/storage {APP}/bootstrap/cache {APP}/public/uploads && echo "owner $O"'),
    ('Vhost root untouched', f'stat -c "%U:%G %a" {VHOST} | grep -q "psaserv 750" && echo "vhost root ok" || (echo "VHOST ROOT CHANGED"; exit 1)'),
    ('Realtime service', 'pm2 restart nextspace-realtime >/dev/null && sleep 2 && pm2 describe nextspace-realtime | grep -m1 -E "status.*online" >/dev/null && echo "realtime online"'),
    ('Smoke checks', f'for u in / /login; do c=$(curl -s -o /dev/null -w "%{{http_code}}" {SITE}$u); echo "$u $c"; [ "$c" = 200 ] || exit 1; done'),
]


def main():
    if DRY_RUN:
        print(f'DRY RUN — {USER}@{HOST}:{PORT}, branch {BRANCH}, app {APP}\n')
        for i, (title, cmd) in enumerate(STEPS, 1):
            print(f'{i:2d}. {title}\n    {cmd}\n')
        return

    import paramiko

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    password = os.environ.get('DEPLOY_PASSWORD')
    key_file = os.environ.get('DEPLOY_KEY_FILE')
    if not password and not key_file:
        sys.exit('Set DEPLOY_PASSWORD or DEPLOY_KEY_FILE')
    client.connect(HOST, port=PORT, username=USER, password=password, key_filename=key_file, timeout=20)

    for i, (title, cmd) in enumerate(STEPS, 1):
        print(f'\n[{i}/{len(STEPS)}] {title}')
        stdin, stdout, stderr = client.exec_command(cmd, timeout=900)
        code = stdout.channel.recv_exit_status()
        out = stdout.read().decode(errors='replace').strip()
        err = stderr.read().decode(errors='replace').strip()
        if out:
            print('   ' + out.replace('\n', '\n   ')[-3000:])
        if code != 0:
            if err:
                print('   ' + err.replace('\n', '\n   ')[-3000:])
            client.close()
            sys.exit(f'Step {i} "{title}" failed (exit {code}). See docs/DEPLOY.md, "Rollback". Uploads copy: {UPLOADS_COPY}')

    client.close()
    print(f'\nDeployed {BRANCH}. Uploads copy kept at {UPLOADS_COPY} — remove it once the site is checked.')


if __name__ == '__main__':
    sys.stdout.reconfigure(errors='replace')
    main()
