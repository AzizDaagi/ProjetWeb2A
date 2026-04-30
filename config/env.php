<?php

return [
    'huggingface_api_key' => getenv('HUGGINGFACE_API_KEY') ?: '',
    'huggingface_model' => getenv('HUGGINGFACE_MODEL') ?: 'mistralai/Mistral-7B-Instruct',
    'huggingface_timeout' => 5,
];
