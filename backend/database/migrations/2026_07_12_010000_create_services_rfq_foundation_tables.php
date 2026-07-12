<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            foreach ([
                'category' => fn () => $table->string('category')->nullable(),
                'name_ar' => fn () => $table->string('name_ar')->nullable(),
                'name_en' => fn () => $table->string('name_en')->nullable(),
                'summary_ar' => fn () => $table->text('summary_ar')->nullable(),
                'summary_en' => fn () => $table->text('summary_en')->nullable(),
                'description_ar' => fn () => $table->longText('description_ar')->nullable(),
                'description_en' => fn () => $table->longText('description_en')->nullable(),
                'featured_image' => fn () => $table->string('featured_image')->nullable(),
                'is_featured' => fn () => $table->boolean('is_featured')->default(false),
                'sort_order' => fn () => $table->integer('sort_order')->default(0),
            ] as $column => $add) {
                if (! Schema::hasColumn('services', $column)) {
                    $add();
                }
            }
        });

        Schema::table('rfq_requests', function (Blueprint $table): void {
            foreach ([
                'service_id' => fn () => $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete(),
                'rfq_number' => fn () => $table->string('rfq_number')->nullable()->unique(),
                'company_name' => fn () => $table->string('company_name')->nullable(),
                'contact_name' => fn () => $table->string('contact_name')->nullable(),
                'phone' => fn () => $table->string('phone')->nullable(),
                'email' => fn () => $table->string('email')->nullable(),
                'origin_country' => fn () => $table->string('origin_country')->nullable(),
                'destination_country' => fn () => $table->string('destination_country')->nullable(),
                'shipping_method' => fn () => $table->string('shipping_method')->nullable(),
                'incoterm' => fn () => $table->string('incoterm')->nullable(),
                'currency' => fn () => $table->string('currency', 3)->nullable(),
                'expected_date' => fn () => $table->date('expected_date')->nullable(),
                'assigned_to' => fn () => $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(),
                'submitted_at' => fn () => $table->timestamp('submitted_at')->nullable(),
            ] as $column => $add) {
                if (! Schema::hasColumn('rfq_requests', $column)) {
                    $add();
                }
            }
        });

        Schema::table('rfq_items', function (Blueprint $table): void {
            foreach ([
                'item_name' => fn () => $table->string('item_name')->nullable(),
                'quantity' => fn () => $table->decimal('quantity', 12, 3)->nullable(),
                'unit' => fn () => $table->string('unit')->nullable(),
                'target_price' => fn () => $table->decimal('target_price', 14, 2)->nullable(),
                'specifications_json' => fn () => $table->json('specifications_json')->nullable(),
            ] as $column => $add) {
                if (! Schema::hasColumn('rfq_items', $column)) {
                    $add();
                }
            }
        });

        if (! Schema::hasTable('rfq_documents')) {
            Schema::create('rfq_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('rfq_request_id')->constrained()->cascadeOnDelete();
                $table->string('document_type');
                $table->string('file_path');
                $table->string('original_name');
                $table->string('mime_type');
                $table->unsignedBigInteger('file_size');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_public_to_customer')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rfq_quotations')) {
            Schema::create('rfq_quotations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('rfq_request_id')->constrained()->cascadeOnDelete();
                $table->string('quotation_number')->unique();
                $table->string('status')->default('draft');
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('tax_total', 14, 2)->default(0);
                $table->decimal('shipping_total', 14, 2)->default(0);
                $table->decimal('grand_total', 14, 2)->default(0);
                $table->string('currency', 3)->default('EGP');
                $table->date('valid_until')->nullable();
                $table->text('terms')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rfq_quotation_items')) {
            Schema::create('rfq_quotation_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quotation_id')->constrained('rfq_quotations')->cascadeOnDelete();
                $table->foreignId('rfq_item_id')->nullable()->constrained('rfq_items')->nullOnDelete();
                $table->text('description');
                $table->decimal('quantity', 12, 3);
                $table->string('unit');
                $table->decimal('unit_price', 14, 2);
                $table->decimal('subtotal', 14, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rfq_activity_logs')) {
            Schema::create('rfq_activity_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('rfq_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event');
                $table->string('from_status')->nullable();
                $table->string('to_status')->nullable();
                $table->json('metadata_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_activity_logs');
        Schema::dropIfExists('rfq_quotation_items');
        Schema::dropIfExists('rfq_quotations');
        Schema::dropIfExists('rfq_documents');
    }
};
