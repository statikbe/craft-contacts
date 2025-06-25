<?php
/**
 * Contacts Translation (English)
 *
 * @author    Statik.be
 * @package   Contacts
 * @since     1.0.0
 */

return [
    // ==============================================
    // PLUGIN CORE & NAVIGATION
    // ==============================================
    
    'Contacts' => 'Contacts',
    'Contact' => 'Contact',
    'contact' => 'contact',
    'contacts' => 'contacts',
    'New Contact' => 'New Contact',
    'All contacts' => 'All contacts',
    
    // ==============================================
    // CONTACT MANAGEMENT
    // ==============================================
    
    // Contact creation and editing
    'Email Address' => 'Email Address',
    'Enter the contact\'s email address' => 'Enter the contact\'s email address',
    'Full Name' => 'Full Name',
    'Enter the contact\'s full name' => 'Enter the contact\'s full name',
    'Create Contact' => 'Create Contact',
    
    // Contact status and conversion
    'User Status' => 'User Status',
    'Convert to User' => 'Convert to User',
    'Are you sure you want to convert this contact to a user? They will be assigned to the default user group and sent an activation email with login instructions.' => 'Are you sure you want to convert this contact to a user? They will be assigned to the default user group and sent an activation email with login instructions.',
    'Converting...' => 'Converting...',
    
    // ==============================================
    // SUCCESS MESSAGES
    // ==============================================
    
    'Contact created successfully.' => 'Contact created successfully.',
    'Contact successfully converted. Activation email sent.' => 'Contact successfully converted. Activation email sent.',
    'Contact successfully converted. An activation email has been sent to {email}.' => 'Contact successfully converted. An activation email has been sent to {email}.',
    'Filter saved.' => 'Filter saved.',
    '{count} filter(s) deleted.' => '{count} filter(s) deleted.',
    '"{name}" deleted.' => '"{name}" deleted.',
    
    // ==============================================
    // ERROR MESSAGES
    // ==============================================
    
    // Contact errors
    'Contact not found.' => 'Contact not found.',
    'Could not create contact.' => 'Could not create contact.',
    'Contact ID is required.' => 'Contact ID is required.',
    'This contact is already an active user.' => 'This contact is already an active user.',
    'An error occurred while converting the contact.' => 'An error occurred while converting the contact.',
    'An error occurred while converting the contact: {error}' => 'An error occurred while converting the contact: {error}',
    
    // Validation errors
    'Email and full name are required.' => 'Email and full name are required.',
    'A contact with this email address already exists.' => 'A contact with this email address already exists.',
    'Could not prepare user for activation: {errors}' => 'Could not prepare user for activation: {errors}',
    'User was prepared for activation but the activation email could not be sent. Check your email settings.' => 'User was prepared for activation but the activation email could not be sent. Check your email settings.',
    
    // Filter errors
    'Couldn\'t save filter.' => 'Couldn\'t save filter.',
    'Couldn\'t delete "{name}".' => 'Couldn\'t delete "{name}".',
    
    // ==============================================
    // FILTERING SYSTEM
    // ==============================================
    
    // Filter sections and navigation
    'Filters' => 'Filters',
    'Contact Filters' => 'Contact Filters',
    'My Filters' => 'My Filters',
    'Shared Filters' => 'Shared Filters',
    'Filter' => 'Filter',
    'New filter' => 'New filter',
    'Create a new filter' => 'Create a new filter',
    
    // Filter form labels
    'Filter Name' => 'Filter Name',
    'Give your filter a descriptive name' => 'Give your filter a descriptive name',
    'Shared' => 'Shared',
    'Allow other users to see and use this filter' => 'Allow other users to see and use this filter',
    'Filter Conditions' => 'Filter Conditions',
    'Define the conditions that contacts must meet to be included in this filter.' => 'Define the conditions that contacts must meet to be included in this filter.',
    'Create Filter' => 'Create Filter',
    'Save Filter' => 'Save Filter',
    
    // Filter states and search
    'Search filters…' => 'Search filters…',
    'No filters exist yet.' => 'No filters exist yet.',
    'No filters found.' => 'No filters found.',
    'Private' => 'Private',
    'Owner' => 'Owner',
    'System' => 'System',
    
    // Filter condition summaries
    'No conditions' => 'No conditions',
    '1 condition' => '1 condition',
    '{count} conditions' => '{count} conditions',
    
    // Filter confirmation messages
    'Are you sure you want to delete "{name}"?' => 'Are you sure you want to delete "{name}"?',
    
    // ==============================================
    // BULK ACTIONS & EXPORT
    // ==============================================
    
    // Email actions
    'Copy Email' => 'Copy Email',
    'Copy Email Address' => 'Copy Email Address',
    'Copy Email Addresses ({count})' => 'Copy Email Addresses ({count})',
    
    // Export actions
    'Export to Excel' => 'Export to Excel',
    
    // ==============================================
    // PLUGIN SETTINGS
    // ==============================================
    
    // Settings form labels
    'User groups' => 'User groups',
    'Default user group for new contacts' => 'Default user group for new contacts',
    'New contacts will be automatically assigned to this user group.' => 'New contacts will be automatically assigned to this user group.',
    'None' => 'None',
    'Tabs' => 'Tabs',
    'Select which tabs should be visible in the contact detail view.' => 'Select which tabs should be visible in the contact detail view.',
    'Main content template' => 'Main content template',
    'Sidebar template' => 'Sidebar template',
    'Contact title format' => 'Contact title format',
    'This format is used to display the title of the contact\'s detail page.' => 'This format is used to display the title of the contact\'s detail page.',
];