<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(['slug' => 'home'], [
            'status' => 'published',
            'translations' => [
                'en' => ['title' => 'West East Freight | Global logistics', 'meta_title' => 'West East Freight | Global logistics', 'meta_description' => 'Freight and logistics solutions for growing businesses.'],
                'my' => ['title' => 'West East Freight | အပြည်ပြည်ဆိုင်ရာပို့ဆောင်ရေး', 'meta_title' => 'West East Freight | အပြည်ပြည်ဆိုင်ရာပို့ဆောင်ရေး', 'meta_description' => 'သင့်လုပ်ငန်းအတွက် ယုံကြည်စိတ်ချရသော ပို့ဆောင်ရေးဝန်ဆောင်မှုများ။'],
                'th' => ['title' => 'West East Freight | โลจิสติกส์ระดับโลก', 'meta_title' => 'West East Freight | โลจิสติกส์ระดับโลก', 'meta_description' => 'โซลูชันการขนส่งและโลจิสติกส์สำหรับธุรกิจที่กำลังเติบโต'],
            ],
            'blocks' => [
                ['type' => 'hero', 'translations' => [
                    'en' => ['eyebrow' => 'Reliable freight forwarding', 'heading' => 'Moving business forward, worldwide.', 'body' => '<p>Flexible air, sea and land freight solutions with a team that stays close to every shipment.</p>', 'button_label' => 'Request a quote', 'button_url' => '#contact'],
                    'my' => ['eyebrow' => 'ယုံကြည်စိတ်ချရသော ပို့ဆောင်ရေး', 'heading' => 'သင့်လုပ်ငန်းကို ကမ္ဘာတစ်ဝှမ်း ချိတ်ဆက်ပို့ဆောင်ပေးပါသည်။', 'body' => '<p>လေကြောင်း၊ ရေကြောင်းနှင့် ကုန်းလမ်းပို့ဆောင်ရေးကို shipment တိုင်းအတွက် အနီးကပ်စောင့်ရှောက်ပေးပါသည်။</p>', 'button_label' => 'ဈေးနှုန်းမေးရန်', 'button_url' => '#contact'],
                    'th' => ['eyebrow' => 'บริการขนส่งที่ไว้วางใจได้', 'heading' => 'ขับเคลื่อนธุรกิจของคุณไปทั่วโลก', 'body' => '<p>บริการขนส่งทางอากาศ ทางเรือ และทางบกที่ยืดหยุ่น พร้อมทีมงานที่ดูแลทุกการจัดส่งอย่างใกล้ชิด</p>', 'button_label' => 'ขอใบเสนอราคา', 'button_url' => '#contact'],
                ]],
                ['type' => 'services', 'translations' => [
                    'en' => ['eyebrow' => 'Our services', 'heading' => 'Logistics built around your cargo', 'body' => '<p>Choose the right route and service level for your timeline.</p>', 'items' => "Air Freight | Fast, dependable delivery for time-sensitive shipments.\nSea Freight | Cost-effective FCL and LCL solutions across global ports.\nLand Transport | Flexible overland movement and local delivery."],
                    'my' => ['eyebrow' => 'ဝန်ဆောင်မှုများ', 'heading' => 'သင့်ကုန်ပစ္စည်းအတွက် သင့်တော်သော ပို့ဆောင်မှု', 'body' => '<p>အချိန်နှင့်ဘတ်ဂျက်အလိုက် အကောင်းဆုံးလမ်းကြောင်းကို ရွေးချယ်နိုင်ပါသည်။</p>', 'items' => "လေကြောင်းပို့ဆောင်ရေး | အချိန်အရေးကြီးသော ကုန်ပစ္စည်းများအတွက် မြန်ဆန်သောဝန်ဆောင်မှု။\nရေကြောင်းပို့ဆောင်ရေး | FCL နှင့် LCL ကို ကမ္ဘာတစ်ဝှမ်း ဆိပ်ကမ်းများသို့။\nကုန်းလမ်းပို့ဆောင်ရေး | နယ်စပ်ဖြတ်ကျော်နှင့် ပြည်တွင်းပို့ဆောင်မှုများ။"],
                    'th' => ['eyebrow' => 'บริการของเรา', 'heading' => 'โลจิสติกส์ที่ออกแบบเพื่อสินค้าของคุณ', 'body' => '<p>เลือกเส้นทางและระดับบริการที่เหมาะกับเวลาและงบประมาณของคุณ</p>', 'items' => "ขนส่งทางอากาศ | รวดเร็วและเชื่อถือได้สำหรับสินค้าที่ต้องการความเร่งด่วน\nขนส่งทางเรือ | บริการ FCL และ LCL ที่คุ้มค่าผ่านท่าเรือทั่วโลก\nขนส่งทางบก | การขนส่งข้ามพรมแดนและจัดส่งภายในประเทศที่ยืดหยุ่น"],
                ]],
                ['type' => 'stats', 'translations' => [
                    'en' => ['items' => "24/7 | Shipment support\nGlobal | Partner network\n3 modes | Air, sea & land"],
                    'my' => ['items' => "24/7 | Shipment support\nGlobal | Partner network\n3 မျိုး | လေ၊ ရေ၊ ကုန်းလမ်း"],
                    'th' => ['items' => "24/7 | ดูแลการจัดส่ง\nทั่วโลก | เครือข่ายพันธมิตร\n3 รูปแบบ | อากาศ เรือ และบก"],
                ]],
                ['type' => 'cta', 'translations' => [
                    'en' => ['heading' => 'Ready to move your next shipment?', 'body' => '<p>Tell us where it is going and our team will prepare a route for you.</p>', 'button_label' => 'Talk to our team', 'button_url' => 'mailto:info@example.com'],
                    'my' => ['heading' => 'သင့်နောက်တစ်ခေါက်ပို့ဆောင်မှုအတွက် အဆင်သင့်ဖြစ်ပြီလား?', 'body' => '<p>ပို့ဆောင်လိုသည့်နေရာကို ပြောပြပါ၊ သင့်အတွက် လမ်းကြောင်းစီစဉ်ပေးပါမည်။</p>', 'button_label' => 'ကျွန်ုပ်တို့ကိုဆက်သွယ်ရန်', 'button_url' => 'mailto:info@example.com'],
                    'th' => ['heading' => 'พร้อมส่งสินค้าครั้งต่อไปของคุณแล้วหรือยัง?', 'body' => '<p>บอกเราว่าคุณต้องการส่งสินค้าไปที่ใด แล้วทีมงานของเราจะวางแผนเส้นทางให้คุณ</p>', 'button_label' => 'ติดต่อทีมงานของเรา', 'button_url' => 'mailto:info@example.com'],
                ]],
            ],
        ]);
    }
}
