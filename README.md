# Contacts

Adds a crm-like layers over Craft's users

## Requirements

This plugin requires Craft CMS 5.7.0 or later, and PHP 8.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project's Control Panel and search for "Contacts". Then press "Install".

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require statikbe/craft-contacts

# tell Craft to install the plugin
./craft plugin/install contacts
```

## Features

- **Contact Management**: Create and manage contacts as inactive users
- **Advanced Filtering**: Save personal and shared filters for quick access
- **Export Functionality**: Export contacts to XLSX format
- **User Conversion**: Convert contacts to active users with activation emails
- **Field Layout Customization**: Control which User fields are visible for contacts
- **Relations Table Template**: Display related data in clean, formatted tables

## Displaying Related Data

The plugin includes a flexible relations table template for displaying related data on contact detail pages.

### Relations Table Template

Include the `_relations-table.twig` template to display any related data in a formatted table:

```twig
{% set columns = [
    {label: 'Title', key: 'title', link: true},
    {label: 'Status', key: 'status'},
    {label: 'Created', key: 'dateCreated', type: 'date'}
] %}

{% include 'contacts/_relations-table' with {
    title: 'Related Entries',
    columns: columns,
    rows: relatedEntries,
    emptyMessage: 'No related entries found.'
} %}
```

### Column Configuration

Each column supports the following properties:

- **`label`** (string): Column header label
- **`key`** (string): Property key to access in row data
- **`link`** (boolean): Whether to link to element's CP edit URL (default: `false`)
- **`type`** (string): Format type for cell value (default: `'text'`)

### Supported Column Types

- **`text`**: Renders value as-is (supports HTML)
- **`date`**: Formats date using `|date('short')`
- **`datetime`**: Formats datetime using `|datetime('short')`
- **`timestamp`**: Formats timestamp using `|timestamp`
- **`relation`**: Resolves relation field and displays title (automatically linked)
- **`dropdown`**: Displays dropdown field label instead of value

### Example: Display Registrations

```twig
{# Get related registrations for this contact #}
{% set registrations = craft.entries()
    .section('registrations')
    .relatedTo(element)
    .all() %}

{% set columns = [
    {label: 'Registration', key: 'title', link: true},
    {label: 'Course', key: 'course', type: 'relation'},
    {label: 'Status', key: 'registrationStatus', type: 'dropdown'},
    {label: 'Date', key: 'dateCreated', type: 'date'}
] %}

{% include 'contacts/_relations-table' with {
    title: 'Registrations',
    columns: columns,
    rows: registrations
} %}
```

This will display:
- **Registration**: Clickable title linking to the registration entry
- **Course**: Related course title, linking to the course entry
- **Status**: Human-readable dropdown label (e.g., "Pending Approval")
- **Date**: Formatted date (e.g., "Jan 15, 2024")

All links open in a new tab to preserve the contact detail page.
