<?php

use Illuminate\Database\Seeder;

class ExperienceCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = collect([]);

        $items->push(new \App\FollowoutCategory([ 'name' => 'Arts and Entertainment' ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Beauty'                 ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Birthday'               ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Business'               ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Celebration'            ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Enthusiast'             ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Faith'                  ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Fashion'                ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Food and Drink'         ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Giving'                 ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Health and Fitness'     ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Motivational'           ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Pet'                    ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Political'              ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Retail'                 ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Scholar'                ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Science and Technology' ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Social'                 ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Sports'                 ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Travel'                 ]));
        $items->push(new \App\FollowoutCategory([ 'name' => 'Uncategorized'          ]));

        foreach ($items as $item) {
            $item->save();
        }
    }
}
