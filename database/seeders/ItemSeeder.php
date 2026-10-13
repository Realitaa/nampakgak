<?php

namespace Database\Seeders;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->templates();

        $users = User::query()->where('role', 'user')->orderBy('id')->get();
        $categoryIds = Category::query()->pluck('id', 'slug');
        $categorySlugs = $categoryIds->keys()->all();
        $categoryIds = $categoryIds->values()->all();
        $locationIds = Location::query()->pluck('id')->all();

        if ($users->isEmpty() || $categoryIds === [] || $locationIds === []) {
            return;
        }

        DB::transaction(function () use ($templates, $users, $categorySlugs, $categoryIds, $locationIds): void {
            foreach ($templates as $index => $template) {
                $slug = $categorySlugs[$index % count($categorySlugs)];

                $attributes = [
                    'user_id' => $users[$template['reporter'] % $users->count()]->id,
                    'category_id' => $categoryIds[array_search($slug, $categorySlugs, true)],
                    'location_id' => $locationIds[$index % count($locationIds)],
                    'title' => $template['title'],
                ];

                Item::updateOrCreate($attributes, [
                    'type' => $template['type'],
                    'description' => $template['description'],
                    'image_path' => $template['image'] ? 'items/placeholder-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT).'.jpg' : null,
                    'status' => $template['status'],
                    'event_date' => $template['event_date'],
                ]);
            }
        });
    }

    /**
     * Realistic lost and found reports spread across categories, locations and statuses.
     *
     * @return array<int, array{reporter: int, category: string, title: string, type: ItemType, description: string, image: bool, status: ItemStatus, event_date: string}>
     */
    private function templates(): array
    {
        return [
            ['reporter' => 0, 'category' => 'id-cards-and-documents', 'title' => 'Student ID Card', 'type' => ItemType::Lost, 'description' => 'Lost after the morning lecture in Building A. The card is from the Biology study program.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-05'],
            ['reporter' => 1, 'category' => 'electronics', 'title' => 'Black Wireless Earbuds', 'type' => ItemType::Found, 'description' => 'Found in a charging case on a seat in Lecture Hall 1 after class.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-08'],
            ['reporter' => 2, 'category' => 'wallets', 'title' => 'Brown Leather Wallet', 'type' => ItemType::Lost, 'description' => 'Believed dropped near the cafeteria during the lunch break.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-09-11'],
            ['reporter' => 3, 'category' => 'bags', 'title' => 'Navy Canvas Backpack', 'type' => ItemType::Lost, 'description' => 'Left behind in the study room on the second floor of the library.', 'image' => true, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-27'],
            ['reporter' => 0, 'category' => 'keys', 'title' => 'Room Keys with Green Tag', 'type' => ItemType::Found, 'description' => 'Found near the lecture hall entrance and left at the front desk.', 'image' => true, 'status' => ItemStatus::Resolved, 'event_date' => '2026-09-14'],
            ['reporter' => 1, 'category' => 'clothing', 'title' => 'Grey Hoodie Jacket', 'type' => ItemType::Found, 'description' => 'Found draped over a chair after the laboratory practicum session.', 'image' => false, 'status' => ItemStatus::Claimed, 'event_date' => '2026-09-16'],
            ['reporter' => 2, 'category' => 'accessories', 'title' => 'Silver Wristwatch', 'type' => ItemType::Lost, 'description' => 'Noticed missing after the sports session at the campus field.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-18'],
            ['reporter' => 3, 'category' => 'books-and-stationery', 'title' => 'Calculus Textbook with Notes', 'type' => ItemType::Lost, 'description' => 'Left in the Mathematics Lecture Hall 2 after the evening class.', 'image' => false, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-21'],
            ['reporter' => 0, 'category' => 'eyewear', 'title' => 'Round Frame Glasses', 'type' => ItemType::Found, 'description' => 'Found inside a case on a table in the FMIPA library.', 'image' => true, 'status' => ItemStatus::Claimed, 'event_date' => '2026-09-20'],
            ['reporter' => 1, 'category' => 'other-items', 'title' => 'Metal Water Bottle', 'type' => ItemType::Found, 'description' => 'Found in the computer laboratory and kept for safekeeping.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-09-22'],
            ['reporter' => 2, 'category' => 'electronics', 'title' => 'White Wired Earphones', 'type' => ItemType::Lost, 'description' => 'Missing after the group assignment discussion in the student center.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-09-24'],
            ['reporter' => 3, 'category' => 'wallets', 'title' => 'Black Fold Wallet', 'type' => ItemType::Found, 'description' => 'Handed in by a student who found it in the FMIPA parking area.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-26'],
            ['reporter' => 0, 'category' => 'id-cards-and-documents', 'title' => 'KTM Card in a Blue Sleeve', 'type' => ItemType::Found, 'description' => 'Found on the floor near Building B lobby during the afternoon break.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-09-28'],
            ['reporter' => 1, 'category' => 'bags', 'title' => 'Black Tote Bag', 'type' => ItemType::Found, 'description' => 'Found unattended in the cafeteria and handed to the front desk.', 'image' => true, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-30'],
            ['reporter' => 2, 'category' => 'keys', 'title' => 'Motorcycle Keys with Red Keychain', 'type' => ItemType::Lost, 'description' => 'Last seen while locking the motorcycle in the FMIPA parking area.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-10-01'],
            ['reporter' => 3, 'category' => 'clothing', 'title' => 'Black Windbreaker Jacket', 'type' => ItemType::Lost, 'description' => 'Missing after attending a student organization event at the sports hall.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-02'],
            ['reporter' => 0, 'category' => 'accessories', 'title' => 'Brown Beaded Bracelet', 'type' => ItemType::Found, 'description' => 'Found near the washbasin outside the physics laboratory.', 'image' => true, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-24'],
            ['reporter' => 1, 'category' => 'books-and-stationery', 'title' => 'Blue Notebook and Pen Case', 'type' => ItemType::Found, 'description' => 'Found on a desk in Mathematics Lecture Hall 1 after the lecture.', 'image' => false, 'status' => ItemStatus::Claimed, 'event_date' => '2026-09-09'],
            ['reporter' => 2, 'category' => 'eyewear', 'title' => 'Prescription Glasses in a Grey Case', 'type' => ItemType::Lost, 'description' => 'Realized missing on the way back to the dormitory after the laboratory session.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-12'],
            ['reporter' => 3, 'category' => 'other-items', 'title' => 'Pink Umbrella', 'type' => ItemType::Lost, 'description' => 'Left behind near the main library entrance during the rain.', 'image' => false, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-18'],
            ['reporter' => 0, 'category' => 'electronics', 'title' => 'Power Bank 10000mAh', 'type' => ItemType::Found, 'description' => 'Found plugged into a wall socket in the computer laboratory.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-30'],
            ['reporter' => 1, 'category' => 'wallets', 'title' => 'Small Coin Purse', 'type' => ItemType::Found, 'description' => 'Found on a bench outside the FMIPA dean\'s office.', 'image' => false, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-15'],
            ['reporter' => 2, 'category' => 'id-cards-and-documents', 'title' => 'Library Membership Card', 'type' => ItemType::Lost, 'description' => 'Believed dropped somewhere between the main library and the cafeteria.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-03'],
            ['reporter' => 3, 'category' => 'bags', 'title' => 'Green Drawstring Bag', 'type' => ItemType::Lost, 'description' => 'Left behind after the sports session at the sports hall.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-10-04'],
            ['reporter' => 0, 'category' => 'keys', 'title' => 'Locker Key on a Wrist Strap', 'type' => ItemType::Found, 'description' => 'Found in the chemistry laboratory and handed to the laboratory assistant.', 'image' => false, 'status' => ItemStatus::Claimed, 'event_date' => '2026-09-19'],
            ['reporter' => 1, 'category' => 'clothing', 'title' => 'Denim Jacket', 'type' => ItemType::Found, 'description' => 'Found on the back of a chair in the student center after the event.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-21'],
            ['reporter' => 2, 'category' => 'accessories', 'title' => 'Black Baseball Cap', 'type' => ItemType::Lost, 'description' => 'Missing after the group study session in the FMIPA library.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-05'],
            ['reporter' => 3, 'category' => 'books-and-stationery', 'title' => 'Physics Laboratory Report Folder', 'type' => ItemType::Lost, 'description' => 'Realized missing after submitting a report in the physics laboratory.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-06'],
            ['reporter' => 0, 'category' => 'eyewear', 'title' => 'Sunglasses with Brown Frame', 'type' => ItemType::Found, 'description' => 'Found on a table in the campus cafeteria during the lunch break.', 'image' => true, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-12'],
            ['reporter' => 1, 'category' => 'other-items', 'title' => 'Scientific Calculator', 'type' => ItemType::Found, 'description' => 'Found in Mathematics Lecture Hall 2 after the evening class.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-10-07'],
            ['reporter' => 2, 'category' => 'electronics', 'title' => 'USB Flash Drive 32GB', 'type' => ItemType::Found, 'description' => 'Found still inserted in a computer in the computer laboratory.', 'image' => false, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-09'],
            ['reporter' => 3, 'category' => 'wallets', 'title' => 'Card Holder with Student Card', 'type' => ItemType::Lost, 'description' => 'Believed dropped near Building B lobby after the afternoon break.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-10-08'],
            ['reporter' => 0, 'category' => 'id-cards-and-documents', 'title' => 'Printed Practicum Report', 'type' => ItemType::Found, 'description' => 'Found on a seat in the mathematics lecture hall with no name written.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-09-15'],
            ['reporter' => 1, 'category' => 'bags', 'title' => 'Small Sling Bag', 'type' => ItemType::Found, 'description' => 'Found near the entrance of the FMIPA building and kept for safekeeping.', 'image' => true, 'status' => ItemStatus::Claimed, 'event_date' => '2026-09-23'],
            ['reporter' => 2, 'category' => 'keys', 'title' => 'House Keys with Blue Keychain', 'type' => ItemType::Found, 'description' => 'Found on the floor of the main campus field after the morning run.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-09'],
            ['reporter' => 3, 'category' => 'clothing', 'title' => 'Grey Sports Jersey', 'type' => ItemType::Lost, 'description' => 'Missing after the sports hall session on the weekend.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-10'],
            ['reporter' => 0, 'category' => 'accessories', 'title' => 'Gold Ring with Engraving', 'type' => ItemType::Found, 'description' => 'Found near the washbasin in the FMIPA library and kept for safekeeping.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-11'],
            ['reporter' => 1, 'category' => 'books-and-stationery', 'title' => 'Organic Chemistry Module', 'type' => ItemType::Found, 'description' => 'Found on a laboratory bench with highlighted notes inside.', 'image' => true, 'status' => ItemStatus::Resolved, 'event_date' => '2026-08-06'],
            ['reporter' => 2, 'category' => 'eyewear', 'title' => 'Blue Light Blocking Glasses', 'type' => ItemType::Found, 'description' => 'Found in a computer laboratory chair after the practicum session.', 'image' => true, 'status' => ItemStatus::Active, 'event_date' => '2026-09-27'],
            ['reporter' => 3, 'category' => 'other-items', 'title' => 'Folding Umbrella with Stripes', 'type' => ItemType::Lost, 'description' => 'Left behind near the student center entrance after the rainy afternoon.', 'image' => false, 'status' => ItemStatus::Active, 'event_date' => '2026-10-09'],
        ];
    }
}
