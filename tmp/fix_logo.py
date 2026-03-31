import os
from PIL import Image

def fix_logo_identity():
    input_path = r'C:\Users\Lenovo\.gemini\antigravity\brain\5e14f71c-86fc-47bb-8de9-57389411d457\media__1774928337322.png'
    output_icon = r'c:\laragon\www\Algoritma\public\assets\logo-icon.png'
    output_favicon = r'c:\laragon\www\Algoritma\public\favicon.ico'
    
    # Ensure directory exists
    os.makedirs(os.path.dirname(output_icon), exist_ok=True)
    
    # Process Image
    img = Image.open(input_path).convert("RGBA")
    
    # CROP ONLY THE GRAPHIC PART (Estimated left side of the logo)
    # The original image is 1024x1024 usually from DALL-E or similar
    width, height = img.size
    # Crop the left ~40% where the graphic is
    icon_part = img.crop((0, 0, int(width * 0.45), height))
    
    # Remove background from icon part
    datas = icon_part.getdata()
    newData = []
    for item in datas:
        if item[0] > 240 and item[1] > 240 and item[2] > 240:
            newData.append((255, 255, 255, 0))
        else:
            # Increase brightness slightly to pop on dark bg
            r = min(255, int(item[0] * 1.2))
            g = min(255, int(item[1] * 1.2))
            b = min(255, int(item[2] * 1.2))
            newData.append((r, g, b, item[3]))
            
    icon_part.putdata(newData)
    
    # Tight crop to non-transparent pixels
    bbox = icon_part.getbbox()
    if bbox:
        icon_part = icon_part.crop(bbox)
    
    # Save processed icon
    icon_part.save(output_icon, "PNG")
    
    # Update Favicon
    favicon_img = icon_part.copy()
    favicon_img.thumbnail((64, 64))
    favicon_img.save(output_favicon, "ICO")
    
    print("SUCCESS: Optimized Icon and Favicon generated.")

if __name__ == "__main__":
    fix_logo_identity()
