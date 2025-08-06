<?php

namespace App\Enums;

enum QuestionType: string
{
    case YesNo = 'YesNo';
    case TrueFalse = 'TrueFalse';
    case MCQSingle = 'MCQSingle';
    case MCQMultiple = 'MCQMultiple';
    case Rating1to5 = 'Rating1to5';
    case Rating1to10 = 'Rating1to10';
    case ScaleAgreeDisagree = 'ScaleAgreeDisagree';

    public function getAnswers(): array
    {
        return match($this) {
            self::YesNo => ['Yes', 'No'],
            self::TrueFalse => ['True', 'False'],
            self::Rating1to5 => ['1', '2', '3', '4', '5'],
            self::Rating1to10 => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            self::ScaleAgreeDisagree => [
                'Strongly Agree',
                'Agree', 
                'Neutral',
                'Disagree',
                'Strongly Disagree'
            ],
            default => []
        };
    }

    public function requiresCustomAnswers(): bool
    {
        return in_array($this, [self::MCQSingle, self::MCQMultiple]);
    }

    public function getLabel(): string
    {
        return match($this) {
            self::YesNo => 'Yes/No',
            self::TrueFalse => 'True/False',
            self::MCQSingle => 'Multiple Choice - Single Answer',
            self::MCQMultiple => 'Multiple Choice - Multiple Answers',
            self::Rating1to5 => 'Rating 1 to 5',
            self::Rating1to10 => 'Rating 1 to 10',
            self::ScaleAgreeDisagree => 'Agree/Disagree Scale',
        };
    }
} 