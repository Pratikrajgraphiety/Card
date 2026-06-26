<?php

namespace App\Models;

use App\Core\Database;

final class Category
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name');
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE id = ? LIMIT 1', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE slug = ? LIMIT 1', [$slug]);
    }

    public static function fieldsFor(?array $category): array
    {
        if (!$category) {
            return [];
        }

        $rows = Database::fetchAll(
            'SELECT field_key, label, field_type, placeholder, help_text, is_required, is_public
             FROM category_fields
             WHERE category_id = ?
             ORDER BY sort_order, id',
            [(int) $category['id']]
        );

        if ($rows) {
            return array_map(static fn (array $row): array => [
                'name' => $row['field_key'],
                'label' => $row['label'],
                'type' => $row['field_type'],
                'placeholder' => $row['placeholder'],
                'help_text' => $row['help_text'],
                'required' => (bool) $row['is_required'],
                'public' => (bool) $row['is_public'],
                'accept' => $row['field_type'] === 'file' ? ($row['field_key'] === 'business_pdf_path' ? '.pdf' : '.pdf,.doc,.docx') : '',
            ], $rows);
        }

        $stored = json_decode((string) ($category['fields_json'] ?? '[]'), true);
        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        return config('category_fields.' . $category['slug'], []);
    }
}
