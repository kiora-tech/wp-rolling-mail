# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-10-29

### Added

#### Phase 3 - Dynamic Thematic Routing
- **Dynamic field selection**: Choose any radio/select field for thematic routing
- **Automatic value detection**: System detects all possible values from the selected field
- **Value normalization**: Automatically groups similar values (e.g., "Prévoyance" and "Type : Prévoyance")
- **Email mapping per theme**: Configure different email lists for each thematic value
- **Fallback mechanism**: Automatically uses main rotation list if thematic list is empty
- **Auto-refresh interface**: Page reloads automatically when changing thematic field

#### Phase 5 - UX Improvements & Robustness
- **5.1 Enhanced UX**:
  - 12 detailed help messages throughout admin interface
  - 15 informative notices with context
  - 23 emojis for visual clarity
- **5.2 Debug Logging**:
  - 33 strategically placed debug logs with [FSS] prefix
  - Complete audit trail of email sending process
  - Detailed error messages for troubleshooting
- **5.3 Edge Case Handling**:
  - Case 1: Invalid field ID detection
  - Case 2: Empty field value handling
  - Case 3: No email addresses configured (critical error)
  - Case 4: Empty thematic list (fallback to main)
  - Case 5: Invalid email filtering
  - Case 6: Missing form in filter list
  - Case 7: wp_mail() failure handling
  - Case 8: Inconsistent thematic configuration
- **5.4 Documentation**:
  - Complete user documentation (160KB)
  - Installation guide
  - Configuration examples
  - Troubleshooting guide

#### Phase 6 - Automated Testing
- 10 automated tests created
- 90% success rate (9/10 tests passing)
- Coverage: rotation, CC, BCC, thematic routing, edge cases

#### Bonus Features
- **BCC Support**: Blind carbon copy for discreet email archiving
- **Custom Sender Name**: Changed from "WordPress Dev" to "Contact" in production
- **Form Filtering**: Select which forms use rotation (all/include/exclude modes)

#### Migration & Compatibility
- Automatic migration from Phase 3 v1 to v2 format
- Migration detection and execution on first load
- Backward compatibility maintained

### Fixed

#### Critical Fixes
- **Duplicate email prevention**: Uses WordPress transients to detect and block duplicate hook calls
  - Protection expires after 1 hour
  - Stores metadata: timestamp, recipient, CC/BCC counts
  - Prevents split leads across multiple commercials
- **Checkbox value display**: Array values now properly converted to comma-separated strings
  - Before: Case à cocher: Array
  - After: Case à cocher: Option1, Option2, Option3

#### UI/UX Fixes
- Thematic field dropdown now only shows single-choice fields (radio, select)
- Removed checkbox fields from thematic selection
- Auto-refresh triggers properly on field change (800ms delay)
- "Add email" buttons for thematic sections now functional
- Proper event delegation for dynamically added elements

### Changed

#### Data Structure
- Email lists stored in new dynamic structure
- Thematic keys normalized (lowercase, no accents, underscores)

#### Admin Interface
- Enhanced sections with detailed descriptions
- Visual indicators (emojis) for different email types
- Field selector with form name and type displayed
- Real-time validation feedback

#### Email Handler
- Rotation logic now supports both main and thematic lists
- CC/BCC headers properly formatted
- Message body builder handles array values
- Comprehensive logging at each step

### Security

- All email addresses validated with WordPress is_email() function
- Invalid emails filtered out and logged
- SQL queries use wpdb->prepare() for injection prevention
- Nonce verification for admin form submissions
- Permission checks (manage_options) for sensitive operations

### Performance

- Field values cached with WordPress transients (1 hour TTL)
- Reduced database queries through intelligent caching
- Array re-indexing with array_values() after filtering

### Developer

- 33 debug logs with consistent [FSS] prefix
- Clear separation between admin and email handler logic
- DocBlock comments for all major functions
- Edge case handling documented in code
- Migration system for version upgrades

## [1.0.0] - 2024-04-03

### Added
- Initial release
- Sequential email rotation (round-robin)
- Basic email list configuration
- CC (carbon copy) support
- Formidable Forms integration via frm_after_create_entry hook
- Email subject customization
- WordPress Settings API integration
- Admin interface for configuration
- Internationalization ready (i18n)

### Features
- Automatic rotation using array_shift() and array_push()
- Email address validation
- Settings saved in WordPress options
- Admin menu under "Sequential Submissions"
- Dynamic email field management (add/remove)

---

## Future Releases

See GitHub Issues for planned enhancements:
- [#2] - Centralized TTL constant for duplicate protection
- [#3] - Hook for customizing checkbox value separator
- [#4] - Dashboard with email statistics and analytics
- [#5] - WordPress Transient Notices for auto-refresh
- [#6] - Externalize inline JavaScript to separate files
- [#7] - Manual cache invalidation button
- [#8] - Migration logging enhancement

---

## Version History

- **1.1.0** (2025-10-29) - Dynamic thematic routing + critical fixes
- **1.0.0** (2024-04-03) - Initial release

[1.1.0]: https://github.com/kiora-tech/wp-rolling-mail/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/kiora-tech/wp-rolling-mail/releases/tag/v1.0.0
