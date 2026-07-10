<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->string('wholesale_status')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('price_list_id');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
            $table->decimal('credit_limit', 12, 2)->nullable()->after('rejection_reason');
            $table->string('payment_terms')->nullable()->after('credit_limit');
            $table->foreignId('assigned_sales_user_id')->nullable()->after('payment_terms')->constrained('users')->nullOnDelete();
            $table->string('wholesale_access_token_hash')->nullable()->after('assigned_sales_user_id');
        });

        Schema::create('wholesale_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('applicant_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('commercial_record')->nullable();
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('requested_price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            $table->text('message')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['business_unit_id', 'status']);
            $table->index(['business_unit_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_applications');

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropConstrainedForeignId('assigned_sales_user_id');
            $table->dropColumn([
                'wholesale_status',
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'credit_limit',
                'payment_terms',
                'wholesale_access_token_hash',
            ]);
        });
    }
};
