<?php

namespace Cerad\Bundle\CoreBundle\Events;

final class PersonEvents
{
    const FindPersonById          = 'CeradPersonFindPersonById';
    const FindPersonByGuid        = 'CeradPersonFindPersonByGuid';
    const FindPersonByFedKey      = 'CeradPersonFindPersonByFedKey';
    
    const FindOfficialsByProject  = 'CeradPersonFindOfficialsByProject';
    
    const FindPlanByProjectAndPerson     = 'CeradPersonFindPlanByProjectAndPerson';
    const FindPlanByProjectAndPersonId   = 'CeradPersonFindPlanByProjectAndPersonId';
    const FindPlanByProjectAndPersonName = 'CeradPersonFindPlanByProjectAndPersonName';
}
