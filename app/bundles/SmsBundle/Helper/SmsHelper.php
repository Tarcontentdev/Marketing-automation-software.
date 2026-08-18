<?php

namespace MailVotech\SmsBundle\Helper;

use libphonenumber\PhoneNumberFormat;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\PhoneNumberHelper;
use MailVotech\LeadBundle\Entity\DoNotContact as DoNotContactEntity;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Model\DoNotContact;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\SmsBundle\Form\Type\ConfigType;
use MailVotech\SmsBundle\Model\SmsModel;

class SmsHelper
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected LeadModel $leadModel,
        protected PhoneNumberHelper $phoneNumberHelper,
        protected SmsModel $smsModel,
        protected IntegrationHelper $integrationHelper,
        private readonly DoNotContact $doNotContact,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function unsubscribe($number)
    {
        $number = $this->phoneNumberHelper->format($number, PhoneNumberFormat::E164);

        $args = [
            'filter' => [
                'force' => [
                    [
                        'column' => 'mobile',
                        'expr'   => 'eq',
                        'value'  => $number,
                    ],
                ],
            ],
        ];

        $leads = $this->leadRepository->getEntities($args);

        if (!empty($leads)) {
            $lead = array_shift($leads);
        } else {
            // Try to find the lead based on the given phone number
            $args['filter']['force'][0]['column'] = 'phone';

            $leads = $this->leadRepository->getEntities($args);

            if (!empty($leads)) {
                $lead = array_shift($leads);
            } else {
                return false;
            }
        }

        return $this->doNotContact->addDncForContact($lead->getId(), 'sms', DoNotContactEntity::UNSUBSCRIBED);
    }

    public function getDisableTrackableUrls(): bool
    {
        return $this->coreParametersHelper->get(ConfigType::SMS_DISABLE_TRACKABLE_URLS);
    }
}
