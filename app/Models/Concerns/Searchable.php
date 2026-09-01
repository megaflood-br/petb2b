<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Busca textual reutilizável.
     *
     * - MySQL/MariaDB: usa índice FULLTEXT (whereFullText) — rápido e relevante.
     * - Demais drivers (ex.: SQLite em dev/testes): fallback para LIKE.
     *
     * @param  array<int,string>  $columns
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns): Builder
    {
        $term = trim((string) $term);

        if ($term === '' || empty($columns)) {
            return $query;
        }

        if (in_array($query->getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $query->whereFullText($columns, $term);
        }

        return $query->where(function (Builder $q) use ($columns, $term) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', '%' . $term . '%');
            }
        });
    }
}
