import json
from pathlib import Path
from PIL import Image

root = Path(__file__).resolve().parent
bundle = root / 'portable-demo'
manifest = json.loads((bundle / 'manifest.json').read_text(encoding='utf-8'))
# Giữ nguyên bytes nguồn. Chỉ chấp nhận 3 tag số/byte hằng đã quan sát trong encoder,
# không chấp nhận GPS, tên tác giả, owner, serial, comment hoặc chuỗi metadata.
allowed = {20752: b'\x01', 20753: 0, 20754: 0}
review = []
counts = {}
for row in manifest['images']:
    with Image.open(bundle / 'media' / row['path']) as image:
        for key, value in image.getexif().items():
            counts[str(key)] = counts.get(str(key), 0) + 1
            if key not in allowed or value != allowed[key]:
                review.append({'path': row['path'], 'tag': key})
result = {'images_checked': len(manifest['images']), 'tag_counts': counts,
          'metadata_requiring_review': review, 'passed': not review,
          'method': 'Pillow EXIF parse; only three observed constant numeric/byte tags allowed; original bytes unchanged'}
(root / 'metadata-check.json').write_text(json.dumps(result, indent=2), encoding='utf-8')
print(json.dumps(result, indent=2))
if review:
    raise SystemExit(1)
