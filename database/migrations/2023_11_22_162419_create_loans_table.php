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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum("type", ["personal", "business", 'property']);
            $table->foreignId("user_id")->references("id")->on("users");
            $table->boolean("is_active")->default(true);
            $table->foreignId("company_id")->references("id")->on("companies");
            $table->enum("payment_frequency", ["monthly", "quarterly", "yearly"]);
            $table->string("amount")->nullable();
            $table->string("term")->nullable();
            $table->string("interest")->nullable();
            $table->string("balance")->nullable();
            $table->string("monthly_payment")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
