import os
import json
import math
from PIL import Image, ImageDraw, ImageFilter

def create_office_chair(output_path, style="mesh", color="charcoal", details=None):
    if details is None:
        details = {}

    size = 140
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))

    # 1. Contact shadow
    shadow_img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    s_draw = ImageDraw.Draw(shadow_img)
    cx, cy = size // 2, size // 2
    s_draw.ellipse([cx - 42, cy - 40, cx + 42, cy + 44], fill=(0, 0, 0, 100))
    shadow_img = shadow_img.filter(ImageFilter.GaussianBlur(8))
    img.paste(shadow_img, (0, 0), shadow_img)

    # 2. Chair drawing layer
    draw = ImageDraw.Draw(img)

    # 5-Star Caster Base (visible from top-down)
    if details.get("star_base", True):
        base_color = (40, 45, 55) if color != "chrome" else (148, 163, 184)
        for i in range(5):
            angle = i * (2 * math.pi / 5) - math.pi / 2
            ex = cx + int(46 * math.cos(angle))
            ey = cy + int(46 * math.sin(angle))
            draw.line([cx, cy, ex, ey], fill=base_color, width=6)
            # Caster wheel at tip
            draw.ellipse([ex - 4, ey - 4, ex + 4, ey + 4], fill=(15, 23, 42))

    # Center piston cylinder
    draw.ellipse([cx - 10, cy - 10, cx + 10, cy + 10], fill=(24, 24, 27), outline=(71, 85, 105), width=2)

    # Color Mapping
    if color == "charcoal":
        seat_color = (30, 41, 59)
        cushion_border = (51, 65, 85)
        accent_color = (15, 23, 42)
    elif color == "black_leather":
        seat_color = (20, 20, 24)
        cushion_border = (39, 39, 42)
        accent_color = (9, 9, 11)
    elif color == "teal":
        seat_color = (13, 148, 136)
        cushion_border = (15, 118, 110)
        accent_color = (17, 94, 89)
    elif color == "blue":
        seat_color = (37, 99, 235)
        cushion_border = (29, 78, 216)
        accent_color = (30, 58, 138)
    elif color == "beige":
        seat_color = (214, 197, 175)
        cushion_border = (184, 167, 145)
        accent_color = (144, 127, 105)
    elif color == "orange":
        seat_color = (234, 88, 12)
        cushion_border = (194, 65, 12)
        accent_color = (154, 52, 18)
    else:
        seat_color = (40, 50, 65)
        cushion_border = (60, 75, 95)
        accent_color = (25, 35, 45)

    # Seat Cushion (Padded curved square / rounded rect)
    draw.rounded_rectangle([cx - 30, cy - 26, cx + 30, cy + 28], radius=14, fill=seat_color, outline=cushion_border, width=2)

    # Armrests (Left and Right)
    if details.get("armrests", True):
        arm_color = (15, 23, 42)
        draw.rounded_rectangle([cx - 38, cy - 14, cx - 28, cy + 18], radius=4, fill=arm_color, outline=(71, 85, 105), width=1)
        draw.rounded_rectangle([cx + 28, cy - 14, cx + 38, cy + 18], radius=4, fill=arm_color, outline=(71, 85, 105), width=1)

    # Backrest (Curved spine / mesh backrest at top)
    if style == "mesh":
        # Ergonomic mesh curve
        draw.arc([cx - 32, cy - 44, cx + 32, cy - 12], start=180, end=0, fill=(6, 182, 212), width=5)
        draw.arc([cx - 30, cy - 42, cx + 30, cy - 14], start=180, end=0, fill=(15, 23, 42), width=3)
    elif style == "executive":
        # Thick padded backrest with headrest
        draw.rounded_rectangle([cx - 28, cy - 42, cx + 28, cy - 24], radius=8, fill=accent_color, outline=(180, 140, 60), width=2)
        # Headrest pillow
        draw.rounded_rectangle([cx - 16, cy - 50, cx + 16, cy - 38], radius=6, fill=(15, 15, 18), outline=(180, 140, 60), width=1)
    elif style == "stool":
        # Round circular stool
        draw.ellipse([cx - 26, cy - 26, cx + 26, cy + 26], fill=seat_color, outline=cushion_border, width=2)
        draw.ellipse([cx - 12, cy - 12, cx + 12, cy + 12], fill=accent_color)
    else:
        # Standard curved backrest
        draw.rounded_rectangle([cx - 28, cy - 40, cx + 28, cy - 24], radius=6, fill=accent_color, outline=cushion_border, width=2)

    # Auto-crop transparent boundaries with balanced padding
    bbox = img.getbbox()
    if bbox:
        img = img.crop(bbox)
        final = Image.new("RGBA", (img.width + 12, img.height + 12), (0, 0, 0, 0))
        final.paste(img, (6, 6), img)
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        final.save(output_path, "PNG")
    else:
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        img.save(output_path, "PNG")

    print(f"Generated Office Chair Asset: {output_path}")

