<?php

namespace MailVotech\ChannelBundle\Helper;

use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Event\ChannelEvent;
use MailVotech\CoreBundle\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChannelListHelper
{
    /**
     * @var array<string,string>
     */
    private array $channels = [];

    /**
     * @var array<string,string[]>
     */
    private array $featureChannels = [];

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Translator $translator,
    ) {
    }

    /**
     * Get contact channels.
     */
    public function getChannelList(): array
    {
        $channels = [];
        foreach ($this->getChannels() as $channel => $details) {
            $channelName            = isset($details['label']) ? $this->translator->trans($details['label']) : $this->getChannelLabel($channel);
            $channels[$channelName] = $channel;
        }

        return $channels;
    }

    /**
     * @param bool $listOnly
     */
    public function getFeatureChannels($features, $listOnly = false): array
    {
        $this->setupChannels();

        if (!is_array($features)) {
            $features = [$features];
        }
        $channels = [];
        foreach ($features as $feature) {
            $featureChannels = $this->featureChannels[$feature] ?? [];
            $returnChannels  = [];
            foreach ($featureChannels as $channel => $details) {
                if (!isset($details['label'])) {
                    $featureChannels[$channel]['label'] = $this->getChannelLabel($channel);
                }

                if ($listOnly) {
                    $returnChannels[$featureChannels[$channel]['label']] = $channel;
                } else {
                    $returnChannels[$channel] = $featureChannels[$channel];
                }
            }
            unset($featureChannels);
            $channels[$feature] = $returnChannels;
        }

        if (1 === count($features)) {
            $channels = $channels[$features[0]];
        }

        return $channels;
    }

    public function getChannels(): array
    {
        $this->setupChannels();

        return $this->channels;
    }

    public function getChannelLabel($channel): string
    {
        return match (true) {
            $this->translator->hasId('mailvotech.channel.'.$channel)      => $this->translator->trans('mailvotech.channel.'.$channel),
            $this->translator->hasId('mailvotech.'.$channel.'.'.$channel) => $this->translator->trans('mailvotech.'.$channel.'.'.$channel),
            default                                                   => ucfirst($channel),
        };
    }

    public function getName(): string
    {
        return 'chanel';
    }

    /**
     * Done this way to avoid a circular dependency error with LeadModel.
     */
    private function setupChannels(): void
    {
        if ([] !== $this->channels) {
            return;
        }

        $event                 = $this->dispatcher->dispatch(new ChannelEvent(), ChannelEvents::ADD_CHANNEL);
        $this->channels        = $event->getChannelConfigs();
        $this->featureChannels = $event->getFeatureChannels();
        unset($event);
    }
}
