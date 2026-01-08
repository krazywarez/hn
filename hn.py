#!/usr/bin/env python3
"""
Static Hacker News page generator.

* Top stories → output/index.html (site root)
* Best, New, Ask, Show, Job → output/<section>/index.html
"""

import json
import urllib.request
from pathlib import Path
from datetime import datetime
from typing import List, Dict

BASE_URL = "https://hacker-news.firebaseio.com/v0"
OUTPUT_DIR = Path(__file__).parent / "output"
TEMPLATE_PATH = Path(__file__).parent / "templates" / "base.html"


# ----------------------------------------------------------------------
# Helper functions
# ----------------------------------------------------------------------
def fetch_json(url: str) -> dict:
    """GET a JSON endpoint and return the parsed object."""
    with urllib.request.urlopen(url) as resp:
        return json.load(resp)


def get_story_ids(endpoint: str, limit: int = 10) -> List[int]:
    """Return the first ``limit`` IDs for a given endpoint."""
    url = f"{BASE_URL}/{endpoint}.json"
    all_ids = fetch_json(url)
    return all_ids[:limit]


def get_item(item_id: int) -> Dict:
    """Fetch a single Hacker News item."""
    url = f"{BASE_URL}/item/{item_id}.json"
    return fetch_json(url)


def render_page(title: str, items_html: str, build_time: str) -> str:
    """
    Insert title, items, and the build timestamp into the base template.
    """
    template = TEMPLATE_PATH.read_text(encoding="utf-8")
    rendered = (
        template
        .replace("{{title}}", title)
        .replace("{{items}}", items_html)
        .replace("{{build}}", build_time)
    )
    return rendered


def build_list_item(story: Dict) -> str:
    """Turn a story dict into a single <li> element."""
    url = story.get("url") or f"https://news.ycombinator.com/item?id={story['id']}"
    title = story.get("title", "(no title)")
    score = story.get("score", 0)
    by = story.get("by", "unknown")
    return f'<li><a href="{url}">{title}</a> ({score} points) by {by}</li>'


# ----------------------------------------------------------------------
# Main generation logic
# ----------------------------------------------------------------------
def generate_static_pages():
    # Make sure the top‑level output folder exists
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    # One timestamp for the whole run
    build_timestamp = datetime.utcnow().strftime("%Y-%m-%d %H:%M UTC")

    # Mapping: endpoint → (human‑readable title, sub‑folder name or None for root)
    sections = {
        "topstories": ("Top Stories", None),      # None → write to root index.html
        "beststories": ("Best Stories", "best"),
        "newstories": ("New Stories", "new"),
        "askstories": ("Ask HN", "ask"),
        "showstories": ("Show HN", "show"),
        "jobstories": ("Jobs", "job"),
    }

    for endpoint, (title, subdir) in sections.items():
        print(f"Fetching {title}…")
        ids = get_story_ids(endpoint, limit=10)
        stories = [get_item(i) for i in ids]

        items_html = "\n".join(build_list_item(s) for s in stories)

        page_html = render_page(title, items_html, build_timestamp)

        # Determine where to write the file
        if subdir is None:
            target_path = OUTPUT_DIR / "index.html"
        else:
            target_dir = OUTPUT_DIR / subdir
            target_dir.mkdir(parents=True, exist_ok=True)
            target_path = target_dir / "index.html"

        target_path.write_text(page_html, encoding="utf-8")
        print(f" → wrote {target_path}")

    print("All pages generated (built at", build_timestamp, ")")


if __name__ == "__main__":
    generate_static_pages()