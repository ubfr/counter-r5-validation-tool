<?php
return [
    'enableRegistration' => env('C5TOOLS_ENABLE_REGISTRATION', true),
    'enableConsortiumTool' => env('C5TOOLS_ENABLE_CONSORTIUM_TOOL', true),
    'userAgent' => env('C5TOOLS_USER_AGENT', 'COUNTER R5 Validation Tool/Preview (+https://www.projectcounter.org/)'),
    'cleanupAfterDays' => env('C5TOOLS_CLEANUP_AFTER_DAYS', '7')
];
