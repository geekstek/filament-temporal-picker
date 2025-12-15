这是一份针对 Filament v4 的 **Geekstek Temporal Picker** 插件开发指南。这份指南将帮助你构建一套既符合 Filament 设计规范（Tailwind 4），又具备高性能（Alpine.js + Flatpickr）的日期时间选择组件库。

---

# 📅 Geekstek Temporal Picker 开发指南

## 1. 项目架构与策略

为了保证最佳体验和维护性，我们将组件分为两类技术实现：

| 组件类型 | 组件名称 | 推荐技术栈 | 原因 |
| :--- | :--- | :--- | :--- |
| **标准日期流** | `MonthPicker`, `WeekPicker`, `YearPicker` | **Flatpickr** (JS) | 需要复杂的日历计算、年份滚动，且需保持与 Filament 原生 UI 一致。 |
| **离散选择流** | `DayOfMonthPicker`, `WeekdayPicker` | **Alpine.js + Tailwind** | 选项固定（1-31 或 周一-周日），用原生 Grid/Flex 布局更轻量，多选逻辑更容易控制。 |

---

## 2. 环境初始化

首先创建插件骨架（如果你还没创建）：

```bash
php artisan make:filament-plugin geekstek/temporal-picker
```

### 2.1 安装 NPM 依赖
我们需要 Flatpickr 及其插件。

```bash
npm install flatpickr --save-dev
```

### 2.2 目录结构规划
```text
src/
  Forms/
    Components/
      MonthPicker.php      (Flatpickr)
      DayOfMonthPicker.php (Alpine)
      WeekPicker.php       (Flatpickr)
      ...
resources/
  js/
    index.js              (打包入口)
  views/
    month-picker.blade.php
    day-of-month-picker.blade.php
    ...
```

---

## 3. 核心功能实现：MonthPicker (基于 Flatpickr)

这是最复杂的组件之一，实现了年/月选择。

### 3.1 PHP 类定义 (`src/Forms/Components/MonthPicker.php`)

继承 `Field` 并引入 `HasPlaceholder` 等特性，即可自动获得 `required`, `live`, `hint`, `disabled` 等能力。

```php
namespace Geekstek\TemporalPicker\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns;
use Closure;

class MonthPicker extends Field
{
    use Concerns\HasPlaceholder;

    protected string $view = 'geekstek-temporal-picker::month-picker';

    protected bool | Closure $isMultiple = false;
    
    // 默认存储格式 (Y-m)
    protected string | Closure | null $format = 'Y-m'; 

    public function multiple(bool | Closure $condition = true): static
    {
        $this->isMultiple = $condition;
        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }
    
    // 省略 minDate, maxDate 的 Setter/Getter，逻辑同上
}
```

### 3.2 JS 逻辑 (`resources/js/index.js`)

这里我们将编写一个通用的 Alpine 组件来包装 Flatpickr。

```javascript
import flatpickr from 'flatpickr';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect';
import 'flatpickr/dist/plugins/monthSelect/style.css';
import 'flatpickr/dist/flatpickr.css'; // 基础样式

// 注册 Alpine 组件
document.addEventListener('alpine:init', () => {
    Alpine.data('temporalMonthPicker', ({
        state,      // Livewire 绑定的状态
        multiple,   // 是否多选
        format,     // 格式
        minDate,
        maxDate,
    }) => ({
        instance: null,
        state, 
        
        init() {
            const config = {
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true, 
                        dateFormat: format, 
                        altFormat: format, 
                    })
                ],
                mode: multiple ? 'multiple' : 'single',
                defaultDate: this.state,
                minDate: minDate,
                maxDate: maxDate,
                disableMobile: true, // 强制使用 Flatpickr UI
                onChange: (selectedDates, dateStr) => {
                    // 更新 Livewire 状态
                    this.state = dateStr; 
                }
            };

            this.instance = flatpickr(this.$refs.input, config);

            // 监听外部状态变化（如 Livewire reset）
            this.$watch('state', (value) => {
                if (this.instance && value !== this.instance.input.value) {
                    this.instance.setDate(value, false);
                }
            });
        },

        destroy() {
            this.instance?.destroy();
        }
    }));
});
```

