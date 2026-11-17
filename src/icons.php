<?php
declare(strict_types=1);

// SVG geometry from Lucide 0.468.0; see public/assets/licenses/lucide.txt.
function lucide_icon(string $name, string $class = ''): string {
    static $icons = [
        'menu' => '<line x1="4" x2="20" y1="12" y2="12" /><line x1="4" x2="20" y1="6" y2="6" /><line x1="4" x2="20" y1="18" y2="18" />',
        'x' => '<path d="M18 6 6 18" /><path d="m6 6 12 12" />',
        'shopping-cart' => '<circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />',
        'house' => '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" /><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />',
        'grid-2x2' => '<path d="M12 3v18" /><path d="M3 12h18" /><rect x="3" y="3" width="18" height="18" rx="2" />',
        'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />',
        'circle-user-round' => '<path d="M18 20a6 6 0 0 0-12 0" /><circle cx="12" cy="10" r="4" /><circle cx="12" cy="12" r="10" />',
        'search' => '<circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />',
        'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />',
        'chevron-down' => '<path d="m6 9 6 6 6-6" />',
        'sliders-horizontal' => '<line x1="21" x2="14" y1="4" y2="4" /><line x1="10" x2="3" y1="4" y2="4" /><line x1="21" x2="12" y1="12" y2="12" /><line x1="8" x2="3" y1="12" y2="12" /><line x1="21" x2="16" y1="20" y2="20" /><line x1="12" x2="3" y1="20" y2="20" /><line x1="14" x2="14" y1="2" y2="6" /><line x1="8" x2="8" y1="10" y2="14" /><line x1="16" x2="16" y1="18" y2="22" />',
        'arrow-down-up' => '<path d="m3 16 4 4 4-4" /><path d="M7 20V4" /><path d="m21 8-4-4-4 4" /><path d="M17 4v16" />',
        'arrow-up-right' => '<path d="M7 7h10v10" /><path d="M7 17 17 7" />',
    ];
    if (!isset($icons[$name])) throw new InvalidArgumentException('Unknown Lucide icon: '.$name);
    return '<svg class="lucide '.e($class).'" aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">'.$icons[$name].'</svg>';
}
