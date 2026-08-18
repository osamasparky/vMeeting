import sys
import os
from PIL import Image

def process_transparency(input_path, output_path, tolerance=240, shadow_decay=True):
    img = Image.open(input_path).convert("RGBA")
    datas = img.getdata()

    new_data = []
    width, height = img.size

    for item in datas:
        # item is (r, g, b, a)
        r, g, b, a = item
        brightness = (r + g + b) / 3.0

        # If near pure white
        if r >= 248 and g >= 248 and b >= 248:
            new_data.append((255, 255, 255, 0))
        elif brightness >= tolerance:
            # Soft transition for contact shadow
            diff = 255.0 - brightness
            max_diff = 255.0 - tolerance
            alpha = int((diff / max_diff) * 220) if max_diff > 0 else 0
            if shadow_decay:
                new_data.append((0, 0, 0, alpha))
            else:
                new_data.append((r, g, b, alpha))
        else:
            new_data.append((r, g, b, 255))

    img.putdata(new_data)
    
    # Auto-crop excessive transparent borders while keeping balanced padding
    bbox = img.getbbox()
    if bbox:
        cropped = img.crop(bbox)
        # Add 12px padding around object
        padded = Image.new("RGBA", (cropped.width + 24, cropped.height + 24), (0, 0, 0, 0))
        padded.paste(cropped, (12, 12), cropped)
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        padded.save(output_path, "PNG")
    else:
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        img.save(output_path, "PNG")

    print(f"Saved transparent PNG to {output_path}")

if __name__ == "__main__":
    if len(sys.argv) >= 3:
        process_transparency(sys.argv[1], sys.argv[2])
    else:
        print("Usage: python make_transparent.py <input> <output>")
