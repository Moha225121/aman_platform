import json
import sys
from pypdf import PdfReader

reader = PdfReader(sys.argv[1])
sys.stdout.reconfigure(encoding="utf-8")
json.dump([page.extract_text() or "" for page in reader.pages], sys.stdout, ensure_ascii=False)
