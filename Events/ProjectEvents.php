<?php

namespace Cerad\Bundle\CoreBundle\Events;

final class ProjectEvents
{
    // id or key or slug
    const FindProject       = 'CeradProjectFindProject';
    
    const FindProjectById   = 'CeradProjectFindProjectById';
    const FindProjectByKey  = 'CeradProjectFindProjectByKey';
    const FindProjectBySlug = 'CeradProjectFindProjectBySlug';
}
