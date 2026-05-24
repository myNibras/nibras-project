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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5)->default('USD');
            $table->string('status')->default('pending');
            $table->string('order_id')->nullable()->unique();
            $table->string('payment_method')->nullable();
            $table->string('language', 5)->default('en');
            $table->boolean('send_invoice')->default(false);
            $table->string('name_on_card')->nullable();
            $table->string('bank_issuer')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string("title");
            $table->string("title_en");
            $table->string("short_description");
            $table->string("short_description_en");
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
