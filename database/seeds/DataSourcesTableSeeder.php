<?php

use Illuminate\Database\Seeder;

class DataSourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $manual_ds = App\DataSource::create(array('name'=>'manual_input'));
        $ballotpedia_ds = App\DataSource::create(array('name'=>'ballotpedia'));

        App\Models\DataSourcePriority::create(
            array(
                'data_source_id' => $manual_ds->id,
                'priority' => 0,
                'destination_table' => 'elections'
            ));

        
        App\Models\DataSourcePriority::create(
            array(
                'data_source_id' => $ballotpedia_ds->id,
                'priority' => 0,
                'destination_table' => 'elections'
            ));

        App\Models\DataSourcePriority::create(
            array(
                'data_source_id' => $manual_ds->id,
                'priority' => 0,
                'destination_table' => 'candidates'
            ));

        
        App\Models\DataSourcePriority::create(
            array(
                'data_source_id' => $ballotpedia_ds->id,
                'priority' => 0,
                'destination_table' => 'candidates'
            ));
    }
}
