<?php

namespace Database\Seeders;

use App\Models\WheelPrompt;
use App\Wheel;
use Illuminate\Database\Seeder;

/**
 * Fills both wheels with eight segments each.
 *
 * The copy below is a placeholder: the Figma file only has lorem ipsum. Replace each body
 * with the approved question or challenge text before the event (re-running is safe,
 * rows are matched on wheel + position).
 */
class WheelPromptSeeder extends Seeder
{
    /**
     * Segments per wheel, as drawn in the design.
     */
    public const SEGMENTS = 8;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Wheel::cases() as $wheel) {
            foreach (range(1, self::SEGMENTS) as $position) {
                WheelPrompt::query()->firstOrCreate(
                    ['wheel' => $wheel, 'position' => $position],
                    ['body' => "Placeholder {$wheel->label()} {$position}: replace with the approved text.", 'is_active' => true],
                );
            }
        }
    }
}
