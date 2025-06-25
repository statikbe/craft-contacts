# CLAUDE.md - Craft Contacts Plugin

This file provides guidance to Claude Code when working with the Craft Contacts plugin.

## Plugin Overview

The **craft-contacts** plugin is a CRM-like layer over Craft's user system, providing specialized contact management functionality for Craft CMS 5.7+. It extends Craft's native User element to create a dedicated contact management interface.

## Core Architecture

### Contact Element (`src/elements/Contact.php`)
- **Extends**: `craft\elements\User`
- **Purpose**: Specialized interface for managing contacts as inactive users
- **Key Features**:
  - Custom CP navigation and templates
  - Advanced filtering system integration
  - Bulk actions (copy emails, export)
  - User conversion capabilities
  - Permission-based access control

### Controllers
**ContactsController** (`src/controllers/ContactsController.php`)
- Contact CRUD operations (create, read, update, delete)
- User conversion (contact → active user with activation email)
- XLSX export functionality
- CP screen integration

**FilterController** (`src/controllers/FilterController.php`)
- Filter management interface
- Save/load filter configurations
- Share filter functionality

### Services

**FilterService** (`src/services/FilterService.php`)
- Advanced filtering using Craft's ElementCondition system
- Personal and shared filter management
- Query modification for filtered results
- Pagination and search capabilities

**ExportService** (`src/services/ExportService.php`)
- XLSX export generation using OpenSpout
- Customizable field selection based on visible tabs
- Bulk export functionality
- Temporary file handling and cleanup

## Key Features

### 1. Contact Management
- Create contacts as inactive users
- Edit contact information using Craft's field system
- Configurable field layout inheritance from User element
- Convert contacts to active users with activation emails

### 2. Advanced Filtering System
- **Personal Filters**: User-specific saved filters
- **Shared Filters**: Organization-wide filter sharing
- **ElementCondition Integration**: Uses Craft's native condition system
- **Dynamic Sources**: Filters appear as sources in CP sidebar

### 3. Export Functionality
- XLSX export with customizable fields
- Respects plugin settings for visible tabs
- Handles complex field types (relations, dates, arrays)
- Bulk export via element actions

### 4. Settings Configuration
- **Visible Tabs**: Control which User field layout tabs are shown
- **User Groups**: Define which user groups contacts can be assigned to
- **Default User Group**: Auto-assign new contacts to specific group
- **Contact Title Format**: Customize how contact titles display

## Plugin Configuration

### Settings Model (`src/models/Settings.php`)
```php
public string|null $contactTitleFormat = '{user.email}';
public array $visibleTabs = [];           // Tab UIDs to show
public array $userGroups = [];            // Available user groups
public int|null $defaultUserGroup = null; // Default group for new contacts
```

### Database Tables
- **Contacts**: Extends User table (no additional contact-specific table)
- **Filters**: Custom table for storing filter configurations
  - `id`, `label`, `conditionConfig`, `ownerId`, `shared`, `dateCreated`, `dateUpdated`

## File Structure

```
src/
├── Contacts.php                    # Main plugin class
├── controllers/
│   ├── ContactsController.php      # Contact CRUD operations
│   └── FilterController.php        # Filter management
├── elements/
│   ├── Contact.php                 # Main Contact element
│   ├── actions/
│   │   ├── CopyEmail.php          # Bulk email copy action
│   │   └── ExportXlsx.php         # Bulk export action
│   └── db/
│       └── ContactQuery.php       # Custom query class
├── models/
│   ├── Settings.php               # Plugin settings
│   └── FilterModel.php            # Filter configuration model
├── records/
│   └── FilterRecord.php           # Filter database record
├── services/
│   ├── FilterService.php          # Advanced filtering logic
│   └── ExportService.php          # Export functionality
└── templates/                     # CP templates
    ├── _settings.twig
    ├── contacts/
    │   ├── _detail.twig           # Contact edit form
    │   ├── _index.twig            # Contact listing
    │   ├── _new.twig              # New contact form
    │   └── _sidebar.twig          # Contact edit sidebar
    └── filters/
        ├── _buttons.twig
        ├── _edit.twig             # Filter edit form
        └── _index.twig            # Filter listing
```

## Development Patterns

### Element Extension Pattern
The plugin follows Craft's element extension pattern by:
- Extending `craft\elements\User` for the Contact element
- Overriding display methods (`displayName()`, `pluralDisplayName()`, etc.)
- Implementing custom query class (`ContactQuery`)
- Defining custom sources for CP sidebar

### Service Pattern
Services handle complex business logic:
- **FilterService**: Manages filter CRUD and query application
- **ExportService**: Handles file generation and download responses

### Permission System
Implements custom permissions:
- `viewContacts`: View contact records
- `saveContacts`: Create/edit contacts
- `deleteContacts`: Delete contact records

## Integration Points

### With Craft CMS Core
- **User System**: Extends User element for contact functionality
- **Field Layouts**: Inherits User field layout configuration
- **Permissions**: Integrates with Craft's permission system
- **CP Integration**: Custom CP sections and navigation

### With User Groups
- Contacts can be assigned to user groups
- Default group assignment for new contacts
- Group-based filtering capabilities

## Common Development Tasks

### Adding New Export Formats
1. Extend `ExportService` with new format methods
2. Add format-specific dependencies to `composer.json`
3. Create new element action for the format
4. Update CP templates with export options

### Adding New Filter Types
1. The plugin uses Craft's native `ElementCondition` system
2. Filters automatically support all User element condition rules
3. Custom conditions can be added by extending `UserCondition`

### Customizing Contact Display
1. Modify `contactTitleFormat` in plugin settings
2. Override `Contact::__toString()` for custom string representation
3. Customize CP templates in `templates/contacts/` directory

## Testing Considerations

### Contact Creation
- Verify inactive user creation
- Test user group assignment
- Validate field value preservation

### Filter Functionality
- Test personal vs shared filter access
- Verify condition rule application
- Check pagination and search

### Export Features
- Test various field types in export
- Verify file cleanup after download
- Check large dataset handling

## Performance Notes

- **Filtering**: Uses database-level query modification for efficiency
- **Export**: Streams data to temporary files for memory efficiency
- **Pagination**: Implements proper offset/limit for large contact lists
- **Caching**: Leverages Craft's native element caching system