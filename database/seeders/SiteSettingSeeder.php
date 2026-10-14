<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $logo = 'site/logo/west-east-freight.png';
        if (! Storage::disk('public')->exists($logo)) {
            Storage::disk('public')->put($logo, file_get_contents(public_path('assets/img/west-east-freight-meta.png')));
        }

        $settings = SiteSetting::query()->firstOrCreate([], [
            'header_logo' => $logo,
            'brand_name' => 'WEST EAST FREIGHT',
            'brand_mark' => 'WE',
            'header_menu' => [
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
            ],
            'footer_menu' => [],
            'footer_description_en' => 'Your trusted partner for worldwide freight movement.',
            'footer_description_my' => 'ကမ္ဘာတစ်ဝှမ်း ပို့ဆောင်ရေးအတွက် သင့်ယုံကြည်ရသော မိတ်ဖက်။',
            'footer_description_th' => 'พันธมิตรที่คุณไว้วางใจสำหรับการขนส่งสินค้าทั่วโลก',
            'email' => 'info@example.com',
            'open_hours_en' => 'Monday to Friday, 9:00 AM to 5:00 PM',
            'open_hours_my' => 'တနင်္လာမှ သောကြာ၊ နံနက် ၉:၀၀ မှ ညနေ ၅:၀၀ အထိ',
            'open_hours_th' => 'วันจันทร์ถึงศุกร์ เวลา 09:00–17:00 น.',
            'quotation_countries' => [
                ['name' => 'Myanmar'],
                ['name' => 'Thailand'],
                ['name' => 'Singapore'],
                ['name' => 'China'],
            ],
        ]);

        if ($settings->wasRecentlyCreated) {
            $solutions = collect($settings->header_menu)->first(fn (array $item) => ($item['type'] ?? null) === 'Group')['sub_menu'] ?? [];
            $settings->update([
                'footer_quick_links_title_en' => 'Quick Links',
                'footer_quick_links_title_my' => 'အမြန်လင့်ခ်များ',
                'footer_quick_links_title_th' => 'ลิงก์ด่วน',
                'footer_services_title_en' => 'Solutions',
                'footer_services_title_my' => 'ဝန်ဆောင်မှုများ',
                'footer_services_title_th' => 'บริการ',
                'footer_menu' => collect($settings->header_menu)
                    ->filter(fn (array $item) => ($item['type'] ?? null) !== 'Group')
                    ->map(fn (array $item) => [...$item, 'type' => 'Pages'])->values()->all(),
                'footer_services_menu' => collect($solutions)
                    ->map(fn (array $item) => [...$item, 'type' => 'Pages'])->values()->all(),
                'footer_bottom_menu' => [
                    ['label_en' => 'Support', 'label_my' => 'အကူအညီ', 'label_th' => 'ติดต่อ', 'url' => '/contact', 'type' => 'Pages', 'is_active' => true],
                    ['label_en' => 'Privacy Policy', 'label_my' => 'ကိုယ်ရေးလုံခြုံမှုမူဝါဒ', 'label_th' => 'นโยบายความเป็นส่วนตัว', 'url' => '/privacy-policy', 'type' => 'Pages', 'is_active' => true],
                ],
            ]);
        }
    }
}
