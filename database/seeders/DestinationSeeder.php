<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Starter destinations for /destinations, each with a photo. Safe on production and safe to re-run:
 *   php artisan db:seed --class=DestinationSeeder --force
 *
 * - Adds only destinations whose slug isn't in the table yet (deleted ones come back if
 *   you run it again, so run it once).
 * - Never changes a destination you have edited, except to give a photo-less one its photo.
 *
 * Photos are openly licensed (Wikimedia Commons), credited on /photo-credits. Edit the text,
 * photos and order in the admin under Website Pages → Destinations.
 */
class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $added = 0;

        foreach (self::destinations() as $i => $destination) {
            $image = $this->copyPhoto($destination['photo']);
            unset($destination['photo']);

            $existing = Destination::where('slug', $destination['slug'])->first();
            if ($existing) {
                if (blank($existing->image)) {
                    $existing->update(['image' => $image]);
                }

                continue;
            }

            Destination::create([...$destination, 'image' => $image, 'is_published' => true, 'sort_order' => $i + 1]);
            $added++;
        }

        $this->command?->info("Destinations: {$added} added.");
    }

    /**
     * Copies a bundled photo ("folder/file.jpg" under database/seeders/images) to the public disk.
     */
    private function copyPhoto(string $source): string
    {
        $path = 'destinations/'.basename($source);
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/images/{$source}")));
        }

        return $path;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function destinations(): array
    {
        return [
            [
                'name' => 'Serengeti National Park',
                'slug' => 'serengeti',
                'region' => 'Northern Circuit',
                'photo' => 'safaris/lion.jpg',
                'tagline' => 'Home of the Great Migration and endless golden plains teeming with wildlife.',
                'best_time' => 'All year — June to October for river crossings, January to March for calving',
                'places' => ['serengeti', 'serengeti-north'],
                'highlights' => ['The Great Migration of wildebeest and zebra', 'Big cats on the open plains', 'Mara River crossings in the north', 'Hot-air balloon safaris at sunrise'],
                'description' => "The Serengeti is Tanzania's most famous national park: nearly 15,000 square kilometres of grassland, acacia woodland and rocky kopjes, and a UNESCO World Heritage Site.\n\nEvery year more than a million wildebeest, with zebra and gazelle, move through the park in the Great Migration — calving in the south early in the year and crossing the Mara River in the north from around July. Lions, leopards and cheetahs follow the herds, and game viewing is excellent all year round.",
            ],
            [
                'name' => 'Ngorongoro Crater',
                'slug' => 'ngorongoro',
                'region' => 'Northern Circuit',
                'photo' => 'safaris/ngorongoro.jpg',
                'tagline' => 'A UNESCO World Heritage Site and natural wonder alive with the Big Five.',
                'best_time' => 'All year — June to October is driest',
                'places' => ['ngorongoro'],
                'highlights' => ['The world\'s largest unbroken volcanic caldera', 'One of the best places in Tanzania to see black rhino', 'Lions, elephants, buffalo and hippo on the crater floor', 'Views from the crater rim'],
                'description' => "The Ngorongoro Crater is the collapsed caldera of an ancient volcano, around 20 kilometres across with walls about 600 metres high. The crater floor holds grassland, a soda lake, swamps and forest, and a remarkable density of wildlife.\n\nA day on the crater floor can bring lions, elephants, buffalo, hippos and — with luck — the rare black rhino. The surrounding Ngorongoro Conservation Area is also home to Maasai communities.",
            ],
            [
                'name' => 'Tarangire National Park',
                'slug' => 'tarangire',
                'region' => 'Northern Circuit',
                'photo' => 'destinations/tarangire.jpg',
                'tagline' => 'Famous for ancient baobabs and one of Africa\'s largest elephant herds.',
                'best_time' => 'June to October, when wildlife gathers along the river',
                'places' => ['tarangire'],
                'highlights' => ['Large herds of elephants', 'Giant baobab trees', 'Wildlife along the Tarangire River', 'Over 500 bird species'],
                'description' => "Tarangire is known for its elephants and its giant baobab trees. In the dry season the Tarangire River is the only permanent water in the area, and herds of elephant, zebra, wildebeest and buffalo gather along its banks.\n\nIt is an easy drive from Arusha and a favourite first stop on a northern safari.",
            ],
            [
                'name' => 'Lake Manyara National Park',
                'slug' => 'lake-manyara',
                'region' => 'Northern Circuit',
                'photo' => 'destinations/lake-manyara.jpg',
                'tagline' => 'Tree-climbing lions, flamingo-filled shores and lush groundwater forest.',
                'best_time' => 'All year — wet season for birds, dry season for big game',
                'places' => ['lake-manyara'],
                'highlights' => ['Lions known for resting in trees', 'Flamingos and other water birds on the lake', 'Groundwater forest with baboons and blue monkeys', 'The Great Rift Valley escarpment'],
                'description' => "Lake Manyara lies at the foot of the Great Rift Valley escarpment. In a small area it packs groundwater forest, grassland, swamps and the shallow soda lake, which often turns pink with flamingos.\n\nThe park is famous for its tree-climbing lions and its large troops of baboons, and it fits easily into a safari between Arusha, Tarangire and the Ngorongoro Crater.",
            ],
            [
                'name' => 'Mount Kilimanjaro',
                'slug' => 'kilimanjaro',
                'region' => 'Kilimanjaro',
                'photo' => 'kilimanjaro/kilimanjaro-amboseli.jpg',
                'tagline' => 'Africa\'s highest peak — a snow-capped giant rising 5,895m above the plains.',
                'best_time' => 'January to mid-March and June to October',
                'places' => ['machame-gate', 'machame-camp', 'londorossi-gate', 'mti-mkubwa', 'shira-1', 'shira', 'lava-tower', 'barranco', 'karanga', 'barafu', 'uhuru-peak', 'mweka-camp', 'mweka-gate', 'marangu-gate', 'mandara-hut', 'horombo-hut', 'kibo-hut'],
                'highlights' => ['Uhuru Peak, the Roof of Africa, at 5,895m', 'No technical climbing needed', 'Rainforest, moorland, alpine desert and glaciers on one trek', 'Routes from 5 to 9 days'],
                'description' => "Kilimanjaro is the highest mountain in Africa and the tallest free-standing mountain in the world. Reaching Uhuru Peak needs no ropes or climbing experience — just good preparation, enough days to acclimatise and an experienced crew.\n\nOn the way up you pass through rainforest, heath and moorland and alpine desert before the icy summit. Popular routes include Machame, Lemosho and Marangu.",
            ],
            [
                'name' => 'Zanzibar Island',
                'slug' => 'zanzibar',
                'region' => 'Zanzibar & Islands',
                'photo' => 'zanzibar/nungwi.jpg',
                'tagline' => 'White-sand beaches, turquoise waters and rich Swahili culture.',
                'best_time' => 'June to October and December to February',
                'places' => ['stone-town', 'nungwi', 'kendwa', 'paje', 'jambiani', 'mnemba'],
                'highlights' => ['Stone Town, a UNESCO World Heritage Site', 'Beaches at Nungwi, Kendwa and Paje', 'Spice farm tours', 'Snorkelling and dhow sailing'],
                'description' => "Zanzibar is the perfect way to end a safari or climb. Stone Town's narrow streets, carved doors and markets tell the story of centuries of Swahili, Arab and Indian trade.\n\nAround the island, white-sand beaches and warm Indian Ocean water are ideal for swimming, snorkelling, diving and sunset dhow cruises.",
            ],
            [
                'name' => 'Arusha National Park',
                'slug' => 'arusha-national-park',
                'region' => 'Northern Circuit',
                'photo' => 'destinations/arusha-national-park.jpg',
                'tagline' => 'A hidden gem beneath Mount Meru with giraffes, buffalo and canoe safaris.',
                'best_time' => 'All year',
                'places' => ['arusha-national-park'],
                'highlights' => ['Walking safaris with an armed ranger', 'Ngurdoto Crater and the Momella Lakes', 'Black-and-white colobus monkeys', 'Views of Mount Meru and Kilimanjaro'],
                'description' => "Arusha National Park is close to Arusha town, which makes it ideal for a day trip or a first afternoon on safari. Its forests, the Ngurdoto Crater and the Momella Lakes are home to giraffes, buffalo, colobus monkeys and many birds.\n\nThe park is one of the few in the north where you can walk with an armed ranger, and on clear days Kilimanjaro rises on the horizon.",
            ],
            [
                'name' => 'Nyerere National Park (Selous)',
                'slug' => 'nyerere',
                'region' => 'Southern Circuit',
                'photo' => 'destinations/nyerere.jpg',
                'tagline' => 'Africa\'s largest protected wilderness with boat safaris and wild dogs.',
                'best_time' => 'June to October',
                'places' => ['nyerere'],
                'highlights' => ['Boat safaris on the Rufiji River', 'African wild dogs', 'Walking safaris', 'Few vehicles and a true wilderness feel'],
                'description' => "Nyerere National Park, formed from the northern part of the vast Selous Game Reserve, is one of Africa's largest protected areas. The Rufiji River and its lakes are the heart of the park.\n\nAs well as game drives you can explore by boat and on foot, looking for elephants, hippos, crocodiles, lions and the endangered African wild dog — with far fewer vehicles than in the north.",
            ],
            [
                'name' => 'Ruaha National Park',
                'slug' => 'ruaha',
                'region' => 'Southern Circuit',
                'photo' => 'navigation/southern-circuit.jpg',
                'tagline' => 'A remote, untouched wilderness with Tanzania\'s largest lion population.',
                'best_time' => 'June to October',
                'places' => ['ruaha'],
                'highlights' => ['Large lion prides', 'Big herds of elephants along the Great Ruaha River', 'Both greater and lesser kudu', 'Remote and uncrowded'],
                'description' => "Ruaha is one of Tanzania's largest and wildest national parks. Its rugged landscape of baobabs, rocky hills and the Great Ruaha River supports large lion prides, big elephant herds and predators such as leopards, cheetahs and wild dogs.\n\nFew visitors make it this far, so game drives here feel private and remote.",
            ],
            [
                'name' => 'Lake Natron',
                'slug' => 'lake-natron',
                'region' => 'Northern Circuit',
                'photo' => 'destinations/lake-natron.jpg',
                'tagline' => 'A surreal red-tinted soda lake beneath Ol Doinyo Lengai volcano.',
                'best_time' => 'June to December',
                'places' => ['lake-natron'],
                'highlights' => ['Lesser flamingo breeding grounds', 'Ol Doinyo Lengai, the Maasai "Mountain of God"', 'Walks to the Ngare Sero waterfalls', 'Maasai culture'],
                'description' => "Lake Natron is a shallow soda lake in the Rift Valley near the Kenyan border. Its salty, mineral-rich water can turn red, and it is the main breeding ground for East Africa's lesser flamingos.\n\nAbove the lake rises Ol Doinyo Lengai, an active volcano sacred to the Maasai. Lake Natron is a dramatic, off-the-beaten-track add-on to a northern safari.",
            ],
            [
                'name' => 'Mikumi National Park',
                'slug' => 'mikumi',
                'region' => 'Southern Circuit',
                'photo' => 'destinations/mikumi.jpg',
                'tagline' => 'Easily accessible plains dotted with giraffe, lion and elephant herds.',
                'best_time' => 'June to October',
                'places' => ['mikumi'],
                'highlights' => ['Open plains of the Mkata floodplain', 'Giraffe, zebra, elephant and buffalo', 'Easy road access from Dar es Salaam', 'Good for short safaris'],
                'description' => "Mikumi is the most accessible park in southern Tanzania, a few hours' drive from Dar es Salaam. The open Mkata floodplain is often compared to the Serengeti, with giraffe, zebra, wildebeest, buffalo, elephants and lions.\n\nIt is a great choice for a short safari, or to combine with Udzungwa, Ruaha or Nyerere.",
            ],
            [
                'name' => 'Mafia Island',
                'slug' => 'mafia-island',
                'region' => 'Zanzibar & Islands',
                'photo' => 'destinations/mafia-island.jpg',
                'tagline' => 'A tranquil marine park famous for whale sharks and pristine reefs.',
                'best_time' => 'October to March for whale sharks',
                'places' => ['mafia-island'],
                'highlights' => ['Swimming with whale sharks (seasonal)', 'Mafia Island Marine Park', 'Diving and snorkelling on quiet reefs', 'Relaxed island life'],
                'description' => "Mafia Island lies south of Zanzibar and is far quieter. Much of the coast is protected in the Mafia Island Marine Park, with healthy coral reefs that are excellent for diving and snorkelling.\n\nFrom around October to March, whale sharks visit the waters off the island — one of the best places in East Africa to swim alongside them.",
            ],
        ];
    }
}
