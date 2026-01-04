<?php

return [
    [
        'username' => 'bayer.hudson',
        'auth_key' => 'HP187Mvq7Mmm3CTU80dLkGmni_FUH_lR',
        //password_0
        'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO',
        'password_reset_token' => 'ExzkCOaYc1L8IOBs4wdTGGbgNiG3Wz1I_1402312317',
        'created_at' => '1402312317',
        'updated_at' => '1402312317',
        'email' => 'nicole.paucek@schultz.info',
        'status' => 10, // active
    ],
    [
        'username' => 'test.inactive',
        'auth_key' => 'test_auth_key_inactive',
        'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO',
        'verification_token' => 'test_verification_token_' . time(),
        'created_at' => time(),
        'updated_at' => time(),
        'email' => 'test.inactive@example.com',
        'status' => 0, // inactive
    ],
];
