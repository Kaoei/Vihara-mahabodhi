<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Puja Bakti Umum & Dhammadesana',
            'category' => fake()->randomElement(['Puja Bakti', 'Meditasi', 'Kelas Dharma', 'Kegiatan Sosial']),
            'day_of_week' => 'Minggu',
            'event_date' => null,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'speaker' => 'YM. Bhikkhu Dhammasubho Mahathera',
            'location' => 'Dharmasala Utama Lt. 2',
            'description' => 'Kegiatan Puja Bakti bersama umat disertai pembacaan Paritta Suci, meditasi singkat, dan pembabaran Dharma.',
            'is_recurring' => true,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
