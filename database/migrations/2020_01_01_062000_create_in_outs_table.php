<?php

use HDSSolutions\Finpar\Blueprints\BaseBlueprint as Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInOutsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // get schema builder
        $schema = DB::getSchemaBuilder();

        // replace blueprint
        $schema->blueprintResolver(fn($table, $callback) => new Blueprint($table, $callback));

        // create table
        $schema->create('in_outs', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('Company');
            $table->foreignTo('Branch');
            $table->foreignTo('Warehouse');
            $table->foreignTo('Employee');
            $table->morphable('partner');
            $table->foreignTo('Invoice')->nullable();
            $table->timestamp('transacted_at')->useCurrent();
            $table->string('stamping')->nullable();
            $table->string('document_number');
            $table->boolean('is_purchase')->default(false);
            $table->boolean('is_material_return')->default(false);
            $table->boolean('is_complete')->default(false);
            // use table as document
            $table->asDocument();
        });

        $schema->create('in_out_lines', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('InOut');
            $table->foreignTo('OrderLine');
            $table->foreignTo('Product');
            $table->foreignTo('Variant')->nullable();
            $table->unique([ 'in_out_id', 'product_id', 'variant_id' ]);
            $table->foreignTo('Locator')->nullable();
            $table->unsignedInteger('quantity_ordered');
            $table->unsignedInteger('quantity_movement');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('in_out_lines');
        Schema::dropIfExists('in_outs');
    }

}
