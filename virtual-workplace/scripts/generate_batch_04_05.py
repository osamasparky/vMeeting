import os
import json
import math
from PIL import Image, ImageDraw, ImageFilter

def create_table(output_path, width_px, height_px, shape="rect", material="oak", details=None):
    if details is None:
        details = {}

    canvas_w = width_px + 80
    canvas_h = height_px + 80
    img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))

    # 1. Contact shadow layer
    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    s_draw = ImageDraw.Draw(shadow_img)
    ox, oy = 40, 40

    if shape == "round":
        s_draw.ellipse([ox + 4, oy + 8, ox + width_px - 4, oy + height_px + 8], fill=(0, 0, 0, 100))
    elif shape == "oval":
        s_draw.ellipse([ox + 4, oy + 8, ox + width_px - 4, oy + height_px + 10], fill=(0, 0, 0, 100))
    else:
        s_draw.rounded_rectangle([ox + 4, oy + 8, ox + width_px - 4, oy + height_px + 10], radius=16, fill=(0, 0, 0, 100))

    shadow_img = shadow_img.filter(ImageFilter.GaussianBlur(10))
    img.paste(shadow_img, (0, 0), shadow_img)

    # 2. Table Surface Layer
    table_surface = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    draw = ImageDraw.Draw(table_surface)

    # Colors
    if material == "walnut":
        base_color = (68, 38, 22)
        accent_color = (84, 46, 28)
        border_color = (42, 22, 12)
        trim_color = (180, 140, 60)
    elif material == "dark_wood":
        base_color = (35, 24, 20)
        accent_color = (50, 34, 28)
        border_color = (20, 14, 10)
        trim_color = (100, 116, 139)
    elif material == "glass":
        base_color = (186, 230, 253, 160)
        accent_color = (224, 242, 254, 200)
        border_color = (56, 189, 248, 240)
        trim_color = (148, 163, 184)
    elif material == "white":
        base_color = (248, 250, 252)
        accent_color = (226, 232, 240)
        border_color = (203, 213, 225)
        trim_color = (148, 163, 184)
    elif material == "marble":
        base_color = (241, 245, 249)
        accent_color = (203, 213, 225)
        border_color = (148, 163, 184)
        trim_color = (212, 175, 55)
    else: # oak
        base_color = (220, 180, 140)
        accent_color = (195, 155, 115)
        border_color = (160, 125, 85)
        trim_color = (30, 41, 59)

    # Draw Shape
    if shape == "round":
        draw.ellipse([ox, oy, ox + width_px, oy + height_px], fill=base_color, outline=border_color, width=2)
        # Bevel rim
        draw.ellipse([ox + 4, oy + 4, ox + width_px - 4, oy + height_px - 4], outline=(255, 255, 255, 80), width=1)
    elif shape == "oval":
        draw.ellipse([ox, oy, ox + width_px, oy + height_px], fill=base_color, outline=border_color, width=2)
        draw.ellipse([ox + 4, oy + 4, ox + width_px - 4, oy + height_px - 4], outline=(255, 255, 255, 80), width=1)
    else:
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=14, fill=base_color, outline=border_color, width=2)
        draw.line([ox + 6, oy + 2, ox + width_px - 6, oy + 2], fill=(255, 255, 255, 100), width=2)

    # Conference Cable / Power Modules in Center
    if details.get("power_strip"):
        box_w = min(width_px - 60, 90)
        box_h = 24
        bx = ox + (width_px - box_w) // 2
        by = oy + (height_px - box_h) // 2
        draw.rounded_rectangle([bx, by, bx + box_w, by + box_h], radius=4, fill=(15, 23, 42), outline=trim_color, width=1)
        # Power / HDMI socket dots
        for dot_x in range(bx + 12, bx + box_w - 8, 16):
            draw.ellipse([dot_x, by + 8, dot_x + 6, by + 14], fill=(71, 85, 105))

    img.paste(table_surface, (0, 0), table_surface)

    # Auto-crop transparent boundaries
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

    print(f"Generated Table Asset: {output_path}")

