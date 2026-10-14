<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class ServicePageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'slug' => 'air-freight',
                'titles' => ['en' => 'Air Freight', 'my' => 'လေကြောင်းပို့ဆောင်ရေး', 'th' => 'ขนส่งทางอากาศ'],
                'body' => ['en' => 'Fast and dependable delivery for time-sensitive shipments.', 'my' => 'အချိန်အရေးကြီးသောကုန်ပစ္စည်းများအတွက် မြန်ဆန်ပြီး ယုံကြည်စိတ်ချရသော ပို့ဆောင်မှု။', 'th' => 'รวดเร็วและเชื่อถือได้สำหรับสินค้าที่ต้องการความเร่งด่วน'],
            ],
            [
                'slug' => 'sea-freight',
                'titles' => ['en' => 'Sea Freight', 'my' => 'ရေကြောင်းပို့ဆောင်ရေး', 'th' => 'ขนส่งทางเรือ'],
                'body' => ['en' => 'Cost-effective FCL and LCL solutions across global ports.', 'my' => 'ကမ္ဘာတစ်ဝှမ်းရှိ ဆိပ်ကမ်းများသို့ ကုန်ကျစရိတ်သက်သာသော FCL နှင့် LCL ဝန်ဆောင်မှုများ။', 'th' => 'บริการ FCL และ LCL ที่คุ้มค่าผ่านท่าเรือทั่วโลก'],
            ],
            [
                'slug' => 'land-transport',
                'titles' => ['en' => 'Land Transport', 'my' => 'ကုန်းလမ်းပို့ဆောင်ရေး', 'th' => 'ขนส่งทางบก'],
                'body' => ['en' => 'Flexible overland movement and local delivery.', 'my' => 'နယ်စပ်ဖြတ်ကျော်နှင့် ပြည်တွင်းပို့ဆောင်မှုအတွက် လိုက်လျောညီထွေရှိသော ဝန်ဆောင်မှု။', 'th' => 'การขนส่งข้ามพรมแดนและจัดส่งภายในประเทศที่ยืดหยุ่น'],
            ],
        ] as $service) {
            $translations = collect($service['titles'])->mapWithKeys(fn (string $title, string $locale) => [$locale => [
                'title' => $title.' | West East Freight',
                'meta_title' => $title.' | West East Freight',
                'meta_description' => $service['body'][$locale],
            ]])->all();

            $blocks = [[
                'type' => 'content',
                'translations' => collect($service['titles'])->mapWithKeys(fn (string $title, string $locale) => [$locale => [
                    'eyebrow' => 'West East Freight',
                    'heading' => $title,
                    'body' => '<p>'.$service['body'][$locale].'</p>',
                ]])->all(),
            ]];

            Page::query()->firstOrCreate(
                ['slug' => $service['slug']],
                ['page_type' => 'service', 'status' => 'published', 'translations' => $translations, 'blocks' => $blocks],
            );
        }
    }
}
