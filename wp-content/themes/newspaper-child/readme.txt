
Newspaper Child Theme (TagDiv) — fixed API
==========================================

- Uses `Template: Newspaper` (case-sensitive)
- Registers custom block via `td_api_block::add` (correct API)
- Hooked on `init` with priority 20 to ensure API is loaded

Install:
1) Upload 'newspaper-child-templateNewspaper-fixed.zip' via Appearance → Themes → Add New → Upload Theme.
2) Activate "Newspaper Child" (parent "Newspaper" must be installed, folder exactly 'Newspaper').
3) In TagDiv Composer, insert "NNS Custom Filter Block".
