<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class HeaderMenuSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::query()->firstOrFail()->update(['header_menu' => [
            ['label_en' => 'Home', 'label_my' => 'မူလစာမျက်နှာ', 'label_th' => 'หน้าหลัก', 'type' => 'Pages', 'url' => '/', 'is_active' => true],
            ['label_en' => 'About', 'label_my' => 'ကျွန်ုပ်တို့အကြောင်း', 'label_th' => 'เกี่ยวกับหน้า', 'type' => 'Pages', 'url' => '/about', 'is_active' => true],
            [
                'label_en' => 'Solutions', 'label_my' => 'ဝန်ဆောင်မှုများ', 'label_th' => 'บริการ',
                'type' => 'Group', 'url' => '/services', 'is_active' => true,
                'sub_menu' => [
                    ['label_en' => 'Road Freight', 'label_my' => 'ကုန်လမ်းပို့ဆောင်ရေး', 'label_th' => 'การขนส่งระหว่างประเทศ', 'type' => 'URL', 'url' => '/solutions/roadfreight', 'is_active' => true],
                    ['label_en' => 'Sea Freight', 'label_my' => 'ပင်လယ်ရေကြောင်းပို့ဆောင်ရေး', 'label_th' => 'การขนส่งทางรางในท้องถิ่น', 'type' => 'URL', 'url' => '/solutions/sea', 'is_active' => true],
                    ['label_en' => 'Air Freight', 'label_my' => 'လေကြောင်းပို့ဆောင်ရေး', 'label_th' => 'การขนส่งทางทะเลที่ปลอดภัย', 'type' => 'URL', 'url' => '/solutions/air', 'is_active' => true],
                    ['label_en' => 'Storage Services', 'label_my' => 'စတိုး သိမ်းဆည်းရေး', 'label_th' => 'บริการจัดเก็บสินค้า', 'type' => 'URL', 'url' => '/solutions/storage', 'is_active' => true],
                    ['label_en' => 'Customs Clearence', 'label_my' => 'အခွန်ကင်းလွတ်ခွင့်', 'label_th' => 'การผ่านพิธีการศุลกากร', 'type' => 'URL', 'url' => '/solutions/custom-clearance', 'is_active' => true],
                    ['label_en' => 'Moving & Relocation', 'label_my' => 'နေရာပြောင်းရွှေ့ခြင်း', 'label_th' => 'การขนย้ายและการย้ายที่อยู่', 'type' => 'URL', 'url' => '/solutions/moving-relocation', 'is_active' => true],
                ],
            ],
            ['label_en' => 'Contact', 'label_my' => 'ဆက်သွယ်ရန်', 'label_th' => 'ติดต่อ', 'type' => 'Pages', 'url' => '/contact', 'is_active' => true],
        ]]);
    }
}
