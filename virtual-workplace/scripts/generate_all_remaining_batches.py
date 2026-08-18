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

def draw_shadow(w, h, radius=10):
    s = Image.new("RGBA", (w + 60, h + 60), (0, 0, 0, 0))
    ImageDraw.Draw(s).rounded_rectangle([30, 34, 30 + w, 30 + h], radius=radius, fill=(0, 0, 0, 90))
    return s.filter(ImageFilter.GaussianBlur(8))

# 1. Partitions (Batch 13)
def render_partition(output_path, w=140, h=16, mat="glass"):
    img = Image.new("RGBA", (w + 60, h + 60), (0, 0, 0, 0))
    img.paste(draw_shadow(w, h, 4), (0, 0))
    d = ImageDraw.Draw(img)
    base_col = (186, 230, 253, 200) if mat == "glass" else ((6, 182, 212) if mat == "acoustic" else (220, 180, 140))
    border_col = (56, 189, 248) if mat == "glass" else ((8, 145, 178) if mat == "acoustic" else (160, 125, 85))
    d.rounded_rectangle([30, 30, 30 + w, 30 + h], radius=3, fill=base_col, outline=border_col, width=2)
    # Support footings at both ends
    d.rounded_rectangle([30 + 6, 26, 30 + 18, 30 + h + 4], radius=2, fill=(30, 41, 59))
    d.rounded_rectangle([30 + w - 18, 26, 30 + w - 6, 30 + h + 4], radius=2, fill=(30, 41, 59))
    save_crop(img, output_path)

# 2. Lighting & Lamps (Batch 14)
def render_lamp(output_path, style="floor"):
    size = 70
    img = Image.new("RGBA", (size + 40, size + 40), (0, 0, 0, 0))
    cx, cy = (size + 40) // 2, (size + 40) // 2
    d = ImageDraw.Draw(img)
    # Ambient glow aura
    d.ellipse([cx - 30, cy - 30, cx + 30, cy + 30], fill=(253, 224, 71, 50))
    # Lamp shade / head
    d.ellipse([cx - 18, cy - 18, cx + 18, cy + 18], fill=(254, 240, 138), outline=(234, 179, 8), width=2)
    d.ellipse([cx - 8, cy - 8, cx + 8, cy + 8], fill=(255, 255, 255))
    save_crop(img, output_path)

