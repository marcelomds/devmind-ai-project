<?php

namespace App\Services\Ai;

use App\Enums\AnalyzerType\AnalyzerType;
use App\Enums\Severity\Severity;
use App\Services\Ai\Data\AnalysisResult;
use App\Services\Ai\Data\FindingData;
use App\Services\Ai\Exceptions\AiRefusalException;
use App\Services\Ai\Exceptions\AiRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiProvider implements AiProvider
{
    public function __construct(
        private readonly AnalysisPromptBuilder $promptBuilder,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function analyze(string $code, AnalyzerType $analyzer, string $language): AnalysisResult
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->promptBuilder->build($analyzer, $language)],
                        ['role' => 'user', 'content' => $code],
                    ],
                    'response_format' => $this->responseFormat(),
                ]);
        } catch (ConnectionException $e) {
            throw new AiRequestException("OpenAI request failed: {$e->getMessage()}", previous: $e);
        }

        if ($response->failed()) {
            throw new AiRequestException("OpenAI request failed with status {$response->status()}: {$response->body()}");
        }

        $message = $response->json('choices.0.message');

        if (! empty($message['refusal'])) {
            throw new AiRefusalException($message['refusal']);
        }

        $content = json_decode($message['content'] ?? '', true);

        if (! is_array($content)) {
            throw new AiRequestException('OpenAI response content was not valid JSON.');
        }

        return new AnalysisResult(
            score: (int) $content['score'],
            summary: (string) $content['summary'],
            findings: array_map(
                fn (array $finding) => new FindingData(
                    severity: Severity::from($finding['severity']),
                    category: $finding['category'],
                    title: $finding['title'],
                    message: $finding['message'],
                    suggestion: $finding['suggestion'],
                    filePath: $finding['file_path'],
                    lineStart: $finding['line_start'],
                    lineEnd: $finding['line_end'],
                ),
                $content['findings'],
            ),
        );
    }

    private function responseFormat(): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'analysis_result',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'score' => ['type' => 'integer'],
                        'summary' => ['type' => 'string'],
                        'findings' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'severity' => [
                                        'type' => 'string',
                                        'enum' => array_column(Severity::cases(), 'value'),
                                    ],
                                    'category' => ['type' => 'string'],
                                    'title' => ['type' => 'string'],
                                    'message' => ['type' => 'string'],
                                    'suggestion' => ['type' => ['string', 'null']],
                                    'file_path' => ['type' => ['string', 'null']],
                                    'line_start' => ['type' => ['integer', 'null']],
                                    'line_end' => ['type' => ['integer', 'null']],
                                ],
                                'required' => [
                                    'severity', 'category', 'title', 'message',
                                    'suggestion', 'file_path', 'line_start', 'line_end',
                                ],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                    'required' => ['score', 'summary', 'findings'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
