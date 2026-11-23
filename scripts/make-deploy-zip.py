# scripts/make-deploy-zip.py
# Usage: python -I scripts/make-deploy-zip.py <zip-name.zip>
# Zips the files in deploy/pending-files.txt plus public/build/ into ~/Downloads, with forward slashes.
import glob, json, os, sys, zipfile

root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
out = os.path.join(os.path.expanduser('~'), 'Downloads', sys.argv[1])
skip = ('.env', 'vendor/', 'node_modules/', '.git/', 'storage/logs/', 'storage/framework/', 'bootstrap/cache/', 'deploy/')

lines = [l.strip().replace(chr(92), '/') for l in open(os.path.join(root, 'deploy', 'pending-files.txt'), encoding='utf-8')]
lines = [l for l in lines if l and not l.startswith('#')]
deleted = [l[2:].strip() for l in lines if l.startswith('- ')]
files = [l for l in lines if not l.startswith('- ')]
files += [os.path.relpath(f, root).replace(os.sep, '/') for f in glob.glob(os.path.join(root, 'public', 'build', '**', '*'), recursive=True) if os.path.isfile(f)]
files = [f for f in dict.fromkeys(files) if not f.startswith(skip)]

missing = [f for f in files if not os.path.isfile(os.path.join(root, f))]
if missing:
    sys.exit('Missing files (if deleted, mark them "- path" in the list): ' + ', '.join(missing))

with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
    for f in files:
        z.write(os.path.join(root, f), f)

with zipfile.ZipFile(out) as z:
    names = z.namelist()
    assert z.testzip() is None, 'corrupt zip'
    assert not any(chr(92) in n for n in names), 'backslash in a zip path'
    assert len(names) == len(set(names)), 'duplicate entries'
    if 'public/build/manifest.json' in names:
        manifest = json.loads(z.read('public/build/manifest.json'))
        lost = [v['file'] for v in manifest.values() if 'public/build/' + v['file'] not in names]
        assert not lost, 'assets in manifest but not in zip: %s' % lost

print('%d files -> %s (%d KB), verified' % (len(files), out, os.path.getsize(out) // 1024))
for d in deleted:
    print('DELETED (add rm -f to the commands):', d)
