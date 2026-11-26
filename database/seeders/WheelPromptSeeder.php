<?php

namespace Database\Seeders;

use App\Models\WheelPrompt;
use App\Wheel;
use Illuminate\Database\Seeder;

/**
 * Wheel content from "SPIN The wheel Questions .docx".
 *
 * The document holds two sets of ten multiple-choice items with no labels. The first set
 * (RA / GCA) goes on the Question wheel, the second (axSpA / PsA, styled as Heading 2 in the
 * document) on the Challenge wheel. Text is as written, with formatting cleaned up only:
 * stray spaces, broken lines and options that had run together.
 *
 * Safe to re-run: rows are matched on wheel + position and overwritten, and any other
 * position on a seeded wheel is switched off.
 */
class WheelPromptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::prompts() as $wheelValue => $prompts) {
            $wheel = Wheel::from($wheelValue);

            foreach ($prompts as $position => $prompt) {
                WheelPrompt::query()->updateOrCreate(
                    ['wheel' => $wheel, 'position' => $position],
                    [
                        'body' => $prompt['body'],
                        'options' => $prompt['options'],
                        'answer' => $prompt['answer'],
                        'key_message' => $prompt['key_message'],
                        'is_active' => $prompt['is_active'] ?? true,
                    ],
                );
            }

