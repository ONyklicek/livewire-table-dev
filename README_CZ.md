# Laravel Livewire Table 📊

Pokročilý, plně objektový a server-driven tabulkový systém pro Laravel s Livewire 3, Alpine.js a Tailwind CSS 3.x/4.x.

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.0-purple)](https://livewire.laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 🎯 Vlastnosti

- ✅ **SDUI (Server-Driven UI)** - Vše konfigurovatelné z PHP
- ✅ **Inline Editing** - Úprava přímo v buňkách tabulky
- ✅ **Grouping** - Seskupování řádků s collapsible groups
- ✅ **Sub-rows** - Expandable vnořený obsah
- ✅ **Column Toggle** - Dynamické skrývání/zobrazování sloupců
- ✅ **Saved Filters** - Uložené kombinace filtrů
- ✅ **Relationships** - Podpora dot notation (`company.name`)
- ✅ **Enum Support** - Automatická detekce PHP 8.1+ enums
- ✅ **Responsive** - Mobile/Tablet/Desktop optimalizace
- ✅ **Sorting & Filtering** - Per-column i globální
- ✅ **Bulk Actions** - Hromadné operace
- ✅ **Live Updates** - Auto-refresh každých N sekund
- ✅ **Htmlable** - Plná podpora `__toString()` a `toHtml()`
- ✅ **Tailwind 3.x/4.x** - Kompatibilní s oběma verzemi

---

## 📋 Požadavky

| Požadavek | Verze |
|-----------|-------|
| PHP | 8.2+ |
| Laravel | 11.x nebo 12.x |
| Livewire | 3.0+ |
| Tailwind CSS | 3.x nebo 4.x |
| Alpine.js | 3.x (zahrnuto v Livewire 3) |

---

## 📦 Instalace

### Krok 1: Instalace přes Composer

```bash
composer require nyoncode/livewire-table
```

### Krok 2: Publikování konfigurace a assets

```bash
# Publikovat konfiguraci
php artisan vendor:publish --tag="livewire-table-config"

# Publikovat migrace (pro Saved Filters)
php artisan vendor:publish --tag="livewire-table-migrations"

# Publikovat views (volitelné - pouze pokud chcete customizovat)
php artisan vendor:publish --tag="livewire-table-views"

# Publikovat překlady (volitelné)
php artisan vendor:publish --tag="livewire-table-translations"
```

### Krok 3: Spuštění migrací

```bash
php artisan migrate
```

To vytvoří tabulku `table_filter_presets` pro ukládání uživatelských filtrů.

### Krok 4: Konfigurace Tailwind CSS

Přidej cesty k package views do `tailwind.config.js`:

#### Pro Tailwind CSS 3.x:

```js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './vendor/nyoncode/livewire-table/resources/views/**/*.blade.php',
    ],
    safelist: [
        // Badge colors - nutné pro dynamické barvy
        'bg-green-100', 'text-green-800', 'ring-green-600/20', 'fill-green-500',
        'bg-red-100', 'text-red-800', 'ring-red-600/20', 'fill-red-500',
        'bg-yellow-100', 'text-yellow-800', 'ring-yellow-600/20', 'fill-yellow-500',
        'bg-blue-100', 'text-blue-800', 'ring-blue-600/20', 'fill-blue-500',
        'bg-gray-100', 'text-gray-800', 'ring-gray-600/20', 'fill-gray-500',
        'bg-orange-100', 'text-orange-800', 'ring-orange-600/20', 'fill-orange-500',
        'bg-purple-100', 'text-purple-800', 'ring-purple-600/20', 'fill-purple-500',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}
```

#### Pro Tailwind CSS 4.x:

```js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './vendor/nyoncode/livewire-table/resources/views/**/*.blade.php',
    ],
    safelist: [
        {
            pattern: /^(bg|text|ring|fill)-(green|red|yellow|blue|gray|orange|purple)-(100|500|800)$/,
        },
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}
```

### Krok 5: Build assets

```bash
npm run build
# nebo pro development
npm run dev
```

### Krok 6: Ověření instalace

Zkontroluj, že service provider byl zaregistrován:

```bash
php artisan about
```

Měl bys vidět sekci s informacemi o **Livewire Table** package.

---

## 🚀 Rychlý start

### 1. Vytvoř Livewire komponentu

```bash
php artisan make:livewire UsersTable
```

### 2. Implementuj tabulku

```php
<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\Tables\Table;
use App\Support\Tables\Columns\TextColumn;
use App\Support\Tables\Columns\BadgeColumn;
use App\Support\Tables\Filters\TextFilter;
use App\Support\Tables\Filters\SelectFilter;
use App\Support\Tables\Actions\Action;
use Livewire\Component;

class UsersTable extends Component
{
    use \NyonCode\LivewireTable\Livewire\Concerns\HasTable;

    public function table(Table $table): Table
    {
        return $table
            ->model(User::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Jméno')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'active' => 'green',
                        'inactive' => 'gray',
                    ])
                    ->sortable(),
            ])
            ->filters([
                TextFilter::make('name_filter', 'name')
                    ->label('Jméno')
                    ->placeholder('Hledat...'),
                
                SelectFilter::make('status_filter', 'status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktivní',
                        'inactive' => 'Neaktivní',
                    ]),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Upravit')
                    ->color('blue')
                    ->action(fn($record) => $this->redirect(route('users.edit', $record))),
            ])
            ->perPage(25);
    }

    public function render()
    {
        return view('livewire.users-table');
    }
}
```

### 3. Vytvoř Blade view

```blade
{{-- resources/views/livewire/users-table.blade.php --}}

<div>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Uživatelé</h1>
        
        {!! $this->table->render() !!}
    </div>
</div>
```

### 4. Použij v route

```php
use App\Livewire\UsersTable;

Route::get('/users', UsersTable::class);
```

---

## 📘 Kompletní průvodce

### Sloupce (Columns)

#### TextColumn

Základní textový sloupec s pokročilými funkcemi.

```php
use NyonCode\LivewireTable\Columns\TextColumn;

TextColumn::make('name')
    ->label('Jméno')
    ->searchable()      // Lze prohledávat
    ->sortable()        // Lze řadit
    ->copyable()        // Tlačítko pro kopírování
    ->limit(50)         // Ořízne text na 50 znaků
    ->placeholder('—')  // Zobrazí když je hodnota null
    ->hideOn(['sm', 'md'])  // Skryje na mobilech a tabletech
    ->format(fn($value) => strtoupper($value))  // Custom formátování
    ->visible(auth()->user()->isAdmin())  // Podmíněná viditelnost
    ->hidden(!config('app.debug'))  // Nebo skrytí
```

#### BadgeColumn

Barevné odznaky pro stavy a kategorie.

```php
use NyonCode\LivewireTable\Columns\BadgeColumn;

BadgeColumn::make('status')
    ->label('Status')
    ->colors([
        'active' => 'green',
        'inactive' => 'gray',
        'suspended' => 'red',
        'pending' => 'yellow',
    ])
    ->icons([
        'active' => '<circle cx="3" cy="3" r="3" />',
        'suspended' => '<path d="M6 18L18 6M6 6l12 12"/>',
    ])
    ->size('lg')  // sm, md, lg, xl
    ->format(fn($value) => match($value) {
        true, 1 => 'Aktivní',
        false, 0 => 'Neaktivní',
        default => 'Neznámý'
    })
```

#### ImageColumn

Sloupcec pro zobrazení obrázků a avatarů.

```php
use NyonCode\LivewireTable\Columns\ImageColumn;

ImageColumn::make('avatar')
    ->label('Avatar')
    ->circular()  // Kulatý obrázek
    ->size('md')  // sm, md, lg
    ->defaultImage('https://ui-avatars.com/api/?name=User')
```

#### EditableColumn

Editovatelný sloupec přímo v tabulce (inline editing).

```php
use NyonCode\LivewireTable\Columns\EditableColumn;

EditableColumn::make('name')
    ->label('Jméno')
    ->inputType('text')  // text, number, select, date, textarea
    ->rules('required|min:3')
    ->options([  // Pro select
        'option1' => 'Label 1',
        'option2' => 'Label 2',
    ])
    ->onSave(function ($record, $value) {
        $record->update(['name' => $value]);
        $this->dispatch('notify', ['message' => 'Uloženo']);
    })
```

#### Custom Column

Vytvoř vlastní typ sloupce:

```php
use NyonCode\LivewireTable\Columns\Column;
use Illuminate\Database\Eloquent\Model;

class MyColumn extends Column
{
    public function formatValue(mixed $value, Model $record): mixed
    {
        return strtoupper($value);
    }
    
    public function render(): string
    {
        return view('components.table.columns.my-column', [
            'column' => $this,
            'record' => $this->record,
            'value' => $this->getFormattedValue($this->record),
        ])->render();
    }
}
```

---

### Relationships (Vztahy)

Plná podpora Eloquent vztahů pomocí **dot notation**:

```php
// Jednoduchý vztah
TextColumn::make('department.name')
    ->label('Oddělení')
    ->searchable()
    ->sortable()

// Vnořený vztah
TextColumn::make('user.company.name')
    ->label('Firma')

// Automatický eager loading
// RelationshipResolver automaticky načte všechny relationships
```

**Filtry pro relationships:**

```php
TextFilter::make('company_name', 'company.name')
    ->label('Firma')
    ->placeholder('Hledat firmu...')

SelectFilter::make('department_id', 'department.id')
    ->label('Oddělení')
    ->options(Department::pluck('name', 'id'))
```

---

### Filtry (Filters)

#### TextFilter

```php
use NyonCode\LivewireTable\Filters\TextFilter;

TextFilter::make('name_filter', 'name')
    ->label('Jméno')
    ->placeholder('Hledat...')
    ->operator('like')  // like, =, !=, >, <, >=, <=
```

#### SelectFilter

```php
use NyonCode\LivewireTable\Filters\SelectFilter;

SelectFilter::make('status_filter', 'status')
    ->label('Status')
    ->options([
        'active' => 'Aktivní',
        'inactive' => 'Neaktivní',
    ])
    ->placeholder('Vyberte status')
```

#### DateFilter

```php
use NyonCode\LivewireTable\Filters\DateFilter;

DateFilter::make('created_after', 'created_at')
    ->label('Od data')
    ->operator('>=')
```

#### Global Filters

Filtry ve dropdown menu místo pod sloupci:

```php
->globalFilters([
    SelectFilter::make('department_id', 'department.id')
        ->label('Oddělení')
        ->options(Department::pluck('name', 'id'))
        ->global(),
])
```

---

### Akce (Actions)

#### Jednoduchá akce

```php
use NyonCode\LivewireTable\Actions\Action;

Action::make('edit')
    ->label('Upravit')
    ->icon('<path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>') 
    ->color('blue')
    ->action(function ($record) {
        return redirect()->route('users.edit', $record);
    })
```

#### Akce s potvrzením

```php
Action::make('delete')
    ->label('Smazat')
    ->color('red')
    ->requireConfirmation(
        'Smazat uživatele?',
        'Tato akce je nevratná.'
    )
    ->action(function ($record) {
        $record->delete();
    })
```

#### Akce s modálem

```php
use NyonCode\LivewireTable\Actions\Modal;

Action::make('details')
    ->label('Detail')
    ->modal(
        Modal::make('Detail uživatele')
            ->info('Zde jsou detailní informace')
            ->size('lg')  // sm, md, lg, xl, full
    )
```

---

### Bulk Actions (Hromadné akce)

```php
use NyonCode\LivewireTable\Actions\BulkAction;

->bulkActions([
    BulkAction::make('activate')
        ->label('Aktivovat vybrané')
        ->color('green')
        ->action(function ($records) {
            $records->each->update(['status' => 'active']);
        }),
    
    BulkAction::make('delete')
        ->label('Smazat vybrané')
        ->color('red')
        ->requireConfirmation()
        ->action(function ($records) {
            $records->each->delete();
        }),
])
```

---

### Grouping (Seskupování)

```php
->groupBy('department.name')
->groupHeader(function ($key, $items) {
    return sprintf('%s (%d uživatelů)', $key, $items->count());
})
->collapsibleGroups(true)
```

**S custom logikou:**

```php
->groupBy('created_at', function ($item) {
    return $item->created_at->format('Y-m');
})
->groupHeader(function ($key, $items) {
    $total = $items->sum('salary');
    return "$key - Celkem: " . number_format($total) . " Kč";
})
```

---

### Sub-rows (Vnořené řádky)

```php
->subRows('posts', function ($posts, $user) {
    return view('tables.user-posts', [
        'posts' => $posts,
        'user' => $user,
    ])->render();
})
->lazyLoadSubRows(true)
```

---

### Column Toggle (Skrývání sloupců)

```php
->enableColumnToggle(true)
->alwaysVisible(['name', 'email'])  // Tyto sloupce nelze skrýt
```

---

### Saved Filters (Uložené filtry)

```php
->enablePresets(true)
```

**Programaticky:**

```php
// V Livewire komponentě
public function saveMyPreset()
{
    $this->savePreset('Aktivní uživatelé', [
        'status' => 'active',
        'department_id' => 1,
    ], isDefault: true);
}
```

---

### Responsive Design

```php
->scheme([
    'mobile' => ['stack'],   // Karty místo tabulky
    'tablet' => ['scroll'],  // Horizontální scroll
    'desktop' => ['full'],   // Plná tabulka
])
```

**Per-column responsive:**

```php
TextColumn::make('description')
    ->hideOn(['sm', 'md'])  // Skryj na mobilu a tabletu
```

---

### Live Updates

```php
->liveUpdate(60)  // Auto-refresh každých 60 sekund
```

---

### Pagination

```php
->perPage(25)
->pageOptions([10, 25, 50, 100])
```

---

## 🔧 Pokročilé použití

### Enum Support

Automatická detekce PHP 8.1+ enums:

```php
// Model
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

protected $casts = [
    'status' => UserStatus::class,
];

// Table
BadgeColumn::make('status')
    ->colors([
        'active' => 'green',
        'inactive' => 'gray',
    ])
```

### Podmíněná viditelnost sloupců

```php
TextColumn::make('internal_notes')
    ->visible(auth()->user()->isAdmin())

TextColumn::make('debug_info')
    ->hidden(fn() => !config('app.debug'))
```

---

## 🐛 Troubleshooting

### "Object could not be converted to string"

**Řešení:** Použij `{!! $this->table->render() !!}` místo `{{ $this->table }}`

### "Undefined variable $tableSearch"

**Řešení:** Zkontroluj, že používáš trait `HasTable` v Livewire komponentě:

```php
use NyonCode\LivewireTable\Livewire\Concerns\HasTable;

class MyTable extends Component
{
    use HasTable;
    
    // ...
}
```

### "Method resetPage does not exist"

**Řešení:** Trait `HasTable` už obsahuje `WithPagination`, nemusíš ho přidávat ručně.

### Badge barvy se neaplikují

**Řešení:**
1. Přidej barvy do Tailwind `safelist` (viz instalace)
2. Spusť `npm run build`
3. Nepoužívej dynamické třídy typu `bg-{{ $color }}-100`

### Filtry se nezobrazují pod sloupci

**Řešení:** Druhý parametr filtru musí odpovídat `field` sloupce:

```php
// SPRÁVNĚ
TextColumn::make('name'),
TextFilter::make('name_filter', 'name'),  // 'name' odpovídá field

// ŠPATNĚ
TextColumn::make('name'),
TextFilter::make('name_filter', 'username'),  // Neshoduje se
```

---

## 📊 Příklady použití

### E-commerce objednávky

```php
public function table(Table $table): Table
{
    return $table
        ->model(Order::with(['customer', 'items']))
        ->columns([
            TextColumn::make('order_number')
                ->label('Číslo objednávky')
                ->searchable()
                ->copyable(),
            
            TextColumn::make('customer.name')
                ->label('Zákazník')
                ->searchable(),
            
            BadgeColumn::make('status')
                ->colors([
                    'pending' => 'yellow',
                    'processing' => 'blue',
                    'completed' => 'green',
                    'cancelled' => 'red',
                ]),
            
            TextColumn::make('total')
                ->format(fn($value) => number_format($value, 2) . ' Kč'),
        ])
        ->groupBy('status')
        ->subRows('items', function ($items) {
            return view('tables.order-items', compact('items'))->render();
        });
}
```

---

## 🤝 Contributing

Pull requesty jsou vítány! Pro větší změny prosím nejprve otevřete issue.

## 📄 License

MIT License - viz [LICENSE](LICENSE)

## 👨‍💻 Autor

Vytvořeno s ❤️ [NyonCode](https://nyoncode.cz)

**Ondřej Nyklíček**  
📧 ondrej@nyoncode.cz

---

## 🔗 Related Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire 3 Documentation](https://livewire.laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)

---

**Happy coding! 🎉**