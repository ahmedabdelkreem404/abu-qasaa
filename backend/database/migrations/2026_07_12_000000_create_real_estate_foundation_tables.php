<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createOrExtendProjects();
        $this->createOrExtendProperties();
        $this->createOrExtendUnits();

        if (! Schema::hasTable('real_estate_leads')) {
            Schema::create('real_estate_leads', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('real_estate_projects')->nullOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('property_units')->nullOnDelete();
                $table->string('source')->default('public');
                $table->string('name');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('preferred_contact_method')->nullable();
                $table->decimal('budget_min', 14, 2)->nullable();
                $table->decimal('budget_max', 14, 2)->nullable();
                $table->text('message')->nullable();
                $table->string('status')->default('new');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('next_follow_up_at')->nullable();
                $table->json('metadata_json')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['business_unit_id', 'status']);
            });
        }

        if (! Schema::hasTable('property_appointments')) {
            Schema::create('property_appointments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lead_id')->constrained('real_estate_leads')->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('real_estate_projects')->nullOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('property_units')->nullOnDelete();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('scheduled_at');
                $table->unsignedInteger('duration_minutes')->default(60);
                $table->string('location')->nullable();
                $table->string('status')->default('scheduled');
                $table->text('notes')->nullable();
                $table->text('outcome')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('property_reservations')) {
            Schema::create('property_reservations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lead_id')->nullable()->constrained('real_estate_leads')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('project_id')->constrained('real_estate_projects')->cascadeOnDelete();
                $table->foreignId('unit_id')->constrained('property_units')->cascadeOnDelete();
                $table->string('reservation_number')->unique();
                $table->string('status')->default('pending');
                $table->decimal('reservation_amount', 14, 2)->nullable();
                $table->string('currency', 3)->default('EGP');
                $table->timestamp('reserved_at');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['business_unit_id', 'status']);
                $table->index(['unit_id', 'status']);
            });
        }

        if (! Schema::hasTable('installment_plans')) {
            Schema::create('installment_plans', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('real_estate_projects')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('property_units')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('down_payment', 14, 2)->default(0);
                $table->unsignedInteger('installment_count')->default(1);
                $table->string('frequency')->default('monthly');
                $table->decimal('installment_amount', 14, 2)->default(0);
                $table->string('currency', 3)->default('EGP');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('installment_plan_items')) {
            Schema::create('installment_plan_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('sequence');
                $table->string('label')->nullable();
                $table->decimal('amount', 14, 2);
                $table->unsignedInteger('due_offset_days')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plan_items');
        Schema::dropIfExists('installment_plans');
        Schema::dropIfExists('property_reservations');
        Schema::dropIfExists('property_appointments');
        Schema::dropIfExists('real_estate_leads');
    }

    private function createOrExtendProjects(): void
    {
        if (! Schema::hasTable('real_estate_projects')) {
            Schema::create('real_estate_projects', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
                $table->string('name_ar');
                $table->string('name_en')->nullable();
                $table->string('slug');
                $table->string('project_code');
                $table->string('status')->default('draft');
                $table->string('project_type');
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        Schema::table('real_estate_projects', function (Blueprint $table): void {
            foreach ([
                'name_ar' => fn () => $table->string('name_ar')->nullable()->after('business_unit_id'),
                'name_en' => fn () => $table->string('name_en')->nullable()->after('name_ar'),
                'project_code' => fn () => $table->string('project_code')->nullable()->after('slug'),
                'project_type' => fn () => $table->string('project_type')->nullable()->after('status'),
                'developer_name' => fn () => $table->string('developer_name')->nullable(),
                'description_ar' => fn () => $table->text('description_ar')->nullable(),
                'description_en' => fn () => $table->text('description_en')->nullable(),
                'address' => fn () => $table->string('address')->nullable(),
                'city' => fn () => $table->string('city')->nullable(),
                'governorate' => fn () => $table->string('governorate')->nullable(),
                'featured_image' => fn () => $table->string('featured_image')->nullable(),
                'gallery_json' => fn () => $table->json('gallery_json')->nullable(),
                'amenities_json' => fn () => $table->json('amenities_json')->nullable(),
                'delivery_date' => fn () => $table->date('delivery_date')->nullable(),
                'starting_price' => fn () => $table->decimal('starting_price', 14, 2)->nullable(),
                'currency' => fn () => $table->string('currency', 3)->default('EGP'),
                'is_featured' => fn () => $table->boolean('is_featured')->default(false),
            ] as $column => $add) {
                if (! Schema::hasColumn('real_estate_projects', $column)) {
                    $add();
                }
            }
            if (! Schema::hasColumn('real_estate_projects', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function createOrExtendProperties(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            foreach ([
                'name' => fn () => $table->string('name')->nullable(),
                'code' => fn () => $table->string('code')->nullable(),
                'property_type' => fn () => $table->string('property_type')->nullable(),
                'floors_count' => fn () => $table->unsignedInteger('floors_count')->nullable(),
                'metadata_json' => fn () => $table->json('metadata_json')->nullable(),
                'description' => fn () => $table->text('description')->nullable(),
            ] as $column => $add) {
                if (! Schema::hasColumn('properties', $column)) {
                    $add();
                }
            }
            if (! Schema::hasColumn('properties', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function createOrExtendUnits(): void
    {
        Schema::table('property_units', function (Blueprint $table): void {
            foreach ([
                'project_id' => fn () => $table->foreignId('project_id')->nullable()->after('business_unit_id')->constrained('real_estate_projects')->nullOnDelete(),
                'unit_code' => fn () => $table->string('unit_code')->nullable(),
                'unit_type' => fn () => $table->string('unit_type')->nullable(),
                'floor' => fn () => $table->integer('floor')->nullable(),
                'bedrooms' => fn () => $table->unsignedInteger('bedrooms')->nullable(),
                'bathrooms' => fn () => $table->unsignedInteger('bathrooms')->nullable(),
                'area' => fn () => $table->decimal('area', 12, 2)->nullable(),
                'currency' => fn () => $table->string('currency', 3)->default('EGP'),
                'down_payment' => fn () => $table->decimal('down_payment', 14, 2)->nullable(),
                'installment_months' => fn () => $table->unsignedInteger('installment_months')->nullable(),
                'finishing_type' => fn () => $table->string('finishing_type')->nullable(),
                'view_type' => fn () => $table->string('view_type')->nullable(),
                'featured_image' => fn () => $table->string('featured_image')->nullable(),
                'gallery_json' => fn () => $table->json('gallery_json')->nullable(),
                'specs_json' => fn () => $table->json('specs_json')->nullable(),
                'is_featured' => fn () => $table->boolean('is_featured')->default(false),
            ] as $column => $add) {
                if (! Schema::hasColumn('property_units', $column)) {
                    $add();
                }
            }
            if (! Schema::hasColumn('property_units', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }
};
