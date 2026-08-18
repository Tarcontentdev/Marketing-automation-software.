<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechCrmBundle\Services;

interface TransportInterface
{
    public function post($uri, array $options);

    public function put($uri, array $options);

    public function get($uri, array $options);

    public function delete($uri, array $options);
}
