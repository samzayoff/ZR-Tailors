<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks in a DB-agnostic way
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // ── Garment types ──────────────────────────────────────────────
        DB::table('garment_types')->truncate();
        DB::table('garment_types')->insert([
            ['id' => 1, 'code' => 'kameez',    'name_en' => 'Kameez',    'name_ur' => 'قمیض',    'icon' => 'i-kameez', 'sort_order' => 1],
            ['id' => 2, 'code' => 'waistcoat', 'name_en' => 'Waistcoat', 'name_ur' => 'واسکٹ',   'icon' => 'i-vest',   'sort_order' => 2],
        ]);

        // ── Measurement points ─────────────────────────────────────────
        DB::table('measurement_points')->truncate();
        DB::table('measurement_points')->insert([
            // Kameez (garment_type_id = 1)
            ['garment_type_id' => 1, 'code' => 'length',   'name_en' => 'Length',   'name_ur' => 'لمبائی', 'icon' => 'i-len',      'sort_order' => 1],
            ['garment_type_id' => 1, 'code' => 'shoulder', 'name_en' => 'Shoulder', 'name_ur' => 'تیرہ',   'icon' => 'i-shoulder', 'sort_order' => 2],
            ['garment_type_id' => 1, 'code' => 'sleeve',   'name_en' => 'Sleeve',   'name_ur' => 'بازو',   'icon' => 'i-sleeve',   'sort_order' => 3],
            ['garment_type_id' => 1, 'code' => 'chest',    'name_en' => 'Chest',    'name_ur' => 'چھاتی',  'icon' => 'i-chest',    'sort_order' => 4],
            ['garment_type_id' => 1, 'code' => 'waist',    'name_en' => 'Waist',    'name_ur' => 'کمر',    'icon' => 'i-waist',    'sort_order' => 5],
            ['garment_type_id' => 1, 'code' => 'daman',    'name_en' => 'Daman',    'name_ur' => 'دامن',   'icon' => 'i-daman',    'sort_order' => 6],
            ['garment_type_id' => 1, 'code' => 'collar',   'name_en' => 'Collar',   'name_ur' => 'کالر',   'icon' => 'i-collar',   'sort_order' => 7],
            ['garment_type_id' => 1, 'code' => 'shalwar',  'name_en' => 'Shalwar',  'name_ur' => 'شلوار',  'icon' => 'i-shalwar',  'sort_order' => 8],
            ['garment_type_id' => 1, 'code' => 'pancha',   'name_en' => 'Pancha',   'name_ur' => 'پانچہ',  'icon' => 'i-pancha',   'sort_order' => 9],
            // Waistcoat (garment_type_id = 2)
            ['garment_type_id' => 2, 'code' => 'length',   'name_en' => 'Length',   'name_ur' => 'لمبائی', 'icon' => 'i-len',      'sort_order' => 1],
            ['garment_type_id' => 2, 'code' => 'shoulder', 'name_en' => 'Shoulder', 'name_ur' => 'تیرہ',   'icon' => 'i-shoulder', 'sort_order' => 2],
            ['garment_type_id' => 2, 'code' => 'sleeve',   'name_en' => 'Sleeve',   'name_ur' => 'بازو',   'icon' => 'i-sleeve',   'sort_order' => 3],
            ['garment_type_id' => 2, 'code' => 'chest',    'name_en' => 'Chest',    'name_ur' => 'چھاتی',  'icon' => 'i-chest',    'sort_order' => 4],
            ['garment_type_id' => 2, 'code' => 'waist',    'name_en' => 'Waist',    'name_ur' => 'کمر',    'icon' => 'i-waist',    'sort_order' => 5],
            ['garment_type_id' => 2, 'code' => 'bais',     'name_en' => 'Bais',     'name_ur' => 'بیس',    'icon' => 'i-bais',     'sort_order' => 6],
            ['garment_type_id' => 2, 'code' => 'collar',   'name_en' => 'Collar',   'name_ur' => 'کالر',   'icon' => 'i-collar',   'sort_order' => 7],
        ]);

        // ── Design options ─────────────────────────────────────────────
        DB::table('design_options')->truncate();
        DB::table('design_options')->insert([
            // Stitch type
            ['category' => 'stitch',    'code' => 'silky_single', 'name_en' => 'Silky thread single', 'name_ur' => 'سلکی تار سنگل', 'icon' => null, 'is_default' => 0, 'sort_order' => 1],
            ['category' => 'stitch',    'code' => 'silky_double', 'name_en' => 'Silky thread double', 'name_ur' => 'سلکی تار ڈبل',  'icon' => null, 'is_default' => 0, 'sort_order' => 2],
            ['category' => 'stitch',    'code' => 'chowka',       'name_en' => 'Chowka stitch',       'name_ur' => 'چوکا سلائی',   'icon' => null, 'is_default' => 0, 'sort_order' => 3],
            ['category' => 'stitch',    'code' => 'double',       'name_en' => 'Double stitch',       'name_ur' => 'ڈبل سلائی',    'icon' => null, 'is_default' => 0, 'sort_order' => 4],
            ['category' => 'stitch',    'code' => 'zanjeeri',     'name_en' => 'Zanjeeri stitch',     'name_ur' => 'زنجیری سلائی', 'icon' => null, 'is_default' => 1, 'sort_order' => 5],
            ['category' => 'stitch',    'code' => 'pair',         'name_en' => 'Pair stitch',         'name_ur' => 'پیر سلائی',    'icon' => null, 'is_default' => 0, 'sort_order' => 6],
            // Cuff & Kaaj
            ['category' => 'cuff_kaaj', 'code' => 'cuff_1pleat',  'name_en' => 'Cuff one pleat',     'name_ur' => 'کف میں ایک پلیٹ', 'icon' => 'i-cuff', 'is_default' => 1, 'sort_order' => 1],
            ['category' => 'cuff_kaaj', 'code' => 'cuff_nopleat', 'name_en' => 'No cuff pleat',      'name_ur' => 'کف پلیٹ نہیں',    'icon' => 'i-cuff', 'is_default' => 0, 'sort_order' => 2],
            ['category' => 'cuff_kaaj', 'code' => 'chaak_kaaj',   'name_en' => 'Chaak butti kaaj',   'name_ur' => 'چاک بٹی کاج',     'icon' => null,     'is_default' => 1, 'sort_order' => 3],
            ['category' => 'cuff_kaaj', 'code' => 'kaaj_5',       'name_en' => '5 kaaj on butti',    'name_ur' => 'بٹی میں 5 کاج',   'icon' => null,     'is_default' => 1, 'sort_order' => 4],
            // Extras
            ['category' => 'extra',     'code' => 'shalwar_pocket','name_en' => 'Shalwar pocket',    'name_ur' => 'شلوار جیب',   'icon' => null,    'is_default' => 1, 'sort_order' => 1],
            ['category' => 'extra',     'code' => 'btn_from_shop', 'name_en' => 'Buttons from shop', 'name_ur' => 'بٹن دکان سے', 'icon' => null,    'is_default' => 0, 'sort_order' => 2],
            ['category' => 'extra',     'code' => 'no_name',       'name_en' => 'No name tag',       'name_ur' => 'نام نہیں',    'icon' => null,    'is_default' => 0, 'sort_order' => 3],
            ['category' => 'extra',     'code' => 'make_drawing',  'name_en' => 'Make drawing',      'name_ur' => 'ڈرائنگ کرنا', 'icon' => 'i-pen', 'is_default' => 0, 'sort_order' => 4],
            // Collar & Cuff style
            ['category' => 'style',     'code' => 'khal_been',     'name_en' => 'Khal been collar',  'name_ur' => 'خل بین',    'icon' => 'i-collar', 'is_default' => 1, 'sort_order' => 1],
            ['category' => 'style',     'code' => 'half_cuff',     'name_en' => 'Half cuff',         'name_ur' => 'ھاف کف',    'icon' => 'i-cuff',   'is_default' => 0, 'sort_order' => 2],
            ['category' => 'style',     'code' => 'single_bais',   'name_en' => 'Single bais',       'name_ur' => 'شنگل بیس',  'icon' => 'i-bais',   'is_default' => 1, 'sort_order' => 3],
            ['category' => 'style',     'code' => 'round_side',    'name_en' => 'Round side',        'name_ur' => 'گول سھے',   'icon' => 'i-daman',  'is_default' => 0, 'sort_order' => 4],
            ['category' => 'style',     'code' => 'seedha',        'name_en' => 'Straight',          'name_ur' => 'سیدھا',     'icon' => 'i-len',    'is_default' => 1, 'sort_order' => 5],
            ['category' => 'style',     'code' => 'zail_patti',    'name_en' => 'Zail patti',        'name_ur' => 'ذیل پٹی',   'icon' => 'i-vest',   'is_default' => 0, 'sort_order' => 6],
            ['category' => 'style',     'code' => 'round_sleeve',  'name_en' => 'Round sleeve',      'name_ur' => 'گول بازو',  'icon' => 'i-sleeve', 'is_default' => 0, 'sort_order' => 7],
            ['category' => 'style',     'code' => 'cup_sleeve',    'name_en' => 'Cup sleeve',        'name_ur' => 'کپ بازو',   'icon' => 'i-sleeve', 'is_default' => 0, 'sort_order' => 8],
            // Buttons
            ['category' => 'button',    'code' => 'double_chaak',  'name_en' => 'Double chaak',      'name_ur' => 'ڈبل چاک',   'icon' => null, 'is_default' => 1, 'sort_order' => 1],
            ['category' => 'button',    'code' => 'two_button',    'name_en' => 'Two buttons',       'name_ur' => 'دو بٹن',    'icon' => null, 'is_default' => 1, 'sort_order' => 2],
            ['category' => 'button',    'code' => 'three_button',  'name_en' => 'Three buttons',     'name_ur' => 'تین بٹن',   'icon' => null, 'is_default' => 0, 'sort_order' => 3],
        ]);

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command->info('Reference data seeded successfully.');
    }
}
