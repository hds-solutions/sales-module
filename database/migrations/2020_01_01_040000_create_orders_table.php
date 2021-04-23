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
            $table->morphable('partner');
            $table->foreignTo('Company');
            $table->foreignTo('Branch');
            $table->foreignTo('Currency');
            $table->integer('address_id')->nullable();
            $table->integer('conversion_rate')->default(1);
            $table->date('transaction_date');
            $table->amount('total');
            $table->string('invoice_number')->nullable();
            $table->string('stamping')->nullable();
            $table->boolean('is_purchase')->default(false);

            $table->asDocument();

        });

        $schema->create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignTo('Order');
            $table->foreignTo('Product');
            $table->foreignTo('Variant')->nullable();
            $table->amount('original_price');
            $table->amount('price');
            $table->integer('quantity');
            $table->amount('total');
            $table->foreignTo('Currency');
            $table->integer('conversion_rate')->default(1);
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
