<?php

declare(strict_types=1);

enum ConfirmDeletionSection: string
{
    case CHECKBOX = 'checkbox_section';
    case IMPACT = 'impact_section';
    case OPTIONS = 'options_section';
    case SUMMARY = 'summary_section';
}
