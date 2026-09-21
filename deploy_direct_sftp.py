import os
import paramiko

host = "173.212.248.192"
port = 22
username = "root"
password = "fE1X4qniGZDCjO2"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(host, port=port, username=username, password=password, timeout=15)

sftp = client.open_sftp()

remote_base = "/var/www/vhosts/munazzah.com/nextspace.munazzah.com/virtual-workplace"
local_base = r"d:\Vwork\virtual-workplace"

# Sync comprehensive directory trees
dirs_to_sync = [
    (r"app", f"{remote_base}/app"),
    (r"database\migrations", f"{remote_base}/database/migrations"),
    (r"resources\views", f"{remote_base}/resources/views"),
    (r"resources\css", f"{remote_base}/resources/css"),
    (r"routes", f"{remote_base}/routes"),
    (r"lang", f"{remote_base}/lang"),
    (r"public\css", f"{remote_base}/public/css"),
    (r"public\fonts", f"{remote_base}/public/fonts"),
    (r"public\images\brand", f"{remote_base}/public/images/brand"),
    (r"public\build", f"{remote_base}/public/build"),
]

def sync_directory(local_dir, remote_dir):
    for root, _, files in os.walk(local_dir):
        rel_dir = os.path.relpath(root, local_dir)
        target_remote_dir = remote_dir if rel_dir == "." else f"{remote_dir}/{rel_dir.replace(os.sep, '/')}"
        try:
            sftp.stat(target_remote_dir)
        except IOError:
            client.exec_command(f"mkdir -p '{target_remote_dir}'")
        for f in files:
            if f.endswith('.pyc') or f == '.DS_Store':
                continue
            local_f = os.path.join(root, f)
            remote_f = f"{target_remote_dir}/{f}"
            print(f"Uploading {local_f} -> {remote_f}...")
            sftp.put(local_f, remote_f)

for local_d_rel, remote_d in dirs_to_sync:
    local_d = os.path.join(local_base, local_d_rel)
    if os.path.exists(local_d):
        print(f"\n=== Syncing directory: {local_d_rel} ===")
        sync_directory(local_d, remote_d)

sftp.close()

# Execute Plesk PHP commands on live server
php_binary = "/opt/plesk/php/8.2/bin/php"
cmds = [
    f"cd {remote_base} && {php_binary} artisan migrate --force",
    f"cd {remote_base} && {php_binary} artisan view:clear",
    f"cd {remote_base} && {php_binary} artisan cache:clear",
    f"cd {remote_base} && {php_binary} artisan route:clear",
    f"cd {remote_base} && {php_binary} artisan config:clear",
    f"P_USER=$(stat -c '%U:%G' /var/www/vhosts/munazzah.com/nextspace.munazzah.com) && chown -R $P_USER {remote_base}",
    f"chmod -R 777 {remote_base}/storage {remote_base}/bootstrap/cache",
    f"chmod -R 777 {remote_base}/storage/logs && touch {remote_base}/storage/logs/laravel.log && chmod 777 {remote_base}/storage/logs/laravel.log"
]

for cmd in cmds:
    print(f"\n--- Running: {cmd} ---")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode().strip()
    err = stderr.read().decode().strip()
    if out:
        print("STDOUT:\n", out)
    if err:
        print("STDERR:\n", err)

client.close()
print("\n Deployment to live server completed successfully!")
