<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('merchandising_json')->nullable()->after('specs_json');
            $table->json('gift_options_json')->nullable()->after('merchandising_json');
        });

        Schema::create('product_badges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['business_unit_id', 'key']);
        });

        Schema::create('product_badge_product', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_badge_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['product_id', 'product_badge_id']);
        });

        Schema::create('product_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('slug');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('seo_title_ar')->nullable();
            $table->string('seo_title_en')->nullable();
            $table->text('seo_description_ar')->nullable();
            $table->text('seo_description_en')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['business_unit_id', 'slug']);
        });

        Schema::create('product_collection_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->unique(['product_collection_id', 'product_id']);
            $table->index(['product_id', 'is_featured']);
        });

        Schema::create('product_bundles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('bundle_type')->default('fixed_box');
            $table->string('pricing_mode')->default('use_parent_product_price');
            $table->decimal('fixed_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->index(['business_unit_id', 'is_active']);
        });

        Schema::create('product_bundle_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_bundle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('child_product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->integer('sort_order')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('corporate_gift_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_collection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_name')->nullable();
            $table->string('contact_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('occasion')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['business_unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_gift_inquiries');
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('product_collection_items');
        Schema::dropIfExists('product_collections');
        Schema::dropIfExists('product_badge_product');
        Schema::dropIfExists('product_badges');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['merchandising_json', 'gift_options_json']);
        });
    }
};
