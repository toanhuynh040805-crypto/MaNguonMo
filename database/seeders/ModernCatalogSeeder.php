<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModernCatalogSeeder extends Seeder
{
    // Sample prices and inventory only. No live pricing or retail affiliation.
    public function run(): void
    {
        $catalog = [
            'Điện thoại' => [
                ['iPhone 16 Pro 128GB', 24990000, 12],
                ['iPhone 15 128GB', 15690000, 20],
                ['Samsung Galaxy S25 Ultra 256GB', 29490000, 11],
                ['Samsung Galaxy S24 256GB', 15990000, 16],
                ['Google Pixel 9 Pro XL 128GB', 22990000, 9],
                ['Samsung Galaxy Z Fold6 256GB', 33490000, 7],
            ],
            'Laptop' => [
                ['MacBook Air 13 M4 16GB/256GB', 25490000, 14],
                ['MacBook Air 15 M4 16GB/256GB', 31890000, 11],
                ['Microsoft Surface Laptop 7 13.8 inch', 26990000, 8],
            ],
            'Máy tính bảng' => [
                ['iPad Air 13 M2 Wifi 128GB', 19290000, 17],
                ['iPad Gen 10 Wifi 64GB', 8790000, 23],
                ['Samsung Galaxy Tab S9 128GB', 14390000, 12],
            ],
            'Đồng hồ thông minh' => [
                ['Apple Watch Series 10 GPS', 9690000, 14],
                ['Samsung Galaxy Watch7', 5390000, 21],
            ],
            'Âm thanh' => [
                ['Apple AirPods 4', 3290000, 31],
                ['Apple AirPods Pro 2', 5290000, 24],
            ],
            'Máy chơi game' => [
                ['Nintendo Switch OLED', 7290000, 10],
            ],
        ];

        foreach ($catalog as $categoryName => $models) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'description' => 'Danh mục demo thiết bị công nghệ.'],
            );
            foreach ($models as [$name, $price, $stock]) {
                Product::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => 'Thiết bị công nghệ thực tế có ảnh chụp tham khảo ghi rõ nguồn. Giá bán, biến thể dung lượng và số lượng tồn kho trên trang đều là dữ liệu demo của đồ án.',
                        'price' => $price,
                        'stock' => $stock,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