### 3.3 Blade 视图 (`resources/views/month-picker.blade.php`)

关键点是使用 `x-data` 绑定刚才定义的 `temporalMonthPicker`，并使用 `$entangle` 同步数据。

```blade
@php
    $isDisabled = $isDisabled();
    $isMultiple = $isMultiple();
    // 构造传给 JS 的配置对象
    $alpineConfig = [
        'state' => $entangle($getStatePath()),
        'multiple' => $isMultiple,
        'format' => $getFormat() ?? 'Y-m',
        'minDate' => $getMinDate(), // 需在 PHP 类实现
        'maxDate' => $getMaxDate(), // 需在 PHP 类实现
    ];
@endphp

<x-filament-forms::field-wrapper
    :id="$getId()"
    :label="$getLabel()"
    :state-path="$getStatePath()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :required="$isRequired()"
>
    <div
        x-ignore
        ax-load
        x-data="temporalMonthPicker(@js($alpineConfig))"
        class="relative"
    >
        <x-filament::input.wrapper :disabled="$isDisabled">
            <input
                x-ref="input"
                type="text"
                id="{{ $getId() }}"
                placeholder="{{ $getPlaceholder() }}"
                {{ 
                    $getExtraInputAttributeBag()->merge([
                        'disabled' => $isDisabled,
                        'readonly' => 'readonly',
                        'class' => 'block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 dark:text-white sm:text-sm sm:leading-6 focus:ring-0',
                    ], escape: false) 
                }}
            />
        </x-filament::input.wrapper>
    </div>
</x-filament-forms::field-wrapper>
```

---

## 4. 核心功能实现：DayOfMonthPicker (基于 Alpine + Grid)

这个组件不需要 Flatpickr，我们用 Tailwind Grid 手写一个。

### 4.1 PHP 类定义 (`src/Forms/Components/DayOfMonthPicker.php`)

```php
namespace Geekstek\TemporalPicker\Forms\Components;

use Filament\Forms\Components\Field;

class DayOfMonthPicker extends Field
{
    protected string $view = 'geekstek-temporal-picker::day-of-month-picker';
    
    protected bool $isMultiple = false;
    protected array $disabledDays = [];

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;
        return $this;
    }
    
    // ... 添加 disabledDays() 等方法
}
```

### 4.2 Blade 视图 (`resources/views/day-of-month-picker.blade.php`)

这里通过 Alpine 处理选择逻辑，利用 Tailwind 4 的 Grid 布局。

```blade
<x-filament-forms::field-wrapper
    :id="$getId()"
    :label="$getLabel()"
    :state-path="$getStatePath()"
    :helper-text="$getHelperText()"
    :required="$isRequired()"
>
    <div 
        x-data="{
            state: $entangle($getStatePath()),
            multiple: @js($isMultiple()),
            disabledDays: @js($getDisabledDays()),
            
            toggle(day) {
                if (this.disabledDays.includes(day)) return;

                if (this.multiple) {
                    // 初始化数组
                    if (!Array.isArray(this.state)) this.state = [];
                    
                    if (this.state.includes(day)) {
                        this.state = this.state.filter(d => d !== day);
                    } else {
                        this.state.push(day);
                    }
                } else {
                    // 单选
                    this.state = this.state === day ? null : day;
                }
            },
            isSelected(day) {
                if (this.multiple) {
                    return Array.isArray(this.state) && this.state.includes(day);
                }
                return this.state === day;
            }
        }"
        class="grid grid-cols-7 gap-1 w-full max-w-[300px]"
    >
        @foreach(range(1, 31) as $day)
            <button
                type="button"
                x-on:click="toggle({{ $day }})"
                :disabled="disabledDays.includes({{ $day }})"
                :class="{
                    'bg-primary-600 text-white hover:bg-primary-500': isSelected({{ $day }}),
                    'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300': !isSelected({{ $day }}),
                    'opacity-50 cursor-not-allowed': disabledDays.includes({{ $day }})
                }"
                class="flex items-center justify-center rounded-md p-2 text-sm font-medium transition duration-75"
            >
                {{ $day }}
            </button>
        @endforeach
    </div>
</x-filament-forms::field-wrapper>
```

