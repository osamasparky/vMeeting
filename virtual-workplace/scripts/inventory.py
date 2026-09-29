import re, sys, os

def inventory(group_name, files):
    pattern = re.compile(r'id="[^"]+"|name="[^"]+"|route\([^)]+\)|@(if|foreach|can|auth|guest|include)\b[^\r\n]{0,60}')
    matches = set()
    for f in files:
        if os.path.exists(f):
            with open(f, 'r', encoding='utf-8') as fp:
                content = fp.read()
                for m in pattern.finditer(content):
                    matches.add(m.group(0))
    
    os.makedirs('storage/framework/inventory', exist_ok=True)
    out_path = f'storage/framework/inventory/{group_name}.txt'
    sorted_matches = sorted(list(matches))
    with open(out_path, 'w', encoding='utf-8') as out:
        for item in sorted_matches:
            out.write(item + '\n')
    print(f'Wrote {len(sorted_matches)} items to {out_path}')

if __name__ == '__main__':
    group = sys.argv[1]
    files = sys.argv[2:]
    inventory(group, files)
