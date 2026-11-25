import json
from pathlib import Path
from collections import Counter
from PIL import Image

root = Path(__file__).resolve().parent
bundle = Path(json.loads((root / 'inputs.json').read_text())['extracted'])
manifest = json.loads((bundle / 'manifest.json').read_text())
allowed = {20752: b'\x01', 20753: 0, 20754: 0}
review, tags, segments = [], Counter(), Counter()
for entry in manifest['images']:
    image_path = bundle / 'media' / entry['path']
    with Image.open(image_path) as image:
        for key, value in image.getexif().items():
            tags[str(key)] += 1
            if key not in allowed or value != allowed[key]:
                review.append({'path': entry['path'], 'reason': f'EXIF tag {key} requires review'})
    raw = image_path.read_bytes()
    pos = 2
    while pos < len(raw):
        if raw[pos] != 255:
            raise ValueError('Invalid JPEG marker')
        while raw[pos] == 255:
            pos += 1
        marker = raw[pos]
        pos += 1
        if marker in (218, 217):
            break
        length = int.from_bytes(raw[pos:pos+2], 'big')
        body = raw[pos+2:pos+length]
        pos += length
        if 224 <= marker <= 239 or marker == 254:
            label = f'{marker:02x}'
            segments[label] += 1
            ok = ((marker == 224 and body.startswith(b'JFIF\0')) or
                  (marker == 225 and body.startswith(b'Exif\0\0')) or
                  (marker == 226 and body.startswith(b'ICC_PROFILE\0')) or
                  (marker == 238 and body.startswith(b'Adobe')))
            if not ok:
                review.append({'path': entry['path'], 'reason': f'JPEG metadata marker {label} requires review'})
result = {'images': len(manifest['images']), 'exif_tag_counts': dict(tags), 'metadata_segment_counts': dict(segments), 'requires_review': review, 'passed': not review, 'scope': 'EXIF values and JPEG APP/COM segment signatures; decoded JPEGs; no metadata altered'}
(root / 'metadata.json').write_text(json.dumps(result, indent=2))
print(json.dumps(result, indent=2))
if review:
    raise SystemExit(1)
