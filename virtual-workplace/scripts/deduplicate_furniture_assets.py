import os
import json
import hashlib

def get_file_md5(filepath):
    hasher = hashlib.md5()
    with open(filepath, 'rb') as f:
        hasher.update(f.read())
    return hasher.hexdigest()

catalog_path = 'database/data/furniture_catalog.json'
with open(catalog_path, 'r', encoding='utf-8') as f:
    catalog = json.load(f)

print(f"Initial catalog size: {len(catalog)}")

# Map each catalog entry to its file and md5 hash
unique_entries = []
seen_hashes = {}
removed_entries = []
id_replacement_map = {}

for entry in catalog:
    rel_img = entry.get('asset', {}).get('image', '').lstrip('/')
    filepath = os.path.join('public', rel_img).replace('/', os.sep)
    
    if not os.path.exists(filepath):
        print(f"Warning: Missing file for {entry['id']}: {filepath}")
        continue

    file_hash = get_file_md5(filepath)
    
    if file_hash in seen_hashes:
        primary_entry = seen_hashes[file_hash]
        id_replacement_map[entry['id']] = primary_entry['id']
        removed_entries.append((entry, filepath, primary_entry['id']))
    else:
        seen_hashes[file_hash] = entry
        unique_entries.append(entry)

print(f"Unique catalog entries remaining: {len(unique_entries)}")
print(f"Duplicate entries identified and to be removed: {len(removed_entries)}")

# Save clean unique catalog
with open(catalog_path, 'w', encoding='utf-8') as f:
    json.dump(unique_entries, f, indent=2, ensure_ascii=False)

print(f"Saved deduplicated catalog to {catalog_path}")

# Remove duplicate image files if they are not referenced in the new catalog
kept_image_paths = {
    os.path.abspath(os.path.join('public', e['asset']['image'].lstrip('/')).replace('/', os.sep))
    for e in unique_entries
}

deleted_files_count = 0
for entry, filepath, primary_id in removed_entries:
    abs_fp = os.path.abspath(filepath)
    if abs_fp not in kept_image_paths and os.path.exists(abs_fp):
        try:
            os.remove(abs_fp)
            deleted_files_count += 1
        except Exception as ex:
            print(f"Could not delete {abs_fp}: {ex}")

print(f"Deleted {deleted_files_count} redundant duplicate image files from disk.")

# Save ID replacement map for database migration/updating
with open('database/data/dedup_replacement_map.json', 'w', encoding='utf-8') as f:
    json.dump(id_replacement_map, f, indent=2)

print("Deduplication completed successfully!")
