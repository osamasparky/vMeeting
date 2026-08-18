import os
import json
from PIL import Image, ImageDraw, ImageFilter

def create_executive_desk(output_path, width_px, height_px, material="walnut", details=None):
    if details is None:
        details = {}

    canvas_w = width_px + 90
    canvas_h = height_px + 90
    img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))

    # 1. Soft contact shadow layer
    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    s_draw = ImageDraw.Draw(shadow_img)
    ox = 45
    oy = 45

    s_draw.rounded_rectangle([ox + 4, oy + 8, ox + width_px - 4, oy + height_px + 12], radius=16, fill=(0, 0, 0, 110))
    shadow_img = shadow_img.filter(ImageFilter.GaussianBlur(12))
    img.paste(shadow_img, (0, 0), shadow_img)

    # 2. Main Executive Desk Surface
    desk_surface = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    draw = ImageDraw.Draw(desk_surface)

    # Material Palette
    if material == "walnut":
        base_color = (68, 38, 22)
        accent_color = (84, 46, 28)
        border_color = (42, 22, 12)
        trim_color = (180, 140, 60) # Brass / Gold trim
    elif material == "dark_wood":
        base_color = (40, 26, 20)
        accent_color = (55, 36, 28)
        border_color = (25, 16, 12)
        trim_color = (100, 116, 139) # Silver chrome
    elif material == "glass":
        base_color = (186, 230, 253, 160)
        accent_color = (224, 242, 254, 200)
        border_color = (56, 189, 248, 240)
        trim_color = (148, 163, 184) # Brushed aluminum
    elif material == "luxury":
        base_color = (30, 20, 18)
        accent_color = (45, 30, 25)
        border_color = (212, 175, 55) # Gold border
        trim_color = (234, 179, 8)
    elif material == "oak":
        base_color = (210, 170, 130)
        accent_color = (185, 145, 105)
        border_color = (150, 115, 75)
        trim_color = (30, 41, 59)
    else:
        base_color = (50, 35, 25)
        accent_color = (70, 48, 35)
        border_color = (30, 20, 15)
        trim_color = (180, 140, 60)

    # Shape logic
    shape = details.get("shape", "rect")
    if shape == "L":
        return_w = details.get("return_w", 100)
        return_h = details.get("return_h", 120)
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=12, fill=base_color, outline=border_color, width=2)
        draw.rounded_rectangle([ox + width_px - return_w, oy, ox + width_px, oy + height_px + return_h], radius=12, fill=base_color, outline=border_color, width=2)
    elif shape == "U":
        wing_w = details.get("wing_w", 80)
        wing_h = details.get("wing_h", 100)
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=12, fill=base_color, outline=border_color, width=2)
        draw.rounded_rectangle([ox, oy, ox + wing_w, oy + height_px + wing_h], radius=12, fill=base_color, outline=border_color, width=2)
        draw.rounded_rectangle([ox + width_px - wing_w, oy, ox + width_px, oy + height_px + wing_h], radius=12, fill=base_color, outline=border_color, width=2)
    else:
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=12, fill=base_color, outline=border_color, width=2)

    # Wood grain or Glass reflection lines
    if material == "glass":
        # Diagonal glass reflection shine
        draw.line([ox + 20, oy + 10, ox + width_px - 40, oy + height_px - 10], fill=(255, 255, 255, 90), width=4)
        draw.line([ox + 40, oy + 10, ox + width_px - 20, oy + height_px - 10], fill=(255, 255, 255, 40), width=2)
    else:
        for i in range(oy + 14, oy + height_px - 14, 20):
            draw.line([ox + 12, i, ox + width_px - 12, i], fill=accent_color, width=1)

    # Executive Leather Inlay / Blotter
    if details.get("leather_inlay", True):
        pad_w = min(width_px - 60, 130)
        pad_h = min(height_px - 30, 70)
        px = ox + (width_px - pad_w) // 2
        py = oy + (height_px - pad_h) // 2 + 4
        # Black or dark saddle leather with gold stitch perimeter
        draw.rounded_rectangle([px, py, px + pad_w, py + pad_h], radius=6, fill=(18, 18, 20), outline=trim_color, width=1)

    # Credenza / Side Cabinet details
    if details.get("credenza"):
        cw = 70
        ch = height_px - 12
        cx = ox + width_px - cw - 6
        cy = oy + 6
        draw.rounded_rectangle([cx, cy, cx + cw, cy + ch], radius=4, fill=(accent_color[0]-10, accent_color[1]-10, accent_color[2]-10), outline=trim_color, width=1)
        draw.line([cx + 10, cy + ch//2, cx + cw - 10, cy + ch//2], fill=trim_color, width=2)

    # Dual brass cable grommets
    draw.ellipse([ox + 20, oy + 10, ox + 36, oy + 26], fill=(24, 24, 27), outline=trim_color, width=2)
    draw.ellipse([ox + width_px - 36, oy + 10, ox + width_px - 20, oy + 26], fill=(24, 24, 27), outline=trim_color, width=2)

    # Luxury Beveled Border Rim
    draw.line([ox + 8, oy + 2, ox + width_px - 8, oy + 2], fill=(255, 255, 255, 110), width=2)

    img.paste(desk_surface, (0, 0), desk_surface)

    # Auto-crop transparent boundaries
    bbox = img.getbbox()
    if bbox:
        img = img.crop(bbox)
        final = Image.new("RGBA", (img.width + 16, img.height + 16), (0, 0, 0, 0))
        final.paste(img, (8, 8), img)
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        final.save(output_path, "PNG")
    else:
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        img.save(output_path, "PNG")

    print(f"Generated Executive Desk Asset: {output_path}")

BATCH_02_ASSETS = [
    {
        "id": "FUR-DESK-MGR-001",
        "name": "Manager Desk",
        "file": "desk_manager_executive_01.png",
        "w": 200, "h": 100, "material": "walnut",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 180, "depth_cm": 90, "height_cm": 75 },
        "footprint": { "width_tiles": 4, "height_tiles": 2 },
        "material_name": "walnut", "style": "executive"
    },
    {
        "id": "FUR-DESK-MGR-002",
        "name": "Large Manager Desk",
        "file": "desk_manager_executive_02.png",
        "w": 230, "h": 110, "material": "walnut",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 200, "depth_cm": 100, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 3 },
        "material_name": "walnut", "style": "large_executive"
    },
    {
        "id": "FUR-DESK-MGR-003",
        "name": "Executive Desk",
        "file": "desk_manager_executive_03.png",
        "w": 220, "h": 105, "material": "luxury",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 200, "depth_cm": 95, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 2 },
        "material_name": "ebony_gold", "style": "luxury_executive"
    },
    {
        "id": "FUR-DESK-MGR-004",
        "name": "Executive L-Shape Desk",
        "file": "desk_manager_executive_04.png",
        "w": 220, "h": 110, "material": "walnut",
        "details": {"shape": "L", "return_w": 90, "return_h": 90, "leather_inlay": True},
        "dimensions": { "width_cm": 220, "depth_cm": 180, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 4 },
        "material_name": "walnut", "style": "executive_l"
    },
    {
        "id": "FUR-DESK-MGR-005",
        "name": "Executive U-Shape Desk",
        "file": "desk_manager_executive_05.png",
        "w": 240, "h": 110, "material": "luxury",
        "details": {"shape": "U", "wing_w": 80, "wing_h": 90, "leather_inlay": True},
        "dimensions": { "width_cm": 240, "depth_cm": 200, "height_cm": 75 },
        "footprint": { "width_tiles": 6, "height_tiles": 5 },
        "material_name": "luxury_wood", "style": "executive_u"
    },
    {
        "id": "FUR-DESK-MGR-006",
        "name": "Manager Desk With Credenza",
        "file": "desk_manager_executive_06.png",
        "w": 220, "h": 105, "material": "walnut",
        "details": {"credenza": True, "leather_inlay": True},
        "dimensions": { "width_cm": 200, "depth_cm": 100, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 3 },
        "material_name": "walnut", "style": "credenza_suite"
    },
    {
        "id": "FUR-DESK-MGR-007",
        "name": "Manager Desk With Side Storage",
        "file": "desk_manager_executive_07.png",
        "w": 210, "h": 105, "material": "dark_wood",
        "details": {"credenza": True, "leather_inlay": True},
        "dimensions": { "width_cm": 190, "depth_cm": 95, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 3 },
        "material_name": "dark_wood", "style": "side_storage"
    },
    {
        "id": "FUR-DESK-MGR-008",
        "name": "Premium Wooden Manager Desk",
        "file": "desk_manager_executive_08.png",
        "w": 210, "h": 100, "material": "oak",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 180, "depth_cm": 90, "height_cm": 75 },
        "footprint": { "width_tiles": 4, "height_tiles": 2 },
        "material_name": "solid_oak", "style": "premium_wood"
    },
    {
        "id": "FUR-DESK-MGR-009",
        "name": "Glass Manager Desk",
        "file": "desk_manager_executive_09.png",
        "w": 200, "h": 95, "material": "glass",
        "details": {"leather_inlay": False},
        "dimensions": { "width_cm": 180, "depth_cm": 85, "height_cm": 75 },
        "footprint": { "width_tiles": 4, "height_tiles": 2 },
        "material_name": "tempered_glass", "style": "modern_glass"
    },
    {
        "id": "FUR-DESK-MGR-010",
        "name": "Modern Executive Desk",
        "file": "desk_manager_executive_10.png",
        "w": 210, "h": 100, "material": "dark_wood",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 190, "depth_cm": 90, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 2 },
        "material_name": "dark_wood", "style": "modern_executive"
    },
    {
        "id": "FUR-DESK-MGR-011",
        "name": "Minimal Executive Desk",
        "file": "desk_manager_executive_11.png",
        "w": 190, "h": 90, "material": "oak",
        "details": {"leather_inlay": False},
        "dimensions": { "width_cm": 170, "depth_cm": 80, "height_cm": 75 },
        "footprint": { "width_tiles": 4, "height_tiles": 2 },
        "material_name": "oak", "style": "minimal_executive"
    },
    {
        "id": "FUR-DESK-MGR-012",
        "name": "Dark Executive Desk",
        "file": "desk_manager_executive_12.png",
        "w": 220, "h": 105, "material": "dark_wood",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 200, "depth_cm": 95, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 3 },
        "material_name": "charcoal_wood", "style": "dark_executive"
    },
    {
        "id": "FUR-DESK-MGR-013",
        "name": "Walnut Executive Desk",
        "file": "desk_manager_executive_13.png",
        "w": 220, "h": 105, "material": "walnut",
        "details": {"leather_inlay": True},
        "dimensions": { "width_cm": 200, "depth_cm": 95, "height_cm": 75 },
        "footprint": { "width_tiles": 5, "height_tiles": 3 },
        "material_name": "walnut", "style": "heritage_walnut"
    },
    {
        "id": "FUR-DESK-MGR-014",
        "name": "Luxury Executive Desk",
        "file": "desk_manager_executive_14.png",
        "w": 240, "h": 115, "material": "luxury",
        "details": {"credenza": True, "leather_inlay": True},
        "dimensions": { "width_cm": 220, "depth_cm": 105, "height_cm": 75 },
        "footprint": { "width_tiles": 6, "height_tiles": 3 },
        "material_name": "luxury_ebony", "style": "presidential"
    },
    {
        "id": "FUR-DESK-MGR-015",
        "name": "Corner Executive Desk",
        "file": "desk_manager_executive_15.png",
        "w": 230, "h": 115, "material": "walnut",
        "details": {"shape": "L", "return_w": 100, "return_h": 100, "leather_inlay": True},
        "dimensions": { "width_cm": 220, "depth_cm": 190, "height_cm": 75 },
        "footprint": { "width_tiles": 6, "height_tiles": 5 },
        "material_name": "walnut", "style": "executive_corner"
    }
]

