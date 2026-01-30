# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Statamic 6.x compatibility
- "All" checkbox options for each MIME type category (Images, Documents, Archives, 3D Models, Videos)
- Support for custom category wildcards (`document/*`, `archive/*`) that map to their actual MIME types

## [1.0.0] - 2025-01-28

### Added
- **Control Panel Interface**: Full settings page accessible via CP → Tools → MIME Guard
- Visual interface for managing global MIME type restrictions with checkboxes
- "Toggle all" checkbox for quick selection/deselection of all MIME types
- Per-container rule configuration (allow/deny) directly from CP
- Per-blueprint rule configuration (allow/deny) directly from CP
- Collapsible cards for container and blueprint rules to save space
- "Configured" badge indicator for rules that have been set
- Quick link to create new asset containers from the settings page
- Settings persistence via YAML file (`storage/statamic/addons/mime-guard/settings.yaml`)
- Permission `configure mime-guard` to control access to settings
- French and English translations for all CP interface elements

### Changed
- Settings now saved to YAML instead of requiring config file modifications
- Improved UI with Statamic native components

## [0.2.0] - 2025-01-28

### Added
- Container-level rules: allow/deny MIME types per asset container
- Blueprint-level rules: allow/deny MIME types per blueprint
- Field-level rules via blueprint YAML configuration
- Wildcard support for MIME types (e.g., `image/*`, `video/*`)
- `inherit: false` option to ignore parent rules
- `application/octet-stream` added to default restrictions (catches ZIP, STL, and other binary files)

### Changed
- Improved rule resolution with clear hierarchy: Global → Container → Blueprint → Field
- Cleaner logging (removed debug logs, kept INFO for rejections)
- Refactored listener code for better maintainability

### Fixed
- File deletion now works correctly when uploads are rejected
- HttpResponseException properly stops the upload process

## [0.1.0] - 2025-01-28

### Added
- Initial release
- Global MIME type restriction via configuration
- Server-side validation using magic bytes (finfo)
- Client-side file picker filtering (JavaScript)
- Logging for rejected uploads with user, container, and MIME type info
- French and English translations
- Default restrictions for common risky file types:
  - Archives (zip, rar, 7z, tar, gzip)
  - 3D models (stl, gltf, glb)
  - Vector images (svg)
  - Executable documents (pdf)
  - Videos (mp4, webm, mov)
