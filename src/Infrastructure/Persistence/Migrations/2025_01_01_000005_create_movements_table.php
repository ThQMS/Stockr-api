<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table): void {
            // Movements keep an auto-increment identity (current ID strategy).
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->comment('in, out, adjustment, transfer');
            $table->integer('quantity')->comment('Always positive. Direction determined by type.');
            $table->unsignedInteger('quantity_before')->comment('Snapshot before movement. Enables full audit.');
            $table->unsignedInteger('quantity_after')->comment('Snapshot after movement.');
            $table->text('notes')->nullable();
            $table->string('reference_code', 100)->nullable()->comment('External reference: invoice, PO number, etc.');
            $table->timestamp('moved_at')->comment('Actual movement time, may differ from created_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'moved_at']);
            $table->index(['workspace_id', 'moved_at']);
            $table->index(['workspace_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
