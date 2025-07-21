#!/usr/bin/env python3
"""
Update plugin version in wp-smart-slug.php
"""
import re
import sys


def update_version(file_path, new_version):
    """Update version in plugin file."""
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Update Plugin Header Version
    content = re.sub(
        r'^ \* Version:.*$',
        f' * Version:           {new_version}',
        content,
        flags=re.MULTILINE
    )
    
    # Update WP_SMART_SLUG_VERSION constant
    content = re.sub(
        r"define\('WP_SMART_SLUG_VERSION', '[^']*'\);",
        f"define('WP_SMART_SLUG_VERSION', '{new_version}');",
        content
    )
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"Updated version to {new_version}")
    
    # Verify changes
    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    for i, line in enumerate(lines, 1):
        if 'Version:' in line or 'WP_SMART_SLUG_VERSION' in line:
            print(f"Line {i}: {line.strip()}")


if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("Usage: python update-version.py <file_path> <version>")
        sys.exit(1)
    
    file_path = sys.argv[1]
    version = sys.argv[2]
    
    update_version(file_path, version)