# 3. Whiteboards & Flipcharts (Batch 17)
def render_board(output_path, w=160, h=30, style="whiteboard"):
    img = Image.new("RGBA", (w + 60, h + 60), (0, 0, 0, 0))
    img.paste(draw_shadow(w, h, 6), (0, 0))
    d = ImageDraw.Draw(img)
    d.rounded_rectangle([30, 30, 30 + w, 30 + h], radius=4, fill=(255, 255, 255), outline=(148, 163, 184), width=2)
    # Marker tray in center
    d.rounded_rectangle([30 + w//2 - 25, 30 + h - 6, 30 + w//2 + 25, 30 + h], radius=2, fill=(59, 130, 246))
    save_crop(img, output_path)

# 4. Focus & Phone Pods (Batch 19)
def render_pod(output_path, w=120, h=120, ptype="phone_booth"):
    img = Image.new("RGBA", (w + 60, h + 60), (0, 0, 0, 0))
    img.paste(draw_shadow(w, h, 14), (0, 0))
    d = ImageDraw.Draw(img)
    # Outer acoustic walls
    d.rounded_rectangle([30, 30, 30 + w, 30 + h], radius=16, fill=(15, 23, 42), outline=(51, 65, 85), width=3)
    # Glass door edge (front)
    d.line([30 + 16, 30 + h, 30 + w - 16, 30 + h], fill=(56, 189, 248), width=4)
    # Interior desk & seat
    d.rounded_rectangle([30 + 14, 30 + 14, 30 + w - 14, 30 + 44], radius=6, fill=(220, 180, 140))
    d.rounded_rectangle([30 + w//2 - 18, 30 + 55, 30 + w//2 + 18, 30 + 85], radius=10, fill=(6, 182, 212))
    save_crop(img, output_path)

# 5. Breakroom & Kitchen Equipment (Batch 20)
def render_kitchen(output_path, ktype="coffee_machine"):
    w, h = 80, 60
    img = Image.new("RGBA", (w + 40, h + 40), (0, 0, 0, 0))
    img.paste(draw_shadow(w, h, 8), (0, 0))
    d = ImageDraw.Draw(img)
    if "coffee" in ktype:
        d.rounded_rectangle([20, 20, 20 + w, 20 + h], radius=6, fill=(24, 24, 27), outline=(212, 175, 55), width=2)
        d.ellipse([20 + 14, 20 + 14, 20 + 36, 20 + 36], fill=(40, 40, 45))
        d.ellipse([20 + w - 36, 20 + 14, 20 + w - 14, 20 + 36], fill=(40, 40, 45))
    elif "fridge" in ktype:
        d.rounded_rectangle([20, 20, 20 + w, 20 + h + 20], radius=8, fill=(226, 232, 240), outline=(148, 163, 184), width=2)
        d.line([20, 20 + 40, 20 + w, 20 + 40], fill=(148, 163, 184), width=2)
    else: # counter / sink
        d.rounded_rectangle([20, 20, 20 + w + 40, 20 + h], radius=6, fill=(241, 245, 249), outline=(148, 163, 184), width=2)
        d.rounded_rectangle([20 + 14, 20 + 12, 20 + 50, 20 + h - 12], radius=4, fill=(148, 163, 184), outline=(100, 116, 139))
    save_crop(img, output_path)

# 6. Safety Equipment & Waste Bins (Batch 21 & 22)
def render_safety(output_path, stype="fire_ext"):
    size = 100
    img = Image.new("RGBA", (size + 40, size + 40), (0, 0, 0, 0))
    cx, cy = (size + 40) // 2, (size + 40) // 2
    # Shadow
    s = Image.new("RGBA", (size + 40, size + 40), (0, 0, 0, 0))
    ImageDraw.Draw(s).ellipse([cx - 28, cy - 24, cx + 28, cy + 32], fill=(0, 0, 0, 90))
    s = s.filter(ImageFilter.GaussianBlur(8))
    img.paste(s, (0, 0), s)

    d = ImageDraw.Draw(img)
    if "fire" in stype:
        d.ellipse([cx - 26, cy - 26, cx + 26, cy + 26], fill=(239, 68, 68), outline=(185, 28, 28), width=3)
        d.rectangle([cx - 8, cy - 36, cx + 8, cy - 24], fill=(30, 41, 59))
    elif "bin" in stype or "waste" in stype:
        d.ellipse([cx - 28, cy - 28, cx + 28, cy + 28], fill=(51, 65, 85), outline=(100, 116, 139), width=3)
        d.ellipse([cx - 16, cy - 16, cx + 16, cy + 16], fill=(30, 41, 59))
    else: # First aid / safety sign
        d.rounded_rectangle([cx - 28, cy - 28, cx + 28, cy + 28], radius=8, fill=(16, 185, 129), outline=(5, 150, 105), width=3)
        d.rectangle([cx - 18, cy - 5, cx + 18, cy + 5], fill=(255, 255, 255))
        d.rectangle([cx - 5, cy - 18, cx + 5, cy + 18], fill=(255, 255, 255))
    save_crop(img, output_path)

# 7. Building Elements (Doors, Walls, Windows - Batch 24)
def render_building(output_path, btype="door_single"):
    w, h = 140, 70
    img = Image.new("RGBA", (w + 40, h + 40), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    if "door" in btype:
        d.rectangle([20, 20, 20 + 12, 20 + h], fill=(30, 41, 59)) # Frame post
        d.rectangle([20 + w - 12, 20, 20 + w, 20 + h], fill=(30, 41, 59)) # Frame post
        d.arc([20 + 12, 20 - w//3, 20 + w, 20 + h], start=90, end=180, fill=(59, 130, 246, 180), width=3)
        d.line([20 + 12, 20 + 8, 20 + 12, 20 + h - 8], fill=(220, 180, 140), width=6)
    elif "window" in btype:
        d.rectangle([20, 20, 20 + w, 20 + 60], fill=(186, 230, 253, 180), outline=(56, 189, 248), width=3)
        d.line([20 + w//2, 20, 20 + w//2, 20 + 60], fill=(56, 189, 248), width=2)
    else: # Wall / Floor
        d.rectangle([20, 20, 20 + w, 20 + 60], fill=(51, 65, 85), outline=(30, 41, 59), width=3)
        d.line([20 + 10, 20 + 30, 20 + w - 10, 20 + 30], fill=(71, 85, 105), width=2)
    save_crop(img, output_path)


def generate_all_remaining():
    catalog_path = "database/data/furniture_catalog.json"
    with open(catalog_path, "r", encoding="utf-8") as f:
        catalog = json.load(f)

    # 1. Partitions (Batch 13 - 12 assets)
    for i in range(1, 13):
        fid = f"PAR-DIV-OFF-{i:03d}"
        fname = f"partition_office_{i:02d}.png"
        fpath = os.path.join("public/assets/partitions", fname)
        render_partition(fpath, 140, 16, "glass" if i % 2 == 0 else "acoustic")
        entry = {
            "id": fid, "name": f"Office Partition Screen {i:02d}", "category": "partitions", "subcategory": "dividers", "type": "room_partition",
            "asset": { "image": f"/assets/partitions/{fname}", "thumbnail": f"/assets/partitions/{fname}" },
            "dimensions": { "width_cm": 140, "depth_cm": 15, "height_cm": 150 },
            "footprint": { "width_tiles": 3, "height_tiles": 1 },
            "clearance": { "front_cm": 10, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": False },
            "capacity": 0, "appearance": { "material": "acoustic_fabric", "style": "divider", "color": "cyan" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 2. Lighting (Batch 14 - 12 assets)
    for i in range(1, 13):
        fid = f"LGT-OFF-LMP-{i:03d}"
        fname = f"lighting_office_{i:02d}.png"
        fpath = os.path.join("public/assets/lighting", fname)
        render_lamp(fpath, "floor" if i > 6 else "desk")
        entry = {
            "id": fid, "name": f"Office Lighting Lamp {i:02d}", "category": "lighting", "subcategory": "lamps", "type": "office_lamp",
            "asset": { "image": f"/assets/lighting/{fname}", "thumbnail": f"/assets/lighting/{fname}" },
            "dimensions": { "width_cm": 45, "depth_cm": 45, "height_cm": 160 },
            "footprint": { "width_tiles": 1, "height_tiles": 1 },
            "clearance": { "front_cm": 10, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": False, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 0, "appearance": { "material": "matte_black", "style": "modern_lamp", "color": "warm_yellow" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 3. Whiteboards & Presentation (Batch 17 - 12 assets)
    for i in range(1, 13):
        fid = f"BRD-MTG-WBD-{i:03d}"
        fname = f"board_office_{i:02d}.png"
        fpath = os.path.join("public/assets/meeting", fname)
        render_board(fpath, 160, 24, "whiteboard")
        entry = {
            "id": fid, "name": f"Presentation Whiteboard {i:02d}", "category": "meeting", "subcategory": "presentation", "type": "whiteboard",
            "asset": { "image": f"/assets/meeting/{fname}", "thumbnail": f"/assets/meeting/{fname}" },
            "dimensions": { "width_cm": 180, "depth_cm": 30, "height_cm": 120 },
            "footprint": { "width_tiles": 4, "height_tiles": 1 },
            "clearance": { "front_cm": 60, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 0, "appearance": { "material": "porcelain", "style": "magnetic", "color": "white" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 4. Focus Pods & Phone Booths (Batch 19 - 10 assets)
    for i in range(1, 11):
        fid = f"POD-FOC-BOO-{i:03d}"
        fname = f"pod_office_{i:02d}.png"
        fpath = os.path.join("public/assets/meeting", fname)
        render_pod(fpath, 120, 120, "phone_booth")
        entry = {
            "id": fid, "name": f"Acoustic Focus Pod {i:02d}", "category": "meeting", "subcategory": "pods", "type": "focus_pod",
            "asset": { "image": f"/assets/meeting/{fname}", "thumbnail": f"/assets/meeting/{fname}" },
            "dimensions": { "width_cm": 120, "depth_cm": 120, "height_cm": 220 },
            "footprint": { "width_tiles": 3, "height_tiles": 3 },
            "clearance": { "front_cm": 60, "back_cm": 20, "left_cm": 20, "right_cm": 20 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 1 if i <= 5 else 2, "appearance": { "material": "soundproof_felt", "style": "acoustic_cube", "color": "dark_slate" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 5. Breakroom & Kitchen (Batch 20 - 17 assets)
    ktypes = ["coffee_machine", "water_dispenser", "refrigerator", "microwave", "kitchen_sink"]
    for i in range(1, 18):
        fid = f"BRK-KIT-EQP-{i:03d}"
        fname = f"breakroom_{i:02d}.png"
        fpath = os.path.join("public/assets/breakroom", fname)
        ktype = ktypes[i % len(ktypes)]
        render_kitchen(fpath, ktype)
        entry = {
            "id": fid, "name": f"Kitchen {ktype.replace('_', ' ').title()} {i:02d}", "category": "breakroom", "subcategory": "kitchen", "type": ktype,
            "asset": { "image": f"/assets/breakroom/{fname}", "thumbnail": f"/assets/breakroom/{fname}" },
            "dimensions": { "width_cm": 70, "depth_cm": 60, "height_cm": 90 },
            "footprint": { "width_tiles": 2, "height_tiles": 2 },
            "clearance": { "front_cm": 60, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 0, "appearance": { "material": "stainless_steel", "style": "breakroom", "color": "silver" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 6. Safety & Waste Bins (Batch 21 & 22 - 19 assets)
    stypes = ["fire_ext", "first_aid", "cctv_cam", "waste_bin", "recycling_bin"]
    for i in range(1, 20):
        fid = f"SAF-FAC-EQP-{i:03d}"
        fname = f"safety_office_{i:02d}.png"
        fpath = os.path.join("public/assets/safety", fname)
        stype = stypes[i % len(stypes)]
        render_safety(fpath, stype)
        entry = {
            "id": fid, "name": f"Facility {stype.replace('_', ' ').title()} {i:02d}", "category": "safety", "subcategory": "facilities", "type": stype,
            "asset": { "image": f"/assets/safety/{fname}", "thumbnail": f"/assets/safety/{fname}" },
            "dimensions": { "width_cm": 40, "depth_cm": 40, "height_cm": 60 },
            "footprint": { "width_tiles": 1, "height_tiles": 1 },
            "clearance": { "front_cm": 10, "back_cm": 10, "left_cm": 10, "right_cm": 10 },
            "behavior": { "collision": "bin" in stype, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 0, "appearance": { "material": "metal", "style": "facility", "color": "standard" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    # 7. Building Architectural Elements (Doors, Walls, Windows - Batch 24 - 36 assets)
    btypes = ["door_single", "door_double", "door_glass", "window_standard", "window_floor_to_ceiling", "wall_partition"]
    for i in range(1, 37):
        fid = f"BLD-ARC-ELM-{i:03d}"
        fname = f"building_element_{i:02d}.png"
        subfolder = "doors" if "door" in btypes[i % len(btypes)] else ("windows" if "window" in btypes[i % len(btypes)] else "walls")
        fpath = os.path.join(f"public/assets/building/{subfolder}", fname)
        btype = btypes[i % len(btypes)]
        render_building(fpath, btype)
        entry = {
            "id": fid, "name": f"Architectural {btype.replace('_', ' ').title()} {i:02d}", "category": "building", "subcategory": subfolder, "type": btype,
            "asset": { "image": f"/assets/building/{subfolder}/{fname}", "thumbnail": f"/assets/building/{subfolder}/{fname}" },
            "dimensions": { "width_cm": 100, "depth_cm": 20, "height_cm": 240 },
            "footprint": { "width_tiles": 2, "height_tiles": 1 },
            "clearance": { "front_cm": 40, "back_cm": 40, "left_cm": 0, "right_cm": 0 },
            "behavior": { "collision": "wall" in btype, "movable": True, "rotatable": True, "interactive": "door" in btype },
            "capacity": 0, "appearance": { "material": "glass_metal", "style": "architectural", "color": "dark" }, "status": "active"
        }
        if not any(x["id"] == fid for x in catalog): catalog.append(entry)

    with open(catalog_path, "w", encoding="utf-8") as f:
        json.dump(catalog, f, indent=2, ensure_ascii=False)

    print(f"ALL 27 BATCHES COMPLETED! Grand Total Catalog Assets: {len(catalog)}")

if __name__ == "__main__":
    generate_all_remaining()
