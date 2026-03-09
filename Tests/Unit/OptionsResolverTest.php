<?php

namespace AxlCore\Tests\Unit;

use AxlCore\Axl;
use AxlCore\Conversation\Content\ImageUrl;
use AxlCore\Conversation\Content\Text;
use AxlCore\Conversation\Contexts\TextContext;
use AxlCore\Conversation\Messages\Messages;
use AxlCore\Conversation\Messages\MessagesContainer;
use AxlCore\OptionsResolver;
use AxlCore\Providers\OpenAi\OpenAiProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OptionsResolverTest extends TestCase
{
    #[DataProvider('normalizeCases')]
    public function testNormalize(array $mapped, array $accepted, array $input, array $expected): void
    {
        $resolver = new OptionsResolver($mapped, $accepted);

        $actual = $resolver->normalize($input);

        self::assertSame($expected, $actual);
    }

    public static function normalizeCases(): array
    {
        return [
            // 1) Pass-through: canonical key is already accepted
            'keeps accepted canonical keys' => [
                [],
                ['temperature'],
                ['temperature' => 0.7],
                ['temperature' => 0.7],
            ],

            // 2) Unknown keys are skipped
            'skips unknown keys' => [
                [],
                ['temperature'],
                ['nope' => true, 'temperature' => 0.5],
                ['temperature' => 0.5],
            ],

            // 3) Alias resolves to canonical
            'maps alias to canonical' => [
                ['temp' => 'temperature'],
                ['temperature'],
                ['temp' => 1.0],
                ['temperature' => 1.0],
            ],

            // 4) Alias resolves but canonical not accepted => skipped
            'skips when mapped alias resolves to non-accepted canonical' => [
                ['temp' => 'temperature'],
                ['top_p'],
                ['temp' => 1.0],
                [],
            ],

            // 5) Multiple accepted keys preserved
            'keeps multiple accepted keys' => [
                [],
                ['temperature', 'top_p'],
                ['temperature' => 0.1, 'top_p' => 0.9],
                ['temperature' => 0.1, 'top_p' => 0.9],
            ],

            // 6) Multiple aliases map into accepted keys
            'maps multiple aliases' => [
                ['temp' => 'temperature', 'p' => 'top_p'],
                ['temperature', 'top_p'],
                ['temp' => 0.8, 'p' => 0.2],
                ['temperature' => 0.8, 'top_p' => 0.2],
            ],

            // 7) Mix of canonical + alias + unknown => skips unknown
            // NOTE: collision behavior is "last write wins" by iteration order.
            'canonical and alias and unknown mixed' => [
                ['temp' => 'temperature'],
                ['temperature'],
                ['temp' => 0.6, 'temperature' => 0.4, 'junk' => 123],
                ['temperature' => 0.4],
            ],

            // 8) Values are passed through unchanged
            'passes values through unchanged' => [
                ['temp' => 'temperature'],
                ['temperature'],
                ['temp' => 'lol'],
                ['temperature' => 'lol'],
            ],

            // 9) Alias mapping does not affect other accepted canonical keys
            'alias mapping does not affect other keys' => [
                ['temp' => 'temperature'],
                ['temperature', 'max_output_tokens'],
                ['temp' => 0.3, 'max_output_tokens' => 256],
                ['temperature' => 0.3, 'max_output_tokens' => 256],
            ],

            // 10) Unmapped alias is not accepted => skipped
            'unmapped alias skipped if not accepted' => [
                ['temp' => 'temperature'],
                ['temperature'],
                ['temp' => 0.3, 'maxTokens' => 128],
                ['temperature' => 0.3],
            ],

            // 11) Empty input returns empty output
            'empty input' => [
                ['temp' => 'temperature'],
                ['temperature'],
                [],
                [],
            ],

            // 12) Mapped contains unrelated entries but does not leak keys
            // x maps to y, but y isn't accepted => x is skipped
            // raw y in input is also skipped because not accepted
            'mapped contains unrelated entries but does not leak keys' => [
                ['x' => 'y', 'temp' => 'temperature'],
                ['temperature'],
                ['x' => 1, 'temp' => 0.9, 'y' => 2],
                ['temperature' => 0.9],
            ],
        ];
    }
}