BATCH_03_CHAIRS = [
    {"id": "FUR-CHAIR-OFF-001", "name": "Ergonomic Mesh Chair", "file": "chair_office_01.png", "style": "mesh", "color": "charcoal", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-002", "name": "Modern Office Chair", "file": "chair_office_02.png", "style": "standard", "color": "blue", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-003", "name": "Standard Staff Chair", "file": "chair_office_03.png", "style": "standard", "color": "charcoal", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-004", "name": "Executive Chair", "file": "chair_office_04.png", "style": "executive", "color": "black_leather", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-005", "name": "Manager Chair", "file": "chair_office_05.png", "style": "executive", "color": "charcoal", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-006", "name": "Task Chair", "file": "chair_office_06.png", "style": "mesh", "color": "teal", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-007", "name": "Visitor Chair", "file": "chair_office_07.png", "style": "standard", "color": "beige", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-008", "name": "Meeting Chair", "file": "chair_office_08.png", "style": "mesh", "color": "charcoal", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-009", "name": "Conference Chair", "file": "chair_office_09.png", "style": "executive", "color": "black_leather", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-010", "name": "Guest Chair", "file": "chair_office_10.png", "style": "standard", "color": "charcoal", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-011", "name": "Lounge Chair", "file": "chair_office_11.png", "style": "standard", "color": "orange", "details": {"armrests": True, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-012", "name": "Armchair", "file": "chair_office_12.png", "style": "standard", "color": "teal", "details": {"armrests": True, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-013", "name": "Reception Chair", "file": "chair_office_13.png", "style": "standard", "color": "beige", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-014", "name": "Folding Chair", "file": "chair_office_14.png", "style": "standard", "color": "charcoal", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-015", "name": "Stackable Chair", "file": "chair_office_15.png", "style": "standard", "color": "blue", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-016", "name": "High Stool", "file": "chair_office_16.png", "style": "stool", "color": "charcoal", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-017", "name": "Bar Stool", "file": "chair_office_17.png", "style": "stool", "color": "teal", "details": {"armrests": False, "star_base": False}},
    {"id": "FUR-CHAIR-OFF-018", "name": "Drafting Chair", "file": "chair_office_18.png", "style": "mesh", "color": "charcoal", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-019", "name": "Premium Executive Chair", "file": "chair_office_19.png", "style": "executive", "color": "black_leather", "details": {"armrests": True, "star_base": True}},
    {"id": "FUR-CHAIR-OFF-020", "name": "Minimal Office Chair", "file": "chair_office_20.png", "style": "mesh", "color": "beige", "details": {"armrests": False, "star_base": True}}
]

def run_batch_03():
    out_dir = "public/assets/furniture/chairs"
    catalog_path = "database/data/furniture_catalog.json"

    with open(catalog_path, "r", encoding="utf-8") as f:
        catalog = json.load(f)

    for item in BATCH_03_CHAIRS:
        target_file = os.path.join(out_dir, item["file"])
        create_office_chair(target_file, item["style"], item["color"], item["details"])

        catalog_entry = {
            "id": item["id"],
            "name": item["name"],
            "category": "furniture",
            "subcategory": "chairs",
            "type": "office_chair",
            "asset": {
                "image": f"/assets/furniture/chairs/{item['file']}",
                "thumbnail": f"/assets/furniture/chairs/{item['file']}"
            },
            "dimensions": { "width_cm": 65, "depth_cm": 65, "height_cm": 95 },
            "footprint": { "width_tiles": 1, "height_tiles": 1 },
            "clearance": { "front_cm": 40, "back_cm": 20, "left_cm": 15, "right_cm": 15 },
            "behavior": { "collision": False, "movable": True, "rotatable": True, "interactive": True },
            "capacity": 1,
            "appearance": { "material": item["style"], "style": "ergonomic", "color": item["color"] },
            "status": "active"
        }

        existing = next((x for x in catalog if x["id"] == item["id"]), None)
        if existing:
            existing.update(catalog_entry)
        else:
            catalog.append(catalog_entry)

    with open(catalog_path, "w", encoding="utf-8") as f:
        json.dump(catalog, f, indent=2, ensure_ascii=False)

    print("Successfully processed and saved BATCH 03 (20 Chairs) to catalog.")

if __name__ == "__main__":
    run_batch_03()
