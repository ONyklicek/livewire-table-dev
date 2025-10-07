# Laravel Advanced Table System 📊

Pokročilý, plně objektový a server-driven tabulkový systém pro Laravel s Livewire 3, Alpine.js a Tailwind CSS.

## 🎯 Vlastnosti

- ✅ **SDUI (Server-Driven UI)** - Vše konfigurovatelné z PHP
- ✅ **Inline Editing** - Úprava přímo v buňkách tabulky
- ✅ **Grouping** - Seskupování řádků s collapsible groups
- ✅ **Sub-rows** - Expandable vnořený obsah
- ✅ **Column Toggle** - Dynamické skrývání/zobrazování sloupců
- ✅ **Saved Filters** - Uložené kombinace filtrů
- ✅ **Relationships** - Podpora dot notation (`company.name`)
- ✅ **Enum Support** - Automatická detekce PHP enums
- ✅ **Responsive** - Mobile/Tablet/Desktop optimalizace
- ✅ **Sorting & Filtering** - Per-column i globální
- ✅ **Bulk Actions** - Hromadné operace
- ✅ **Live Updates** - Auto-refresh každých N sekund
- ✅ **Htmlable** - Plná podpora `__toString()` a `toHtml()`

---

## 📦 Instalace

### 1. Zkopíruj soubory

```
app/
├── Support/
│   └── Tables/
│       ├── Table.php
│       ├── Builders/
│       │   ├── QueryBuilder.php
│       │   └── RelationshipResolver.php
│       ├── Columns/
│       │   ├── Column.php
│       │   ├── TextColumn.php
│       │   ├── BadgeColumn.php
│       │   ├── ImageColumn.php
│       │   └── EditableColumn.php
│       ├── Filters/
│       │   ├── Filter.php
│       │   ├── TextFilter.php
│       │   ├── SelectFilter.php
│       │   └── DateFilter.php
│       ├── Actions/
│       │   ├── Action.php
│       │   ├── BulkAction.php
│       │   └── Modal.php
│       └── Concerns/
│           ├── HasColumns.php
│           ├── HasFilters.php
│           ├── HasActions.php
│           ├── HasPagination.php
│           ├── HasResponsiveScheme.php
│           ├── HasGrouping.php
│           ├── HasSubRows.php
│           ├── HasColumnToggle.php
│           └── HasSavedFilters.php
├── Livewire/
│   └── Concerns/
│       └── HasTable.php
└── Models/
    └── TableFilterPreset.php (optional)
```

### 2. Spusť migraci (pro Saved Filters)

```bash
php artisan make:migration create_table_filter_presets_table
```

```php
Schema::create('table_filter_presets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('table_name');
    $table->string('name');
    $table->json('filters');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    
    $table->index(['user_id', 'table_name']);
});
```

```bash
php artisan migrate
```

### 3. Nainstaluj dependencies

```bash
npm install
```

### 4. Aktualizuj Tailwind config

```js
// tailwind.config.js
export default {
    content: [
        './resources/**/*.blade.php',
        './app/Support/Tables/**/*.php',
    ],
    safelist: [
        // Badge colors
        'bg-green-100', 'text-green-800', 'ring-green-600/20', 'fill-green-500',
        'bg-red-100', 'text-red-800', 'ring-red-600/20', 'fill-red-500',
        'bg-yellow-100', 'text-yellow-800', 'ring-yellow-600/20', 'fill-yellow-500',
        'bg-blue-100', 'text-blue-800', 'ring-blue-600/20', 'fill-blue-500',
        'bg-gray-100', 'text-gray-800', 'ring-gray-600/20', 'fill-gray-500',
        'bg-orange-100', 'text-orange-800', 'ring-orange-600/20', 'fill-orange-500',
        'bg-purple-100', 'text-purple-800', 'ring-purple-600/20', 'fill-purple-500',
    ],
}
```

---

## 🚀 Základní použití

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
use App\Support\Tables\Actions\BulkAction;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;
    use \App\Livewire\Concerns\HasTable;

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
        
        {!! $this->tableHtml !!}
    </div>
