<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorías principales
        $categories = [
            [
                'cate_nmname' => 'Technology',
                'cate_cdslug' => 'technology',
                'cate_idpare' => null,
            ],
            [
                'cate_nmname' => 'Business',
                'cate_cdslug' => 'business',
                'cate_idpare' => null,
            ],
            [
                'cate_nmname' => 'Lifestyle',
                'cate_cdslug' => 'lifestyle',
                'cate_idpare' => null,
            ],
            [
                'cate_nmname' => 'Entertainment',
                'cate_cdslug' => 'entertainment',
                'cate_idpare' => null,
            ],
            [
                'cate_nmname' => 'Health',
                'cate_cdslug' => 'health',
                'cate_idpare' => null,
            ],
        ];

        $createdCategories = [];

        foreach ($categories as $category) {
            $createdCategory = Category::firstOrCreate(
                ['cate_cdslug' => $category['cate_cdslug']],
                [
                    'cate_nmname' => $category['cate_nmname'],
                    'cate_idpare' => $category['cate_idpare'],
                ]
            );
            $createdCategories[$category['cate_cdslug']] = $createdCategory;
        }

        // Subcategorías de Technology
        $techSubcategories = [
            [
                'cate_nmname' => 'Web Development',
                'cate_cdslug' => 'web-development',
                'parent_slug' => 'technology',
            ],
            [
                'cate_nmname' => 'Mobile Apps',
                'cate_cdslug' => 'mobile-apps',
                'parent_slug' => 'technology',
            ],
            [
                'cate_nmname' => 'Artificial Intelligence',
                'cate_cdslug' => 'artificial-intelligence',
                'parent_slug' => 'technology',
            ],
            [
                'cate_nmname' => 'Cybersecurity',
                'cate_cdslug' => 'cybersecurity',
                'parent_slug' => 'technology',
            ],
        ];

        foreach ($techSubcategories as $subcategory) {
            Category::firstOrCreate(
                ['cate_cdslug' => $subcategory['cate_cdslug']],
                [
                    'cate_nmname' => $subcategory['cate_nmname'],
                    'cate_idpare' => $createdCategories[$subcategory['parent_slug']]->cate_idcate,
                ]
            );
        }

        // Subcategorías de Business
        $businessSubcategories = [
            [
                'cate_nmname' => 'Marketing',
                'cate_cdslug' => 'marketing',
                'parent_slug' => 'business',
            ],
            [
                'cate_nmname' => 'Finance',
                'cate_cdslug' => 'finance',
                'parent_slug' => 'business',
            ],
            [
                'cate_nmname' => 'Entrepreneurship',
                'cate_cdslug' => 'entrepreneurship',
                'parent_slug' => 'business',
            ],
        ];

        foreach ($businessSubcategories as $subcategory) {
            Category::firstOrCreate(
                ['cate_cdslug' => $subcategory['cate_cdslug']],
                [
                    'cate_nmname' => $subcategory['cate_nmname'],
                    'cate_idpare' => $createdCategories[$subcategory['parent_slug']]->cate_idcate,
                ]
            );
        }

        // Subcategorías de Lifestyle
        $lifestyleSubcategories = [
            [
                'cate_nmname' => 'Travel',
                'cate_cdslug' => 'travel',
                'parent_slug' => 'lifestyle',
            ],
            [
                'cate_nmname' => 'Food & Cooking',
                'cate_cdslug' => 'food-cooking',
                'parent_slug' => 'lifestyle',
            ],
            [
                'cate_nmname' => 'Fitness',
                'cate_cdslug' => 'fitness',
                'parent_slug' => 'lifestyle',
            ],
        ];

        foreach ($lifestyleSubcategories as $subcategory) {
            Category::firstOrCreate(
                ['cate_cdslug' => $subcategory['cate_cdslug']],
                [
                    'cate_nmname' => $subcategory['cate_nmname'],
                    'cate_idpare' => $createdCategories[$subcategory['parent_slug']]->cate_idcate,
                ]
            );
        }

        $this->command->info('✅ Categories seeded successfully');
    }
}
