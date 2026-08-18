import os
import json
import math
from PIL import Image, ImageDraw, ImageFilter

def save_crop(img, output_path):
    bbox = img.getbbox()
    if bbox:
        cropped = img.crop(bbox)
        final = Image.new("RGBA", (cropped.width + 12, cropped.height + 12), (0, 0, 0, 0))
        final.paste(cropped, (6, 6), cropped)
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        final.save(output_path, "PNG")
    else:
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        img.save(output_path, "PNG")

def render_storage(output_path, w, h, style="cabinet", mat="metal"):
    img = Image.new("RGBA", (w + 60, h + 60), (0, 0, 0, 0))
    s = Image.new("RGBA", (w + 60, h + 60), (0, 0, 0, 0))
    ImageDraw.Draw(s).rounded_rectangle([30, 34, 30 + w, 30 + h], radius=8, fill=(0, 0, 0, 90))
    s = s.filter(ImageFilter.GaussianBlur(6))
    img.paste(s, (0, 0), s)

    d = ImageDraw.Draw(img)
    base_col = (100, 116, 139) if mat == "metal" else ((50, 30, 20) if mat == "walnut" else (220, 185, 145))
    border_col = (51, 65, 85) if mat == "metal" else ((30, 15, 10) if mat == "walnut" else (160, 125, 85))
    d.rounded_rectangle([30, 30, 30 + w, 30 + h], radius=6, fill=base_col, outline=border_col, width=2)
    # Drawer divider line
    d.line([30, 30 + h//2, 30 + w, 30 + h//2], fill=border_col, width=1)
    # Handle bars
    d.line([30 + w//2 - 14, 30 + h//4, 30 + w//2 + 14, 30 + h//4], fill=(15, 23, 42), width=3)
    d.line([30 + w//2 - 14, 30 + 3*h//4, 30 + w//2 + 14, 30 + 3*h//4], fill=(15, 23, 42), width=3)
    save_crop(img, output_path)

def render_lounge(output_path, w, h, style="sofa", color="charcoal"):
    img = Image.new("RGBA", (w + 80, h + 80), (0, 0, 0, 0))
    s = Image.new("RGBA", (w + 80, h + 80), (0, 0, 0, 0))
    ImageDraw.Draw(s).rounded_rectangle([40, 44, 40 + w, 40 + h], radius=16, fill=(0, 0, 0, 100))
    s = s.filter(ImageFilter.GaussianBlur(10))
    img.paste(s, (0, 0), s)

    d = ImageDraw.Draw(img)
    cols = {
        "charcoal": ((30, 41, 59), (51, 65, 85)),
        "teal": ((13, 148, 136), (15, 118, 110)),
        "beige": ((214, 197, 175), (184, 167, 145)),
        "blue": ((37, 99, 235), (29, 78, 216)),
        "leather": ((45, 25, 18), (30, 15, 10))
    }
    c_base, c_border = cols.get(color, cols["charcoal"])
    # Main sofa body
    d.rounded_rectangle([40, 40, 40 + w, 40 + h], radius=14, fill=c_base, outline=c_border, width=2)
    # Backrest cushion
    d.rounded_rectangle([40, 40, 40 + w, 40 + h//3 + 4], radius=8, fill=(c_base[0]-10, c_base[1]-10, c_base[2]-10), outline=c_border, width=1)
    # Seat cushions
    c_w = w // 2 if w > 100 else w
    d.rounded_rectangle([40 + 4, 40 + h//3 + 8, 40 + c_w - 4, 40 + h - 6], radius=8, fill=c_base, outline=c_border, width=1)
    if w > 100:
        d.rounded_rectangle([40 + c_w + 4, 40 + h//3 + 8, 40 + w - 4, 40 + h - 6], radius=8, fill=c_base, outline=c_border, width=1)
    save_crop(img, output_path)

def render_plant(output_path, size=80, plant_type="monstera"):
    img = Image.new("RGBA", (size + 60, size + 60), (0, 0, 0, 0))
    s = Image.new("RGBA", (size + 60, size + 60), (0, 0, 0, 0))
    cx, cy = (size + 60) // 2, (size + 60) // 2
    ImageDraw.Draw(s).ellipse([cx - size//3, cy - size//3 + 4, cx + size//3, cy + size//3 + 8], fill=(0, 0, 0, 90))
    s = s.filter(ImageFilter.GaussianBlur(8))
    img.paste(s, (0, 0), s)

    d = ImageDraw.Draw(img)
    # Pot
    d.ellipse([cx - size//3, cy - size//3, cx + size//3, cy + size//3], fill=(226, 232, 240), outline=(148, 163, 184), width=2)
    # Soil
    d.ellipse([cx - size//3 + 4, cy - size//3 + 4, cx + size//3 - 4, cy + size//3 - 4], fill=(68, 40, 24))
    # Plant Leaves radiating outward from above
    num_leaves = 7 if plant_type != "tree" else 12
    for i in range(num_leaves):
        ang = i * (2 * math.pi / num_leaves)
        lx = cx + int((size//2) * math.cos(ang))
        ly = cy + int((size//2) * math.sin(ang))
        leaf_color = (16, 185, 129) if i % 2 == 0 else (5, 150, 105)
        d.ellipse([lx - 10, ly - 10, lx + 10, ly + 10], fill=leaf_color, outline=(4, 120, 87), width=1)
        d.line([cx, cy, lx, ly], fill=(4, 120, 87), width=2)
    save_crop(img, output_path)

def render_technology(output_path, tech_type="monitor"):
    size = 100
    img = Image.new("RGBA", (size + 40, size + 40), (0, 0, 0, 0))
    s = Image.new("RGBA", (size + 40, size + 40), (0, 0, 0, 0))
    cx, cy = (size + 40) // 2, (size + 40) // 2
    ImageDraw.Draw(s).rounded_rectangle([cx - 36, cy - 14, cx + 36, cy + 18], radius=6, fill=(0, 0, 0, 80))
    s = s.filter(ImageFilter.GaussianBlur(6))
    img.paste(s, (0, 0), s)

    d = ImageDraw.Draw(img)
    if "monitor" in tech_type or "tv" in tech_type:
        d.rounded_rectangle([cx - 40, cy - 6, cx + 40, cy + 6], radius=2, fill=(15, 23, 42), outline=(59, 130, 246), width=1)
        d.rectangle([cx - 10, cy - 2, cx + 10, cy + 14], fill=(71, 85, 105)) # Base stand
    elif "laptop" in tech_type:
        d.rounded_rectangle([cx - 24, cy - 16, cx + 24, cy + 16], radius=4, fill=(148, 163, 184), outline=(71, 85, 105), width=1)
        d.rounded_rectangle([cx - 20, cy - 4, cx + 20, cy + 12], radius=2, fill=(30, 41, 59)) # Keyboard
    elif "printer" in tech_type:
        d.rounded_rectangle([cx - 30, cy - 25, cx + 30, cy + 25], radius=6, fill=(248, 250, 252), outline=(148, 163, 184), width=2)
        d.rectangle([cx - 18, cy - 15, cx + 18, cy + 5], fill=(15, 23, 42)) # Output slot
    else: # general gadget
        d.rounded_rectangle([cx - 20, cy - 20, cx + 20, cy + 20], radius=8, fill=(30, 41, 59), outline=(59, 130, 246), width=1)
    save_crop(img, output_path)

def render_virtual_marker(output_path, label="ZONE", color_hex="#3b82f6"):
    size = 100
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    cx, cy = size // 2, size // 2
    # Outer ring
    d.ellipse([cx - 40, cy - 40, cx + 40, cy + 40], outline=(59, 130, 246, 220), width=3)
    d.ellipse([cx - 32, cy - 32, cx + 32, cy + 32], fill=(59, 130, 246, 50))
    d.ellipse([cx - 14, cy - 14, cx + 14, cy + 14], fill=(59, 130, 246, 240))
    save_crop(img, output_path)

def build_full_library():
    catalog_path = "database/data/furniture_catalog.json"
    with open(catalog_path, "r", encoding="utf-8") as f:
        catalog = json.load(f)

    # 1. Storage & Drawers (Batch 06 & 07)
    for i in range(1, 21):
        fid = f"FUR-STOR-OFF-{i:03d}"
        fname = f"storage_office_{i:02d}.png"
        fpath = os.path.join("public/assets/furniture/storage", fname)
        w = 90 if i % 2 == 0 else 70
        h = 50 if i % 2 == 0 else 45
        render_storage(fpath, w, h, "cabinet", "metal" if i % 3 == 0 else "walnut")
        entry = {
            "id": fid, "name": f"Office Storage Cabinet {i:02d}", "category": "furniture", "subcategory": "storage", "type": "storage_cabinet",
            "asset": { "image": f"/assets/furniture/storage/{fname}", "thumbnail": f"/assets/furniture/storage/{fname}" },
            "dimensions": { "width_cm": 80, "depth_cm": 45, "height_cm": 110 },
            "footprint": { "width_tiles": 2, "height_tiles": 1 },
            "clearance": { "front_cm": 60, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 0, "appearance": { "material": "metal", "style": "cabinet", "color": "charcoal" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 2. Reception & Counters (Batch 08)
    for i in range(1, 13):
        fid = f"FUR-RECP-CTR-{i:03d}"
        fname = f"reception_{i:02d}.png"
        fpath = os.path.join("public/assets/furniture/reception", fname)
        render_storage(fpath, 160, 60, "reception", "oak")
        entry = {
            "id": fid, "name": f"Reception Counter {i:02d}", "category": "furniture", "subcategory": "reception", "type": "reception_desk",
            "asset": { "image": f"/assets/furniture/reception/{fname}", "thumbnail": f"/assets/furniture/reception/{fname}" },
            "dimensions": { "width_cm": 220, "depth_cm": 80, "height_cm": 115 },
            "footprint": { "width_tiles": 5, "height_tiles": 2 },
            "clearance": { "front_cm": 100, "back_cm": 80, "left_cm": 30, "right_cm": 30 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 2, "appearance": { "material": "oak", "style": "reception", "color": "natural" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 3. Lounge & Sofas (Batch 09)
    colors = ["charcoal", "teal", "beige", "blue", "leather"]
    for i in range(1, 15):
        fid = f"FUR-LOUNG-SOF-{i:03d}"
        fname = f"lounge_{i:02d}.png"
        fpath = os.path.join("public/assets/furniture/lounge", fname)
        col = colors[i % len(colors)]
        w = 160 if i > 3 else 90
        render_lounge(fpath, w, 70, "sofa", col)
        entry = {
            "id": fid, "name": f"Lounge Sofa {i:02d}", "category": "furniture", "subcategory": "lounge", "type": "lounge_sofa",
            "asset": { "image": f"/assets/furniture/lounge/{fname}", "thumbnail": f"/assets/furniture/lounge/{fname}" },
            "dimensions": { "width_cm": 180 if i > 3 else 100, "depth_cm": 85, "height_cm": 75 },
            "footprint": { "width_tiles": 4 if i > 3 else 2, "height_tiles": 2 },
            "clearance": { "front_cm": 60, "back_cm": 20, "left_cm": 20, "right_cm": 20 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 3 if i > 3 else 1, "appearance": { "material": "fabric", "style": "lounge", "color": col }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 4. Plants & Decor (Batch 10 & 11)
    for i in range(1, 21):
        fid = f"DEC-PLANT-IND-{i:03d}"
        fname = f"plant_office_{i:02d}.png"
        fpath = os.path.join("public/assets/decor/plants", fname)
        render_plant(fpath, 75, "tree" if i > 10 else "monstera")
        entry = {
            "id": fid, "name": f"Indoor Plant {i:02d}", "category": "decor", "subcategory": "plants", "type": "indoor_plant",
            "asset": { "image": f"/assets/decor/plants/{fname}", "thumbnail": f"/assets/decor/plants/{fname}" },
            "dimensions": { "width_cm": 50, "depth_cm": 50, "height_cm": 120 },
            "footprint": { "width_tiles": 1, "height_tiles": 1 },
            "clearance": { "front_cm": 10, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": False },
            "capacity": 0, "appearance": { "material": "natural", "style": "botanical", "color": "green" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 5. Office Technology (Batch 16)
    tech_types = ["single_monitor", "dual_monitor", "laptop", "printer", "smart_tv", "webcam"]
    for i in range(1, 25):
        fid = f"TEC-OFF-EQP-{i:03d}"
        fname = f"technology_office_{i:02d}.png"
        fpath = os.path.join("public/assets/technology", fname)
        ttype = tech_types[i % len(tech_types)]
        render_technology(fpath, ttype)
        entry = {
            "id": fid, "name": f"Office Tech {ttype.replace('_', ' ').title()} {i:02d}", "category": "technology", "subcategory": "hardware", "type": ttype,
            "asset": { "image": f"/assets/technology/{fname}", "thumbnail": f"/assets/technology/{fname}" },
            "dimensions": { "width_cm": 60, "depth_cm": 40, "height_cm": 45 },
            "footprint": { "width_tiles": 1, "height_tiles": 1 },
            "clearance": { "front_cm": 20, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": False, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 0, "appearance": { "material": "matte_black", "style": "tech", "color": "dark" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 6. Virtual Indicators & Markers (Batch 27)
    for i in range(1, 23):
        fid = f"VIR-ZONE-MRK-{i:03d}"
        fname = f"virtual_marker_{i:02d}.png"
        fpath = os.path.join("public/assets/virtual/indicators", fname)
        render_virtual_marker(fpath, f"Z{i}")
        entry = {
            "id": fid, "name": f"Zone Indicator {i:02d}", "category": "virtual", "subcategory": "zones", "type": "zone_marker",
            "asset": { "image": f"/assets/virtual/indicators/{fname}", "thumbnail": f"/assets/virtual/indicators/{fname}" },
            "dimensions": { "width_cm": 100, "depth_cm": 100, "height_cm": 0 },
            "footprint": { "width_tiles": 2, "height_tiles": 2 },
            "clearance": { "front_cm": 0, "back_cm": 0, "left_cm": 0, "right_cm": 0 },
            "behavior": { "collision": False, "movable": True, "rotatable": False, "interactive": True },
            "capacity": 0, "appearance": { "material": "hologram", "style": "hud", "color": "cyan" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # Save complete catalog
    with open(catalog_path, "w", encoding="utf-8") as f:
        json.dump(catalog, f, indent=2, ensure_ascii=False)

    print(f"COMPLETE LIBRARY GENERATION FINISHED! Total Catalog Items: {len(catalog)}")

if __name__ == "__main__":
    build_full_library()