---

## 5. 其他组件实现思路

基于上述两种模式，其他组件可以快速复用：

### 5.1 WeekdayPicker (星期选择)
*   **模式**：参考 `DayOfMonthPicker`。
*   **修改点**：
    *   `grid-cols-7` 布局不变。
    *   循环数据源改为 `['Mon', 'Tue', ...]` (或根据 `weekStartsOn` 调整顺序)。
    *   Value 存 `0-6` 或 `1-7`。

### 5.2 YearPicker (年份选择)
*   **模式**：参考 `MonthPicker`。
*   **修改点**：
    *   Flatpickr 配置中不需要 plugin。
    *   `dateFormat: 'Y'`。
    *   UI 上如果想要像 Excel 那样只展示年份列表而不弹出日历，可以直接使用 Filament 原生的 `Select::make('year')->options(...)` 配合 `range()` 动态生成年份数组，这是最简单且性能最好的方案。如果非要用 Picker，Flatpickr 也可以。

### 5.3 WeekPicker (第几周)
*   **模式**：参考 `MonthPicker`。
*   **修改点**：
    *   引入 `flatpickr/dist/plugins/weekSelect`。
    *   注意：Flatpickr 的 WeekSelect 插件在 `multiple` 模式下体验可能一般，需要测试。如果体验不好，建议改用自定义 Alpine Grid (渲染 1-53 数字)。

---

## 6. 注册与构建

### 6.1 ServiceProvider 注册

在 `GeekstekTemporalPickerServiceProvider.php` 中注册资源：

```php
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

public function packageBooted(): void
{
    FilamentAsset::register([
        Js::make('geekstek-temporal-picker', __DIR__ . '/../../resources/dist/geekstek-temporal-picker.js'),
        // 如果你需要自定义 CSS
        // Css::make('geekstek-temporal-picker', __DIR__ . '/../../resources/dist/geekstek-temporal-picker.css'),
    ], 'geekstek/temporal-picker');
}
```

### 6.2 编译资源

在 `package.json` 中配置编译脚本（通常使用 esbuild 或 vite），将 `resources/js/index.js` 编译到 `resources/dist/geekstek-temporal-picker.js`。

---

## 7. 总结：如何满足你的所有需求

1.  **继承能力 (`required`, `live` 等)**:
    *   通过继承 `Filament\Forms\Components\Field` 并使用 `<x-filament-forms::field-wrapper>`，你无需编写任何额外代码即可自动支持这些功能。
    *   `live()` 生效是因为我们在 Alpine 中使用了 `$entangle`，一旦 JS 更新 state，Livewire 就会收到请求。
    *   `afterStateUpdated()` 是 Livewire 的生命周期，数据同步后自然会触发。

2.  **Tailwind 4 兼容**:
    *   我们使用了 `<x-filament::input.wrapper>`，它会自动应用 Filament 主题配置的圆角、边框颜色、Focus Ring。
    *   在自定义 Grid 组件中，使用 `bg-primary-600` 等类名，这些会自动适配用户定义的主题色。

3.  **多选与禁用**:
    *   Flatpickr 原生支持 `multiple` 和 `disable` 函数。
    *   Alpine 组件通过数组逻辑 (`filter/push`) 轻松实现多选，通过 `:disabled` 属性实现禁用。

按照这个指南开发，你能得到一套高质量、原生感极强的 Filament 插件。