def run_batch_02():
    out_dir = "public/assets/furniture/desks"
    catalog_path = "database/data/furniture_catalog.json"

    # 1. Load existing catalog
    with open(catalog_path, "r", encoding="utf-8") as f:
        catalog = json.load(f)

    # 2. Generate assets and build catalog items
    for item in BATCH_02_ASSETS:
        target_file = os.path.join(out_dir, item["file"])
        create_executive_desk(target_file, item["w"], item["h"], item["material"], item["details"])

        catalog_entry = {
            "id": item["id"],
            "name": item["name"],
            "category": "furniture",
            "subcategory": "desks",
            "type": "manager_executive_desk",
            "asset": {
                "image": f"/assets/furniture/desks/{item['file']}",
                "thumbnail": f"/assets/furniture/desks/{item['file']}"
            },
            "dimensions": item["dimensions"],
            "footprint": item["footprint"],
            "clearance": { "front_cm": 100, "back_cm": 20, "left_cm": 20, "right_cm": 20 },
            "behavior": { "collision": True, "movable": True, "rotatable": True, "interactive": False },
            "capacity": 1,
            "appearance": { "material": item["material_name"], "style": item["style"], "color": item["material"] },
            "status": "active"
        }

        # Check if already in catalog, if so update, else append
        existing = next((x for x in catalog if x["id"] == item["id"]), None)
        if existing:
            existing.update(catalog_entry)
        else:
            catalog.append(catalog_entry)

    # 3. Save catalog JSON
    with open(catalog_path, "w", encoding="utf-8") as f:
        json.dump(catalog, f, indent=2, ensure_ascii=False)

    print("Successfully processed and saved BATCH 02 (15 Assets) to catalog.")

if __name__ == "__main__":
    run_batch_02()
