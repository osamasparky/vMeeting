import os
import json
import hashlib

def get_file_md5(filepath):
    hasher = hashlib.md5()
    with open(filepath, 'rb') as f:
        buf = f.read()
        hasher.update(buf)
    return hasher.hexdigest()

catalog_path = 'database/data/furniture_catalog.json'
with open(catalog_path, 'r', encoding='utf-8') as f:
    catalog = json.load(f)

print(f"Total catalog entries: {len(catalog)}")

# Check duplicate IDs
seen_ids = set()
dup_ids = []
for entry in catalog:
    if entry['id'] in seen_ids:
        dup_ids.append(entry['id'])
    seen_ids.add(entry['id'])

print(f"Duplicate IDs in catalog: {len(dup_ids)} -> {dup_ids}")

# Check duplicate image paths in catalog
seen_images = {}
dup_images = {}
for entry in catalog:
    img = entry.get('asset', {}).get('image')
    if img:
        if img in seen_images:
            dup_images.setdefault(img, []).append(entry['id'])
        else:
            seen_images[img] = [entry['id']]

print(f"Duplicate image paths referenced in catalog: {len(dup_images)}")
for img, ids in list(dup_images.items())[:10]:
    print(f"  Image: {img} referenced by: {ids}")

# Check duplicate image file content hashes in public/assets
hash_map = {}
dup_hashes = {}
for root, dirs, files in os.walk('public/assets'):
    for file in files:
        if file.endswith('.png') or file.endswith('.jpg'):
            fp = os.path.join(root, file)
            h = get_file_md5(fp)
            if h in hash_map:
                dup_hashes.setdefault(h, [hash_map[h]]).append(fp)
            else:
                hash_map[h] = fp

print(f"\nTotal image files in public/assets: {len(hash_map) + sum(len(v)-1 for v in dup_hashes.values())}")
print(f"Identical duplicate file content hashes in public/assets: {len(dup_hashes)}")
for h, files in list(dup_hashes.items())[:10]:
    print(f"  Hash {h[:8]}: {files}")
