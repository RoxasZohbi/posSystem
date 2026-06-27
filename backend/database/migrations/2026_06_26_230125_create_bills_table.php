<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('billed_by')->constrained('users');
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'synced', 'voided'])->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('bills'); }
};
