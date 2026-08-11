<?php

declare(strict_types=1);

enum ConfirmDeletionSection: string
{
    case SUMMARY = 'summary_section';
    case OPTIONS = 'options_section';
    case IMPACT = 'impact_section';
    case CHECKBOX = 'checkbox_section';
}