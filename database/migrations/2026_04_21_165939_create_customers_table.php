<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('address')->nullable();
            $table->string('ward')->nullable();
            $table->string('district')->nullable();

            $table->string('customer_type')->nullable();
            $table->string('company')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('identity_number')->nullable();

            $table->date('birthday')->nullable();
            $table->string('gender')->nullable();
            $table->string('facebook')->nullable();

            $table->string('customer_group')->nullable();
            $table->text('note')->nullable();

            $table->string('created_by')->nullable();
            $table->string('branch_created')->nullable();

            $table->timestamp('last_transaction_at')->nullable();

            $table->decimal('current_debt', 15, 2)->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('total_spent_net', 15, 2)->default(0);

            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
