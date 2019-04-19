<?php
return [
    'enableRegistration' => env('C5TOOLS_ENABLE_REGISTRATION', true),
    'enableConsortiumTool' => env('C5TOOLS_ENABLE_CONSORTIUM_TOOL', true),
    'userAgent' => env('C5TOOLS_USER_AGENT', 'COUNTER R5 Validation Tool/Preview (+https://www.projectcounter.org/)'),
    'clearAfter' => env('C5TOOLS_CLEAR_AFTER', '7 days')
];
