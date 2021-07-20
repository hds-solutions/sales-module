<?php

namespace HDSSolutions\Laravel\Seeders;

use HDSSolutions\Laravel\Models\Setting;
use Illuminate\Database\Seeder;

class SalesSettingsSeeder extends Seeder {

    public function run() {
        // default settings
        $settings = [
            // documents control
            'validate-orders-age'   => [ 'type' => 'boolean', 'value' => true ],
        ];

        // create settings
        $data = [];
        foreach ($settings as $key => $value)
            if (is_array($value))
                $data[] = [ 'name' => $key ] + $value;
            else
                $data[] = [
                    'name'  => $key,
                    'type'  => 'string',
                    'value' => $value,
                ];

        // insert settings
        Setting::insert($data);
    }

}
