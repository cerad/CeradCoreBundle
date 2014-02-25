<?php

namespace Cerad\Bundle\CoreBundle\Events;

final class UserEvents
{
    // id or guid or fedkey
    const FindUser             = 'CeradUserFindUser';
        
    const ResetPasswordRequest = 'CeradUserResetPasswordRequest';
}
