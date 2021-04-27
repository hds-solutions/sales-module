<?php

use HDSSolutions\Finpar\Blueprints\BaseBlueprint as Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration {
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
        $schema->create('orders', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('Company');
            $table->foreignTo('Branch');
            $table->foreignTo('Currency');
            $table->foreignTo('Employee');
            $table->morphable('partner');
            $table->unsignedInteger('address_id')->nullable(); // TODO: Link to Partner.address
            $table->timestamp('transacted_at')->useCurrent();
            $table->string('document_number');
            $table->boolean('is_purchase')->default(false);
            $table->boolean('is_invoiced')->default(false);
            $table->amount('total')->default(0);
            // use table as document
            $table->asDocument();
        });

        $schema->create('order_lines', function(Blueprint $table) {
            $table->id();
            $table->foreignTo('Order');
            $table->foreignTo('Currency');
            $table->foreignTo('Employee');
            $table->foreignTo('Product');
            $table->foreignTo('Variant')->nullable();
            $table->unique([ 'order_id', 'product_id', 'variant_id' ]);
            $table->amount('price_reference');
            $table->amount('price_ordered');
            $table->unsignedInteger('quantity_ordered');
            $table->unsignedInteger('quantity_invoiced')->nullable();
            $table->amount('total');
            $table->boolean('is_invoiced')->default(false);
            $table->unsignedInteger('conversion_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_lines');
        Schema::dropIfExists('orders');
    }

}