            WheelPrompt::query()
                ->where('wheel', $wheel)
                ->whereNotIn('position', array_keys($prompts))
                ->update(['is_active' => false]);
        }
    }

    /**
     * Wheel => [segment number => prompt].
     *
     * @return array<string, array<int, array{body: string, options: list<string>, answer: string, key_message: string|null, is_active?: bool}>>
     */
    public static function prompts(): array
    {
        return [
            Wheel::Question->value => [
                1 => [
                    'body' => 'According to SELECT-COMPARE, what was the relative increase in remission rates with RINVOQ + MTX compared with adalimumab + MTX at Week 12?',
                    'options' => ['45%', '52%', '61%', '75%'],
                    'answer' => 'C',
                    'key_message' => "At Week 12, RINVOQ + MTX demonstrated 61% higher relative remission rates compared with adalimumab + MTX in MTX-IR RA patients.\n• RINVOQ + MTX: 29%\n• Adalimumab + MTX: 18%\n• Placebo + MTX: 6%",
                ],
                2 => [
                    'body' => 'Which patient population was specifically investigated in SELECT-SWITCH?',
                    'options' => [
                        'Biologic-naïve RA patients receiving their first advanced therapy.',
                        'RA patients with inadequate response or intolerance to a single prior TNFi other than adalimumab.',
                        'RA patients who failed two or more JAK inhibitors.',
                        'Newly diagnosed RA patients who had never received MTX.',
                    ],
                    'answer' => 'B',
                    'key_message' => null,
                ],
                3 => [
                    'body' => 'Which statement BEST summarizes the safety findings of RINVOQ in the UPHOLD GCC subgroup analysis?',
                    'options' => [
                        'No treatment-emergent adverse events (TEAEs) were reported.',
                        '45.7% of patients reported TEAEs, with no new safety risks identified.',
                        'More than 20% of patients discontinued treatment due to TEAEs.',
                        'The study identified new safety risks not previously reported.',
                    ],
                    'answer' => 'B',
                    'key_message' => 'The safety profile was consistent with previously reported studies, with 45.7% reporting TEAEs and 6.4% discontinuing treatment due to TEAEs. No new safety risks were identified.',
                ],
                4 => [
                    'body' => 'According to the UPHOLD GCC subgroup analysis, what percentage of patients achieved DAS28-CRP remission at 6 months with RINVOQ 15 mg once daily?',
                    'options' => [
                        '25.5% (mNRI) / 35.8% (AO)',
                        '43.9% (mNRI) / 56.8% (AO)',
                        '52.6% (mNRI) / 68.2% (AO)',
                        '81.8% (mNRI) / 85.7% (AO)',
                    ],
                    'answer' => 'B',
                    'key_message' => null,
                ],
                5 => [
                    'body' => 'According to the SELECT-GCA trial, what percentage of patients achieved sustained remission at Week 52 with RINVOQ 15 mg once daily plus a 26-week glucocorticoid taper versus placebo plus a 52-week glucocorticoid taper?',
                    'options' => ['56% vs 39%', '46% vs 29%', '43% vs 22%', '38% vs 27%'],
                    'answer' => 'B',
                    'key_message' => null,
                ],
                6 => [
                    'body' => 'In SELECT-SWITCH, what percentage of patients achieved low disease activity (DAS28-CRP ≤3.2) at Week 12 with RINVOQ + MTX vs adalimumab + MTX?',
                    'options' => ['28% vs 15%', '38% vs 27%', '43% vs 22%', '29% vs 18%'],
                    'answer' => 'C',
                    'key_message' => null,
                ],
                7 => [
                    'body' => 'How many approved immunology indications do RINVOQ, TOFA, and BARI have, respectively?',
                    'options' => [
                        'RINVOQ: 10 | TOFA: 5 | BARI: 4',
                        'RINVOQ: 12 | TOFA: 6 | BARI: 4',
                        'RINVOQ: 12 | TOFA: 5 | BARI: 4',
                        'RINVOQ: 7 | TOFA: 5 | BARI: 3',
                    ],
                    'answer' => 'C',
                    'key_message' => "• RINVOQ (12): RA, PsA, pJIA, JPsA, GCA, AS, nr-axSpA, AD, UC, CD, AA, Vitiligo\n• TOFA (5): RA, PsA, AS, JIA, UC\n• BARI (4): RA, JIA, AD, AA",
                ],
                8 => [
                    'body' => 'Which statement correctly describes the SELECT-SWITCH results at Week 12?',
                    'options' => [
                        'RINVOQ + MTX demonstrated superior ACR50 response and pain improvement vs adalimumab + MTX.',
                        'Both treatments demonstrated identical ACR50 responses.',
                        'Adalimumab + MTX demonstrated superior pain improvement.',
                        'RINVOQ + MTX demonstrated superior ACR50 response but inferior pain improvement.',
                    ],
                    'answer' => 'A',
                    'key_message' => "RINVOQ + MTX achieved:\n• ACR50: 38% vs 27%\n• Pain improvement: −3.2 vs −2.4 (LS mean change from baseline)",
                ],
                9 => [
                    'body' => 'According to the OPAL real-world study, what was the median time on treatment with RINVOQ compared with other JAK inhibitors in RA patients?',
                    'options' => ['18.5 vs 12.3 months', '28.8 vs 14.3 months', '26.6 vs 13.3 months', '24.5 vs 18.5 months'],
                    'answer' => 'B',
                    'key_message' => null,
                ],
                10 => [
                    'body' => 'According to SELECT-CHOICE, what percentage of bDMARD-IR RA patients achieved remission (DAS28-CRP <2.6) with RINVOQ + csDMARD(s) at Week 12?',
                    'options' => ['13.3%', '22%', '30%', '43%'],
                    'answer' => 'C',
                    'key_message' => 'At Week 12, 30% of patients achieved remission with RINVOQ + csDMARD(s) compared with 13.3% with abatacept + csDMARD(s).',
                ],
            ],

            Wheel::Challenge->value => [
                1 => [
                    'body' => 'In SELECT-AXIS 1, approximately what proportion of bio-naïve AS patients treated with RINVOQ achieved ASDAS-CRP Low Disease Activity (LDA <2.1) by Week 14?',
                    'options' => ['~50%', '~35%', '~20%', '~75%'],
                    'answer' => 'A',
                    'key_message' => 'In SELECT-AXIS 1, ~50% of bio-naïve AS patients treated with RINVOQ achieved ASDAS-CRP Low Disease Activity (LDA <2.1) by Week 14.',
                ],
                2 => [
                    'body' => 'For a Pioneer axSpA HCP, which RINVOQ message is MOST impactful?',
                    'options' => [
                        'Familiar MOA only',
                        'Selective use after multiple failures',
                        'Strong efficacy and safety from SELECT-AXIS in bio-naïve and nr-axSpA, with cross-indication safety profile',
                        'Step-down positioning',
                    ],
                    'answer' => 'C',
                    'key_message' => "Core characteristics of a Pioneer axSpA HCP:\n• More recent medical training; mid-career physicians\n• Most proactive in keeping up to date with the field (attending conferences, involved in clinical trials, engaging with KOLs)",
                ],
                3 => [
                    'body' => 'Which RINVOQ message best aligns with a Traditionalist axSpA HCP?',
                    'options' => [
                        'Early adoption across multiple indications',
                        'Real-world flexibility in complex patients',
                        'Rapid innovation and pipeline leadership',
                        'Robust efficacy and safety data from SELECT-AXIS-2 trials with cross-indication safety profile',
                    ],
                    'answer' => 'D',
                    'key_message' => "Core characteristics of a Traditionalist axSpA HCP:\n• More experienced physicians; practicing for many years\n• Most passive approach to keeping up to date with new developments in the field",
                ],
                4 => [
                    // Audit: the options overlap ("< 50%" also covers "~33%" and "< 15%"). Needs a medical rewrite.
                    'body' => 'In axSpA Adelphi Real World Evidence, what was the reduction of pain percentage across TNF-RINVOQ groups?',
                    'options' => ['< 50%', '~33%', '< 15%', '> 70%'],
                    'answer' => 'A',
                    'key_message' => null,
                ],
                5 => [
                    'body' => 'How is upadacitinib’s activity described in human cellular assays?',
                    'options' => [
                        'It preferentially inhibits signaling by JAK1 or JAK1/3, with functional selectivity over cytokine receptors that signal via pairs of JAK2',
                        'It equally inhibits every JAK family member and all cytokine receptors',
                        'It directly neutralizes individual cytokines outside the JAK-STAT pathway',
                        'It inhibits JAK2 alone, with no effect on JAK1 signaling',
                    ],
                    'answer' => 'A',
                    'key_message' => 'The mechanism overview describes preferential inhibition of JAK1 or JAK1/3 signaling in human cellular assays.',
                ],
                6 => [
                    'body' => 'Which statement best describes the role of JAK1 or JAK1/3 inhibition with RINVOQ in PsA and axSpA?',
                    'options' => [
                        'It reduces signaling of many cytokines involved in inflammation and associated signs and symptoms.',
                        'It blocks all cytokines signaling, including pathways independent of JAK/STAT.',
                        'It targets only cytokines with no known connection to JAK/STAT signaling.',
                        'It eliminates the underlying cause of PsA and axSpA.',
                    ],
                    'answer' => 'A',
                    'key_message' => 'JAK1 or JAK1/3 inhibition may reduce signaling of multiple cytokines involved in PsA and axSpA.',
                ],
                7 => [
                    // Audit: switched off until medical review. The document marks C (~25%) as correct,
                    // but its own key message says ~20% (option B), and "at Week." is missing the week.
                    'body' => 'In bio-naïve patients with active PsA, what proportion of SKYRIZI patients achieved Minimal Disease Activity (MDA) at Week 24?',
                    'options' => ['~10%', '~20%', '~25%', '~40%'],
                    'answer' => 'C',
                    'key_message' => 'In bio-naïve patients with active PsA, ~20% of SKYRIZI patients achieved Minimal Disease Activity (MDA) at Week.',
                    'is_active' => false,
                ],
                8 => [
                    'body' => 'At Week 152 in SELECT-PsA 1, approximately what proportion of patients treated with RINVOQ achieved DAPSA Low Disease Activity (LDA)?',
                    'options' => ['~40%', '~65–70%', '~55%', '~85%'],
                    'answer' => 'C',
                    'key_message' => 'At Week 152 in SELECT-PsA 1, ~55% of patients treated with RINVOQ achieved DAPSA Low Disease Activity (LDA).',
                ],
                9 => [
                    'body' => 'What best describes the UPJOINT interim analysis?',
                    'options' => [
                        'Randomized double-blind clinical trial',
                        'Head-to-head comparative study',
                        'Meta-analysis of PsA trials',
                        'International, prospective, real-world observational study of RINVOQ in PsA',
                    ],
                    'answer' => 'D',
                    'key_message' => 'The UPJOINT interim analysis is an international, prospective, real-world observational study of RINVOQ in PsA.',
                ],
                10 => [
                    'body' => 'In SELECT-AXIS 2, what percentage of nr-axSpA patients achieved ASDAS Low Disease Activity (LDA <2.1) at Week 14?',
                    'options' => [
                        '18% with RINVOQ vs 42% with placebo',
                        '42% with RINVOQ vs 18% with placebo',
                        '25% with RINVOQ vs 20% with placebo',
                        '60% with RINVOQ vs 30% with placebo',
                    ],
                    'answer' => 'B',
                    'key_message' => 'In SELECT-AXIS 2, 42% of nr-axSpA patients achieved ASDAS Low Disease Activity (LDA <2.1) at Week 14 with RINVOQ vs 18% with placebo.',
                ],
            ],
        ];
    }
}
