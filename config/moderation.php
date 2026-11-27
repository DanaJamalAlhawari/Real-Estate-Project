<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ollama Message Moderation
    |--------------------------------------------------------------------------
    */

    'ollama' => [

        'url' => env(
            'MODERATION_OLLAMA_URL',
            env('OLLAMA_URL', 'http://127.0.0.1:11434')
        ),

        'model' => env(
            'MODERATION_OLLAMA_MODEL',
            'qwen3.5:4b'
        ),

        'connect_timeout' => 5,

        'timeout' => 60,

        'keep_alive' => '15m',

        'temperature' => 0,

        'num_predict' => 64,

        'think' => env('MODERATION_OLLAMA_THINK', false),

    ],

];