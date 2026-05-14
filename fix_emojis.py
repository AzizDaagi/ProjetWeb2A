#!/usr/bin/env python
# -*- coding: utf-8 -*-

with open('view/layouts/admin_nav.php', 'r', encoding='utf-8') as f:
    content = f.read()

replacements = [
    ('<span>ðŸŎ</span>', '<i class="fa-solid fa-bowl-food"></i>'),
    ('<span>ðŸ"–</span>', '<i class="fa-solid fa-book-open"></i>'),
    ('<span>ðŸ</span>', '<i class="fa-solid fa-basket-shopping"></i>'),
    ('<span>ðŸ'™</span>', '<i class="fa-solid fa-star"></i>'),
    ('<span>ðŸŎ¯</span>', '<i class="fa-solid fa-bullseye"></i>'),
    ('<span>ðŸ'¬</span>', '<i class="fa-solid fa-comments"></i>'),
    ('<span>ðŸš©</span>', '<i class="fa-solid fa-flag"></i>'),
    ('<span>ðŸ"°</span>', '<i class="fa-solid fa-newspaper"></i>'),
]

for old, new in replacements:
    content = content.replace(old, new)

with open('view/layouts/admin_nav.php', 'w', encoding='utf-8') as f:
    f.write(content)

print('Fixed all emoji spans')
