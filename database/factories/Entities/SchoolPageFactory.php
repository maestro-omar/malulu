<?php
/* NOT NEEDED RIGHT NOW
namespace Database\Factories\Entities;

use App\Models\Entities\School;
use App\Models\Entities\SchoolPage;
use App\Models\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/ **
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entities\SchoolPage>
 * /
class SchoolPageFactory extends Factory
{
    / **
     * The name of the factory's corresponding model.
     *
     * @var string
     * /
    protected $model = SchoolPage::class;

    / **
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * /
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->words(2, true),
            'title' => $title,
            'slug' => Str::slug($title),
            'html_content' => $this->faker->paragraphs(3, true),
            'active' => $this->faker->boolean(80), // 80% chance of being active
            'created_by' => User::factory(),
        ];
    }

    / **
     * Indicate that the school page is active.
     * /
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    / **
     * Indicate that the school page is inactive.
     * /
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
} 

*/