</div>
```

### 4. Použij v route nebo view

```php
Route::get('/users', UsersTable::class);
```

---

## 📘 Dokumentace

### Columns (Sloupce)

#### TextColumn

Základní textový sloupec.

```php
TextColumn::make('name')
    ->label('Jméno')
    ->searchable()
    ->sortable()
    ->copyable()  // Přidá tlačítko pro kopírování
    ->limit(50)   // Ořízne text na 50 znaků
    ->placeholder('—')  // Zobrazí když je hodnota null
    ->hideOn(['sm', 'md'])  // Skryje na mobilech a tabletech
    ->format(fn($value) => strtoupper($value))  // Custom formátování
```

#### BadgeColumn

Barevné badge pro stavy.

```php
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
    ->format(fn($value) => match($value) {
        true, 1 => 'Aktivní',
        false, 0 => 'Neaktivní',
        default => 'Neznámý'
    })
```

#### ImageColumn

Sloupcec pro obrázky.

```php
ImageColumn::make('avatar')
    ->label('Avatar')
    ->circular()  // Kulatý obrázek
    ->size('md')  // sm, md, lg
    ->defaultImage('https://ui-avatars.com/api/?name=User')
```

#### EditableColumn

Editovatelný sloupec přímo v tabulce.

```php
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

```php
use App\Support\Tables\Columns\Column;

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

Podporováno pomocí **dot notation**:

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
// RelationshipResolver automaticky načte relationships
```

---

### Filters (Filtry)

#### TextFilter

```php
TextFilter::make('name_filter', 'name')
    ->label('Jméno')
    ->placeholder('Hledat...')
    ->operator('like')  // like, =, !=, >, <, >=, <=
```

#### SelectFilter

```php
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
DateFilter::make('created_after', 'created_at')
    ->label('Od data')
    ->operator('>=')
```

#### Global Filters

Filtry ve dropdown menu:

```php
->globalFilters([
    SelectFilter::make('department_id', 'department.id')
        ->label('Oddělení')
        ->options(Department::pluck('name', 'id'))
        ->global(),
])
```

#### Filtry pro relationships

```php
TextFilter::make('company_name', 'company.name')
    ->label('Firma')
    ->placeholder('Hledat firmu...')
```

---

### Actions (Akce)

#### Jednoduchá akce

```php
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
use App\Support\Tables\Actions\Modal;

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

**Sub-row view příklad:**

```blade
{{-- resources/views/tables/user-posts.blade.php --}}

<div class="space-y-2">
    <h4 class="font-semibold">Příspěvky ({{ $posts->count() }})</h4>
    @foreach($posts as $post)
        <div class="bg-white border rounded p-3">
            <h5>{{ $post->title }}</h5>
            <p class="text-sm text-gray-600">{{ $post->excerpt }}</p>
        </div>
    @endforeach
</div>
```

---

### Column Toggle (Skrývání sloupců)

```php
->enableColumnToggle(true)
->alwaysVisible(['name', 'email'])  // Tyto sloupce nelze skrýt
```

Uživatel pak může v UI kliknout na "Sloupce" a vybrat, které chce vidět.

---

### Saved Filters (Uložené filtry)

```php
->enablePresets(true)
```

**Uživatel pak může:**
- Uložit aktuální kombinaci filtrů
- Načíst uložené filtry
- Smazat filtry
- Nastavit defaultní preset

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

## 🎨 Styling & Theming

### Tailwind Safelist

Pro dynamické barvy v badges musíš přidat do `safelist`:

```js
safelist: [
    'bg-green-100', 'text-green-800', 'ring-green-600/20',
    'bg-red-100', 'text-red-800', 'ring-red-600/20',
    // ... další barvy
]
```

### Custom Views

Každý column může mít vlastní view:

```php
TextColumn::make('name')
    ->view('components.table.columns.custom-text')
```

```blade
{{-- resources/views/components/table/columns/custom-text.blade.php --}}

<div class="custom-styling">
    <strong>{{ $value }}</strong>
