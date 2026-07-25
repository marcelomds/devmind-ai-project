<?php

namespace App\Enums\AnalysisSource;

enum AnalysisSource: string
{
    case Manual = 'manual';
    case PullRequest = 'pull_request';
}
