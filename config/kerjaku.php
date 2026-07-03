<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Display Timezone
    |--------------------------------------------------------------------------
    |
    | All timestamps are stored in UTC. This is the only place conversion to
    | local time happens — Asia/Makassar (WITA) for NTB. See PRD.md §5.
    |
    */

    'display_timezone' => env('KERJAKU_DISPLAY_TIMEZONE', 'Asia/Makassar'),

];
