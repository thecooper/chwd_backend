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
        $manual_ds = App\DataLayer\DataSource\DataSource::create(array('name'=>'manual_input'));
        $ballotpedia_ds = App\DataLayer\DataSource\DataSource::create(array('name'=>'ballotpedia'));

        App\DataLayer\DataSource\DataSourcePriority::create(
            array(
                'data_source_id' => $manual_ds->id,
                'priority' => 0,
                'destination_table' => 'elections'
            ));

        
        App\DataLayer\DataSource\DataSourcePriority::create(
            array(
                'data_source_id' => $ballotpedia_ds->id,
                'priority' => 0,
                'destination_table' => 'elections'
            ));

        App\DataLayer\DataSource\DataSourcePriority::create(
            array(
                'data_source_id' => $manual_ds->id,
                'priority' => 0,
                'destination_table' => 'candidates'
            ));

        
        App\DataLayer\DataSource\DataSourcePriority::create(
            array(
                'data_source_id' => $ballotpedia_ds->id,
                'priority' => 0,
                'destination_table' => 'candidates'
            ));
    }
}
