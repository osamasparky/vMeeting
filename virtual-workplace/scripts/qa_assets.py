import os
import json
from PIL import Image

def run_qa():
    catalog_path = "database/data/furniture_catalog.json"
    if not os.path.exists(catalog_path):
        print("FAILED: Catalog file does not exist.")
        return False

    with open(catalog_path, "r", encoding="utf-8") as f:
        catalog = json.load(f)

    print(f"==================================================")
    print(f"AUTOMATED ASSET QA VALIDATION REPORT — BATCH 01")
    print(f"==================================================")
    print(f"Total Assets in Catalog: {len(catalog)}")

    all_passed = True
    seen_ids = set()

    for idx, item in enumerate(catalog, 1):
        asset_id = item.get("id")
        name = item.get("name")
        image_rel = item.get("asset", {}).get("image", "")
        # Remove leading slash if needed
        local_path = os.path.join("public", image_rel.lstrip("/"))

        checks = []

        # 1. Asset ID uniqueness
        if asset_id in seen_ids:
            checks.append("ID_DUPLICATE")
        seen_ids.add(asset_id)

        # 2. File exists
        if not os.path.exists(local_path):
            checks.append(f"FILE_NOT_FOUND ({local_path})")
        else:
            # 3. Image validity and dimensions
            try:
                with Image.open(local_path) as img:
                    if img.mode != "RGBA":
                        checks.append(f"NOT_RGBA_TRANSPARENT ({img.mode})")
                    if img.width < 50 or img.height < 50:
                        checks.append(f"TOO_SMALL ({img.width}x{img.height})")
            except Exception as e:
                checks.append(f"INVALID_IMAGE_FILE ({e})")

        # 4. Footprint & Behavior
        footprint = item.get("footprint", {})
        if not footprint.get("width_tiles") or not footprint.get("height_tiles"):
            checks.append("MISSING_FOOTPRINT")

        behavior = item.get("behavior", {})
        if "collision" not in behavior:
            checks.append("MISSING_COLLISION")
        if "rotatable" not in behavior:
            checks.append("MISSING_ROTATABLE")

        status = "PASSED" if not checks else f"FAILED: {', '.join(checks)}"
        if checks:
            all_passed = False

        print(f"[{idx:02d}] {asset_id} — {name:<32} -> {status}")

    print(f"==================================================")
    print(f"SUMMARY: {'ALL ASSETS PASSED QA 100%' if all_passed else 'SOME ASSETS FAILED QA'}")
    print(f"==================================================")
    return all_passed

if __name__ == "__main__":
    run_qa()
