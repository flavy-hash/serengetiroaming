<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;

/**
 * The About Us text. By default it only fills fields that are still empty, so it never overwrites
 * what was typed in the admin — safe to run again. On the live site:
 *   php artisan db:seed --class=AboutPageSeeder
 *
 * To replace the story, "Why choose us" and community text with this standard text:
 *   ABOUT_FORCE=1 php artisan db:seed --class=AboutPageSeeder
 * and publishes the page (the banner, photos, team, reviews and FAQs are never touched).
 */
class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = AboutPage::current();

        $content = [
            'title' => 'About Us',
            'intro_heading' => 'Who Are We?',
            'intro_body' => "Serengeti Roaming African Safaris is a Tanzania-based safari company dedicated to providing memorable, personalized, and high-quality travel experiences. Our passion for wildlife, nature, adventure, and African culture inspires us to create exceptional safari journeys for travelers from around the world.\n\n"
                ."We specialize in organizing wildlife safaris, private tours, family holidays, honeymoon safaris, luxury adventures, budget-friendly safaris, cultural experiences, and tailor-made travel packages across Tanzania. Our approach is built around understanding each traveler’s expectations and transforming their travel dreams into carefully planned adventures.\n\n"
                .'Whether you are visiting Africa for the first time or returning to discover more, we are committed to making your journey comfortable, exciting, rewarding, and truly unforgettable.',
            'why_heading' => 'Why Choose Serengeti Roaming African Safaris?',
            'why_intro' => "Choosing the right safari company makes all the difference. At Serengeti Roaming African Safaris, we go beyond organizing trips. We create extraordinary experiences designed around your dreams, interests, and expectations.\n\n"
                .'What makes us your ideal safari partner?',
            'highlights' => [
                ['title' => 'Personalized Safari Experiences', 'copy' => 'Every itinerary is carefully planned to match your interests, travel dates, and budget.'],
                ['title' => 'Professional Safari Guides', 'copy' => 'Enjoy informative and engaging journeys with knowledgeable guides passionate about Tanzania.'],
                ['title' => 'Exceptional Wildlife Encounters', 'copy' => "Explore famous national parks and discover Africa's incredible wildlife."],
                ['title' => 'Comfortable Safari Transportation', 'copy' => 'Travel in suitable 4x4 safari vehicles designed for wildlife viewing and photography.'],
                ['title' => 'Quality Accommodation', 'copy' => 'Choose from a range of carefully selected lodges, tented camps, and hotels.'],
                ['title' => 'Competitive and Transparent Pricing', 'copy' => 'Enjoy memorable adventures with packages designed to offer excellent value.'],
                ['title' => 'Dedicated Customer Support', 'copy' => 'Receive personal assistance from the planning stage until your safari ends.'],
                ['title' => 'Responsible Tourism', 'copy' => 'Travel with respect for nature, wildlife, and the communities that make Tanzania special.'],
            ],
            'community_heading' => AboutPage::COMMUNITY_HEADING,
            'community_text' => AboutPage::COMMUNITY_TEXT,
        ];

        $force = filter_var(getenv('ABOUT_FORCE') ?: false, FILTER_VALIDATE_BOOLEAN);

        foreach ($content as $field => $value) {
            if ($force || blank($page->$field)) {
                $page->$field = $value;
            }
        }

        // A new page, or a forced reset, goes live straight away.
        if (! $page->exists || $force) {
            $page->is_published = true;
        }

        $page->save();
    }
}
