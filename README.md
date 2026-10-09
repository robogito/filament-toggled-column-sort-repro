# Filament: sorting by a `->counts()` column the user hid through the column manager

Reproduction for a bug in `filament/tables` (v5.9.0 and v5.10.1): when a table is sorted by a
sortable, toggleable column that uses `->counts()` and the user then hides that column through
the column manager, the next query fails with

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'posts_count' in 'ORDER BY'
(SQL: select * from `users` order by `posts_count` asc, `users`.`id` asc limit 10 offset 0)
```

The relationship aggregate is selected for visible columns only
(`Filament\Tables\Concerns\HasRecords::filterTableQuery()` iterates `getVisibleColumns()`, which
excludes `isToggledHidden()` columns), while the sort keeps the hidden column
(`Filament\Tables\Table\Concerns\CanSortRecords::getSortableVisibleColumn()` checks `isHidden()`
and `isSortable()` only).

## What is in this repository

Built from the `5.x` branch of `filamentphp/issue-reproduction-template`, plus:

- `app/Models/Post.php`, its migration and factory; `User::posts()` has-many relationship
- `app/Filament/Resources/Users/UserResource.php` with one column:
  `TextColumn::make('posts_count')->counts('posts')->sortable()->toggleable()`
- `tests/Feature/SortByToggledHiddenCountColumnTest.php`: sorts by the column, hides it through
  the column manager, expects the table to still render
- `.github/workflows/tests.yml`: runs that test against MariaDB on every push (see the Actions tab)

## Run it

The test needs MySQL or MariaDB. SQLite does not show the bug because it treats an unknown
double-quoted identifier in `ORDER BY` as a string literal, so `order by "posts_count"` succeeds there.

```bash
docker compose up -d          # MariaDB on 127.0.0.1:3306, database "repro", root/root
composer setup
php artisan test tests/Feature/SortByToggledHiddenCountColumnTest.php
```

In the browser: `php artisan serve`, log in at <http://127.0.0.1:8000/admin> with
`test@example.com` / `password`, open "Users", click the "Posts count" header to sort, then untick
"Posts count" in the column manager.
