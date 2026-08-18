<?php

use Symfony\Component\Routing\RouteCollection;

// loads MailVotech's custom routing in src/MailVotech/BaseBundle/Routing/MailVotechLoader.php which
// loads all of the MailVotech bundles' routing.php files
$collection = new RouteCollection();

// loads api_platform
$apiCollection = $loader->import('.', 'api_platform');
$apiCollection->addPrefix('/api/v2/');
$collection->addCollection($apiCollection);

// loads MailVotech's custom routing in src/MailVotech/BaseBundle/Routing/MailVotechLoader.php which
// loads all of the MailVotech bundles' routing.php files. It must be the LAST one in the
// collection
$collection->addCollection($loader->import('.', 'mailvotech'));

return $collection;
