<?php

namespace App\Constants;

class SystemCode
{
    // Success Codes
    const SUCCESS = 'KR-200';
    const AUTH_REGISTER_SUCCESS = 'KR-AUTH-201';
    const AUTH_LOGIN_SUCCESS = 'KR-AUTH-200';
    const AUTH_LOGOUT_SUCCESS = 'KR-AUTH-204';
    const CONSULTATION_CREATED = 'KR-CONS-201';
    const CONSULTATION_RETRIEVED = 'KR-CONS-200';
    const IMAGE_UPLOAD_SUCCESS = 'KR-IMG-201';
    const AI_ANALYSIS_SUCCESS = 'KR-AI-201';

    // Error Codes
    const ERR_GENERIC = 'KR-ERR-500';
    const ERR_VALIDATION = 'KR-ERR-422';
    const ERR_UNAUTHORIZED = 'KR-ERR-401';
    const ERR_FORBIDDEN = 'KR-ERR-403';
    const ERR_NOT_FOUND = 'KR-ERR-404';
    
    // Feature Specific Errors
    const ERR_AUTH_INVALID_CREDENTIALS = 'KR-AUTH-E001';
    const ERR_AUTH_ACCOUNT_DEACTIVATED = 'KR-AUTH-E002';
    const ERR_CONSULTATION_NOT_FOUND = 'KR-CONS-E001';
    const ERR_IMAGE_LIMIT_EXCEEDED = 'KR-IMG-E001';
    const ERR_AI_ANALYSIS_FAILED = 'KR-AI-E001';
}
