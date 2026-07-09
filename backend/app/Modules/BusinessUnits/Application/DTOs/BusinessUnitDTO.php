<?php

namespace App\Modules\BusinessUnits\Application\DTOs;

use App\Modules\Core\Application\DTOs\BaseDTO;

readonly class BusinessUnitDTO extends BaseDTO
{
    public function __construct(
        public string $name_ar,
        public string $slug,
        public string $type,
        public string $status = 'draft',
        public ?int $parent_id = null,
        public ?string $name_en = null,
        public ?string $logo = null,
        public ?string $cover_image = null,
        public ?string $description = null,
        public ?string $primary_color = null,
        public ?string $secondary_color = null,
        public ?array $settings_json = null,
        public ?int $created_by = null,
        public ?string $template_key = null,
    ) {}

    public static function fromArray(array $attributes): self
    {
        return new self(
            name_ar: $attributes['name_ar'],
            slug: $attributes['slug'],
            type: $attributes['type'],
            status: $attributes['status'] ?? 'draft',
            parent_id: $attributes['parent_id'] ?? null,
            name_en: $attributes['name_en'] ?? null,
            logo: $attributes['logo'] ?? null,
            cover_image: $attributes['cover_image'] ?? null,
            description: $attributes['description'] ?? null,
            primary_color: $attributes['primary_color'] ?? null,
            secondary_color: $attributes['secondary_color'] ?? null,
            settings_json: $attributes['settings_json'] ?? null,
            created_by: $attributes['created_by'] ?? null,
            template_key: $attributes['template_key'] ?? null,
        );
    }
}
