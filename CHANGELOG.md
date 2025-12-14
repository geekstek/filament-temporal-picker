# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Breaking Changes

#### MonthPicker Architecture Redesign

**Problem Identified:**
The original design had a critical flaw where `MonthPicker` mixed three different data storage patterns:
1. Single selection → stored as string
2. Multiple selection (`multiple()`) → should store as JSON array
3. Range selection (`rangeSelection()`) → stored as object with `start` and `end` keys

This inconsistency made data modeling unclear and violated the principle of single responsibility.

**Solution Implemented:**

1. **MonthPicker** (Refactored)
   - Now supports ONLY single and multiple selection modes
   - **Single mode**: Stores as string `"2024-01"`
   - **Multiple mode**: Stores as JSON array `["2024-01", "2024-04", "2024-08"]`
   - Removed `rangeSelection()` method
   - Removed range-related UI logic from view template

2. **MonthRangePicker** (New Component)
   - Dedicated component for month range selection
   - Uses **two separate database fields** (e.g., `campaign_start` and `campaign_end`)
   - Provides clear separation of concerns
   - Includes built-in validation (start cannot be after end, and vice versa)

**Migration Guide:**

If you were using `MonthPicker` with `rangeSelection()`:

```php
// OLD (deprecated)
MonthPicker::make('campaign_period')
    ->rangeSelection()
    ->rangeKeys('start', 'end');

// NEW (recommended)
MonthRangePicker::make('campaign_period')
    ->fields('campaign_start', 'campaign_end')
    ->labels('Start Month', 'End Month');
```

**Database Schema Changes:**

```php
// OLD - single field with JSON
Schema::table('campaigns', function (Blueprint $table) {
    $table->json('campaign_period')->nullable();
    // Stored as: {"start": "2024-01", "end": "2024-06"}
});

// NEW - two separate fields (clearer and more flexible)
Schema::table('campaigns', function (Blueprint $table) {
    $table->string('campaign_start', 7)->nullable();
    $table->string('campaign_end', 7)->nullable();
    // Stored as: campaign_start = "2024-01", campaign_end = "2024-06"
});
```

**Benefits:**
- ✅ Clear data storage patterns for each selection mode
- ✅ Easier to query and index in database
- ✅ Better separation of concerns
- ✅ More intuitive for developers
- ✅ Follows Laravel/Filament best practices

### Added
- New `MonthRangePicker` component for month range selection with separate fields
- Comprehensive database schema recommendations in README
- Complete usage examples for all picker types
- Validation rules for multiple month selection

### Changed
- `MonthPicker` now only supports single and multiple selection (range removed)
- Updated documentation with clear data storage format table
- Improved translation key references for consistency

### Removed
- `rangeSelection()` method from `MonthPicker`
- Range-related UI elements from month-picker.blade.php

## [1.0.0] - Previous Release

- Initial release with Year, Month, Week, Weekday, and Day of Month pickers
- Multi-select and range selection support
- Localization support
