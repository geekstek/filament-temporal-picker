# Migration Guide

## Migrating from MonthPicker with rangeSelection() to MonthRangePicker

### Background

In earlier versions, `MonthPicker` supported a `rangeSelection()` mode that stored data as a JSON object:

```json
{
  "start": "2024-01",
  "end": "2024-06"
}
```

This design was inconsistent with the single and multiple selection modes, and made database schema design unclear.

### New Architecture

**MonthPicker** now only supports:
- Single selection: `"2024-01"`
- Multiple selection: `["2024-01", "2024-04", "2024-08"]`

**MonthRangePicker** is a new dedicated component for range selection using two separate fields:
- `campaign_start`: `"2024-01"`
- `campaign_end`: `"2024-06"`

---

## Step-by-Step Migration

### 1. Update Your Database Schema

Create a migration to split the single JSON field into two separate fields:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('your_table', function (Blueprint $table) {
            // Add new columns
            $table->string('campaign_start', 7)->nullable()->after('campaign_period');
            $table->string('campaign_end', 7)->nullable()->after('campaign_start');
            
            // Index for queries
            $table->index(['campaign_start', 'campaign_end']);
        });
        
        // Migrate existing data
        DB::table('your_table')
            ->whereNotNull('campaign_period')
            ->get()
            ->each(function ($row) {
                $period = json_decode($row->campaign_period, true);
                
                if (is_array($period) && isset($period['start'], $period['end'])) {
                    DB::table('your_table')
                        ->where('id', $row->id)
                        ->update([
                            'campaign_start' => $period['start'],
                            'campaign_end' => $period['end'],
                        ]);
                }
            });
        
        // Drop old column (optional - keep it temporarily for safety)
        // $table->dropColumn('campaign_period');
    }
    
    public function down(): void
    {
        Schema::table('your_table', function (Blueprint $table) {
            // Restore old column structure
            if (!Schema::hasColumn('your_table', 'campaign_period')) {
                $table->json('campaign_period')->nullable();
            }
            
            // Migrate data back
            DB::table('your_table')
                ->whereNotNull('campaign_start')
                ->orWhereNotNull('campaign_end')
                ->get()
                ->each(function ($row) {
                    DB::table('your_table')
                        ->where('id', $row->id)
                        ->update([
                            'campaign_period' => json_encode([
                                'start' => $row->campaign_start,
                                'end' => $row->campaign_end,
                            ]),
                        ]);
                });
            
            $table->dropColumn(['campaign_start', 'campaign_end']);
        });
    }
};
```

### 2. Update Your Model

**Before:**
```php
class Campaign extends Model
{
    protected $fillable = [
        'name',
        'campaign_period',
    ];
    
    protected function casts(): array
    {
        return [
            'campaign_period' => 'array',
        ];
    }
}
```

**After:**
```php
class Campaign extends Model
{
    protected $fillable = [
        'name',
        'campaign_start',
        'campaign_end',
    ];
    
    protected function casts(): array
    {
        return [
            'campaign_start' => 'string',
            'campaign_end' => 'string',
        ];
    }
    
    // Optional: Add helper accessors
    public function getCampaignPeriodAttribute(): array
    {
        return [
            'start' => $this->campaign_start,
            'end' => $this->campaign_end,
        ];
    }
}
```

### 3. Update Your Filament Resource

**Before:**
```php
use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

MonthPicker::make('campaign_period')
    ->label('Campaign Period')
    ->rangeSelection()
    ->rangeKeys('start', 'end')
    ->minDate('2024-01')
    ->maxDate('2025-12');
```

**After:**
```php
use Geekstek\TemporalPicker\Forms\Components\MonthRangePicker;

MonthRangePicker::make('campaign_period')
    ->label('Campaign Period')
    ->fields('campaign_start', 'campaign_end')
    ->labels('Start Month', 'End Month')  // Optional custom labels
    ->minDate('2024-01')
    ->maxDate('2025-12');
```

### 4. Update Your Table Columns (if used)

**Before:**
```php
use Filament\Tables;

Tables\Columns\TextColumn::make('campaign_period')
    ->label('Campaign Period')
    ->formatStateUsing(fn ($state) => 
        is_array($state) 
            ? ($state['start'] ?? '') . ' → ' . ($state['end'] ?? '')
            : ''
    );
```

**After:**
```php
use Filament\Tables;

Tables\Columns\TextColumn::make('campaign_start')
    ->label('Campaign Period')
    ->formatStateUsing(fn ($record) => 
        $record->campaign_start && $record->campaign_end
            ? "{$record->campaign_start} → {$record->campaign_end}"
            : ($record->campaign_start ?? $record->campaign_end ?? '-')
    );
```

### 5. Update Your Queries

**Before:**
```php
// Query campaigns within a specific period
Campaign::where('campaign_period->start', '>=', '2024-01')
    ->where('campaign_period->end', '<=', '2024-12')
    ->get();
```

**After:**
```php
// Much cleaner queries with separate fields
Campaign::where('campaign_start', '>=', '2024-01')
    ->where('campaign_end', '<=', '2024-12')
    ->get();

// Or find overlapping campaigns
Campaign::where('campaign_start', '<=', '2024-06')
    ->where('campaign_end', '>=', '2024-01')
    ->get();
```

---

## Benefits of the New Approach

### 1. **Clearer Database Schema**
- ✅ Separate columns are easier to understand
- ✅ Can add indexes for better performance
- ✅ Easier to add constraints (e.g., `campaign_end >= campaign_start`)

### 2. **Simpler Queries**
- ✅ No need for JSON operators (`->`)
- ✅ Better query performance
- ✅ More intuitive SQL

### 3. **Better Validation**
- ✅ Database-level constraints possible
- ✅ Built-in validation in the component

### 4. **Easier Data Export**
- ✅ Direct column access in exports
- ✅ No JSON parsing needed

---

## Rollback Plan (Safety)

If you need to rollback temporarily:

1. Keep the old `campaign_period` column during the transition period
2. Use a model accessor to sync data:

```php
class Campaign extends Model
{
    // Keep both representations in sync
    protected static function booted()
    {
        static::saving(function ($model) {
            // Sync separate fields to JSON (for backward compatibility)
            if ($model->campaign_start && $model->campaign_end) {
                $model->campaign_period = [
                    'start' => $model->campaign_start,
                    'end' => $model->campaign_end,
                ];
            }
        });
    }
}
```

---

## Need Help?

If you encounter any issues during migration, please:
1. Check the [README.md](README.md) for complete examples
2. Review the [CHANGELOG.md](CHANGELOG.md) for all breaking changes
3. Open an issue on GitHub with your specific use case
