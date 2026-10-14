# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

```bash
# Install dependencies
npm install

# Development mode with hot reload
npm run dev

# Production build
npm run build

# Bundle analysis
npm run analysis

# Create distribution package
npm run zip    # Unix/Linux/Mac
npm run 7zip   # Windows
```

## Architecture Overview

Media Cleaner Pro is a WordPress plugin that identifies and removes unused media files. It uses a three-step process:

1. **Parse** - Scan WordPress content via parsers to find media references (stored in `mclean_refs` table)
2. **List** - Create inventory of all media entries or files
3. **Match** - Compare references against inventory to identify unused items (stored in `mclean_scan` table)

### Key Directories

- `classes/` - Core PHP functionality
  - `core.php` - Main scanning engine and issue management
  - `engine.php` - Reference collection and matching logic
  - `parsers.php` - Parser loader and registration
  - `rest.php` - REST API endpoints for dashboard
- `premium/` - Pro features (50+ parser integrations, CLI, filesystem scan)
- `app/` - React dashboard frontend
- `common/` - Shared Meow Apps components

### Parser System

Parsers detect media usage in plugins/themes:
- Hook into actions: `wpmc_scan_once`, `wpmc_scan_post`, `wpmc_scan_postmeta`
- Use `add_reference_id()` or `add_reference_url()` to register found media
- Free version: Basic parsers in `classes/parsers/`
- Pro version: Extended parsers in `premium/parsers/`

### Frontend Stack

- React with WordPress's built-in React
- Context API for state management
- SWR/React Query for data fetching
- Styled Components for styling
- Webpack bundling with code splitting

## WP-CLI Commands

```bash
# Basic scan (uses settings from UI)
wp media-cleaner scan

# Scan media library
wp media-cleaner scan library

# Scan filesystem
wp media-cleaner scan filesystem

# Override options
wp media-cleaner scan library check-content uncheck-media
```

## Adding Plugin/Theme Support

1. Create parser in `premium/parsers/[plugin_name].php`
2. Add detection logic to `classes/support.php` for free version warning
3. Hook to appropriate actions and use reference functions:
   - `add_reference_id($id, $type, $origin, $extra)`
   - `add_reference_url($url, $type, $origin, $extra)`

## Testing and Deployment

No automated test suite - manual testing required. Before committing changes:
- Test scanner functionality with various content types
- Verify parser compatibility
- Check React dashboard functionality
- Test both Media Library and Filesystem scan methods

Build for production: `npm run build && npm run zip`