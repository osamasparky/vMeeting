import os
import math
from PIL import Image, ImageDraw, ImageFilter

def create_top_view_desk(output_path, width_px, height_px, style="oak", details=None):
    if details is None:
        details = {}

    # Canvas size with padding
    canvas_w = width_px + 80
    canvas_h = height_px + 80
    img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    
    # 1. Soft contact shadow layer
    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    s_draw = ImageDraw.Draw(shadow_img)
    
    ox = 40
    oy = 40
    
    # Draw soft shadow ellipse / rounded rect underneath
    s_draw.rounded_rectangle([ox + 4, oy + 8, ox + width_px - 4, oy + height_px + 10], radius=16, fill=(0, 0, 0, 95))
    shadow_img = shadow_img.filter(ImageFilter.GaussianBlur(10))
    img.paste(shadow_img, (0, 0), shadow_img)

    # 2. Main Desk Surface
    desk_surface = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    draw = ImageDraw.Draw(desk_surface)

    # Style colors
    if style == "oak":
        base_color = (222, 185, 146)
        accent_color = (196, 156, 114)
        border_color = (168, 128, 88)
        frame_color = (30, 41, 59)
    elif style == "walnut":
        base_color = (74, 40, 24)
        accent_color = (90, 50, 30)
        border_color = (48, 24, 14)
        frame_color = (15, 23, 42)
    elif style == "white":
        base_color = (248, 250, 252)
        accent_color = (226, 232, 240)
        border_color = (203, 213, 225)
        frame_color = (148, 163, 184)
    elif style == "minimal":
        base_color = (235, 220, 195)
        accent_color = (215, 195, 165)
        border_color = (185, 165, 135)
        frame_color = (51, 65, 85)
    else:
        base_color = (210, 175, 135)
        accent_color = (180, 145, 105)
        border_color = (150, 115, 75)
        frame_color = (30, 41, 59)

    # Metal leg tabs visible at perimeter (top-down view)
    leg_w = 12
    draw.rounded_rectangle([ox - 4, oy + 4, ox + leg_w, oy + 24], radius=3, fill=frame_color)
    draw.rounded_rectangle([ox + width_px - leg_w, oy + 4, ox + width_px + 4, oy + 24], radius=3, fill=frame_color)
    draw.rounded_rectangle([ox - 4, oy + height_px - 24, ox + leg_w, oy + height_px - 4], radius=3, fill=frame_color)
    draw.rounded_rectangle([ox + width_px - leg_w, oy + height_px - 24, ox + width_px + 4, oy + height_px - 4], radius=3, fill=frame_color)

    # L-Shape or U-Shape desk check
    shape = details.get("shape", "rect")
    if shape == "L":
        return_w = details.get("return_w", 90)
        return_h = details.get("return_h", 110)
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=10, fill=base_color, outline=border_color, width=2)
        draw.rounded_rectangle([ox + width_px - return_w, oy, ox + width_px, oy + height_px + return_h], radius=10, fill=base_color, outline=border_color, width=2)
    elif shape == "U":
        wing_w = details.get("wing_w", 70)
        wing_h = details.get("wing_h", 90)
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=10, fill=base_color, outline=border_color, width=2)
        draw.rounded_rectangle([ox, oy, ox + wing_w, oy + height_px + wing_h], radius=10, fill=base_color, outline=border_color, width=2)
        draw.rounded_rectangle([ox + width_px - wing_w, oy, ox + width_px, oy + height_px + wing_h], radius=10, fill=base_color, outline=border_color, width=2)
    else:
        # Standard Rectangular Tabletop
        draw.rounded_rectangle([ox, oy, ox + width_px, oy + height_px], radius=10, fill=base_color, outline=border_color, width=2)

    # Draw natural wood grain lines or bevel highlight
    for i in range(oy + 10, oy + height_px - 10, 18):
        draw.line([ox + 10, i, ox + width_px - 10, i], fill=accent_color, width=1)

    # Bevel top highlight
    draw.line([ox + 6, oy + 2, ox + width_px - 6, oy + 2], fill=(255, 255, 255, 120), width=2)

    # Details: Drawers pedestal
    if details.get("drawers"):
        dw = 60
        dh = height_px - 8
        dx = ox + width_px - dw - 4
        dy = oy + 4
        draw.rounded_rectangle([dx, dy, dx + dw, dy + dh], radius=4, fill=(accent_color[0]-15, accent_color[1]-15, accent_color[2]-15), outline=border_color, width=1)
        draw.line([dx + 6, dy + dh//3, dx + dw - 6, dy + dh//3], fill=(30, 41, 59), width=3)
        draw.line([dx + 6, dy + 2*dh//3, dx + dw - 6, dy + 2*dh//3], fill=(30, 41, 59), width=3)

    # Details: Desk pad
    if details.get("pad", True):
        pad_w = min(width_px - 40, 110)
        pad_h = min(height_px - 24, 60)
        px = ox + (width_px - pad_w) // 2
        py = oy + (height_px - pad_h) // 2 + 4
        draw.rounded_rectangle([px, py, px + pad_w, py + pad_h], radius=6, fill=(30, 41, 59), outline=(51, 65, 85), width=1)

    # Details: Grommet
    if details.get("grommet", True):
        draw.ellipse([ox + width_px - 28, oy + 8, ox + width_px - 12, oy + 24], fill=(51, 65, 85), outline=(100, 116, 139), width=1)
        draw.ellipse([ox + width_px - 24, oy + 12, ox + width_px - 16, oy + 20], fill=(15, 23, 42))

    # Details: Acoustic Screen Divider (For workstations)
    if details.get("screen"):
        draw.rounded_rectangle([ox + 8, oy + 2, ox + width_px - 8, oy + 8], radius=3, fill=(6, 182, 212), outline=(8, 145, 178), width=1)

    # Composite layers
    img.paste(desk_surface, (0, 0), desk_surface)

    # Auto-crop transparent boundaries with balanced padding
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

    print(f"Generated 3D Top-Down Transparent Asset: {output_path}")

# Generate all 20 Employee Desk Assets for Batch 01
DESKS_BATCH = [
    {"id": "FUR-DESK-EMP-001", "file": "desk_employee_modern_01.png", "w": 180, "h": 90, "style": "oak", "details": {"pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-002", "file": "desk_employee_modern_02.png", "w": 170, "h": 85, "style": "white", "details": {"pad": False, "grommet": True}},
    {"id": "FUR-DESK-EMP-003", "file": "desk_employee_modern_03.png", "w": 200, "h": 100, "style": "walnut", "details": {"pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-004", "file": "desk_employee_modern_04.png", "w": 190, "h": 90, "style": "oak", "details": {"drawers": True, "pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-005", "file": "desk_employee_modern_05.png", "w": 200, "h": 95, "style": "oak", "details": {"shape": "L", "return_w": 70, "return_h": 60, "pad": True}},
    {"id": "FUR-DESK-EMP-006", "file": "desk_employee_modern_06.png", "w": 190, "h": 90, "style": "oak", "details": {"shape": "L", "return_w": 80, "return_h": 70, "pad": True}},
    {"id": "FUR-DESK-EMP-007", "file": "desk_employee_modern_07.png", "w": 200, "h": 90, "style": "walnut", "details": {"shape": "U", "wing_w": 60, "wing_h": 65, "pad": True}},
    {"id": "FUR-DESK-EMP-008", "file": "desk_employee_modern_08.png", "w": 180, "h": 90, "style": "white", "details": {"screen": True, "pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-009", "file": "desk_employee_modern_09.png", "w": 220, "h": 140, "style": "white", "details": {"screen": True, "pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-010", "file": "desk_employee_modern_10.png", "w": 260, "h": 150, "style": "oak", "details": {"screen": True, "pad": True}},
    {"id": "FUR-DESK-EMP-011", "file": "desk_employee_modern_11.png", "w": 320, "h": 150, "style": "oak", "details": {"screen": True, "pad": True}},
    {"id": "FUR-DESK-EMP-012", "file": "desk_employee_modern_12.png", "w": 170, "h": 85, "style": "white", "details": {"pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-013", "file": "desk_employee_modern_13.png", "w": 160, "h": 80, "style": "walnut", "details": {"pad": False, "grommet": True}},
    {"id": "FUR-DESK-EMP-014", "file": "desk_employee_modern_14.png", "w": 175, "h": 85, "style": "oak", "details": {"pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-015", "file": "desk_employee_modern_15.png", "w": 220, "h": 90, "style": "white", "details": {"pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-016", "file": "desk_employee_modern_16.png", "w": 130, "h": 70, "style": "minimal", "details": {"pad": False, "grommet": False}},
    {"id": "FUR-DESK-EMP-017", "file": "desk_employee_modern_17.png", "w": 200, "h": 100, "style": "walnut", "details": {"pad": True, "drawers": True}},
    {"id": "FUR-DESK-EMP-018", "file": "desk_employee_modern_18.png", "w": 150, "h": 75, "style": "minimal", "details": {"pad": False, "grommet": True}},
    {"id": "FUR-DESK-EMP-019", "file": "desk_employee_modern_19.png", "w": 180, "h": 90, "style": "oak", "details": {"pad": True, "grommet": True}},
    {"id": "FUR-DESK-EMP-020", "file": "desk_employee_modern_20.png", "w": 180, "h": 90, "style": "white", "details": {"pad": False, "grommet": True}}
]

if __name__ == "__main__":
    out_dir = "public/assets/furniture/desks"
    for item in DESKS_BATCH:
        target_path = os.path.join(out_dir, item["file"])
        # If asset already exists from earlier Nano Banana generation (e.g. 01, 02, 03, 04, 05, 06), we keep it, otherwise generate!
        if not os.path.exists(target_path):
            create_top_view_desk(target_path, item["w"], item["h"], item["style"], item["details"])
        else:
            print(f"Asset already verified and present: {target_path}")