BATCH_04_TABLES = [
    {"id": "FUR-TBL-SIDE-001", "name": "Small Side Table", "file": "table_side_01.png", "w": 60, "h": 60, "shape": "square", "mat": "oak", "dim": (50, 50, 45), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-002", "name": "Medium Side Table", "file": "table_side_02.png", "w": 80, "h": 80, "shape": "square", "mat": "walnut", "dim": (70, 70, 45), "fp": (2, 2)},
    {"id": "FUR-TBL-SIDE-003", "name": "Coffee Table", "file": "table_side_03.png", "w": 130, "h": 70, "shape": "rect", "mat": "oak", "dim": (120, 60, 45), "fp": (3, 2)},
    {"id": "FUR-TBL-SIDE-004", "name": "Center Table", "file": "table_side_04.png", "w": 120, "h": 120, "shape": "round", "mat": "marble", "dim": (100, 100, 45), "fp": (2, 2)},
    {"id": "FUR-TBL-SIDE-005", "name": "End Table", "file": "table_side_05.png", "w": 60, "h": 60, "shape": "square", "mat": "dark_wood", "dim": (50, 50, 55), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-006", "name": "Round Side Table", "file": "table_side_06.png", "w": 70, "h": 70, "shape": "round", "mat": "oak", "dim": (60, 60, 50), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-007", "name": "Square Side Table", "file": "table_side_07.png", "w": 70, "h": 70, "shape": "square", "mat": "white", "dim": (60, 60, 50), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-008", "name": "Laptop Table", "file": "table_side_08.png", "w": 60, "h": 45, "shape": "rect", "mat": "walnut", "dim": (55, 40, 65), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-009", "name": "Printer Table", "file": "table_side_09.png", "w": 80, "h": 60, "shape": "rect", "mat": "white", "dim": (70, 55, 70), "fp": (2, 1)},
    {"id": "FUR-TBL-SIDE-010", "name": "Telephone Table", "file": "table_side_10.png", "w": 50, "h": 50, "shape": "square", "mat": "oak", "dim": (45, 45, 75), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-011", "name": "Folding Table", "file": "table_side_11.png", "w": 140, "h": 70, "shape": "rect", "mat": "white", "dim": (120, 60, 75), "fp": (3, 2)},
    {"id": "FUR-TBL-SIDE-012", "name": "High Table", "file": "table_side_12.png", "w": 120, "h": 60, "shape": "rect", "mat": "dark_wood", "dim": (110, 55, 105), "fp": (3, 1)},
    {"id": "FUR-TBL-SIDE-013", "name": "Bar Table", "file": "table_side_13.png", "w": 70, "h": 70, "shape": "round", "mat": "oak", "dim": (60, 60, 105), "fp": (1, 1)},
    {"id": "FUR-TBL-SIDE-014", "name": "Nesting Table", "file": "table_side_14.png", "w": 90, "h": 90, "shape": "round", "mat": "marble", "dim": (80, 80, 48), "fp": (2, 2)},
    {"id": "FUR-TBL-SIDE-015", "name": "Modern Side Table", "file": "table_side_15.png", "w": 65, "h": 65, "shape": "round", "mat": "glass", "dim": (55, 55, 50), "fp": (1, 1)}
]

BATCH_05_MEETING = [
    {"id": "FUR-TBL-MTG-001", "name": "Small Meeting Table", "file": "table_meeting_01.png", "w": 140, "h": 90, "shape": "rect", "mat": "oak", "dim": (140, 90, 75), "fp": (3, 2), "power": False},
    {"id": "FUR-TBL-MTG-002", "name": "Medium Meeting Table", "file": "table_meeting_02.png", "w": 200, "h": 100, "shape": "rect", "mat": "walnut", "dim": (200, 100, 75), "fp": (5, 2), "power": True},
    {"id": "FUR-TBL-MTG-003", "name": "Large Meeting Table", "file": "table_meeting_03.png", "w": 260, "h": 120, "shape": "rect", "mat": "walnut", "dim": (260, 120, 75), "fp": (6, 3), "power": True},
    {"id": "FUR-TBL-MTG-004", "name": "Rectangular Meeting Table", "file": "table_meeting_04.png", "w": 220, "h": 110, "shape": "rect", "mat": "oak", "dim": (220, 110, 75), "fp": (5, 3), "power": True},
    {"id": "FUR-TBL-MTG-005", "name": "Round Meeting Table", "file": "table_meeting_05.png", "w": 130, "h": 130, "shape": "round", "mat": "oak", "dim": (120, 120, 75), "fp": (3, 3), "power": False},
    {"id": "FUR-TBL-MTG-006", "name": "Oval Meeting Table", "file": "table_meeting_06.png", "w": 240, "h": 120, "shape": "oval", "mat": "walnut", "dim": (240, 120, 75), "fp": (6, 3), "power": True},
    {"id": "FUR-TBL-MTG-007", "name": "Modular Meeting Table", "file": "table_meeting_07.png", "w": 200, "h": 100, "shape": "rect", "mat": "white", "dim": (200, 100, 75), "fp": (5, 2), "power": True},
    {"id": "FUR-TBL-MTG-008", "name": "Folding Meeting Table", "file": "table_meeting_08.png", "w": 180, "h": 80, "shape": "rect", "mat": "white", "dim": (180, 80, 75), "fp": (4, 2), "power": False},
    {"id": "FUR-TBL-MTG-009", "name": "Conference Table", "file": "table_meeting_09.png", "w": 320, "h": 130, "shape": "rect", "mat": "dark_wood", "dim": (320, 130, 75), "fp": (8, 3), "power": True},
    {"id": "FUR-TBL-MTG-010", "name": "Video Conference Table", "file": "table_meeting_10.png", "w": 280, "h": 130, "shape": "oval", "mat": "walnut", "dim": (280, 130, 75), "fp": (7, 3), "power": True},
    {"id": "FUR-TBL-MTG-011", "name": "Executive Conference Table", "file": "table_meeting_11.png", "w": 360, "h": 140, "shape": "rect", "mat": "marble", "dim": (360, 140, 75), "fp": (9, 3), "power": True},
    {"id": "FUR-TBL-MTG-012", "name": "Premium Conference Table", "file": "table_meeting_12.png", "w": 400, "h": 150, "shape": "oval", "mat": "walnut", "dim": (400, 150, 75), "fp": (10, 4), "power": True}
]

def run_batches():
    out_side = "public/assets/furniture/tables"
    out_mtg = "public/assets/meeting"
    catalog_path = "database/data/furniture_catalog.json"

    with open(catalog_path, "r", encoding="utf-8") as f:
        catalog = json.load(f)

    # Batch 04
    for item in BATCH_04_TABLES:
        target = os.path.join(out_side, item["file"])
        create_table(target, item["w"], item["h"], item["shape"], item["mat"])
        entry = {
            "id": item["id"],
            "name": item["name"],
            "category": "furniture",
            "subcategory": "tables",
            "type": "side_table",
            "asset": { "image": f"/assets/furniture/tables/{item['file']}", "thumbnail": f"/assets/furniture/tables/{item['file']}" },
            "dimensions": { "width_cm": item["dim"][0], "depth_cm": item["dim"][1], "height_cm": item["dim"][2] },
            "footprint": { "width_tiles": item["fp"][0], "height_tiles": item["fp"][1] },
            "clearance": { "front_cm": 30, "back_cm": 20, "left_cm": 20, "right_cm": 20 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": False },
            "capacity": 1,
            "appearance": { "material": item["mat"], "style": item["shape"], "color": item["mat"] },
            "status": "active"
        }
        existing = next((x for x in catalog if x["id"] == item["id"]), None)
        if existing: existing.update(entry)
        else: catalog.append(entry)

    # Batch 05
    for item in BATCH_05_MEETING:
        target = os.path.join(out_mtg, item["file"])
        create_table(target, item["w"], item["h"], item["shape"], item["mat"], {"power_strip": item["power"]})
        entry = {
            "id": item["id"],
            "name": item["name"],
            "category": "meeting",
            "subcategory": "conference_tables",
            "type": "meeting_table",
            "asset": { "image": f"/assets/meeting/{item['file']}", "thumbnail": f"/assets/meeting/{item['file']}" },
            "dimensions": { "width_cm": item["dim"][0], "depth_cm": item["dim"][1], "height_cm": item["dim"][2] },
            "footprint": { "width_tiles": item["fp"][0], "height_tiles": item["fp"][1] },
            "clearance": { "front_cm": 80, "back_cm": 80, "left_cm": 50, "right_cm": 50 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": True },
            "capacity": max(4, item["fp"][0] * 2),
            "appearance": { "material": item["mat"], "style": "boardroom", "color": item["mat"] },
            "status": "active"
        }
        existing = next((x for x in catalog if x["id"] == item["id"]), None)
        if existing: existing.update(entry)
        else: catalog.append(entry)

    with open(catalog_path, "w", encoding="utf-8") as f:
        json.dump(catalog, f, indent=2, ensure_ascii=False)

    print("Successfully processed BATCH 04 (15 Side Tables) & BATCH 05 (12 Meeting Tables)!")

if __name__ == "__main__":
    run_batches()
