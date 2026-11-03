<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Each stay gets its own page (/accommodations/{slug}) with a photo gallery.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->json('gallery')->nullable()->after('image');
        });

        // Give the stays that already exist a web address.
        $taken = [];
        foreach (DB::table('accommodations')->orderBy('id')->get(['id', 'name']) as $row) {
            $base = Str::slug($row->name) ?: 'stay';
            $slug = $base;
            for ($i = 2; in_array($slug, $taken, true); $i++) {
                $slug = "{$base}-{$i}";
            }
            $taken[] = $slug;

            DB::table('accommodations')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'gallery']);
        });
    }
};