</div>
```

---

## 🔧 Pokročilé funkce

### QueryBuilder Pattern

Systém používá `QueryBuilder` pro čistší query konstrukci:

```php
use App\Support\Tables\Builders\QueryBuilder;
use App\Support\Tables\Builders\RelationshipResolver;

// Automatický eager loading
$relationships = RelationshipResolver::extractRelationships($columns);
$query = RelationshipResolver::eagerLoad($query, $relationships);

// Fluent API
$queryBuilder = new QueryBuilder($query);
$queryBuilder
    ->search(['name', 'email'], 'john')
    ->multiSort(['name' => 'asc', 'created_at' => 'desc']);
```

### Custom Filters

```php
use App\Support\Tables\Filters\Filter;

class CustomFilter extends Filter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('custom_field', $value);
    }
    
    public function render(): string
    {
        return view('filters.custom', ['filter' => $this])->render();
    }
}
```

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
    ->format(fn($value) => $value instanceof BackedEnum 
        ? $value->value 
        : $value
    )
```

### Hidden Columns

```php
TextColumn::make('internal_notes')
    ->hidden(!auth()->user()->isAdmin())

TextColumn::make('debug_info')
    ->hidden(fn() => !config('app.debug'))
```

---

## 🐛 Troubleshooting

### Problém: "Object could not be converted to string"

**Řešení:** Použij `{!! $this->tableHtml !!}` místo `{!! $this->table !!}`

### Problém: "Undefined variable $tableSearch"

**Řešení:** Zkontroluj, že Table::render() předává všechny potřebné proměnné do view.

### Problém: "Method resetPage does not exist"

**Řešení:** Přidej `use WithPagination;` do Livewire komponenty.

### Problém: Badge barvy se neaplikují

**Řešení:**
1. Přidej barvy do Tailwind `safelist`
2. Spusť `npm run build`
3. Nepoužívej dynamické třídy typu `bg-{{ $color }}-100`

### Problém: `&` se zobrazuje jako `&amp;`

**Řešení:** Odstraň `e()` z `formatValue()` - Blade `{{ }}` escapuje automaticky.

```php
// ŠPATNĚ
return e($value);

// SPRÁVNĚ
return $value;
```

### Problém: Filtry se nezobrazují pod sloupci

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
                ->label('Celkem')
                ->format(fn($value) => number_format($value, 2) . ' Kč')
                ->sortable(),
            
            TextColumn::make('created_at')
                ->label('Datum')
                ->format(fn($value) => $value->format('d.m.Y'))
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('status_filter', 'status')
                ->options([
                    'pending' => 'Čekající',
                    'processing' => 'Zpracovává se',
                    'completed' => 'Dokončeno',
                ]),
            
            DateFilter::make('from_date', 'created_at')
                ->operator('>='),
        ])
        ->groupBy('status')
        ->subRows('items', function ($items) {
            return view('tables.order-items', compact('items'))->render();
        })
        ->bulkActions([
            BulkAction::make('mark_shipped')
                ->label('Označit jako odesláno')
                ->action(fn($records) => $records->each->update(['status' => 'shipped'])),
        ]);
}
```

### CRM kontakty

```php
public function table(Table $table): Table
{
    return $table
        ->model(Contact::with(['company', 'tags']))
        ->columns([
            ImageColumn::make('photo')
                ->circular()
                ->size('sm'),
            
            EditableColumn::make('name')
                ->inputType('text')
                ->rules('required'),
            
            EditableColumn::make('email')
                ->inputType('text')
                ->rules('required|email'),
            
            TextColumn::make('company.name')
                ->label('Společnost'),
            
            BadgeColumn::make('lead_status')
                ->colors([
                    'new' => 'blue',
                    'contacted' => 'yellow',
                    'qualified' => 'green',
                    'lost' => 'red',
                ]),
        ])
        ->enableColumnToggle(true)
        ->enablePresets(true)
        ->liveUpdate(30);
}
```

---

## 🤝 Contributing

Pull requesty jsou vítány! Pro větší změny prosím nejprve otevřete issue.

## 📄 License

MIT License

## 👨‍💻 Autor

Vytvořeno s ❤️ pro Laravel komunitu

---

## 🔗 Related Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)

---

**Happy coding! 🎉**