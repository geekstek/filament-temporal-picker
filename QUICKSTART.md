# Quick Start Guide

## 安装

```bash
composer require geekstek/filament-temporal-picker
```

## 基础使用

### 1. 单个月份选择

最简单的用法 - 选择一个月份：

```php
use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

MonthPicker::make('billing_month')
    ->label('Billing Month')
    ->default('2024-01');
```

**数据库字段**：
```php
$table->string('billing_month', 7)->nullable();
```

**Model Cast**：
```php
protected function casts(): array
{
    return [
        'billing_month' => 'string',
    ];
}
```

---

### 2. 多个月份选择

需要选择多个月份时：

```php
MonthPicker::make('available_months')
    ->label('Available Months')
    ->multiple()
    ->default(['2024-01', '2024-06', '2024-12'])
    ->minSelections(1)
    ->maxSelections(12);
```

**数据库字段**：
```php
$table->json('available_months')->nullable();
```

**Model Cast**：
```php
protected function casts(): array
{
    return [
        'available_months' => 'array',  // 自动 JSON 编解码
    ];
}
```

---

### 3. 月份范围选择

需要选择开始和结束月份时：

```php
use Geekstek\TemporalPicker\Forms\Components\MonthRangePicker;

MonthRangePicker::make('campaign_period')
    ->label('Campaign Period')
    ->fields('campaign_start', 'campaign_end')
    ->minDate('2024-01')
    ->maxDate('2025-12');
```

**数据库字段**：
```php
$table->string('campaign_start', 7)->nullable();
$table->string('campaign_end', 7)->nullable();
$table->index(['campaign_start', 'campaign_end']);  // 推荐添加索引
```

**Model Cast**：
```php
protected function casts(): array
{
    return [
        'campaign_start' => 'string',
        'campaign_end' => 'string',
    ];
}
```

---

## 常用配置选项

### 限制日期范围

```php
MonthPicker::make('month')
    ->minDate('2023-01')  // 最早可选
    ->maxDate('2025-12'); // 最晚可选
```

### 自定义格式

```php
MonthPicker::make('month')
    ->format('Y-m')  // 存储格式
    ->displayFormat('Y年m月');  // 显示格式（可选）
```

### 禁用特定选项

```php
MonthPicker::make('month')
    ->disabledOptions(['2024-02', '2024-08']);  // 禁用这些月份
```

### 国际化

```php
MonthPicker::make('month')
    ->locale('zh_CN');  // 使用中文
```

---

## 完整示例

### Filament Resource Form

```php
use Filament\Forms;
use Geekstek\TemporalPicker\Forms\Components\{MonthPicker, MonthRangePicker};

public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Section::make('基本信息')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('活动名称')
                    ->required(),
                    
                MonthPicker::make('billing_month')
                    ->label('账单月份')
                    ->default(now()->format('Y-m')),
            ]),
            
        Forms\Components\Section::make('活动周期')
            ->schema([
                MonthRangePicker::make('campaign_period')
                    ->label('活动周期')
                    ->fields('campaign_start', 'campaign_end')
                    ->labels('开始月份', '结束月份')
                    ->minDate(now()->format('Y-m'))
                    ->required(),
            ]),
            
        Forms\Components\Section::make('可用月份')
            ->schema([
                MonthPicker::make('available_months')
                    ->label('可用月份')
                    ->multiple()
                    ->minSelections(2)
                    ->maxSelections(6)
                    ->helperText('至少选择2个月，最多6个月'),
            ]),
    ]);
}
```

### Migration

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // 单选月份
            $table->string('billing_month', 7)->nullable();
            
            // 范围选择（两个独立字段）
            $table->string('campaign_start', 7)->nullable();
            $table->string('campaign_end', 7)->nullable();
            
            // 多选月份（JSON）
            $table->json('available_months')->nullable();
            
            // 添加索引以提升查询性能
            $table->index('billing_month');
            $table->index(['campaign_start', 'campaign_end']);
            
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
```

### Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'billing_month',
        'campaign_start',
        'campaign_end',
        'available_months',
    ];
    
    protected function casts(): array
    {
        return [
            'billing_month' => 'string',
            'campaign_start' => 'string',
            'campaign_end' => 'string',
            'available_months' => 'array',  // 自动 JSON 编解码
        ];
    }
    
    // 可选：添加辅助方法
    public function isActiveInMonth(string $month): bool
    {
        return $month >= $this->campaign_start 
            && $month <= $this->campaign_end;
    }
    
    public function hasAvailableMonth(string $month): bool
    {
        return in_array($month, $this->available_months ?? []);
    }
}
```

---

## 查询示例

### 单选字段查询

```php
// 查找特定月份的记录
$campaigns = Campaign::where('billing_month', '2024-01')->get();

// 查找某个月份之后的记录
$campaigns = Campaign::where('billing_month', '>=', '2024-01')->get();
```

### 范围字段查询

```php
// 查找在特定月份活动的 campaigns
$campaigns = Campaign::where('campaign_start', '<=', '2024-06')
    ->where('campaign_end', '>=', '2024-06')
    ->get();

// 查找与指定范围重叠的 campaigns
$campaigns = Campaign::where(function ($query) {
    $query->whereBetween('campaign_start', ['2024-01', '2024-12'])
        ->orWhereBetween('campaign_end', ['2024-01', '2024-12'])
        ->orWhere(function ($q) {
            $q->where('campaign_start', '<=', '2024-01')
              ->where('campaign_end', '>=', '2024-12');
        });
})->get();
```

### 多选字段查询（JSON）

```php
// 查找包含特定月份的记录
$campaigns = Campaign::whereJsonContains('available_months', '2024-01')->get();

// 查找包含任一月份的记录
$campaigns = Campaign::where(function ($query) {
    foreach (['2024-01', '2024-06'] as $month) {
        $query->orWhereJsonContains('available_months', $month);
    }
})->get();
```

---

## 常见问题

### Q: 为什么范围选择要用两个字段而不是 JSON？

**A:** 
1. **查询性能更好** - 可以直接添加索引
2. **SQL 更简洁** - 不需要使用 JSON 操作符
3. **数据库无关性** - 所有数据库都支持
4. **更清晰的数据模型** - 一目了然

### Q: 多选模式下，数据如何存储？

**A:** 
多选模式下，数据以 JSON 数组形式存储。使用 Laravel 的 `array` cast，会自动处理 JSON 的编码/解码：

```php
// 存储时（自动编码）
$model->available_months = ['2024-01', '2024-06'];
// 数据库: ["2024-01", "2024-06"]

// 读取时（自动解码）
$months = $model->available_months;
// PHP: ['2024-01', '2024-06']
```

### Q: 如何验证范围选择？

**A:**
在 Model 中添加验证规则：

```php
public static function boot()
{
    parent::boot();
    
    static::saving(function ($model) {
        if ($model->campaign_start && $model->campaign_end) {
            if ($model->campaign_start > $model->campaign_end) {
                throw new \Exception('Start date cannot be after end date');
            }
        }
    });
}
```

---

## 下一步

- 查看 [README.md](README.md) 了解所有配置选项
- 查看 [MIGRATION.md](MIGRATION.md) 了解从旧版本迁移
- 查看 [CHANGELOG.md](CHANGELOG.md) 了解版本变更

---

## 支持

遇到问题？
- 📖 查看完整文档
- 🐛 [提交 Issue](https://github.com/your-repo/issues)
- 💬 [讨论区](https://github.com/your-repo/discussions)
