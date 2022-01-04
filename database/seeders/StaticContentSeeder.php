<?php

use Illuminate\Database\Seeder;

class StaticContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = new \App\StaticContent([
            'name'                => 'about',
            'about'               => 'Nothing here yet...',
            'terms'               => 'Nothing here yet...',
            'privacy'             => 'Nothing here yet...',
            'ach'                 => 'Nothing here yet...',
            'become_followee'     => 'Nothing here yet...',
            'become_followhost'   => 'Nothing here yet...',
            'community_standards' => 'Nothing here yet...',
        ]);
        $item->save();

        $item = new \App\StaticContent([
            'name'                => 'sales_rep_agreement',
            'agreement'           => 'Nothing here yet...',
        ]);
        $item->save();
    }
}
