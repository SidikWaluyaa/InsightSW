import os
from PIL import Image

def remove_background():
    input_path = r'C:\Users\Lenovo\.gemini\antigravity\brain\5e14f71c-86fc-47bb-8de9-57389411d457\media__1774928337322.png'
    output_logo = r'c:\laragon\www\Algoritma\public\assets\logo.png'
    output_favicon = r'c:\laragon\www\Algoritma\public\favicon.ico'
    
    # Ensure directory exists
    os.makedirs(os.path.dirname(output_logo), exist_ok=True)
    
    # Process Logo
    img = Image.open(input_path).convert("RGBA")
    datas = img.getdata()
    
    newData = []
    for item in datas:
        # Threshold to catch nearly-white pixels
        if item[0] > 245 and item[1] > 245 and item[2] > 245:
            newData.append((255, 255, 255, 0)) # Fully transparent
        else:
            newData.append(item)
            
    img.putdata(newData)
    img.save(output_logo, "PNG")
    
    # Process Favicon (Smaller, square-ish if possible or just scaled)
    # Finding the bounding box of non-transparent pixels to crop tight
    bbox = img.getbbox()
    if bbox:
        favicon_img = img.crop(bbox)
        favicon_img.thumbnail((48, 48))
        favicon_img.save(output_favicon, "ICO")
        
    print("SUCCESS: Logo and Favicon generated.")

if __name__ == "__main__":
    remove_background()
