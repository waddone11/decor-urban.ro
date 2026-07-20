<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'feed_enabled')) {
                $table->boolean('feed_enabled')->default(false)->after('availability');
            }
            if (! Schema::hasColumn('products', 'quote_only')) {
                $table->boolean('quote_only')->default(true)->after('feed_enabled');
            }
            if (! Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('products', 'currency')) {
                $table->string('currency', 3)->default('RON')->after('sale_price');
            }
            if (! Schema::hasColumn('products', 'condition')) {
                $table->string('condition')->default('new')->after('availability');
            }
            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->default('Decor Urban')->after('condition');
            }
            if (! Schema::hasColumn('products', 'gtin')) {
                $table->string('gtin')->nullable()->after('brand');
            }
            if (! Schema::hasColumn('products', 'mpn')) {
                $table->string('mpn')->nullable()->after('gtin');
            }
            if (! Schema::hasColumn('products', 'google_product_category')) {
                $table->string('google_product_category')->nullable()->after('mpn');
            }
            if (! Schema::hasColumn('products', 'facebook_product_category')) {
                $table->string('facebook_product_category')->nullable()->after('google_product_category');
            }
            if (! Schema::hasColumn('products', 'shipping_weight')) {
                $table->string('shipping_weight')->nullable()->after('facebook_product_category');
            }
            if (! Schema::hasColumn('products', 'minimum_order_quantity')) {
                $table->unsignedInteger('minimum_order_quantity')->nullable()->after('shipping_weight');
            }
            foreach (range(0, 4) as $i) {
                if (! Schema::hasColumn('products', 'custom_label_'.$i)) {
                    $table->string('custom_label_'.$i)->nullable()->after('minimum_order_quantity');
                }
            }
            if (! Schema::hasColumn('products', 'feed_updated_at')) {
                $table->timestamp('feed_updated_at')->nullable()->after('custom_label_4');
            }
            if (! Schema::hasColumn('products', 'show_in_google_business')) {
                $table->boolean('show_in_google_business')->default(false)->after('feed_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'feed_enabled',
                'quote_only',
                'sale_price',
                'currency',
                'condition',
                'brand',
                'gtin',
                'mpn',
                'google_product_category',
                'facebook_product_category',
                'shipping_weight',
                'minimum_order_quantity',
                'custom_label_0',
                'custom_label_1',
                'custom_label_2',
                'custom_label_3',
                'custom_label_4',
                'feed_updated_at',
                'show_in_google_business',
            ]);
        });
    }
};
