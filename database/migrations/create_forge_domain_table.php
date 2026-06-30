<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forge_domains', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('hostname')->unique();
            $table->string('kind');
            $table->string('status')->default('pending');
            $table->string('verification_method')->nullable();
            $table->string('verification_token')->nullable();
            $table->string('dns_target')->nullable();
            $table->unsignedBigInteger('forge_domain_id')->nullable();
            $table->timestamp('ssl_expires_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forge_domains');
    }
};
